<?php include 'header.php'; ?>

<div class="container-fluid px-0 px-md-2" style="max-width: 760px;">
    <!-- Top Action Bar -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <a href="maintenance.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3 d-flex align-items-center gap-1">
            <i class="bi bi-arrow-left"></i>
            <span>Back to Maintenance</span>
        </a>
        <span id="ticket-priority-badge" class="badge rounded-pill px-3 py-2 fs-6">
            Loading...
        </span>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
        <!-- Defect Photo from Reporter -->
        <div class="ratio ratio-16x9 bg-light border-bottom position-relative">
            <img id="detail-fault-photo" src="picture/images.jpg" alt="" class="img-fluid object-fit-cover w-100 h-100" onerror="this.src='picture/images.jpg'">
        </div>

        <div class="card-body p-4">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
                <div>
                    <h3 class="h4 fw-bold text-dark mb-1" id="detail-asset-name">Loading Asset...</h3>
                    <div class="text-muted small font-monospace" id="detail-asset-id">Tag ID: -</div>
                </div>
                <span id="detail-status-badge" class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-3 py-2 small">
                    Pending
                </span>
            </div>

            <!-- Details List -->
            <div class="list-group list-group-flush rounded-3 border mb-4">
                <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                    <span class="text-muted small fw-semibold"><i class="bi bi-geo-alt me-2 text-danger"></i>Location</span>
                    <span id="detail-location" class="fw-bold text-dark small">-</span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                    <span class="text-muted small fw-semibold"><i class="bi bi-calendar3 me-2 text-primary"></i>Date Reported</span>
                    <span id="detail-report-date" class="fw-bold text-dark small">-</span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                    <span class="text-muted small fw-semibold"><i class="bi bi-person me-2 text-secondary"></i>Reporter Email</span>
                    <span id="detail-reporter" class="small text-secondary">-</span>
                </div>
                <!-- History info: Complete Date -->
                <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3 d-none" id="detail-complete-date-row">
                    <span class="text-muted small fw-semibold"><i class="bi bi-check2-all me-2 text-success"></i>Fixed Date</span>
                    <span id="detail-complete-date" class="fw-bold text-success small">-</span>
                </div>
                <!-- History info: Cost -->
                <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3 d-none" id="detail-cost-row">
                    <span class="text-muted small fw-semibold"><i class="bi bi-cash me-2 text-success"></i>Maintenance Cost</span>
                    <span id="detail-cost" class="fw-bold text-success small">RM 0.00</span>
                </div>
                <!-- Description -->
                <div class="list-group-item py-3 px-3">
                    <div class="text-muted small fw-semibold mb-1"><i class="bi bi-chat-left-text me-2"></i>Fault Description</div>
                    <p id="detail-description" class="text-dark small mb-0 fs-6">-</p>
                </div>
            </div>

            <!-- Complete Section: For Staff on Pending Tickets -->
            <div id="complete-action-card" class="d-none card bg-light border-0 p-3 rounded-4">
                <h6 class="fw-bold text-dark mb-2 d-flex align-items-center">
                    <i class="bi bi-wrench-adjustable-circle text-success me-2"></i>Complete Maintenance Work Order
                </h6>
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary mb-1">Enter Maintenance Cost (RM)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white">RM</span>
                        <input type="number" id="detail-cost-input" step="0.01" min="0" value="0.00" class="form-control" placeholder="0.00">
                    </div>
                    <div class="form-text small text-muted">Enter actual repair expense (can be 0 if fixed internally).</div>
                </div>
                <button type="button" id="btn-submit-complete" class="btn btn-success w-100 rounded-pill py-2 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>Complete Maintenance</span>
                </button>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script type="module">
    import { db, getCurrentUserRole } from './js/config.js';
    import { doc, getDoc, updateDoc, serverTimestamp } from "https://www.gstatic.com/firebasejs/10.10.0/firebase-firestore.js";

    const urlParams = new URLSearchParams(window.location.search);
    const ticketId = urlParams.get('id');
    let currentTicket = null;

    if (!ticketId) {
        alert("No maintenance ticket specified.");
        window.location.href = "maintenance.php";
    } else {
        loadTicketDetail();
    }

    async function loadTicketDetail() {
        try {
            const tDoc = await getDoc(doc(db, "tickets", ticketId));
            if (!tDoc.exists()) {
                alert("Ticket not found.");
                window.location.href = "maintenance.php";
                return;
            }

            currentTicket = { id: tDoc.id, ...tDoc.data() };

            // Fetch referenced asset name & location
            let assetName = "Campus Facility";
            let assetLocation = "TARUMT Campus";
            if (currentTicket.assetId) {
                try {
                    const aSnap = await getDoc(doc(db, "assets", currentTicket.assetId));
                    if (aSnap.exists()) {
                        const ad = aSnap.data();
                        assetName = ad.name || "Unnamed Asset";
                        const bld = ad.building || ad.location?.building || '';
                        const rm = ad.room || ad.location?.room || '';
                        assetLocation = (bld || rm) ? `${bld} - ${rm}` : 'N/A';
                    }
                } catch (ae) {}
            }

            // Populate Elements
            document.getElementById('detail-fault-photo').src = currentTicket.photoUrl || 'picture/images.jpg';
            document.getElementById('detail-asset-name').innerText = assetName;
            document.getElementById('detail-asset-id').innerText = `Tag ID: ${currentTicket.assetId || 'Unspecified'}`;
            document.getElementById('detail-location').innerText = assetLocation;
            document.getElementById('detail-reporter').innerText = currentTicket.reporterEmail || 'Anonymous';
            document.getElementById('detail-description').innerText = currentTicket.description || 'No description provided.';

            // Priority Badge
            const prio = (currentTicket.priority || "MODERATE").toUpperCase();
            const pBadge = document.getElementById('ticket-priority-badge');
            pBadge.innerText = prio;
            if (prio === "CRITICAL") pBadge.className = "badge bg-danger rounded-pill px-3 py-2 fs-6 fw-bold";
            else if (prio === "MINOR") pBadge.className = "badge bg-primary rounded-pill px-3 py-2 fs-6 fw-bold";
            else pBadge.className = "badge bg-warning text-dark rounded-pill px-3 py-2 fs-6 fw-bold";

            // Status Badge
            const isCompleted = currentTicket.status === "Completed" || currentTicket.status === "Resolved";
            const sBadge = document.getElementById('detail-status-badge');
            sBadge.innerText = isCompleted ? "Completed" : "Pending";
            sBadge.className = isCompleted ? "badge bg-success-subtle text-success rounded-pill px-3 py-2" : "badge bg-warning-subtle text-warning-emphasis rounded-pill px-3 py-2";

            // Report Date
            let repDate = currentTicket.reportDate || (currentTicket.createdAt?.toDate ? currentTicket.createdAt.toDate().toLocaleDateString() : "Recent");
            document.getElementById('detail-report-date').innerText = repDate;

            // History fields vs Complete Action
            const compDateRow = document.getElementById('detail-complete-date-row');
            const costRow = document.getElementById('detail-cost-row');
            const completeCard = document.getElementById('complete-action-card');

            if (isCompleted) {
                compDateRow.classList.remove('d-none');
                costRow.classList.remove('d-none');
                completeCard.classList.add('d-none');

                let cDate = "Completed";
                if (currentTicket.completedAt && currentTicket.completedAt.toDate) {
                    cDate = currentTicket.completedAt.toDate().toLocaleDateString();
                }
                document.getElementById('detail-complete-date').innerText = cDate;
                const costVal = parseFloat(currentTicket.cost) || 0;
                document.getElementById('detail-cost').innerText = `RM ${costVal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
            } else {
                compDateRow.classList.add('d-none');
                costRow.classList.add('d-none');

                // Check staff role for complete action
                const role = await getCurrentUserRole();
                if (role === 'staff') {
                    completeCard.classList.remove('d-none');
                } else {
                    completeCard.classList.add('d-none');
                }
            }

        } catch (err) {
            console.error("Error loading detail:", err);
            alert("Error: " + err.message);
        }
    }

    // Complete Maintenance Action
    document.getElementById('btn-submit-complete')?.addEventListener('click', async () => {
        if (!currentTicket) return;

        const costVal = parseFloat(document.getElementById('detail-cost-input').value) || 0;
        const btn = document.getElementById('btn-submit-complete');
        btn.disabled = true;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Updating...`;

        try {
            // Update ticket
            await updateDoc(doc(db, "tickets", currentTicket.id), {
                status: "Completed",
                cost: costVal,
                completedAt: serverTimestamp(),
                isArchived: true
            });

            // Reset asset status to Good Condition
            if (currentTicket.assetId) {
                try {
                    await updateDoc(doc(db, "assets", currentTicket.assetId), {
                        status: "Good Condition"
                    });
                } catch (ae) {}
            }

            alert("Maintenance work order completed successfully! Moved to History.");
            window.location.href = "maintenance.php";

        } catch (err) {
            alert("Failed to complete ticket: " + err.message);
            btn.disabled = false;
            btn.innerHTML = `<i class="bi bi-check-circle-fill"></i> <span>Complete Maintenance</span>`;
        }
    });
</script>

