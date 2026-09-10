<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Issue - TARUMT</title>
    <!-- Bootstrap 5.3.0 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f0f2f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .report-card {
            max-width: 480px;
            width: 100%;
        }
    </style>
</head>
<body>

    <div class="card report-card border-0 shadow-lg rounded-4 overflow-hidden my-3">
        <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0 text-center">
            <div class="d-inline-flex align-items-center justify-content-center bg-danger bg-opacity-10 text-danger rounded-circle mb-3" style="width: 52px; height: 52px;">
                <i class="bi bi-exclamation-triangle-fill fs-4"></i>
            </div>
            <h2 class="h4 fw-bold text-dark mb-1">Report Broken Asset</h2>
            <p class="text-muted small mb-0" id="asset-target-label">Submit a maintenance ticket for campus facilities</p>
        </div>
        
        <div class="card-body p-4">
            <form id="report-form">
                <div class="mb-3" id="asset-id-group">
                    <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Asset Tag ID</label>
                    <input type="text" id="report-asset-id" class="form-control bg-light" placeholder="e.g. ASSET-00123">
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Your TARUMT Email <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-envelope text-muted"></i></span>
                        <input type="email" id="reporter-email" required placeholder="student@tarumt.edu.my" class="form-control">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Describe the Fault / Damage <span class="text-danger">*</span></label>
                    <textarea id="issue-desc" required rows="4" placeholder="E.g., Air conditioner blowing warm air, strange rattling noise..." class="form-control"></textarea>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" id="btn-submit-report" class="btn btn-danger rounded-pill py-2 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-send-fill"></i>
                        <span>Submit Fault Report</span>
                    </button>
                    <a href="inventory.php" class="btn btn-outline-secondary rounded-pill py-2 small">
                        Cancel & Return
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script type="module">
        import { db } from './js/config.js';
        import { collection, addDoc, serverTimestamp } from "https://www.gstatic.com/firebasejs/10.10.0/firebase-firestore.js";

        const urlParams = new URLSearchParams(window.location.search);
        const assetId = urlParams.get('id');
        const assetInput = document.getElementById('report-asset-id');
        const targetLabel = document.getElementById('asset-target-label');

        if (assetId) {
            assetInput.value = assetId;
            assetInput.readOnly = true;
            targetLabel.innerHTML = `Reporting for Asset Tag: <strong class="text-primary font-monospace">${assetId}</strong>`;
        }

        document.getElementById('report-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = document.getElementById('btn-submit-report');
            submitBtn.disabled = true;
            submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Submitting...`;

            const finalAssetId = assetInput.value.trim() || 'General / Unspecified';

            try {
                await addDoc(collection(db, "tickets"), {
                    assetId: finalAssetId,
                    reporterEmail: document.getElementById('reporter-email').value.trim(),
                    description: document.getElementById('issue-desc').value.trim(),
                    status: 'Pending',
                    createdAt: serverTimestamp()
                });

                alert("Report submitted successfully! Maintenance team has been notified.");
                if (assetId) {
                    window.location.href = `info.php?id=${assetId}`;
                } else {
                    window.location.href = "index.php";
                }
            } catch (err) {
                console.error("Error submitting report:", err);
                alert("Failed to submit report: " + err.message);
                submitBtn.disabled = false;
                submitBtn.innerHTML = `<i class="bi bi-send-fill"></i> <span>Submit Fault Report</span>`;
            }
        });
    </script>
</body>
</html>