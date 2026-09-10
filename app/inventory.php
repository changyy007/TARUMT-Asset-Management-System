<?php include 'header.php'; ?>

<!-- HTML5 QR Code Library for Asset Scanning -->
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

<div class="container-fluid px-0 px-md-2">
    <!-- Top Action Bar -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1 d-flex align-items-center">
                <span>Campus Inventory</span>
                <span id="header-archive-badge" class="badge bg-warning text-dark fs-6 ms-2 d-none">
                    <i class="bi bi-archive me-1"></i>Archive View
                </span>
            </h1>
            <p class="text-muted small mb-0">Browse campus equipment by category or review decommissioned items.</p>
        </div>

        <div class="d-flex align-items-center gap-2 w-100 w-sm-auto justify-content-sm-end">
            <!-- Archived Toggle Button -->
            <button id="toggle-archive-btn" onclick="toggleArchiveView()" class="btn btn-outline-secondary btn-sm px-3 rounded-pill d-flex align-items-center justify-content-center gap-2 flex-fill flex-sm-grow-0">
                <i class="bi bi-clock-history"></i>
                <span id="archive-btn-text">View Archived</span>
            </button>
            
            <!-- Add Item Button -->
            <button id="btn-open-add-modal" type="button" onclick="startAddAssetWorkflow()" class="btn btn-primary btn-sm px-3 rounded-pill d-flex align-items-center justify-content-center gap-2 fw-semibold flex-fill flex-sm-grow-0" data-bs-toggle="modal" data-bs-target="#addAssetModal">
                <i class="bi bi-plus-lg"></i>
                <span>Add Asset</span>
            </button>
        </div>
    </div>

    <!-- Archive Banner Notification (visible when in archive mode) -->
    <div id="archive-banner" class="alert alert-warning d-none align-items-center justify-content-between p-3 rounded-3 mb-4 shadow-sm" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
            <div>
                <strong>Viewing Archived Assets:</strong> These items have been decommissioned or archived. You can restore them to active inventory at any time.
            </div>
        </div>
        <button type="button" class="btn btn-sm btn-dark rounded-pill ms-3 text-nowrap" onclick="toggleArchiveView()">
            Back to Active Assets
        </button>
    </div>

    <!-- Category Filter Tabs (Bootstrap Nav Pills with Horizontal Scroll on Mobile) -->
    <div class="border-bottom pb-2 mb-4">
        <ul class="nav nav-pills flex-nowrap overflow-x-auto gap-2 py-1" id="category-tabs" style="scrollbar-width: none;">
            <li class="nav-item">
                <button onclick="filterCategory('Furniture')" class="cat-tab nav-link active rounded-pill px-4 py-2 fw-semibold" data-cat="Furniture">
                    <i class="bi bi-lamp me-1"></i> Furniture
                </button>
            </li>
            <li class="nav-item">
                <button onclick="filterCategory('M&E')" class="cat-tab nav-link rounded-pill px-4 py-2 fw-semibold text-secondary" data-cat="M&E">
                    <i class="bi bi-cpu me-1"></i> M&E (Mechanical & Electrical)
                </button>
            </li>
        </ul>
    </div>

    <!-- Items Grid (Fluid Responsive Grid: 1 col on mobile, 2 on sm, 3 on md/lg, 4 on xl) -->
    <div id="inventory-grid" class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 g-3 g-md-4">
        <!-- Rendered dynamically via JS -->
        <div class="col-12 text-center py-5 text-muted">
            <div class="spinner-border text-primary mb-2" role="status"></div>
            <div>Loading assets...</div>
        </div>
    </div>
</div>

