<?php include 'header.php'; ?>

<div class="container-fluid px-0 px-md-2">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Fault Reports Dashboard</h1>
            <p class="text-muted small mb-0">Real-time status of reported broken assets across campus.</p>
        </div>
    </div>

    <!-- Tickets Table Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
            <h6 class="fw-bold mb-0 text-dark d-flex align-items-center">
                <i class="bi bi-card-checklist text-primary me-2"></i>Reported Issue Tickets
            </h6>
            <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2" id="ticket-count-badge">
                Loading...
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-nowrap">
                    <thead class="table-light text-secondary text-uppercase small">
                        <tr>
                            <th class="ps-4 py-3">Asset Name</th>
                            <th class="py-3">Location</th>
                            <th class="py-3">Reporter Email</th>
                            <th class="py-3" style="min-width: 250px;">Issue Description</th>
                            <th class="pe-4 py-3 text-end">Status</th>
                        </tr>
                    </thead>
                    <tbody id="tickets-table-body">
                        <tr>
                            <td colspan="5" class="p-5 text-center text-muted">
                                <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                Loading maintenance tickets...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script type="module">
    import { db, getCurrentUserRole } from './js/config.js';
    import { collection, getDocs, doc, getDoc } from "https://www.gstatic.com/firebasejs/10.10.0/firebase-firestore.js";

    async function loadTickets() {
        const tbody = document.getElementById('tickets-table-body');
        const countBadge = document.getElementById('ticket-count-badge');

        try {
            const querySnapshot = await getDocs(collection(db, "tickets"));
            
            if (querySnapshot.empty) {
                tbody.innerHTML = `<tr><td colspan="5" class="p-5 text-center text-muted"><i class="bi bi-check2-circle fs-3 d-block text-success mb-2"></i>No issues reported yet.</td></tr>`;
                if (countBadge) countBadge.innerText = "0 Tickets";
                return;
            }

            if (countBadge) countBadge.innerText = `${querySnapshot.size} Ticket${querySnapshot.size > 1 ? 's' : ''}`;
            tbody.innerHTML = '';

            for (const ticketDoc of querySnapshot.docs) {
                const ticket = ticketDoc.data();
                
                // Fetch referenced asset name safely
                let assetName = "Unknown Asset";
                let assetLocation = "N/A";

                if (ticket.assetId) {
                    try {
                        const assetSnap = await getDoc(doc(db, "assets", ticket.assetId));
                        if (assetSnap.exists()) {
                            const assetData = assetSnap.data();
                            assetName = assetData.name || "Unnamed Asset";
                            const bld = assetData.building || (assetData.location && typeof assetData.location === 'object' ? assetData.location.building : '') || '';
                            const rm = assetData.room || (assetData.location && typeof assetData.location === 'object' ? assetData.location.room : '') || '';
                            assetLocation = (bld || rm) ? `${bld} - ${rm}` : (typeof assetData.location === 'string' ? assetData.location : 'N/A');
                        }
                    } catch (e) {
                        console.warn("Could not fetch asset details:", e);
                    }
                }

                const status = ticket.status || 'Pending';
                let badgeClass = "bg-warning-subtle text-warning-emphasis";
                if (status === 'In Progress') badgeClass = "bg-info-subtle text-info-emphasis";
                if (status === 'Completed' || status === 'Resolved') badgeClass = "bg-success-subtle text-success-emphasis";

                tbody.innerHTML += `
                    <tr>
                        <td class="ps-4 py-3 fw-bold text-dark">
                            ${ticket.assetId ? `<a href="info.php?id=${ticket.assetId}" class="text-decoration-none text-dark hover-primary">${assetName}</a>` : assetName}
                        </td>
                        <td class="py-3 text-secondary"><i class="bi bi-geo-alt me-1 text-muted"></i>${assetLocation}</td>
                        <td class="py-3 text-secondary">${ticket.reporterEmail || 'Anonymous'}</td>
                        <td class="py-3 text-dark text-wrap" style="max-width: 350px;">${ticket.description || 'No description'}</td>
                        <td class="pe-4 py-3 text-end">
                            <span class="badge ${badgeClass} rounded-pill px-3 py-2 fw-semibold">
                                ${status}
                            </span>
                        </td>
                    </tr>
                `;
            }
        } catch (err) {
            console.error("Error loading tickets:", err);
            tbody.innerHTML = `<tr><td colspan="5" class="p-4 text-center text-danger">Error loading tickets: ${err.message}</td></tr>`;
        }
    }

    loadTickets();
</script>