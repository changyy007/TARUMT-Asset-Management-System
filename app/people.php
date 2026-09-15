<?php include 'header.php'; ?>

<style>
    .people-row {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 0.85rem;
        transition: all 0.2s ease;
    }
    .people-row:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }
    .avatar-icon {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1.1rem;
        flex-shrink: 0;
    }
</style>

<div class="container-fluid px-0 px-md-2">
    <!-- Top Action Bar -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1 d-flex align-items-center">
                <i class="bi bi-people text-primary me-2"></i>Campus People Directory
            </h1>
            <p class="text-muted small mb-0">Authorized campus staff and students eligible for system access.</p>
        </div>

        <!-- Add People Button at right-upper corner -->
        <div>
            <button type="button" onclick="openAddPersonModal()" class="btn btn-primary btn-sm px-3 rounded-pill d-flex align-items-center gap-2 fw-semibold shadow-sm">
                <i class="bi bi-person-plus-fill fs-6"></i>
                <span>Add People</span>
            </button>
        </div>
    </div>

    <!-- Category Filter Tabs: All, Staff, Student (like inventory did) -->
    <div class="border-bottom pb-2 mb-4">
        <ul class="nav nav-pills flex-nowrap overflow-x-auto gap-2 py-1" id="role-category-tabs" style="scrollbar-width: none;">
            <li class="nav-item">
                <button onclick="filterRoleCategory('ALL')" class="role-tab nav-link active rounded-pill px-4 py-2 fw-semibold" data-role="ALL">
                    <i class="bi bi-people-fill me-1"></i> All Users
                </button>
            </li>
            <li class="nav-item">
                <button onclick="filterRoleCategory('staff')" class="role-tab nav-link rounded-pill px-4 py-2 fw-semibold text-danger" data-role="staff">
                    <i class="bi bi-shield-lock-fill me-1"></i> Staff
                </button>
            </li>
            <li class="nav-item">
                <button onclick="filterRoleCategory('student')" class="role-tab nav-link rounded-pill px-4 py-2 fw-semibold text-primary" data-role="student">
                    <i class="bi bi-mortarboard-fill me-1"></i> Student
                </button>
            </li>
        </ul>
    </div>

    <!-- Line-by-Line List Container -->
    <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 bg-white">
        <!-- List Header -->
        <div class="row g-2 pb-2 mb-3 border-bottom text-uppercase text-secondary fw-bold small px-2 d-none d-md-flex">
            <div class="col-12 col-md-4">User & Login ID</div>
            <div class="col-12 col-md-4">Gmail Account</div>
            <div class="col-12 col-md-2">Access Role</div>
            <div class="col-12 col-md-2 text-end">Actions</div>
        </div>

        <!-- Users List -->
        <div id="people-list-container" class="d-flex flex-column gap-3">
            <div class="text-center py-5 text-muted">
                <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                Loading campus directory...
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add / Edit Person -->
<div class="modal fade" id="personModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center" id="personModalTitle">
                    <i class="bi bi-person-plus text-primary me-2"></i>Add Authorized Person
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="person-form">
                    <input type="hidden" id="person-mode" value="add">
                    <input type="hidden" id="person-doc-id">

                    <!-- Student/Staff ID -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Student / Staff ID <span class="text-danger">*</span></label>
                        <input type="text" id="person-login-id" class="form-control" placeholder="e.g. 23WMD01234 or STF-001" required>
                        <div class="form-text small">This is the ID user types to sign in.</div>
                    </div>

                    <!-- Gmail Account -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Gmail Account <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-envelope text-muted"></i></span>
                            <input type="email" id="person-email" class="form-control" placeholder="username@gmail.com" required>
                        </div>
                    </div>

                    <!-- Role Dropdown: Staff or Student -->
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Role Permission <span class="text-danger">*</span></label>
                        <select id="person-role" class="form-select" required>
                            <option value="student">Student (Read-Only Inventory, File Reports)</option>
                            <option value="staff">Staff (Full CRUD Inventory, Maintenance, Utility, People)</option>
                        </select>
                    </div>

                    <div class="d-flex gap-2 pt-2">
                        <button type="button" class="btn btn-light border flex-grow-1 rounded-pill" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="btn-save-person" class="btn btn-primary flex-grow-1 rounded-pill fw-bold">Save Person</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script type="module">
    import { db } from './js/config.js';
    import { collection, doc, getDocs, setDoc, deleteDoc, updateDoc, serverTimestamp } from "https://www.gstatic.com/firebasejs/10.10.0/firebase-firestore.js";

    let allPeople = [];
    let currentCategory = "ALL";

    // Seed initial users if users collection is empty
    const INITIAL_USERS = [
        { loginId: "STF-001", email: "admin@tarumt.edu.my", role: "staff" },
        { loginId: "23WMD00001", email: "student@tarumt.edu.my", role: "student" }
    ];

    async function loadPeople() {
        const container = document.getElementById('people-list-container');
        try {
            const snap = await getDocs(collection(db, "users"));
            if (snap.empty) {
                // Seed initial accounts
                allPeople = [];
                for (const u of INITIAL_USERS) {
                    const docId = u.loginId.toLowerCase().replace(/[^a-z0-9]/g, '-');
                    await setDoc(doc(db, "users", docId), {
                        ...u,
                        createdAt: serverTimestamp()
                    });
                    allPeople.push({ id: docId, ...u });
                }
            } else {
                allPeople = [];
                snap.forEach(d => {
                    allPeople.push({
                        id: d.id,
                        ...d.data()
                    });
                });
            }
            renderPeopleList();
        } catch (err) {
            console.error("Error loading people:", err);
            allPeople = INITIAL_USERS.map((u, i) => ({ id: "u-" + i, ...u }));
            renderPeopleList();
        }
    }

    window.filterRoleCategory = (role) => {
        currentCategory = role;
        document.querySelectorAll('.role-tab').forEach(tab => {
            if (tab.dataset.role === role) {
                tab.classList.add('active', 'bg-dark', 'text-white');
            } else {
                tab.classList.remove('active', 'bg-dark', 'text-white');
            }
        });
        renderPeopleList();
    };

    function renderPeopleList() {
        const container = document.getElementById('people-list-container');
        container.innerHTML = "";

        const filtered = allPeople.filter(p => {
            if (currentCategory === "ALL") return true;
            return (p.role || "student").toLowerCase() === currentCategory;
        });

        if (filtered.length === 0) {
            container.innerHTML = `
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-person-x display-4 mb-2 d-block"></i>
                    No users found matching category: <strong>${currentCategory.toUpperCase()}</strong>.
                </div>`;
            return;
        }

        filtered.forEach(person => {
            const role = (person.role || "student").toLowerCase();
            const isStaff = (role === "staff");
            const idVal = person.loginId || person.studentId || person.id || "N/A";
            const emailVal = person.email || "No Email Provided";

            const rowHTML = `
            <div class="people-row p-3 p-md-4">
                <div class="row g-3 align-items-center">
                    <!-- Column 1: User & Login ID -->
                    <div class="col-12 col-md-4">
                        <div class="d-flex align-items-center">
                            <div class="avatar-icon me-3 ${isStaff ? 'bg-danger bg-opacity-10 text-danger' : 'bg-primary bg-opacity-10 text-primary'}">
                                <i class="bi ${isStaff ? 'bi-shield-check' : 'bi-mortarboard'}"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-0 font-monospace">${idVal}</h6>
                                <span class="text-muted small">${isStaff ? 'Staff Account' : 'Student Account'}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Column 2: Gmail Account -->
                    <div class="col-12 col-md-4">
                        <div class="d-flex align-items-center text-secondary">
                            <i class="bi bi-envelope me-2 text-muted"></i>
                            <span class="fw-semibold text-dark text-break">${emailVal}</span>
                        </div>
                    </div>

                    <!-- Column 3: Role Badge -->
                    <div class="col-12 col-md-2">
                        <span class="badge ${isStaff ? 'bg-danger-subtle text-danger' : 'bg-primary-subtle text-primary'} rounded-pill px-3 py-2 fw-semibold text-uppercase">
                            ${role}
                        </span>
                    </div>

                    <!-- Column 4: Actions -->
                    <div class="col-12 col-md-2 text-md-end d-flex align-items-center justify-content-md-end gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="openEditPersonModal('${person.id}')" title="Edit Person">
                            <i class="bi bi-pencil me-1"></i>Edit
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger rounded-circle p-1 px-2" onclick="deletePerson('${person.id}', '${idVal}')" title="Remove User">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>`;

            container.innerHTML += rowHTML;
        });
    }

    /* ====================================================
       PEOPLE CRUD
       ==================================================== */
    window.openAddPersonModal = () => {
        document.getElementById('personModalTitle').innerHTML = `<i class="bi bi-person-plus text-primary me-2"></i>Add Authorized Person`;
        document.getElementById('person-mode').value = "add";
        document.getElementById('person-login-id').value = "";
        document.getElementById('person-email').value = "";
        document.getElementById('person-role').value = "student";
        const modal = new bootstrap.Modal(document.getElementById('personModal'));
        modal.show();
    };

    window.openEditPersonModal = (docId) => {
        const person = allPeople.find(p => p.id === docId);
        if (!person) return;

        document.getElementById('personModalTitle').innerHTML = `<i class="bi bi-pencil-square text-primary me-2"></i>Edit Authorized Person`;
        document.getElementById('person-mode').value = "edit";
        document.getElementById('person-doc-id').value = docId;
        document.getElementById('person-login-id').value = person.loginId || person.studentId || "";
        document.getElementById('person-email').value = person.email || "";
        document.getElementById('person-role').value = (person.role || "student").toLowerCase();

        const modal = new bootstrap.Modal(document.getElementById('personModal'));
        modal.show();
    };

    document.getElementById('person-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const mode = document.getElementById('person-mode').value;
        const docId = document.getElementById('person-doc-id').value;
        const loginId = document.getElementById('person-login-id').value.trim();
        const email = document.getElementById('person-email').value.trim();
        const role = document.getElementById('person-role').value;

        if (!loginId || !email) return;

        const btn = document.getElementById('btn-save-person');
        btn.disabled = true;
        btn.innerText = "Saving...";

        try {
            if (mode === 'add') {
                const newDocId = loginId.toLowerCase().replace(/[^a-z0-9]/g, '-');
                await setDoc(doc(db, "users", newDocId), {
                    loginId: loginId,
                    studentId: loginId,
                    email: email,
                    role: role,
                    createdAt: serverTimestamp()
                });
            } else if (mode === 'edit') {
                await updateDoc(doc(db, "users", docId), {
                    loginId: loginId,
                    studentId: loginId,
                    email: email,
                    role: role
                });
            }

            alert("User saved successfully!");
            const modal = bootstrap.Modal.getInstance(document.getElementById('personModal'));
            if (modal) modal.hide();

            await loadPeople();
        } catch (err) {
            alert("Error saving person: " + err.message);
        } finally {
            btn.disabled = false;
            btn.innerText = "Save Person";
        }
    });

    window.deletePerson = async (docId, displayId) => {
        if (confirm(`Are you sure you want to revoke login access and delete "${displayId}"?`)) {
            try {
                await deleteDoc(doc(db, "users", docId));
                await loadPeople();
            } catch (err) {
                alert("Error deleting user: " + err.message);
            }
        }
    };

    // Init
    loadPeople();
</script>