<!-- Bootstrap 5 Add Asset Modal -->
<div class="modal fade" id="addAssetModal" tabindex="-1" aria-labelledby="addAssetModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 shadow border-0">
            <!-- Modal Header -->
            <div class="modal-header border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center" id="addAssetModalLabel">
                    <i class="bi bi-box-seam text-primary me-2"></i>Register New Asset
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="stopScanner()"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4">
                <!-- STEP 1: QR SCANNER & MANUAL ID -->
                <div id="modal-step-1">
                    <div class="text-center mb-3">
                        <h6 class="fw-bold mb-1">Step 1: Scan Blank QR Code</h6>
                        <p class="text-muted small mb-0">Point your smartphone camera at the item's physical QR tag, or enter the ID manually.</p>
                    </div>

                    <!-- Camera Viewport -->
                    <div id="qr-reader" class="w-100 bg-dark rounded-3 overflow-hidden mb-3 border position-relative" style="min-height: 240px;"></div>

                    <!-- Manual Entry Option -->
                    <div class="card bg-light border-0 p-3 rounded-3 mb-3">
                        <label class="form-label small fw-semibold text-secondary mb-1">
                            <i class="bi bi-keyboard me-1"></i>Or Enter Tag ID Manually:
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-qr-code"></i></span>
                            <input type="text" id="manual-qr-input" class="form-control" placeholder="e.g. ASSET-10029">
                            <button class="btn btn-outline-primary" type="button" id="btn-use-manual-qr">Use ID</button>
                        </div>
                    </div>

                    <button type="button" class="btn btn-light border w-100 rounded-pill py-2" data-bs-dismiss="modal" onclick="stopScanner()">
                        Cancel
                    </button>
                </div>

                <!-- STEP 2: ASSET DETAILS FORM -->
                <div id="modal-step-2" class="d-none">
                    <div class="d-flex align-items-center justify-content-between p-2 mb-3 bg-light rounded-3 border">
                        <div>
                            <div class="small text-muted">Scanned Tag:</div>
                            <span class="fw-bold font-monospace text-primary" id="scanned-tag-display">Tag ID: -</span>
                        </div>
                        <button type="button" id="btn-rescan" class="btn btn-link btn-sm text-decoration-none" onclick="startAddAssetWorkflow()">
                            <i class="bi bi-arrow-repeat me-1"></i>Re-scan
                        </button>
                    </div>

                    <form id="new-asset-form">
                        <input type="hidden" id="asset-qr-id">

                        <!-- Photo Upload / Camera -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-uppercase text-secondary mb-1">Take / Upload Photo</label>
                            <input type="file" id="asset-photo" accept="image/*" capture="environment" class="form-control">
                            <div class="form-text small">Take a photo with your smartphone or choose an image.</div>
                        </div>

                        <!-- Asset Name -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-uppercase text-secondary mb-1">Asset Name <span class="text-danger">*</span></label>
                            <input type="text" id="asset-name" required placeholder="e.g. Ergonomic Office Chair" class="form-control">
                        </div>

                        <!-- Category -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-uppercase text-secondary mb-1">Category <span class="text-danger">*</span></label>
                            <select id="asset-category" class="form-select">
                                <option value="Furniture">Furniture</option>
                                <option value="M&E">M&E (Mechanical & Electrical)</option>
                            </select>
                        </div>

                        <!-- Location Dropdowns -->
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold text-uppercase text-secondary mb-1">Building</label>
                                <select id="asset-building" class="form-select">
                                    <option value="Block A">Block A</option>
                                    <option value="Block B">Block B</option>
                                    <option value="Block C">Block C</option>
                                    <option value="Block DK">Block DK</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold text-uppercase text-secondary mb-1">Room</label>
                                <select id="asset-room" class="form-select">
                                    <option value="A-101">A-101</option>
                                    <option value="A-102">A-102</option>
                                    <option value="B-201">B-201</option>
                                    <option value="DK-1">DK-1</option>
                                </select>
                            </div>
                        </div>

                        <!-- GPS Status Bar -->
                        <div class="p-2 mb-3 bg-light rounded-3 border d-flex align-items-center justify-content-between small">
                            <span class="text-muted"><i class="bi bi-geo-alt-fill text-danger me-1"></i>GPS Location:</span>
                            <span id="gps-status" class="font-monospace fw-semibold text-primary">Locating...</span>
                        </div>

                        <!-- Submit / Cancel -->
                        <div class="d-flex gap-2 pt-2">
                            <button type="button" class="btn btn-light border flex-grow-1 rounded-pill" data-bs-dismiss="modal">
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

