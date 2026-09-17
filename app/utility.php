<?php include 'header.php'; ?>

<style>
    .utility-list-card {
        overflow: visible !important;
    }
    .utility-row {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 0.85rem;
        transition: all 0.2s ease;
        overflow: visible;
    }
    .utility-row .dropdown-menu {
        z-index: 1050;
    }
    .utility-row:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }
    .room-pill {
        background-color: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        padding: 0.45rem 0.75rem;
        font-size: 0.9rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: background-color 0.15s;
    }
    .room-pill:hover {
        background-color: #e2e8f0;
    }
</style>

<div class="container-fluid px-0 px-md-2">
    <!-- Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1 d-flex align-items-center">
                <i class="bi bi-building text-primary me-2"></i>Campus Utility Directory
            </h1>
            <p class="text-muted small mb-0">Configure university building blocks and rooms used for asset locations.</p>
        </div>

        <!-- Add Building Block Button -->
        <div data-staff-only class="d-none">
            <button type="button" onclick="openAddBlockModal()" class="btn btn-primary btn-sm px-3 rounded-pill d-flex align-items-center gap-2 fw-semibold shadow-sm">
                <i class="bi bi-plus-lg"></i>
                <span>Add Building Block</span>
            </button>
        </div>
    </div>

    <!-- Line-by-Line List Container (Column structure: Building, Room, Actions) -->
    <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 bg-white utility-list-card">
        <!-- List Table Header -->
        <div class="row g-2 pb-2 mb-3 border-bottom text-uppercase text-secondary fw-bold small px-2 d-none d-md-flex">
            <div class="col-12 col-md-3">Building</div>
            <div class="col-12 col-md-7">Rooms (Classrooms, Labs & Halls)</div>
            <div class="col-12 col-md-2 text-end">Block Actions</div>
        </div>

        <!-- Building Block Rows Container -->
        <div id="utility-list-container" class="d-flex flex-column gap-3">
            <div class="text-center py-5 text-muted">
                <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                Loading university directory...
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add / Rename Building Block -->
<div class="modal fade" id="blockModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold text-dark" id="blockModalTitle">Add Building Block</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="block-form">
                    <input type="hidden" id="block-mode" value="add">
                    <input type="hidden" id="block-id">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Building Block Name</label>
                        <input type="text" id="block-name" class="form-control" placeholder="e.g. Block A, Block DK, North Wing" required>
                    </div>
                    <div class="d-flex gap-2 pt-2">
                        <button type="button" class="btn btn-light border flex-grow-1 rounded-pill" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="btn-save-block" class="btn btn-primary flex-grow-1 rounded-pill fw-bold">Save Building</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add / Rename Room -->
