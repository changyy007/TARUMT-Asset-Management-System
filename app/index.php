<?php include 'header.php'; ?>

<style>
    .stat-card {
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.1) !important;
    }
</style>

<div class="container-fluid px-0 px-md-2">
    <!-- Welcome Header: Campus Facility Dashboard -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Campus Facility Dashboard</h1>
            <p class="text-muted small mb-0">Monthly asset performance, incident tickets, and maintenance metrics.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill small">
                <i class="bi bi-calendar3 me-1 text-primary"></i>
                <span id="current-month-display">Current Month</span>
            </span>
        </div>
    </div>

    <!-- Permission Warning Alert (Hidden by default, shown only if Firestore rules error occurs) -->
    <div id="permission-alert" class="alert alert-warning border-warning-subtle shadow-sm rounded-4 p-3 mb-4 d-none">
        <div class="d-flex align-items-start">
            <i class="bi bi-shield-exclamation text-warning fs-3 me-3"></i>
            <div>
                <h6 class="fw-bold mb-1">Firestore Access Notice</h6>
                <p class="small mb-1 text-secondary">
                    Your Firebase Firestore database rules may be set to test mode expiration. Please update your Firebase Console rules to enable full access.
                </p>
                <a href="#rulesModal" data-bs-toggle="modal" class="small fw-bold text-decoration-none">Click here to view recommended Firebase Security Rules</a>
            </div>
        </div>
    </div>

    <!-- 4-Card Summary Grid (All clickable to their respective modules) -->
    <div class="row g-3 g-md-4 mb-4">
        <!-- 1. Total Assets Added This Month -> Clickable to inventory.php -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card stat-card border-0 shadow-sm rounded-4 h-100 p-3 bg-white position-relative overflow-hidden" 
                 onclick="window.location.href='inventory.php'" title="Click to view Campus Inventory">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-secondary text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Assets Added</div>
                        <h2 class="display-6 fw-bold text-dark my-1" id="stat-assets-month">
                            <span class="spinner-border spinner-border-sm text-primary"></span>
                        </h2>
                        <div class="text-muted small d-flex align-items-center">
                            <i class="bi bi-arrow-right-circle text-primary me-1"></i> View Inventory
                        </div>
                    </div>
                    <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-4">
                        <i class="bi bi-box-seam-fill fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Total Reports This Month -> Clickable to maintenance.php -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card stat-card border-0 shadow-sm rounded-4 h-100 p-3 bg-white position-relative overflow-hidden" 
                 onclick="window.location.href='maintenance.php'" title="Click to view Maintenance Tickets">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-secondary text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Reports Filed</div>
                        <h2 class="display-6 fw-bold text-dark my-1" id="stat-reports-month">
                            <span class="spinner-border spinner-border-sm text-warning"></span>
                        </h2>
                        <div class="text-muted small d-flex align-items-center">
                            <i class="bi bi-arrow-right-circle text-warning me-1"></i> View Maintenance
                        </div>
                    </div>
                    <div class="p-3 bg-warning bg-opacity-10 text-warning rounded-4">
                        <i class="bi bi-flag-fill fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Pending Maintenance -> Clickable to maintenance.php -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card stat-card border-0 shadow-sm rounded-4 h-100 p-3 bg-white position-relative overflow-hidden" 
                 onclick="window.location.href='maintenance.php'" title="Click to view Pending Repairs">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-secondary text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Pending Repairs</div>
                        <h2 class="display-6 fw-bold text-danger my-1" id="stat-pending-maint">
                            <span class="spinner-border spinner-border-sm text-danger"></span>
                        </h2>
                        <div class="text-muted small d-flex align-items-center">
                            <i class="bi bi-arrow-right-circle text-danger me-1"></i> Awaiting Action
                        </div>
                    </div>
                    <div class="p-3 bg-danger bg-opacity-10 text-danger rounded-4">
                        <i class="bi bi-tools fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Maintenance Cost -> Clickable to financial.php -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card stat-card border-0 shadow-sm rounded-4 h-100 p-3 bg-white position-relative overflow-hidden" 
                 onclick="window.location.href='financial.php'" title="Click to view Financial Reports">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-secondary text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Maintenance Cost</div>
                        <h2 class="h3 fw-bold text-success my-1" id="stat-cost-month">
                            <span class="spinner-border spinner-border-sm text-success"></span>
                        </h2>
                        <div class="small mt-1">
                            <span class="badge bg-success-subtle text-success-emphasis rounded-pill" id="stat-cost-total">
                                Total: RM 0.00
                            </span>
                        </div>
                    </div>
                    <div class="p-3 bg-success bg-opacity-10 text-success rounded-4">
                        <i class="bi bi-cash-stack fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recommended Firebase Security Rules Modal -->