<script type="module">
    import { db, storage } from './js/config.js';
    import { doc, setDoc, updateDoc, collection, query, where, getDocs, serverTimestamp } from "https://www.gstatic.com/firebasejs/10.10.0/firebase-firestore.js";
    import { ref, uploadBytes, getDownloadURL } from "https://www.gstatic.com/firebasejs/10.10.0/firebase-storage.js";

    /* =========================================
       1. INVENTORY DISPLAY & FILTERING LOGIC
       ========================================= */
    let currentCategory = "Furniture";
    let isViewingArchived = false;

    window.filterCategory = (category) => {
        currentCategory = category;
        document.querySelectorAll('.cat-tab').forEach(tab => {
            if (tab.dataset.cat === category) {
                tab.classList.add('active', 'text-white');
                tab.classList.remove('text-secondary');
            } else {
                tab.classList.remove('active', 'text-white');
                tab.classList.add('text-secondary');
            }
        });
        fetchItems();
    };

    window.toggleArchiveView = () => {
        isViewingArchived = !isViewingArchived;
        const btnText = document.getElementById('archive-btn-text');
        const toggleBtn = document.getElementById('toggle-archive-btn');
        const headerBadge = document.getElementById('header-archive-badge');
        const archiveBanner = document.getElementById('archive-banner');

        if (isViewingArchived) {
            btnText.innerText = "View Active Assets";
            toggleBtn.className = "btn btn-warning btn-sm px-3 rounded-pill d-flex align-items-center justify-content-center gap-2 flex-fill flex-sm-grow-0 fw-semibold";
            headerBadge.classList.remove('d-none');
            archiveBanner.classList.remove('d-none');
            archiveBanner.classList.add('d-flex');
        } else {
            btnText.innerText = "View Archived";
            toggleBtn.className = "btn btn-outline-secondary btn-sm px-3 rounded-pill d-flex align-items-center justify-content-center gap-2 flex-fill flex-sm-grow-0";
            headerBadge.classList.add('d-none');
            archiveBanner.classList.add('d-none');
            archiveBanner.classList.remove('d-flex');
        }
        fetchItems();
    };

    window.archiveAsset = async (assetId) => {
        if (confirm("Are you sure you want to archive this asset? It will be moved to the archive view.")) {
            try {
                await updateDoc(doc(db, "assets", assetId), { isArchived: true });
                fetchItems();
            } catch (error) {
                console.error("Error archiving asset:", error);
                alert("Failed to archive asset: " + error.message);
            }
        }
    };

    window.restoreAsset = async (assetId) => {
        if (confirm("Are you sure you want to restore this asset to active inventory?")) {
            try {
                await updateDoc(doc(db, "assets", assetId), { isArchived: false });
                fetchItems();
            } catch (error) {
                console.error("Error restoring asset:", error);
                alert("Failed to restore asset: " + error.message);
            }
        }
    };

    async function fetchItems() {
        const grid = document.getElementById('inventory-grid');
        grid.innerHTML = `
            <div class="col-12 text-center py-5 text-muted">
                <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                Loading assets...
            </div>`;

        try {
            const q = query(
                collection(db, "assets"), 
                where("category", "==", currentCategory)
            );
            
            const snapshot = await getDocs(q);
            grid.innerHTML = ""; 

            let itemsCount = 0;

            snapshot.forEach((docSnap) => {
                const asset = docSnap.data();
                const assetId = docSnap.id;
                const photo = asset.photoUrl || 'picture/images.jpg';

                // Check archived flag
                const isItemArchived = asset.isArchived === true || asset.isArchived === "true";
                if (isItemArchived !== isViewingArchived) return;

                itemsCount++;

                const building = asset.building || asset.location?.building || 'N/A';
                const room = asset.room || asset.location?.room || 'N/A';

                const cardHTML = `
                <div class="col">
                    <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden position-relative asset-card" 
                         style="cursor: pointer; transition: transform 0.2s ease, box-shadow 0.2s ease;"
                         onclick="window.location.href='info.php?id=${assetId}'">
                        
                        <!-- Asset Image with Responsive Fixed Aspect Ratio -->
                        <div class="ratio ratio-4x3 bg-light border-bottom position-relative">
                            <img src="${photo}" alt="${asset.name}" class="img-fluid object-fit-cover w-100 h-100" onerror="this.src='picture/images.jpg'">
                            <span class="position-absolute top-0 end-0 m-2 badge bg-dark bg-opacity-75 rounded-pill shadow-sm px-2 py-1 small">
                                <i class="bi bi-geo-alt me-1"></i>${building} - ${room}
                            </span>
                        </div>

                        <!-- Card Details -->
                        <div class="card-body p-3 d-flex flex-column justify-content-between">
                            <div>
                                <h6 class="card-title fw-bold text-dark text-truncate mb-1" title="${asset.name}">
                                    ${asset.name}
                                </h6>
                                <p class="text-muted small mb-0">
                                    <i class="bi bi-building me-1"></i>${building}, Room ${room}
                                </p>
                            </div>

                            <!-- Footer Actions -->
                            <div class="d-flex align-items-center justify-content-between mt-3 pt-2 border-top">
                                <span class="badge ${isItemArchived ? 'bg-secondary' : 'bg-success-subtle text-success'} rounded-pill">
                                    ${isItemArchived ? 'Archived' : 'Active'}
                                </span>

                                ${!isViewingArchived ? `
                                <button onclick="event.stopPropagation(); archiveAsset('${assetId}')" 
                                        class="btn btn-outline-danger btn-sm rounded-pill px-2 py-1" 
                                        title="Archive Asset">
                                    <i class="bi bi-archive me-1"></i>Archive
                                </button>
                                ` : `
                                <button onclick="event.stopPropagation(); restoreAsset('${assetId}')" 
                                        class="btn btn-outline-success btn-sm rounded-pill px-2 py-1" 
                                        title="Restore Asset">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i>Restore
                                </button>
                                `}
                            </div>
                        </div>
                    </div>
                </div>`;
                grid.innerHTML += cardHTML;
            });

            if (itemsCount === 0) {
                grid.innerHTML = `
                    <div class="col-12 text-center py-5">
                        <div class="p-4 bg-white rounded-4 shadow-sm border d-inline-block" style="max-width: 400px;">
                            <i class="bi ${isViewingArchived ? 'bi-archive text-secondary' : 'bi-inbox text-muted'} display-4 mb-3 d-block"></i>
                            <h5 class="fw-bold text-dark mb-1">No ${isViewingArchived ? 'Archived' : 'Active'} Assets Found</h5>
                            <p class="text-muted small mb-0">There are no assets in the <strong>${currentCategory}</strong> category matching this view.</p>
                        </div>
                    </div>`;
            }

        } catch (error) {
            console.error("Error fetching items:", error);
            grid.innerHTML = `
                <div class="col-12 text-center py-5 text-danger">
                    <i class="bi bi-exclamation-triangle-fill fs-3 mb-2 d-block"></i>
                    Error loading assets: ${error.message}
                </div>`;
        }
    }

    // Initial load
    fetchItems();

    // Auto-launch Add Asset modal if redirected with ?action=add
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('action') === 'add') {
        const modalEl = document.getElementById('addAssetModal');
        if (modalEl) {
            const bsModal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            bsModal.show();
            startAddAssetWorkflow();
        }
    }

    /* =========================================
       2. QR SCANNER & ADD ASSET LOGIC
       ========================================= */
    let html5QrCode = null;
    let currentLat = null;
    let currentLng = null;

    window.startAddAssetWorkflow = function() {
        document.getElementById('modal-step-1').classList.remove('d-none');
        document.getElementById('modal-step-2').classList.add('d-none');
        document.getElementById('manual-qr-input').value = "";
        
        fetchGPSLocation();

        // Initialize camera scanner
        try {
            if (html5QrCode && html5QrCode.isScanning) {
                html5QrCode.stop().then(() => initScanner()).catch(() => initScanner());
            } else {
                initScanner();
            }
        } catch (e) {
            console.warn("Scanner initialization note:", e);
        }
    };

    function initScanner() {
        const qrContainer = document.getElementById("qr-reader");
        if (!qrContainer) return;

        html5QrCode = new Html5Qrcode("qr-reader");
        
        // Try environment camera first, then fallback to default camera
        html5QrCode.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: { width: 220, height: 220 } },
            onQrCodeScanned,
            (errorMessage) => { /* Ignore background scan frame errors */ }
        ).catch(err => {
            console.log("Back camera not available, trying any available camera...", err);
            html5QrCode.start(
                { facingMode: "user" },
                { fps: 10, qrbox: { width: 220, height: 220 } },
                onQrCodeScanned,
                () => {}
            ).catch(err2 => {
                console.warn("No camera available or camera permission denied:", err2);
                qrContainer.innerHTML = `
                    <div class="p-3 text-center text-secondary small d-flex flex-column align-items-center justify-content-center h-100" style="min-height: 200px;">
                        <i class="bi bi-camera-video-off fs-3 mb-2 text-warning"></i>
                        <span>Camera access not available or permission denied.</span>
                        <span class="fw-semibold text-dark mt-1">Please enter the Tag ID manually below.</span>
                    </div>`;
            });
        });
    }

    window.stopScanner = function() {
        if (html5QrCode) {
            try {
                if (html5QrCode.isScanning) {
                    html5QrCode.stop().then(() => html5QrCode.clear()).catch(console.error);
                }
            } catch (e) {
                console.warn("Error stopping scanner:", e);
            }
        }
    };

    // Make sure scanner stops when modal is hidden
    const modalEl = document.getElementById('addAssetModal');
    if (modalEl) {
        modalEl.addEventListener('hidden.bs.modal', stopScanner);
    }

    // Manual QR Code Input Button
    document.getElementById('btn-use-manual-qr')?.addEventListener('click', () => {
        const manualVal = document.getElementById('manual-qr-input').value.trim();
        if (!manualVal) {
            alert("Please enter a valid Tag ID.");
            return;
        }
        onQrCodeScanned(manualVal);
    });

    function onQrCodeScanned(decodedText) {
        stopScanner();
        document.getElementById('asset-qr-id').value = decodedText;
        document.getElementById('scanned-tag-display').innerText = `Tag ID: ${decodedText}`;
        document.getElementById('modal-step-1').classList.add('d-none');
        document.getElementById('modal-step-2').classList.remove('d-none');
    }

    function fetchGPSLocation() {
        const gpsStatus = document.getElementById('gps-status');
        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    currentLat = position.coords.latitude;
                    currentLng = position.coords.longitude;

                    if (gpsStatus) gpsStatus.innerText = `${currentLat.toFixed(4)}, ${currentLng.toFixed(4)}`;
                },
                (error) => {
                    if (gpsStatus) gpsStatus.innerText = "Location permission denied / unavailable";
                },
                { timeout: 8000 }
            );
        } else {
            if (gpsStatus) gpsStatus.innerText = "Not supported by browser";
        }
    }

    // Handle Asset Registration Form Submit
    document.getElementById('new-asset-form')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const assetId = document.getElementById('asset-qr-id').value.trim();
        const fileInput = document.getElementById('asset-photo');
        const file = fileInput.files ? fileInput.files[0] : null;

        if (!assetId) {
            alert("Missing Asset Tag ID. Please re-scan or enter manually.");
            return;
        }

        const submitBtn = document.getElementById('btn-save-asset');
        submitBtn.innerText = "Saving...";
        submitBtn.disabled = true;

        try {
            let photoUrl = "";

            if (file) {
                try {
                    const storageRef = ref(storage, `assets/${assetId}_${Date.now()}`);
                    const snapshot = await uploadBytes(storageRef, file);
                    photoUrl = await getDownloadURL(snapshot.ref);
                } catch (uploadErr) {
                    console.warn("Storage upload error (proceeding with default):", uploadErr);
                }
            }

            const building = document.getElementById('asset-building').value;
            const room = document.getElementById('asset-room').value;
            const category = document.getElementById('asset-category').value;
            const assetName = document.getElementById('asset-name').value;

            // Save document with structured and flat location for full compatibility
            await setDoc(doc(db, "assets", assetId), {
                name: assetName,
                category: category,
                building: building,
                room: room,
                location: {
                    building: building,
                    room: room
                },
                locationStr: `${building} - ${room}`,
                latitude: currentLat !== null ? currentLat : null,
                longitude: currentLng !== null ? currentLng : null,
                photoUrl: photoUrl || "",
                status: "Good Condition",
                isArchived: false,
                createdAt: serverTimestamp()
            });

            alert("Asset successfully registered!");
            
            // Close modal using Bootstrap modal API
            const bsModal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            bsModal.hide();

            e.target.reset();
            submitBtn.innerText = "Save Asset";
            submitBtn.disabled = false;

            // Switch back to active view and selected category
            if (isViewingArchived) {
                toggleArchiveView();
            }
            filterCategory(category);

        } catch (err) {
            console.error("Save asset error:", err);
            alert("Failed to save asset: " + err.message);
            submitBtn.innerText = "Save Asset";
            submitBtn.disabled = false;
        }
    });
</script>

<?php include 'footer.php'; ?>