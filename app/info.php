<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Details - TARUMT Asset Management</title>
    <!-- Bootstrap 5.3.0 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f4f6f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem 1rem;
            font-family: system-ui, -apple-system, sans-serif;
        }
        .info-card {
            max-width: 540px;
            width: 100%;
        }
        .asset-img-preview {
            max-height: 280px;
            object-fit: cover;
            width: 100%;
            border-radius: 0.75rem;
        }
    </style>
</head>
<body>

    <div class="card info-card border-0 shadow-lg rounded-4 overflow-hidden my-3 bg-white position-relative">
        <!-- Top Bar Header with Exit Button at top-right corner -->
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
            <span class="badge bg-primary-subtle text-primary text-uppercase fw-bold rounded-pill px-3 py-2 small">
                <i class="bi bi-box-seam me-1"></i>TARUMT Campus Asset
            </span>

            <!-- Exit Button at right-upper corner -->
            <button type="button" id="btn-exit-inventory" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-1 d-flex align-items-center gap-1" title="Return to Inventory">
                <span>Exit</span>
                <i class="bi bi-box-arrow-right"></i>
            </button>
        </div>

        <div class="card-body p-4" id="main-content-body">
            <!-- Asset Photo Centered (QR Code removed as requested) -->
            <div class="text-center mb-4">
                <img id="item-photo" src="picture/images.jpg" alt="" class="asset-img-preview shadow-sm border" onerror="this.src='picture/images.jpg'">
            </div>

            <!-- Title & ID -->
            <div class="mb-3">
                <h2 id="item-name" class="h4 fw-bold text-dark mb-1">Loading Asset...</h2>
                <div class="text-muted small font-monospace" id="item-id">Tag ID: -</div>
            </div>

            <!-- Detailed Specifications List (Operational Status removed as requested) -->
            <div class="list-group list-group-flush rounded-3 border mb-4">
                <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                    <span class="text-muted small fw-semibold"><i class="bi bi-tag me-2 text-primary"></i>Category</span>
                    <span id="item-cat" class="fw-bold text-dark small">-</span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                    <span class="text-muted small fw-semibold"><i class="bi bi-geo-alt me-2 text-danger"></i>Location</span>
                    <span id="item-loc" class="fw-bold text-dark small">-</span>
                </div>
                <!-- Price: HIDDEN FROM STUDENTS & GUESTS -->
                <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3 d-none" id="price-row">
                    <span class="text-muted small fw-semibold"><i class="bi bi-cash me-2 text-success"></i>Price</span>
                    <span id="item-price" class="fw-bold text-success small">-</span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                    <span class="text-muted small fw-semibold"><i class="bi bi-calendar-check me-2 text-info"></i>Brought In Date</span>
                    <span id="item-date" class="fw-bold text-dark small">-</span>
                </div>
                <div class="list-group-item py-2 px-3">
                    <div class="text-muted small fw-semibold mb-1"><i class="bi bi-card-text me-2 text-secondary"></i>Description</div>
                    <p id="item-desc" class="text-secondary small mb-0">None</p>
                </div>
            </div>

            <!-- Maintenance History Section -->
            <div class="mb-4">
                <h6 class="fw-bold text-dark d-flex align-items-center mb-2">
                    <i class="bi bi-clock-history text-danger me-2"></i>Maintenance History
                </h6>
                <div id="maintenance-history-list" class="p-3 bg-light rounded-3 border">
                    <div class="text-muted small text-center py-2">
                        <span class="spinner-border spinner-border-sm text-secondary me-1"></span> Checking history logs...
                    </div>
                </div>
            </div>

            <!-- Action Buttons Area: 3 buttons (Archive, Update, Report) -->
            <div class="d-flex flex-column gap-2" id="actions-container">
                <!-- 1. Report Broken Item (Visible to Staff, Student, Guest) -->
                <a id="report-link" href="#" class="btn btn-danger rounded-pill py-2 fw-bold d-flex align-items-center justify-content-center gap-2 shadow-sm">
                    <i class="bi bi-exclamation-triangle-fill"></i> Report Broken Item
                </a>

                <!-- Staff-Only Action Row (Archive & Update) -->
                <div id="staff-buttons-row" class="d-none gap-2">
                    <!-- 2. Update Data Button -->
                    <button type="button" id="btn-open-update-modal" class="btn btn-outline-primary rounded-pill py-2 flex-grow-1 fw-semibold d-flex align-items-center justify-content-center gap-2" data-bs-toggle="modal" data-bs-target="#updateAssetModal">
                        <i class="bi bi-pencil-square"></i> Update Data
                    </button>

                    <!-- 3. Archive / Soft-delete Button -->
                    <button type="button" id="btn-archive-item" class="btn btn-outline-secondary rounded-pill py-2 flex-grow-1 fw-semibold d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-archive"></i> Archive Asset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Asset Modal (Staff only) -->
    <div class="modal fade" id="updateAssetModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-bottom px-4 py-3">
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center">
                        <i class="bi bi-pencil-square text-primary me-2"></i>Update Asset Information
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="update-asset-form">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Asset Name</label>
                            <input type="text" id="edit-name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Category</label>
                            <select id="edit-category" class="form-select" required>
                                <option value="Furniture">Furniture</option>
                                <option value="M&E">M&E</option>
                            </select>
                        </div>

                        <!-- Dropdown list options from utility for Building & Room as requested -->
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Building Block</label>
                                <select id="edit-building" class="form-select" required onchange="onEditBuildingChange()">
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Class / Room</label>
                                <select id="edit-room" class="form-select" required>
                                </select>
                            </div>
                        </div>

                        <!-- Price: Increase/decrease by 1.00 every click (step="1.00") -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Price (RM)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">RM</span>
                                <input type="number" step="1.00" min="0" id="edit-price" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Brought In Date</label>
                            <input type="date" id="edit-date" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Description</label>
                            <textarea id="edit-desc" rows="2" class="form-control"></textarea>
                        </div>

                        <div class="d-flex gap-2 pt-2">
                            <button type="button" class="btn btn-light border flex-grow-1 rounded-pill" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" id="btn-save-edit" class="btn btn-primary flex-grow-1 rounded-pill fw-bold">Update Asset</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script type="module">
        import { auth, db, getCurrentUserRole } from './js/config.js';
        import { doc, getDoc, updateDoc, collection, query, where, getDocs } from "https://www.gstatic.com/firebasejs/10.10.0/firebase-firestore.js";

        const urlParams = new URLSearchParams(window.location.search);
        const assetId = urlParams.get('id');
        let currentAssetData = null;
        let campusFacilities = {};

        // 1. EXIT BUTTON HANDLER
        document.getElementById('btn-exit-inventory').addEventListener('click', () => {
            const user = auth.currentUser;
            if (user) {
                window.location.href = "inventory.php";
            } else {
                // Guest scanning QR: prompt login first
                window.location.href = "login.php?redirect=inventory.php";
            }
        });

        // Load Utility Building/Room Options for Edit Dropdowns
        async function loadUtilityOptions() {
            try {
                const snap = await getDocs(collection(db, "utilities"));
                if (!snap.empty) {
                    snap.forEach(d => {
                        const bData = d.data();
                        const bName = bData.name || d.id;
                        campusFacilities[bName] = bData.rooms || [];
                    });
                } else {
                    campusFacilities = {
                        "Block A": ["Room A-101", "Room A-102", "Room A-201", "Lab A-1"],
                        "Block B": ["Room B-101", "Room B-201", "Lecture Theatre B"],
                        "Block C": ["Room C-101", "Room C-102", "Workshop C-3"],
                        "Block DK": ["DK-1", "DK-2", "DK-3", "DK-Auditorium"]
                    };
                }
            } catch (e) {
                campusFacilities = {
                    "Block A": ["Room A-101", "Room A-102", "Room A-201"],
                    "Block B": ["Room B-101", "Room B-201"],
                    "Block C": ["Room C-101", "Room C-102"],
                    "Block DK": ["DK-1", "DK-2"]
                };
            }
            populateEditBuildingDropdown();
        }

        function populateEditBuildingDropdown() {
            const bldSelect = document.getElementById('edit-building');
            if (!bldSelect) return;
            bldSelect.innerHTML = "";
            Object.keys(campusFacilities).forEach(b => {
                const opt = document.createElement('option');
                opt.value = b;
                opt.innerText = b;
                bldSelect.appendChild(opt);
            });
            onEditBuildingChange();
        }

        window.onEditBuildingChange = function() {
            const bldSelect = document.getElementById('edit-building');
            const rmSelect = document.getElementById('edit-room');
            if (!bldSelect || !rmSelect) return;
            const selBld = bldSelect.value;
            const rooms = campusFacilities[selBld] || [];
            rmSelect.innerHTML = "";
            if (rooms.length === 0) {
                rmSelect.innerHTML = `<option value="General">General</option>`;
            } else {
                rooms.forEach(r => {
                    const opt = document.createElement('option');
                    opt.value = r;
                    opt.innerText = r;
                    rmSelect.appendChild(opt);
                });
            }
        };

        if (!assetId) {
            document.getElementById('main-content-body').innerHTML = `
                <div class="text-center py-5 text-danger">
                    <i class="bi bi-qr-code-scan display-4 mb-3 d-block text-secondary"></i>
                    <h5 class="fw-bold">No Asset ID Specified</h5>
                    <p class="text-muted small mb-3">Please scan a valid asset QR code or enter from inventory.</p>
                    <a href="inventory.php" class="btn btn-primary rounded-pill px-4">Go to Inventory</a>
                </div>`;
        } else {
            loadUtilityOptions().then(() => loadAssetInfo());
        }

        async function loadAssetInfo() {
            try {
                const docSnap = await getDoc(doc(db, "assets", assetId));
                if (!docSnap.exists()) {
                    document.getElementById('main-content-body').innerHTML = `
                        <div class="text-center py-5 text-danger">
                            <i class="bi bi-search display-4 mb-3 d-block text-secondary"></i>
                            <h5 class="fw-bold text-dark">Asset Not Found</h5>
                            <p class="text-muted small mb-3">No asset found matching Tag ID: <span class="font-monospace">${assetId}</span></p>
                            <a href="inventory.php" class="btn btn-primary rounded-pill px-4">Back to Inventory</a>
                        </div>`;
                    return;
                }

                currentAssetData = docSnap.data();

                document.getElementById('item-name').innerText = currentAssetData.name || 'Unnamed Asset';
                document.getElementById('item-id').innerText = `Tag ID: ${assetId}`;
                document.getElementById('item-cat').innerText = currentAssetData.category || 'N/A';
                
                const bld = currentAssetData.building || (currentAssetData.location?.building) || '';
                const rm = currentAssetData.room || (currentAssetData.location?.room) || '';
                document.getElementById('item-loc').innerText = (bld || rm) ? `${bld} - ${rm}` : 'N/A';

                const broughtDate = currentAssetData.broughtInDate || (currentAssetData.createdAt?.toDate ? currentAssetData.createdAt.toDate().toISOString().split('T')[0] : 'N/A');
                document.getElementById('item-date').innerText = broughtDate;
                document.getElementById('item-desc').innerText = currentAssetData.description || "None provided";

                if (currentAssetData.photoUrl) {
                    document.getElementById('item-photo').src = currentAssetData.photoUrl;
                }

                document.getElementById('report-link').href = `report.php?id=${encodeURIComponent(assetId)}`;

                evaluateRolePermissions(currentAssetData);
                loadMaintenanceHistory(assetId);

                const archiveBtn = document.getElementById('btn-archive-item');
                if (currentAssetData.isArchived) {
                    archiveBtn.innerHTML = `<i class="bi bi-arrow-counterclockwise"></i> Restore Asset`;
                    archiveBtn.className = "btn btn-outline-success rounded-pill py-2 flex-grow-1 fw-semibold d-flex align-items-center justify-content-center gap-2";
                } else {
                    archiveBtn.innerHTML = `<i class="bi bi-archive"></i> Archive Asset`;
                    archiveBtn.className = "btn btn-outline-secondary rounded-pill py-2 flex-grow-1 fw-semibold d-flex align-items-center justify-content-center gap-2";
                }

            } catch (err) {
                console.error("Error loading asset info:", err);
            }
        }

        async function evaluateRolePermissions(assetData) {
            const role = await getCurrentUserRole();
            const priceRow = document.getElementById('price-row');
            const staffButtonsRow = document.getElementById('staff-buttons-row');

            if (role === 'staff') {
                if (priceRow) {
                    priceRow.classList.remove('d-none');
                    const priceVal = parseFloat(assetData.price) || 0;
                    document.getElementById('item-price').innerText = `RM ${priceVal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                }
                if (staffButtonsRow) {
                    staffButtonsRow.classList.remove('d-none');
                    staffButtonsRow.classList.add('d-flex');
                }

                // Pre-populate Edit Modal with dropdown selections
                document.getElementById('edit-name').value = assetData.name || '';
                document.getElementById('edit-category').value = assetData.category || 'Furniture';

                const bldSelect = document.getElementById('edit-building');
                if (assetData.building && bldSelect) {
                    bldSelect.value = assetData.building;
                    onEditBuildingChange();
                }
                const rmSelect = document.getElementById('edit-room');
                if (assetData.room && rmSelect) {
                    rmSelect.value = assetData.room;
                }

                document.getElementById('edit-price').value = assetData.price || 0;
                document.getElementById('edit-date').value = assetData.broughtInDate || '';
                document.getElementById('edit-desc').value = assetData.description || '';
            } else {
                if (priceRow) priceRow.classList.add('d-none');
                if (staffButtonsRow) staffButtonsRow.classList.add('d-none');
            }
        }

        async function loadMaintenanceHistory(assetId) {
            const historyContainer = document.getElementById('maintenance-history-list');
            try {
                const q = query(collection(db, "tickets"), where("assetId", "==", assetId));
                const snapshot = await getDocs(q);

                if (snapshot.empty) {
                    historyContainer.innerHTML = `
                        <div class="text-center text-muted small py-2">
                            <i class="bi bi-shield-check text-success me-1"></i> No prior maintenance issues recorded.
                        </div>`;
                    return;
                }

                let historyHTML = `<ul class="list-unstyled mb-0">`;
                snapshot.forEach(docSnap => {
                    const ticket = docSnap.data();
                    let repDateStr = ticket.reportDate || (ticket.createdAt?.toDate ? ticket.createdAt.toDate().toLocaleDateString() : "Recent");
                    let compDateStr = ticket.completedAt?.toDate ? ticket.completedAt.toDate().toLocaleDateString() : (ticket.status === "Completed" ? "Completed" : "Pending Fix");
                    const desc = ticket.description || "No description provided.";

                    historyHTML += `
                        <li class="pb-2 mb-2 border-bottom">
                            <div class="fw-bold small ${ticket.status === 'Completed' ? 'text-success' : 'text-warning-emphasis'}">
                                (${repDateStr}) - (${compDateStr})
                            </div>
                            <div class="text-secondary small mt-1">
                                ${desc}
                            </div>
                        </li>`;
                });
                historyHTML += `</ul>`;
                historyContainer.innerHTML = historyHTML;
            } catch (err) {
                historyContainer.innerHTML = `<div class="text-muted small text-center">No maintenance history available.</div>`;
            }
        }

        // Archive Toggle
        document.getElementById('btn-archive-item')?.addEventListener('click', async () => {
            if (!currentAssetData) return;
            const newArchived = !currentAssetData.isArchived;
            if (confirm(`Are you sure you want to ${newArchived ? 'archive' : 'restore'} this asset?`)) {
                try {
                    await updateDoc(doc(db, "assets", assetId), { isArchived: newArchived });
                    alert(`Asset ${newArchived ? 'archived' : 'restored'} successfully!`);
                    window.location.reload();
                } catch (e) {
                    alert("Error: " + e.message);
                }
            }
        });

        // Save Edit
        document.getElementById('update-asset-form')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = document.getElementById('btn-save-edit');
            submitBtn.disabled = true;
            submitBtn.innerText = "Saving...";

            try {
                await updateDoc(doc(db, "assets", assetId), {
                    name: document.getElementById('edit-name').value.trim(),
                    category: document.getElementById('edit-category').value,
                    building: document.getElementById('edit-building').value,
                    room: document.getElementById('edit-room').value,
                    price: parseFloat(document.getElementById('edit-price').value) || 0,
                    broughtInDate: document.getElementById('edit-date').value,
                    description: document.getElementById('edit-desc').value.trim()
                });

                alert("Asset updated successfully!");
                const modal = bootstrap.Modal.getInstance(document.getElementById('updateAssetModal'));
                if (modal) modal.hide();
                loadAssetInfo();
            } catch (err) {
                alert("Update failed: " + err.message);
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerText = "Update Asset";
            }
        });
    </script>
</body>
</html>