<div class="modal fade" id="rulesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center">
                    <i class="bi bi-shield-check text-success me-2"></i>Firebase Security Rules
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="small text-muted mb-2">
                    Paste these rules into your <strong>Firebase Console &gt; Firestore Database &gt; Rules</strong>:
                </p>
                <pre class="bg-dark text-light p-3 rounded-3 small font-monospace" style="user-select: all;">rules_version = '2';
service cloud.firestore {
  match /databases/{database}/documents {
    match /assets/{assetId} {
      allow read: if true;
      allow write: if request.auth != null;
    }
    match /tickets/{ticketId} {
      allow create: if true;
      allow read, update, delete: if request.auth != null;
    }
    match /users/{userId} {
      allow read, write: if true;
    }
    match /utilities/{utilityId} {
      allow read: if true;
      allow write: if request.auth != null;
    }
  }
}</pre>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script type="module">
    import { db } from './js/config.js';
    import { collection, getDocs } from "https://www.gstatic.com/firebasejs/10.10.0/firebase-firestore.js";

    const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    const now = new Date();
    const currentYear = now.getFullYear();
    const currentMonth = now.getMonth();
    const monthStart = new Date(currentYear, currentMonth, 1);
    const monthEnd = new Date(currentYear, currentMonth + 1, 0, 23, 59, 59);

    const monthDisplay = document.getElementById('current-month-display');
    if (monthDisplay) {
        monthDisplay.innerText = `${monthNames[currentMonth]} ${currentYear}`;
    }

    async function loadDashboardStats() {
        const statAssets = document.getElementById('stat-assets-month');
        const statReports = document.getElementById('stat-reports-month');
        const statPending = document.getElementById('stat-pending-maint');
        const statCostMonth = document.getElementById('stat-cost-month');
        const statCostTotal = document.getElementById('stat-cost-total');
        const permAlert = document.getElementById('permission-alert');

        try {
            // 1. Fetch Assets
            let assetsAddedCount = 0;
            const assetSnap = await getDocs(collection(db, "assets"));
            assetSnap.forEach(docSnap => {
                const data = docSnap.data();
                let addedDate = null;
                if (data.createdAt && data.createdAt.toDate) {
                    addedDate = data.createdAt.toDate();
                } else if (data.broughtInDate) {
                    const parsed = new Date(data.broughtInDate);
                    if (!isNaN(parsed.getTime())) addedDate = parsed;
                }
                if (addedDate && addedDate >= monthStart && addedDate <= monthEnd) {
                    assetsAddedCount++;
                }
            });
            if (statAssets) statAssets.innerText = assetsAddedCount;

            // 2. Fetch Tickets
            let reportsMonthCount = 0;
            let pendingCount = 0;
            let costMonthSum = 0;
            let costTotalSum = 0;

            const ticketSnap = await getDocs(collection(db, "tickets"));
            ticketSnap.forEach(docSnap => {
                const ticket = docSnap.data();
                const isCompleted = ticket.status === "Completed" || ticket.status === "Resolved";
                const isPending = !isCompleted;

                if (isPending) {
                    pendingCount++;
                }

                let createdDate = null;
                if (ticket.createdAt && ticket.createdAt.toDate) {
                    createdDate = ticket.createdAt.toDate();
                } else if (ticket.reportDate) {
                    const p = new Date(ticket.reportDate);
                    if (!isNaN(p.getTime())) createdDate = p;
                }

                if (createdDate && createdDate >= monthStart && createdDate <= monthEnd) {
                    reportsMonthCount++;
                }

                if (isCompleted) {
                    const costVal = parseFloat(ticket.cost) || 0;
                    costTotalSum += costVal;

                    let completedDate = null;
                    if (ticket.completedAt && ticket.completedAt.toDate) {
                        completedDate = ticket.completedAt.toDate();
                    } else if (createdDate) {
                        completedDate = createdDate;
                    }

                    if (completedDate && completedDate >= monthStart && completedDate <= monthEnd) {
                        costMonthSum += costVal;
                    }
                }
            });

            if (statReports) statReports.innerText = reportsMonthCount;
            if (statPending) statPending.innerText = pendingCount;
            if (statCostMonth) statCostMonth.innerText = `RM ${costMonthSum.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
            if (statCostTotal) statCostTotal.innerText = `Total: RM ${costTotalSum.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

        } catch (err) {
            console.error("Dashboard stats error:", err);
            if (err.message && err.message.includes("permission")) {
                if (permAlert) permAlert.classList.remove('d-none');
            }
            if (statAssets) statAssets.innerText = "0";
            if (statReports) statReports.innerText = "0";
            if (statPending) statPending.innerText = "0";
            if (statCostMonth) statCostMonth.innerText = "RM 0.00";
            if (statCostTotal) statCostTotal.innerText = "Total: RM 0.00";
        }
    }

    loadDashboardStats();
</script>