<?php include 'header.php'; ?>

<!-- HTML5 QR Code Library for Asset Scanning -->
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

<style>
    .asset-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        cursor: pointer;
    }
    .asset-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.1) !important;
    }
    .maintenance-dot {
        width: 14px;
        height: 14px;
        background-color: #dc3545;
        border: 2px solid #ffffff;
        border-radius: 50%;
        position: absolute;
        top: 10px;
        left: 10px;
        z-index: 10;
        box-shadow: 0 0 8px rgba(220, 53, 69, 0.8);
        animation: pulse-red 1.8s infinite;
        pointer-events: none;
    }
    @keyframes pulse-red {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
        70% { transform: scale(1.15); box-shadow: 0 0 0 6px rgba(220, 53, 69, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
    }
</style>

<div class="container-fluid px-0 px-md-2">
    <!-- Top Action Bar -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1 d-flex align-items-center">
                <span>Campus Inventory</span>
                <span id="header-archive-badge" class="badge bg-warning text-dark fs-6 ms-2 d-none">
                    <i class="bi bi-archive me-1"></i>Archived Items View
                </span>
            </h1>
            <p class="text-muted small mb-0">Browse university assets, check operational status, and manage inventory.</p>
        </div>

        <!-- Action Buttons: Always visible to staff & authorized users -->
        <div id="staff-action-container" class="d-flex align-items-center gap-2 w-100 w-sm-auto justify-content-sm-end">
            <!-- Archived Toggle Button -->
            <button id="toggle-archive-btn" onclick="toggleArchiveView()" class="btn btn-outline-secondary btn-sm px-3 rounded-pill d-flex align-items-center justify-content-center gap-2 flex-fill flex-sm-grow-0">
                <i class="bi bi-archive"></i>
                <span id="archive-btn-text">Archived Assets</span>
            </button>
            
            <!-- Add Item Button -->
            <button id="btn-open-add-modal" type="button" onclick="startAddAssetWorkflow()" class="btn btn-primary btn-sm px-3 rounded-pill d-flex align-items-center justify-content-center gap-2 fw-semibold flex-fill flex-sm-grow-0" data-bs-toggle="modal" data-bs-target="#addAssetModal">
                <i class="bi bi-plus-lg"></i>
                <span>Add Asset</span>
            </button>
        </div>
    </div>

    <!-- Archive Banner Notification (visible when in archive mode) -->
    <div id="archive-banner" class="alert alert-warning d-none align-items-center justify-content-between p-3 rounded-4 mb-4 shadow-sm" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-archive-fill fs-4 me-3 text-warning"></i>
            <div>
                <strong>Viewing Archived Assets:</strong> These items have been soft-deleted. You can restore them back to active inventory anytime.
            </div>
        </div>
        <button type="button" class="btn btn-sm btn-dark rounded-pill ms-3 text-nowrap" onclick="toggleArchiveView()">
            Back to Active Assets
        </button>
    </div>

    <!-- Category Filter Tabs: All Items default, Furniture, M&E -->
    <div class="border-bottom pb-2 mb-4">
        <ul class="nav nav-pills flex-nowrap overflow-x-auto gap-2 py-1" id="category-tabs" style="scrollbar-width: none;">
            <li class="nav-item">
                <button onclick="filterCategory('All')" class="cat-tab nav-link active rounded-pill px-4 py-2 fw-semibold" data-cat="All">
                    <i class="bi bi-grid-fill me-1"></i> All Items
                </button>
            </li>
            <li class="nav-item">
                <button onclick="filterCategory('Furniture')" class="cat-tab nav-link rounded-pill px-4 py-2 fw-semibold text-secondary" data-cat="Furniture">
                    <i class="bi bi-lamp me-1"></i> Furniture
                </button>
            </li>
            <li class="nav-item">
                <button onclick="filterCategory('M&E')" class="cat-tab nav-link rounded-pill px-4 py-2 fw-semibold text-secondary" data-cat="M&E">
                    <i class="bi bi-cpu me-1"></i> M&E
                </button>
            </li>
        </ul>
    </div>

    <!-- Items Grid -->
    <div id="inventory-grid" class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 g-3 g-md-4">
        <div class="col-12 text-center py-5 text-muted">
            <div class="spinner-border text-primary mb-2" role="status"></div>
            <div>Loading assets...</div>
        </div>
    </div>
</div>

<!-- Add Asset Modal -->
<div class="modal fade" id="addAssetModal" tabindex="-1" aria-labelledby="addAssetModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 shadow border-0">
            <div class="modal-header border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center" id="addAssetModalLabel">
                    <i class="bi bi-box-seam text-primary me-2"></i>Register New Asset
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="stopScanner()"></button>
            </div>

            <div class="modal-body p-4">
                <!-- STEP 1: SCAN QR CODE -->
                <div id="modal-step-1">
                    <div class="text-center mb-3">
                        <h6 class="fw-bold mb-1">Step 1: Scan Physical QR Code Tag</h6>
                        <p class="text-muted small mb-0">Point your smartphone camera at the item's QR tag, or enter the ID manually.</p>
                    </div>

                    <div id="qr-reader-container" class="position-relative mb-3">
                        <div id="qr-reader" class="w-100 bg-dark rounded-3 overflow-hidden border position-relative" style="min-height: 250px;"></div>
                        <div id="qr-spinner" class="position-absolute top-50 start-50 translate-middle text-center text-white d-none">
                            <div class="spinner-border spinner-border-sm text-light mb-2"></div>
                            <div class="small">Starting camera...</div>
                        </div>
                    </div>

                    <div class="card bg-light border-0 p-3 rounded-3 mb-3">
                        <label class="form-label small fw-semibold text-secondary mb-1">
                            <i class="bi bi-keyboard me-1"></i>Or Enter Tag ID Manually:
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-qr-code"></i></span>
                            <input type="text" id="manual-qr-input" class="form-control" placeholder="e.g. ASSET-00123">
                            <button class="btn btn-outline-primary" type="button" id="btn-use-manual-qr">Use ID</button>
                        </div>
                    </div>

                    <button type="button" class="btn btn-light border w-100 rounded-pill py-2" data-bs-dismiss="modal" onclick="stopScanner()">
                        <i class="bi bi-x-circle me-1"></i> Cancel Add Asset
                    </button>
                </div>

                <!-- STEP 2: ASSET DETAILS FORM -->
                <div id="modal-step-2" class="d-none">
                    <div class="d-flex align-items-center justify-content-between p-2 mb-3 bg-light rounded-3 border">
                        <div>
                            <div class="small text-muted">Item ID / QR Tag:</div>
                            <span class="fw-bold font-monospace text-primary" id="scanned-tag-display">Tag ID: -</span>
                        </div>
                        <button type="button" id="btn-rescan" class="btn btn-outline-secondary btn-sm rounded-pill" onclick="startAddAssetWorkflow()">
                            <i class="bi bi-arrow-repeat me-1"></i>Re-scan
                        </button>
                    </div>

                    <form id="new-asset-form">
                        <input type="hidden" id="asset-qr-id">

                        <!-- Photo Upload to Firebase Storage -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-uppercase text-secondary mb-1">Photo of Item <span class="text-danger">*</span></label>
                            <input type="file" id="asset-photo" accept="image/*" capture="environment" class="form-control" required>
                            <div class="form-text small">Photo will be stored securely in Firebase Storage.</div>
                        </div>

                        <!-- Asset Name -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-uppercase text-secondary mb-1">Asset Name <span class="text-danger">*</span></label>
                            <input type="text" id="asset-name" required placeholder="e.g. Ergonomic Office Chair" class="form-control">
                        </div>

                        <!-- Category -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-uppercase text-secondary mb-1">Category <span class="text-danger">*</span></label>
                            <select id="asset-category" class="form-select" required>
                                <option value="Furniture">Furniture</option>
                                <option value="M&E">M&E</option>
                            </select>
                        </div>

                        <!-- Location Dropdowns from Utility -->
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold text-uppercase text-secondary mb-1">Building Block <span class="text-danger">*</span></label>
                                <select id="asset-building" class="form-select" required onchange="onBuildingChange()">
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold text-uppercase text-secondary mb-1">Class / Room <span class="text-danger">*</span></label>
                                <select id="asset-room" class="form-select" required>
                                </select>
                            </div>
                        </div>

                        <!-- Price -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-uppercase text-secondary mb-1">Price (RM) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">RM</span>
                                <input type="number" id="asset-price" step="1.00" min="0" required placeholder="0.00" class="form-control">
                            </div>
                        </div>

                        <!-- Brought In Date -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-uppercase text-secondary mb-1">Brought In Date <span class="text-danger">*</span></label>
                            <input type="date" id="asset-brought-date" class="form-control" required>
                        </div>

                        <!-- Description (Optional) -->
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-uppercase text-secondary mb-1">Description (Optional)</label>
                            <textarea id="asset-desc" rows="2" placeholder="e.g. Model number, brand, warranty notes..." class="form-control"></textarea>
                        </div>

                        <!-- Submit / Cancel -->
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-light border flex-grow-1 rounded-pill" data-bs-dismiss="modal" onclick="stopScanner()">
                                Cancel
                            </button>
                            <button type="submit" id="btn-save-asset" class="btn btn-primary flex-grow-1 rounded-pill fw-bold">
                                Save Asset
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script type="module">
    import { db, storage } from './js/config.js';
    import { doc, setDoc, updateDoc, collection, getDocs, serverTimestamp } from "https://www.gstatic.com/firebasejs/10.10.0/firebase-firestore.js";
    import { ref, uploadBytes, getDownloadURL } from "https://www.gstatic.com/firebasejs/10.10.0/firebase-storage.js";

    /* ====================================================
       1. STATE & UTILITY DATA
       ==================================================== */
    let currentCategory = "All";
    let isViewingArchived = false;
    let allAssets = [];
    let reportedAssetIds = new Set();
    let campusFacilities = {
        "Block A": ["Room A-101", "Room A-102", "Room A-201", "Lab A-1"],
        "Block B": ["Room B-101", "Room B-201", "Lecture Theatre B"],
        "Block C": ["Room C-101", "Room C-102", "Workshop C-3"],
        "Block DK": ["DK-1", "DK-2", "DK-3", "DK-Auditorium"]
    };

    async function loadCampusFacilities() {
        try {
            const snap = await getDocs(collection(db, "utilities"));
            if (!snap.empty) {
                campusFacilities = {};
                snap.forEach(d => {
                    const blockData = d.data();
                    const blockName = blockData.name || d.id;
                    campusFacilities[blockName] = blockData.rooms || [];
                });
            }
        } catch (e) {
            console.warn("Using default facilities:", e);
        }
        populateBuildingDropdown();
    }

    function populateBuildingDropdown() {
        const bldSelect = document.getElementById('asset-building');
        if (!bldSelect) return;
        bldSelect.innerHTML = "";
        const blocks = Object.keys(campusFacilities);
        blocks.forEach(b => {
            const opt = document.createElement('option');
            opt.value = b;
            opt.innerText = b;
            bldSelect.appendChild(opt);
        });
        onBuildingChange();
    }

    window.onBuildingChange = function() {
        const bldSelect = document.getElementById('asset-building');
        const rmSelect = document.getElementById('asset-room');
        if (!bldSelect || !rmSelect) return;
        const selectedBld = bldSelect.value;
        const rooms = campusFacilities[selectedBld] || [];
        rmSelect.innerHTML = "";
        if (rooms.length === 0) {
            rmSelect.innerHTML = `<option value="General">General / Main</option>`;
        } else {
            rooms.forEach(r => {
                const opt = document.createElement('option');
                opt.value = r;
                opt.innerText = r;
                rmSelect.appendChild(opt);
            });
        }
    };

    /* ====================================================
       2. INVENTORY DISPLAY & FILTERING
       ==================================================== */
    window.filterCategory = (cat) => {
        currentCategory = cat;
        document.querySelectorAll('.cat-tab').forEach(tab => {
            if (tab.dataset.cat === cat) {
                tab.classList.add('active', 'text-white');
                tab.classList.remove('text-secondary');
            } else {
                tab.classList.remove('active', 'text-white');
                tab.classList.add('text-secondary');
            }
        });
        renderItems();
    };

    window.toggleArchiveView = () => {
        isViewingArchived = !isViewingArchived;
        const btnText = document.getElementById('archive-btn-text');
        const toggleBtn = document.getElementById('toggle-archive-btn');
        const headerBadge = document.getElementById('header-archive-badge');
        const archiveBanner = document.getElementById('archive-banner');

        if (isViewingArchived) {
            btnText.innerText = "Active Assets";
            toggleBtn.className = "btn btn-warning btn-sm px-3 rounded-pill d-flex align-items-center justify-content-center gap-2 flex-fill flex-sm-grow-0 fw-semibold";
            headerBadge.classList.remove('d-none');
            archiveBanner.classList.remove('d-none');
            archiveBanner.classList.add('d-flex');
        } else {
            btnText.innerText = "Archived Assets";
            toggleBtn.className = "btn btn-outline-secondary btn-sm px-3 rounded-pill d-flex align-items-center justify-content-center gap-2 flex-fill flex-sm-grow-0";
            headerBadge.classList.add('d-none');
            archiveBanner.classList.add('d-none');
            archiveBanner.classList.remove('d-flex');
        }
        renderItems();
    };

    async function fetchAssetsAndReports() {
        const grid = document.getElementById('inventory-grid');
        grid.innerHTML = `
            <div class="col-12 text-center py-5 text-muted">
                <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                Loading campus assets...
            </div>`;

        try {
            reportedAssetIds.clear();
            try {
                const ticketSnap = await getDocs(collection(db, "tickets"));
                ticketSnap.forEach(tDoc => {
                    const ticket = tDoc.data();
                    if (ticket.status !== "Completed" && ticket.status !== "Resolved") {
                        if (ticket.assetId) reportedAssetIds.add(ticket.assetId);
                    }
                });
            } catch (tErr) {
                console.warn("Ticket fetch note:", tErr);
            }

            allAssets = [];
            const assetSnap = await getDocs(collection(db, "assets"));
            assetSnap.forEach(docSnap => {
                allAssets.push({
                    id: docSnap.id,
                    ...docSnap.data()
                });
            });

            renderItems();

        } catch (error) {
            console.error("Error fetching items:", error);
            grid.innerHTML = `
                <div class="col-12 text-center py-5 text-danger">
                    <i class="bi bi-exclamation-triangle-fill fs-3 mb-2 d-block"></i>
                    Error loading assets: ${error.message}
                </div>`;
        }
    }

    function renderItems() {
        const grid = document.getElementById('inventory-grid');
        grid.innerHTML = "";

        const isStaff = window.currentUserRole === 'staff';

        const filtered = allAssets.filter(item => {
            const isArchived = item.isArchived === true || item.isArchived === "true";
            if (isArchived !== isViewingArchived) return false;
            if (currentCategory === "All") return true;
            return item.category === currentCategory;
        });

        if (filtered.length === 0) {
            grid.innerHTML = `
                <div class="col-12 text-center py-5">
                    <div class="p-4 bg-white rounded-4 shadow-sm border d-inline-block" style="max-width: 420px;">
                        <i class="bi ${isViewingArchived ? 'bi-archive text-secondary' : 'bi-inbox text-muted'} display-4 mb-3 d-block"></i>
                        <h5 class="fw-bold text-dark mb-1">No ${isViewingArchived ? 'Archived' : 'Active'} Assets</h5>
                        <p class="text-muted small mb-0">No assets found matching category: <strong>${currentCategory}</strong>.</p>
                    </div>
                </div>`;
            return;
        }

        filtered.forEach(asset => {
            const photo = asset.photoUrl || 'picture/images.jpg';
            const building = asset.building || asset.location?.building || 'N/A';
            const room = asset.room || asset.location?.room || 'N/A';
            const hasReport = reportedAssetIds.has(asset.id);

            // Empty alt="" fixes phone browser showing item name text under the red dot!
            // Removed QR text content and removed "view details" words as requested.
            const cardHTML = `
            <div class="col">
                <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden position-relative asset-card" 
                     onclick="window.location.href='info.php?id=${encodeURIComponent(asset.id)}'">
                    
                    <!-- Small Red Dot at top-left corner if reported -->
                    ${hasReport ? `<span class="maintenance-dot" title="Active Maintenance Ticket Reported"></span>` : ''}

                    <!-- Asset Image Centered with Aspect Ratio -->
                    <div class="ratio ratio-4x3 bg-light border-bottom position-relative overflow-hidden">
                        <img src="${photo}" alt="" class="img-fluid object-fit-cover w-100 h-100" onerror="this.src='picture/images.jpg'">
                        
                        <!-- Location Badge at top-right corner of box -->
                        <span class="position-absolute top-0 end-0 m-2 badge bg-dark bg-opacity-75 rounded-pill shadow-sm px-2 py-1 small">
                            <i class="bi bi-geo-alt me-1"></i>${building} - ${room}
                        </span>
                    </div>

                    <!-- Details Area below picture -->
                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                        <div>
                            <h6 class="card-title fw-bold text-dark text-truncate mb-1" title="${asset.name || 'Unnamed Asset'}">
                                ${asset.name || 'Unnamed Asset'}
                            </h6>
                            <span class="badge bg-secondary-subtle text-secondary rounded-pill small">${asset.category || 'N/A'}</span>
                        </div>

                        <!-- Card Footer -->
                        <div class="d-flex align-items-center justify-content-between mt-3 pt-2 border-top">
                            <span class="badge ${hasReport ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success'} rounded-pill">
                                ${hasReport ? '<i class="bi bi-tools me-1"></i>Reported' : '<i class="bi bi-check-circle me-1"></i>Good Condition'}
                            </span>
                            
                            ${isStaff ? `
                                ${!isViewingArchived ? `
                                <button onclick="event.stopPropagation(); archiveAsset('${asset.id}')" 
                                        class="btn btn-outline-danger btn-sm rounded-pill px-2 py-1 small" 
                                        title="Archive Asset">
                                    <i class="bi bi-archive me-1"></i>Archive
                                </button>
                                ` : `
                                <button onclick="event.stopPropagation(); restoreAsset('${asset.id}')" 
                                        class="btn btn-outline-success btn-sm rounded-pill px-2 py-1 small" 
                                        title="Restore Asset">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i>Restore
                                </button>
                                `}
                            ` : ''}
                        </div>
                    </div>
                </div>
            </div>`;

            grid.innerHTML += cardHTML;
        });
    }

    window.archiveAsset = async (assetId) => {
        if (confirm("Are you sure you want to archive this asset?")) {
            try {
                await updateDoc(doc(db, "assets", assetId), { isArchived: true });
                fetchAssetsAndReports();
            } catch (error) {
                alert("Failed to archive asset: " + error.message);
            }
        }
    };

    window.restoreAsset = async (assetId) => {
        if (confirm("Restore this asset to active inventory?")) {
            try {
                await updateDoc(doc(db, "assets", assetId), { isArchived: false });
                fetchAssetsAndReports();
            } catch (error) {
                alert("Failed to restore asset: " + error.message);
            }
        }
    };

    /* ====================================================
       3. QR SCANNER
       ==================================================== */
    let html5QrCode = null;
    let isScannerRunning = false;

    window.startAddAssetWorkflow = async function() {
        document.getElementById('modal-step-1').classList.remove('d-none');
        document.getElementById('modal-step-2').classList.add('d-none');
        document.getElementById('manual-qr-input').value = "";

        const dateInput = document.getElementById('asset-brought-date');
        if (dateInput && !dateInput.value) {
            dateInput.value = new Date().toISOString().split('T')[0];
        }

        await stopScanner();
        await initScanner();
    };

    async function initScanner() {
        const qrContainer = document.getElementById("qr-reader");
        const spinner = document.getElementById("qr-spinner");
        if (!qrContainer) return;

        if (spinner) spinner.classList.remove('d-none');
        qrContainer.innerHTML = "";

        try {
            html5QrCode = new Html5Qrcode("qr-reader");
            const config = { fps: 10, qrbox: { width: 220, height: 220 } };

            try {
                await html5QrCode.start(
                    { facingMode: "environment" },
                    config,
                    onQrCodeScanned,
                    (errorMessage) => {}
                );
                isScannerRunning = true;
            } catch (cameraErr) {
                await html5QrCode.start(
                    { facingMode: "user" },
                    config,
                    onQrCodeScanned,
                    () => {}
                );
                isScannerRunning = true;
            }
        } catch (err) {
            isScannerRunning = false;
            qrContainer.innerHTML = `
                <div class="p-4 text-center text-light small d-flex flex-column align-items-center justify-content-center h-100" style="min-height: 220px;">
                    <i class="bi bi-camera-video-off fs-1 text-warning mb-2"></i>
                    <span class="fw-bold">Camera unavailable or permission denied.</span>
                    <span class="text-secondary mt-1">Please enter the Tag ID manually below.</span>
                </div>`;
        } finally {
            if (spinner) spinner.classList.add('d-none');
        }
    }

    window.stopScanner = async function() {
        if (html5QrCode) {
            try {
                if (isScannerRunning || html5QrCode.isScanning) {
                    await html5QrCode.stop();
                }
            } catch (e) {}
            try {
                await html5QrCode.clear();
            } catch (e) {}
            html5QrCode = null;
            isScannerRunning = false;
        }

        const qrContainer = document.getElementById("qr-reader");
        if (qrContainer) {
            const videos = qrContainer.querySelectorAll("video");
            videos.forEach(vid => {
                if (vid.srcObject && typeof vid.srcObject.getTracks === 'function') {
                    vid.srcObject.getTracks().forEach(track => {
                        try { track.stop(); } catch(e){}
                    });
                    vid.srcObject = null;
                }
            });
            qrContainer.innerHTML = "";
        }
    };

    const modalEl = document.getElementById('addAssetModal');
    if (modalEl) {
        modalEl.addEventListener('hidden.bs.modal', () => {
            stopScanner();
        });
    }

    document.getElementById('btn-use-manual-qr')?.addEventListener('click', () => {
        const manualVal = document.getElementById('manual-qr-input').value.trim();
        if (!manualVal) {
            alert("Please enter a valid Tag ID.");
            return;
        }
        onQrCodeScanned(manualVal);
    });

    async function onQrCodeScanned(decodedText) {
        await stopScanner();
        document.getElementById('asset-qr-id').value = decodedText;
        document.getElementById('scanned-tag-display').innerText = decodedText;
        document.getElementById('modal-step-1').classList.add('d-none');
        document.getElementById('modal-step-2').classList.remove('d-none');
    }

    /* ====================================================
       HELPER: Compress image to max 900px, JPEG 75% quality
       Returns { blob, base64 } – blob ~60KB, base64 ~80KB (always under Firestore 1MB limit)
       ==================================================== */
    function compressImage(file, maxDimension = 900, quality = 0.75) {
        return new Promise((resolve) => {
            const reader = new FileReader();
            reader.onload = (e) => {
                const img = new Image();
                img.onload = () => {
                    let w = img.width, h = img.height;
                    if (w > maxDimension || h > maxDimension) {
                        if (w > h) { h = Math.round(h * maxDimension / w); w = maxDimension; }
                        else       { w = Math.round(w * maxDimension / h); h = maxDimension; }
                    }
                    const canvas = document.createElement('canvas');
                    canvas.width = w; canvas.height = h;
                    canvas.getContext('2d').drawImage(img, 0, 0, w, h);
                    const base64 = canvas.toDataURL('image/jpeg', quality);
                    canvas.toBlob((blob) => resolve({ blob, base64 }), 'image/jpeg', quality);
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        });
    }

    /* ====================================================
       4. ASSET REGISTRATION SUBMIT & IMAGE UPLOAD
       Upload order: Firebase Storage → PHP server → compressed base64 (~80KB, safe)
       ==================================================== */
    document.getElementById('new-asset-form')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const assetId = document.getElementById('asset-qr-id').value.trim();
        const fileInput = document.getElementById('asset-photo');
        const file = fileInput.files ? fileInput.files[0] : null;

        if (!assetId) {
            alert("Missing Asset Tag ID. Please re-scan or enter ID.");
            return;
        }

        const submitBtn = document.getElementById('btn-save-asset');
        submitBtn.innerText = "Uploading & Saving...";
        submitBtn.disabled = true;

        try {
            let photoUrl = "";

            if (file) {
                // Step A: Compress image client-side (max 900px, 75% quality → ~60KB blob)
                submitBtn.innerText = "Compressing image...";
                const compressed = await compressImage(file);

                // Step B: Try Firebase Storage (short download URL ~120 bytes)
                submitBtn.innerText = "Uploading photo...";
                try {
                    const storageRef = ref(storage, `assets/${assetId}_${Date.now()}.jpg`);
                    const snap = await uploadBytes(storageRef, compressed.blob, { contentType: 'image/jpeg' });
                    photoUrl = await getDownloadURL(snap.ref);
                    console.log("✅ Firebase Storage upload OK:", photoUrl.length, "bytes URL");
                } catch (storageErr) {
                    console.warn("Firebase Storage blocked, trying PHP server upload:", storageErr.code || storageErr.message);
                }

                // Step C: If Firebase failed, try PHP server upload (returns short relative path)
                if (!photoUrl) {
                    try {
                        const fd = new FormData();
                        fd.append('image_base64', compressed.base64);
                        const srvRes = await fetch('api/upload_image.php', { method: 'POST', body: fd });
                        const srvData = await srvRes.json();
                        if (srvData.success && srvData.url) {
                            photoUrl = srvData.url;
                            console.log("✅ PHP server upload OK:", photoUrl);
                        }
                    } catch (srvErr) {
                        console.warn("PHP server upload failed:", srvErr);
                    }
                }

                // Step D: Fallback to compressed base64 (~80KB – always well under Firestore's 1MB limit)
                if (!photoUrl) {
                    photoUrl = compressed.base64;
                    console.log("ℹ️ Using compressed base64 fallback:", Math.round(compressed.base64.length / 1024), "KB");
                }
            }

            const building = document.getElementById('asset-building').value;
            const room = document.getElementById('asset-room').value;
            const category = document.getElementById('asset-category').value;
            const assetName = document.getElementById('asset-name').value.trim();
            const price = parseFloat(document.getElementById('asset-price').value) || 0;
            const broughtInDate = document.getElementById('asset-brought-date').value;
            const description = document.getElementById('asset-desc').value.trim();

            await setDoc(doc(db, "assets", assetId), {
                name: assetName,
                category: category,
                building: building,
                room: room,
                price: price,
                broughtInDate: broughtInDate,
                description: description,
                photoUrl: photoUrl || "",
                status: "Good Condition",
                isArchived: false,
                createdAt: serverTimestamp()
            });

            alert("Asset successfully saved in campus inventory!");
            
            const bsModal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            bsModal.hide();

            e.target.reset();
            submitBtn.innerText = "Save Asset";
            submitBtn.disabled = false;

            if (isViewingArchived) toggleArchiveView();
            filterCategory("All");
            fetchAssetsAndReports();

        } catch (err) {
            console.error("Save asset error:", err);
            alert("Failed to save asset: " + err.message);
            submitBtn.innerText = "Save Asset";
            submitBtn.disabled = false;
        }
    });

    // Initialize
    loadCampusFacilities();
    fetchAssetsAndReports();
</script>