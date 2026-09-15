<?php include 'header.php'; ?>

<style>
    .report-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        cursor: pointer;
    }
    .report-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.1) !important;
    }
</style>

<div class="container-fluid px-0 px-md-2">
    <!-- Top Action Bar -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1 d-flex align-items-center">
                <span>Maintenance Queue</span>
                <span id="history-badge" class="badge bg-secondary text-white fs-6 ms-2 d-none">
                    <i class="bi bi-clock-history me-1"></i>History Records
                </span>
            </h1>
            <p class="text-muted small mb-0">Prioritized incident tickets, AI severity categorization, and work order completion.</p>
        </div>

        <div class="d-flex align-items-center gap-2 w-100 w-sm-auto justify-content-sm-end">
            <!-- View Toggle: Active Queue vs History -->
            <button id="btn-toggle-history" onclick="toggleHistoryView()" class="btn btn-outline-secondary btn-sm px-3 rounded-pill d-flex align-items-center justify-content-center gap-2 flex-fill flex-sm-grow-0">
                <i class="bi bi-clock-history"></i>
                <span id="toggle-history-text">View History</span>
            </button>
        </div>
    </div>

    <!-- History Mode Notification Banner -->
    <div id="history-banner" class="alert alert-secondary d-none align-items-center justify-content-between p-3 rounded-4 mb-4 shadow-sm" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-check2-circle fs-4 me-3 text-success"></i>
            <div>
                <strong>Viewing Maintenance History:</strong> These are previously resolved and completed maintenance jobs with documented repair costs.
            </div>
        </div>
        <button type="button" class="btn btn-sm btn-dark rounded-pill ms-3 text-nowrap" onclick="toggleHistoryView()">
            Back to Active Queue
        </button>
    </div>

    <!-- Priority Filter Pill Navs (for Active Queue) -->
    <div class="border-bottom pb-2 mb-4" id="filter-nav-container">
        <ul class="nav nav-pills flex-nowrap overflow-x-auto gap-2 py-1" style="scrollbar-width: none;">
            <li class="nav-item">
                <button onclick="filterPriority('ALL')" class="prio-tab nav-link active rounded-pill px-3 py-2 fw-semibold" data-prio="ALL">
                    All Reports
                </button>
            </li>
            <li class="nav-item">
                <button onclick="filterPriority('CRITICAL')" class="prio-tab nav-link rounded-pill px-3 py-2 fw-semibold text-danger" data-prio="CRITICAL">
                    <i class="bi bi-exclamation-octagon-fill me-1"></i> Critical
                </button>
            </li>
            <li class="nav-item">
                <button onclick="filterPriority('MODERATE')" class="prio-tab nav-link rounded-pill px-3 py-2 fw-semibold text-warning" data-prio="MODERATE">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Moderate
                </button>
            </li>
            <li class="nav-item">
                <button onclick="filterPriority('MINOR')" class="prio-tab nav-link rounded-pill px-3 py-2 fw-semibold text-primary" data-prio="MINOR">
                    <i class="bi bi-info-circle-fill me-1"></i> Minor
                </button>
            </li>
        </ul>
    </div>

    <!-- Reports Grid -->
    <div id="maintenance-grid" class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 g-3 g-md-4">
        <div class="col-12 text-center py-5 text-muted">
            <div class="spinner-border text-primary mb-2" role="status"></div>
            <div>Loading maintenance tickets...</div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script type="module">
    import { db } from './js/config.js';
    import { collection, getDocs, doc, getDoc } from "https://www.gstatic.com/firebasejs/10.10.0/firebase-firestore.js";

    let allTickets = [];
    let assetCache = {};
    let isViewingHistory = false;
    let selectedPriority = "ALL";

    /* ====================================================
       1. TOGGLE HISTORY & PRIORITY FILTER
       ==================================================== */
    window.toggleHistoryView = () => {
        isViewingHistory = !isViewingHistory;
        const btnText = document.getElementById('toggle-history-text');
        const toggleBtn = document.getElementById('btn-toggle-history');
        const historyBadge = document.getElementById('history-badge');
        const historyBanner = document.getElementById('history-banner');
        const filterContainer = document.getElementById('filter-nav-container');

        if (isViewingHistory) {
            btnText.innerText = "Active Queue";
            toggleBtn.className = "btn btn-dark btn-sm px-3 rounded-pill d-flex align-items-center justify-content-center gap-2 flex-fill flex-sm-grow-0 fw-semibold";
            historyBadge.classList.remove('d-none');
            historyBanner.classList.remove('d-none');
            historyBanner.classList.add('d-flex');
            if (filterContainer) filterContainer.classList.add('d-none');
        } else {
            btnText.innerText = "View History";
            toggleBtn.className = "btn btn-outline-secondary btn-sm px-3 rounded-pill d-flex align-items-center justify-content-center gap-2 flex-fill flex-sm-grow-0";
            historyBadge.classList.add('d-none');
            historyBanner.classList.add('d-none');
            historyBanner.classList.remove('d-flex');
            if (filterContainer) filterContainer.classList.remove('d-none');
        }
        renderTickets();
    };

    window.filterPriority = (prio) => {
        selectedPriority = prio;
        document.querySelectorAll('.prio-tab').forEach(tab => {
            if (tab.dataset.prio === prio) {
                tab.classList.add('active', 'bg-primary', 'text-white');
            } else {
                tab.classList.remove('active', 'bg-primary', 'text-white');
            }
        });
        renderTickets();
    };

    /* ====================================================
       2. FETCH & RENDER TICKETS
       ==================================================== */
    async function loadTickets() {
        const grid = document.getElementById('maintenance-grid');
        grid.innerHTML = `
            <div class="col-12 text-center py-5 text-muted">
                <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                Loading facility reports...
            </div>`;

        try {
            allTickets = [];
            const snapshot = await getDocs(collection(db, "tickets"));
            
            for (const docSnap of snapshot.docs) {
                const t = docSnap.data();
                const ticketId = docSnap.id;

                let assetInfo = { name: "Campus Facility", location: "TARUMT Campus" };
                if (t.assetId) {
                    if (assetCache[t.assetId]) {
                        assetInfo = assetCache[t.assetId];
                    } else {
                        try {
                            const aSnap = await getDoc(doc(db, "assets", t.assetId));
                            if (aSnap.exists()) {
                                const ad = aSnap.data();
                                const bld = ad.building || ad.location?.building || '';
                                const rm = ad.room || ad.location?.room || '';
                                assetInfo = {
                                    name: ad.name || "Unnamed Asset",
                                    location: (bld || rm) ? `${bld} - ${rm}` : 'N/A'
                                };
                                assetCache[t.assetId] = assetInfo;
                            }
                        } catch (ae) {}
                    }
                }

                allTickets.push({
                    id: ticketId,
                    ...t,
                    assetName: assetInfo.name,
                    assetLocation: assetInfo.location
                });
            }

            renderTickets();

        } catch (err) {
            console.error("Error loading tickets:", err);
            grid.innerHTML = `
                <div class="col-12 text-center py-5 text-danger">
                    <i class="bi bi-exclamation-triangle-fill fs-3 mb-2 d-block"></i>
                    Error loading maintenance tickets: ${err.message}
                </div>`;
        }
    }

    function renderTickets() {
        const grid = document.getElementById('maintenance-grid');
        grid.innerHTML = "";

        const filtered = allTickets.filter(t => {
            const isCompleted = t.status === "Completed" || t.status === "Resolved";
            if (isViewingHistory) {
                return isCompleted;
            } else {
                if (isCompleted) return false;
                if (selectedPriority === "ALL") return true;
                return (t.priority || "MODERATE").toUpperCase() === selectedPriority;
            }
        });

        if (!isViewingHistory) {
            const prioWeight = { "CRITICAL": 3, "MODERATE": 2, "MINOR": 1 };
            filtered.sort((a, b) => {
                const wA = prioWeight[(a.priority || "MODERATE").toUpperCase()] || 0;
                const wB = prioWeight[(b.priority || "MODERATE").toUpperCase()] || 0;
                return wB - wA;
            });
        } else {
            filtered.sort((a, b) => {
                const dateA = a.completedAt?.toDate ? a.completedAt.toDate() : new Date(0);
                const dateB = b.completedAt?.toDate ? b.completedAt.toDate() : new Date(0);
                return dateB - dateA;
            });
        }

        if (filtered.length === 0) {
            grid.innerHTML = `
                <div class="col-12 text-center py-5">
                    <div class="p-4 bg-white rounded-4 shadow-sm border d-inline-block" style="max-width: 420px;">
                        <i class="bi ${isViewingHistory ? 'bi-archive text-secondary' : 'bi-check2-all text-success'} display-4 mb-3 d-block"></i>
                        <h5 class="fw-bold text-dark mb-1">No ${isViewingHistory ? 'History Records' : 'Active Tickets'}</h5>
                        <p class="text-muted small mb-0">${isViewingHistory ? 'No completed maintenance records found.' : 'All campus facility tickets have been resolved!'}</p>
                    </div>
                </div>`;
            return;
        }

        filtered.forEach(ticket => {
            const photo = ticket.photoUrl || 'picture/images.jpg';
            const priority = (ticket.priority || "MODERATE").toUpperCase();

            let badgeClass = "bg-warning text-dark";
            if (priority === "CRITICAL") badgeClass = "bg-danger text-white";
            if (priority === "MINOR") badgeClass = "bg-primary text-white";

            let repDateStr = "Recent";
            if (ticket.createdAt && ticket.createdAt.toDate) {
                repDateStr = ticket.createdAt.toDate().toLocaleDateString();
            } else if (ticket.reportDate) {
                repDateStr = ticket.reportDate;
            }

            const cardHTML = `
            <div class="col">
                <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden position-relative report-card"
                     onclick="window.location.href='report_detail.php?id=${encodeURIComponent(ticket.id)}'">
                    
                    <!-- Defect Photo from Reporter -->
                    <div class="ratio ratio-4x3 bg-light border-bottom position-relative overflow-hidden">
                        <img src="${photo}" alt="" class="img-fluid object-fit-cover w-100 h-100" onerror="this.src='picture/images.jpg'">
                        
                        <!-- Severity Badge at Top-Left of Photo -->
                        <span class="position-absolute top-0 start-0 m-2 badge ${badgeClass} rounded-pill shadow-sm px-2 py-1 small fw-bold">
                            ${priority}
                        </span>

                        <!-- Location Badge at Top-Right -->
                        <span class="position-absolute top-0 end-0 m-2 badge bg-dark bg-opacity-75 rounded-pill shadow-sm px-2 py-1 small">
                            <i class="bi bi-geo-alt me-1"></i>${ticket.assetLocation}
                        </span>
                    </div>

                    <!-- Details Area below picture -->
                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                        <div>
                            <h6 class="card-title fw-bold text-dark text-truncate mb-1" title="${ticket.assetName}">
                                ${ticket.assetName}
                            </h6>
                            <span class="text-muted small font-monospace">${ticket.assetId || 'Unspecified'}</span>
                        </div>

                        <!-- Card Footer with Report Date in Bottom-Right Corner -->
                        <div class="d-flex align-items-center justify-content-between mt-3 pt-2 border-top">
                            <span class="badge ${ticket.status === 'Completed' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning-emphasis'} rounded-pill small">
                                ${ticket.status || 'Pending'}
                            </span>
                            <span class="text-muted small">
                                <i class="bi bi-calendar3 me-1"></i>${repDateStr}
                            </span>
                        </div>
                    </div>
                </div>
            </div>`;

            grid.innerHTML += cardHTML;
        });
    }

    // Initial Load
    loadTickets();
</script>