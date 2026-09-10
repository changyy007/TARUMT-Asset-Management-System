<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Details - TARUMT</title>
    <!-- Bootstrap 5.3.0 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        body {
            background-color: #f0f2f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .info-card {
            max-width: 480px;
            width: 100%;
        }
        .asset-img-preview {
            max-height: 220px;
            object-fit: cover;
            width: 100%;
            border-radius: 0.75rem;
        }
    </style>
</head>
<body>

    <div class="card info-card border-0 shadow-lg rounded-4 overflow-hidden my-3">
        <!-- Card Header -->
        <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-2 text-center">
            <span class="badge bg-danger-subtle text-danger text-uppercase fw-bold rounded-pill px-3 py-2 small">
                TARUMT Asset Information
            </span>
            <h2 id="item-name" class="h4 fw-bold text-dark mt-3 mb-1">Loading Item...</h2>
            <p id="item-id" class="small text-muted font-monospace mb-0"></p>
        </div>

        <div class="card-body p-4">
            <!-- Asset Image / QR Code Container -->
            <div class="text-center mb-4 p-3 bg-light rounded-4 border">
                <img id="item-photo" src="picture/images.jpg" alt="Asset Photo" class="asset-img-preview shadow-sm mb-3">
                <div id="qrcode" class="d-flex justify-content-center"></div>
            </div>

            <!-- Details List -->
            <div class="list-group list-group-flush rounded-3 border mb-4">
                <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                    <span class="text-muted small fw-semibold"><i class="bi bi-tag me-2"></i>Category</span>
                    <span id="item-cat" class="fw-bold text-dark small">-</span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                    <span class="text-muted small fw-semibold"><i class="bi bi-geo-alt me-2"></i>Location</span>
                    <span id="item-loc" class="fw-bold text-dark small">-</span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                    <span class="text-muted small fw-semibold"><i class="bi bi-calendar me-2"></i>Date Added</span>
                    <span id="item-date" class="fw-bold text-dark small">-</span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                    <span class="text-muted small fw-semibold"><i class="bi bi-shield-check me-2"></i>Status</span>
                    <span id="item-status" class="badge bg-success-subtle text-success rounded-pill px-2 py-1">Good Condition</span>
                </div>
                <div class="list-group-item py-2 px-3">
                    <div class="text-muted small fw-semibold mb-1"><i class="bi bi-chat-text me-2"></i>Description:</div>
                    <p id="item-desc" class="text-secondary small mb-0">N/A</p>
                </div>
            </div>

            <!-- Actions -->
            <div class="d-grid gap-2">
                <a id="report-link" href="#" class="btn btn-danger rounded-pill py-2 fw-bold d-flex align-items-center justify-content-center gap-2 shadow-sm">
                    <i class="bi bi-exclamation-triangle-fill"></i> Report Broken Item
                </a>
                <a href="inventory.php" class="btn btn-outline-secondary rounded-pill py-2 small d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-arrow-left"></i> Back to Inventory
                </a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script type="module">
        import { db } from './js/config.js';
        import { doc, getDoc } from "https://www.gstatic.com/firebasejs/10.10.0/firebase-firestore.js";

        const urlParams = new URLSearchParams(window.location.search);
        const assetId = urlParams.get('id');

        if (!assetId) {
            document.querySelector('.card-body').innerHTML = `
                <div class="text-center py-4 text-danger">
                    <i class="bi bi-exclamation-circle display-4 mb-3 d-block"></i>
                    <h5 class="fw-bold">Invalid Asset Link</h5>
                    <p class="text-muted small mb-3">No asset ID was provided in the URL query parameters.</p>
                    <a href="inventory.php" class="btn btn-primary rounded-pill">Go to Inventory</a>
                </div>`;
        } else {
            try {
                const docSnap = await getDoc(doc(db, "assets", assetId));
                if (docSnap.exists()) {
                    const data = docSnap.data();
                    document.getElementById('item-name').innerText = data.name || 'Unnamed Asset';
                    document.getElementById('item-id').innerText = `Tag ID: ${assetId}`;
                    document.getElementById('item-cat').innerText = data.category || 'N/A';

                    const bld = data.building || (data.location && typeof data.location === 'object' ? data.location.building : '') || '';
                    const rm = data.room || (data.location && typeof data.location === 'object' ? data.location.room : '') || '';
                    document.getElementById('item-loc').innerText = (bld || rm) ? `Building ${bld}, Room ${rm}` : (typeof data.location === 'string' ? data.location : 'N/A');

                    const dateStr = data.broughtInDate || (data.createdAt?.toDate ? data.createdAt.toDate().toLocaleDateString() : 'N/A');
                    document.getElementById('item-date').innerText = dateStr;
                    document.getElementById('item-desc').innerText = data.description || "N/A";
                    
                    if (data.status) {
                        document.getElementById('item-status').innerText = data.status;
                    }

                    if (data.photoUrl) {
                        const img = document.getElementById('item-photo');
                        img.src = data.photoUrl;
                    }

                    // Direct link to Report Page
                    document.getElementById('report-link').href = `report.php?id=${assetId}`;

                    // Generate QR Code dynamically pointing to this page
                    const qrContainer = document.getElementById("qrcode");
                    if (qrContainer) {
                        new QRCode(qrContainer, { text: window.location.href, width: 128, height: 128 });
                    }
                } else {
                    document.querySelector('.card-body').innerHTML = `
                        <div class="text-center py-4 text-danger">
                            <i class="bi bi-search display-4 mb-3 d-block text-secondary"></i>
                            <h5 class="fw-bold text-dark">Asset Not Found</h5>
                            <p class="text-muted small mb-3">No registered asset found matching ID: ${assetId}</p>
                            <a href="inventory.php" class="btn btn-primary rounded-pill">Back to Inventory</a>
                        </div>`;
                }
            } catch (err) {
                console.error("Error fetching asset info:", err);
                document.querySelector('.card-body').innerHTML = `
                    <div class="text-center py-4 text-danger">
                        <i class="bi bi-exclamation-triangle display-4 mb-3 d-block"></i>
                        <h5 class="fw-bold">Error Loading Asset</h5>
                        <p class="text-muted small">${err.message}</p>
                    </div>`;
            }
        }
    </script>
</body>
</html>