<div class="modal fade" id="roomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold text-dark" id="roomModalTitle">Add Room</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="room-form">
                    <input type="hidden" id="room-mode" value="add">
                    <input type="hidden" id="room-block-id">
                    <input type="hidden" id="room-old-name">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Room Name</label>
                        <input type="text" id="room-name" class="form-control" placeholder="e.g. Room A-101, Computer Lab 2, LT-1" required>
                    </div>
                    <div class="d-flex gap-2 pt-2">
                        <button type="button" class="btn btn-light border flex-grow-1 rounded-pill" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="btn-save-room" class="btn btn-primary flex-grow-1 rounded-pill fw-bold">Save Room</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script type="module">
    import { db, getCurrentUserRole } from './js/config.js';
    import { collection, doc, getDocs, setDoc, deleteDoc, updateDoc } from "https://www.gstatic.com/firebasejs/10.10.0/firebase-firestore.js";

    let blocks = {};
    let isStaff = false;

    const DEFAULT_BLOCKS = {
        "block-a": { name: "Block A", rooms: ["Room A-101", "Room A-102", "Room A-201", "Lab A-1"] },
        "block-b": { name: "Block B", rooms: ["Room B-101", "Room B-201", "Lecture Theatre B"] },
        "block-c": { name: "Block C", rooms: ["Room C-101", "Room C-102", "Workshop C-3"] },
        "block-dk": { name: "Block DK", rooms: ["DK-1", "DK-2", "DK-3", "DK-Auditorium"] }
    };

    async function loadDirectory() {
        const container = document.getElementById('utility-list-container');
        try {
            const snap = await getDocs(collection(db, "utilities"));
            if (snap.empty) {
                blocks = { ...DEFAULT_BLOCKS };
                // Seed initial records
                for (const [id, data] of Object.entries(DEFAULT_BLOCKS)) {
                    try {
                        await setDoc(doc(db, "utilities", id), data);
                    } catch (e) {}
                }
            } else {
                blocks = {};
                snap.forEach(d => {
                    blocks[d.id] = d.data();
                });
            }
            renderList();
        } catch (err) {
            console.warn("Using default utilities:", err);
            blocks = { ...DEFAULT_BLOCKS };
            renderList();
        }
    }

    function renderList() {
        const container = document.getElementById('utility-list-container');
        container.innerHTML = "";

        const keys = Object.keys(blocks);
        if (keys.length === 0) {
            container.innerHTML = `
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-building-x display-4 mb-2 d-block"></i>
                    No building blocks registered yet. Click "Add Building Block" above to begin.
                </div>`;
            return;
        }

        keys.forEach(blockId => {
            const block = blocks[blockId];
            const blockName = block.name || blockId;
            const rooms = Array.isArray(block.rooms) ? block.rooms : [];

            // Generate Room Pills line-by-line
            let roomsHTML = "";
            if (rooms.length === 0) {
                roomsHTML = `<span class="text-muted small fst-italic">No rooms added to this block yet.</span>`;
            } else {
                roomsHTML = `<div class="d-flex flex-wrap gap-2">`;
                rooms.forEach(roomName => {
                    roomsHTML += `
                        <div class="room-pill shadow-xs">
                            <i class="bi bi-door-closed text-primary"></i>
                            <span class="fw-semibold text-dark">${roomName}</span>
                            ${isStaff ? `<div class="d-inline-flex gap-1 ms-1 border-start ps-1">
                                <button type="button" class="btn btn-link text-secondary p-0" title="Update Room" onclick="openRenameRoomModal('${blockId}', '${encodeURIComponent(roomName)}')">
                                    <i class="bi bi-pencil" style="font-size: 0.75rem;"></i>
                                </button>
                                <button type="button" class="btn btn-link text-danger p-0" title="Delete Room" onclick="deleteRoom('${blockId}', '${encodeURIComponent(roomName)}')">
                                    <i class="bi bi-x-circle" style="font-size: 0.75rem;"></i>
                                </button>
                            </div>` : ''}
                        </div>
                    `;
                });
                roomsHTML += `</div>`;
            }

            // List row with comfortable padding / thickness
            const rowHTML = `
            <div class="utility-row p-3 p-md-4">
                <div class="row g-3 align-items-center">
                    <!-- Column 1: Building (Block Name & Room Count) -->
                    <div class="col-12 col-md-3">
                        <div class="d-flex align-items-center">
                            <div class="p-2 bg-primary bg-opacity-10 text-primary rounded-3 me-3">
                                <i class="bi bi-building fs-4"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark mb-1">${blockName}</h5>
                                <span class="badge bg-secondary-subtle text-secondary rounded-pill small">
                                    ${rooms.length} Room${rooms.length === 1 ? '' : 's'}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Column 2: Room (Room Names line-by-line) -->
                    <div class="col-12 col-md-7">
                        ${roomsHTML}
                    </div>

                    <!-- Column 3: Actions -->
                    <div class="col-12 col-md-2 text-md-end d-flex align-items-center justify-content-md-end gap-2">
                        ${isStaff ? `
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1" onclick="openAddRoomModal('${blockId}')" title="Add Room">
                            <i class="bi bi-plus-lg me-1"></i>Add Room
                        </button>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary rounded-circle p-1 px-2" type="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false" title="More Options">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3">
                                <li>
                                    <button type="button" class="dropdown-item small" onclick="openRenameBlockModal('${blockId}', '${encodeURIComponent(blockName)}')">
                                        <i class="bi bi-pencil me-2 text-primary"></i>Update
                                    </button>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <button type="button" class="dropdown-item small text-danger" onclick="deleteBlock('${blockId}', '${encodeURIComponent(blockName)}')">
                                        <i class="bi bi-trash me-2"></i>Delete
                                    </button>
                                </li>
                            </ul>
                        </div>` : `<span class="text-muted small">View only</span>`}
                    </div>
                </div>
            </div>`;

            container.innerHTML += rowHTML;
        });
    }

    /* ====================================================
       BUILDING BLOCK CRUD
       ==================================================== */
    window.openAddBlockModal = () => {
        if (!isStaff) return;
        document.getElementById('blockModalTitle').innerText = "Add Building Block";
        document.getElementById('block-mode').value = "add";
        document.getElementById('block-name').value = "";
        const modal = new bootstrap.Modal(document.getElementById('blockModal'));
        modal.show();
    };

    window.openRenameBlockModal = (blockId, encName) => {
        if (!isStaff) return;
        const name = decodeURIComponent(encName);
        document.getElementById('blockModalTitle').innerText = "Rename Building Block";
        document.getElementById('block-mode').value = "rename";
        document.getElementById('block-id').value = blockId;
        document.getElementById('block-name').value = name;
        const modal = new bootstrap.Modal(document.getElementById('blockModal'));
        modal.show();
    };

    document.getElementById('block-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const mode = document.getElementById('block-mode').value;
        const name = document.getElementById('block-name').value.trim();
        const blockId = document.getElementById('block-id').value;

        if (!name) return;

        const btn = document.getElementById('btn-save-block');
        btn.disabled = true;
        btn.innerText = "Saving...";

        try {
            if (mode === 'add') {
                const newId = name.toLowerCase().replace(/[^a-z0-9]/g, '-');
                await setDoc(doc(db, "utilities", newId), {
                    name: name,
                    rooms: []
                });
            } else if (mode === 'rename') {
                await updateDoc(doc(db, "utilities", blockId), {
                    name: name
                });
            }

            const modal = bootstrap.Modal.getInstance(document.getElementById('blockModal'));
            if (modal) modal.hide();

            await loadDirectory();
        } catch (err) {
            alert("Error: " + err.message);
        } finally {
            btn.disabled = false;
            btn.innerText = "Save Building";
        }
    });

    window.deleteBlock = async (blockId, encName) => {
        if (!isStaff) return;
        const name = decodeURIComponent(encName);
        if (confirm(`Are you sure you want to delete "${name}" and all its rooms?`)) {
            try {
                await deleteDoc(doc(db, "utilities", blockId));
                await loadDirectory();
            } catch (err) {
                alert("Error deleting building block: " + err.message);
            }
        }
    };

    /* ====================================================
       ROOM CRUD
       ==================================================== */
    window.openAddRoomModal = (blockId) => {
        if (!isStaff) return;
        document.getElementById('roomModalTitle').innerText = "Add Room to Block";
        document.getElementById('room-mode').value = "add";
        document.getElementById('room-block-id').value = blockId;
        document.getElementById('room-name').value = "";
        const modal = new bootstrap.Modal(document.getElementById('roomModal'));
        modal.show();
    };

    window.openRenameRoomModal = (blockId, encRoom) => {
        if (!isStaff) return;
        const roomName = decodeURIComponent(encRoom);
        document.getElementById('roomModalTitle').innerText = "Rename Room";
        document.getElementById('room-mode').value = "rename";
        document.getElementById('room-block-id').value = blockId;
        document.getElementById('room-old-name').value = roomName;
        document.getElementById('room-name').value = roomName;
        const modal = new bootstrap.Modal(document.getElementById('roomModal'));
        modal.show();
    };

    document.getElementById('room-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const mode = document.getElementById('room-mode').value;
        const blockId = document.getElementById('room-block-id').value;
        const oldName = document.getElementById('room-old-name').value;
        const newName = document.getElementById('room-name').value.trim();

        if (!blockId || !newName) return;

        const btn = document.getElementById('btn-save-room');
        btn.disabled = true;
        btn.innerText = "Saving...";

        try {
            const block = blocks[blockId];
            let currentRooms = (block && Array.isArray(block.rooms)) ? [...block.rooms] : [];

            if (mode === 'add') {
                if (!currentRooms.includes(newName)) {
                    currentRooms.push(newName);
                }
            } else if (mode === 'rename') {
                currentRooms = currentRooms.map(r => r === oldName ? newName : r);
            }

            await updateDoc(doc(db, "utilities", blockId), {
                rooms: currentRooms
            });

            const modal = bootstrap.Modal.getInstance(document.getElementById('roomModal'));
            if (modal) modal.hide();

            await loadDirectory();
        } catch (err) {
            alert("Error: " + err.message);
        } finally {
            btn.disabled = false;
            btn.innerText = "Save Room";
        }
    });

    window.deleteRoom = async (blockId, encRoom) => {
        if (!isStaff) return;
        const roomName = decodeURIComponent(encRoom);
        if (confirm(`Are you sure you want to delete room "${roomName}"?`)) {
            try {
                const block = blocks[blockId];
                let currentRooms = (block && Array.isArray(block.rooms)) ? [...block.rooms] : [];
                currentRooms = currentRooms.filter(r => r !== roomName);

                await updateDoc(doc(db, "utilities", blockId), {
                    rooms: currentRooms
                });

                await loadDirectory();
            } catch (err) {
                alert("Error deleting room: " + err.message);
            }
        }
    };

    window.addEventListener('userRoleReady', async (e) => {
        isStaff = e.detail.role === 'staff';
        renderList();
    });

    getCurrentUserRole().then(role => {
        isStaff = role === 'staff';
        loadDirectory();
    });
</script>
