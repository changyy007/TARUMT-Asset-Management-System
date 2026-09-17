<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TARUMT Asset Management - Sign In</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #1a1c1e 0%, #26292d 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem 1rem;
            font-family: system-ui, -apple-system, sans-serif;
        }
        .login-card { max-width: 420px; width: 100%; }
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }
    </style>
</head>
<body>

    <div class="card login-card border-0 shadow-lg rounded-4 p-4 p-sm-5 bg-white">

        <!-- Logo & Header -->
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-3" style="width: 60px; height: 60px;">
                <i class="bi bi-shield-lock-fill fs-2"></i>
            </div>
            <h2 class="h4 fw-bold text-dark mb-1">TARUMT Sign In</h2>
            <p class="text-muted small mb-0">Enter your Student / Staff ID and password</p>
        </div>

        <!-- Inactivity timeout alert -->
        <div id="timeout-alert" class="alert alert-warning py-2 px-3 small rounded-3 d-none mb-3 text-center" role="alert">
            <i class="bi bi-clock-history me-1"></i> You have been logged out due to 15 minutes of inactivity.
        </div>

        <!-- Error / Permission alerts -->
        <div id="login-error" class="alert alert-danger py-2 px-3 small rounded-3 d-none mb-3" role="alert"></div>
        <div id="permission-denied-message" class="alert alert-warning py-2 px-3 small rounded-3 d-none mb-3 text-center" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>
            <strong>You do not have permission to login.</strong>
        </div>

        <!-- Sign-In Form -->
        <form id="login-form" onsubmit="return false;">
            <div class="mb-3">
                <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Student / Staff ID</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-person-badge text-muted"></i></span>
                    <input type="text" id="login-id-input" required
                           placeholder="e.g. 2410848 or STF-001"
                           class="form-control border-start-0 ps-0" autocomplete="username">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                    <input type="password" id="password-input" required
                           placeholder="••••••••"
                           class="form-control border-start-0 ps-0" autocomplete="current-password">
                    <button class="btn btn-outline-secondary border-start-0" type="button" id="toggle-password" tabindex="-1">
                        <i class="bi bi-eye" id="eye-icon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" id="btn-login-submit"
                    class="btn btn-primary w-100 rounded-pill py-2 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2">
                <span>Sign In</span>
                <i class="bi bi-box-arrow-in-right"></i>
            </button>

            <!-- Forgot Password link -->
            <div class="text-center mt-3">
                <a href="javascript:void(0)" id="btn-forgot-password"
                   class="text-primary text-decoration-none small fw-semibold">
                    <i class="bi bi-question-circle me-1"></i>Forgot Password?
                </a>
            </div>
        </form>
    </div>

    <!-- ============================================================
         FORGOT PASSWORD MODAL
         ============================================================ -->
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-bottom px-4 py-3">
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center">
                        <i class="bi bi-key-fill text-warning me-2"></i>Reset Password
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="small text-muted mb-3">
                        Enter your registered email address. We will send a secure password reset link to your email inbox.
                    </p>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Registered Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-envelope text-muted"></i></span>
                            <input type="email" id="fp-email" class="form-control"
                                   placeholder="e.g. user@gmail.com" autocomplete="email">
                        </div>
                    </div>
                    <button type="button" id="btn-send-reset" class="btn btn-primary w-100 rounded-pill fw-semibold py-2">
                        <i class="bi bi-send me-1"></i>Send Reset Link
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script type="module">
        import { auth, db } from './js/config.js';
        import {
            signInWithEmailAndPassword,
            createUserWithEmailAndPassword,
            sendPasswordResetEmail,
            signOut
        } from "https://www.gstatic.com/firebasejs/10.10.0/firebase-auth.js";
        import {
            collection, query, where, getDocs, doc, getDoc
        } from "https://www.gstatic.com/firebasejs/10.10.0/firebase-firestore.js";

        const urlParams = new URLSearchParams(window.location.search);
        const redirectUrl = urlParams.get('redirect') ? decodeURIComponent(urlParams.get('redirect')) : "index.php";

        // Show inactivity timeout banner if redirected here by auth.js
        if (urlParams.has('timeout')) {
            document.getElementById('timeout-alert')?.classList.remove('d-none');
        }

        /* ============================================================
           PASSWORD TOGGLE
           ============================================================ */
        document.getElementById('toggle-password')?.addEventListener('click', () => {
            const input = document.getElementById('password-input');
            const icon = document.getElementById('eye-icon');
            if (!input || !icon) return;
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'bi bi-eye';
            }
        });

        /* ============================================================
           1. LOGIN WITH STUDENT / STAFF ID + PASSWORD
           ============================================================ */
        document.getElementById('login-form')?.addEventListener('submit', async (e) => {
            e.preventDefault();

            const loginId = document.getElementById('login-id-input').value.trim();
            const password = document.getElementById('password-input').value;
            const errDiv = document.getElementById('login-error');
            const permDiv = document.getElementById('permission-denied-message');
            const submitBtn = document.getElementById('btn-login-submit');

            errDiv.classList.add('d-none');
            permDiv.classList.add('d-none');
            submitBtn.disabled = true;
            submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Authenticating...`;

            try {
                let targetEmail = "";
                let userRole = "";
                let canonicalLoginId = loginId;

                // 1. Look up user by string loginId
                let snap = await getDocs(query(collection(db, "users"), where("loginId", "==", loginId)));

                // 2. Try integer loginId if input is numeric
                if (snap.empty && /^\d+$/.test(loginId)) {
                    snap = await getDocs(query(collection(db, "users"), where("loginId", "==", parseInt(loginId, 10))));
                }

                // 3. Try matching direct document ID (e.g. "stf-001" or "2410849")
                if (snap.empty) {
                    const slugId = loginId.toLowerCase().replace(/[^a-z0-9]/g, '-');
                    const directDoc = await getDoc(doc(db, "users", slugId));
                    if (directDoc.exists()) {
                        const d = directDoc.data();
                        targetEmail = d.email || "";
                        userRole = (d.role || "student").toLowerCase();
                        canonicalLoginId = String(d.loginId || loginId);
                    } else {
                        const directDocRaw = await getDoc(doc(db, "users", loginId));
                        if (directDocRaw.exists()) {
                            const d = directDocRaw.data();
                            targetEmail = d.email || "";
                            userRole = (d.role || "student").toLowerCase();
                            canonicalLoginId = String(d.loginId || loginId);
                        }
                    }
                }

                // 4. If user entered email directly as ID
                if (snap.empty && !targetEmail && loginId.includes('@')) {
                    snap = await getDocs(query(collection(db, "users"), where("email", "==", loginId.toLowerCase())));
                }

                // 5. Legacy studentId field lookup fallback
                if (snap.empty && !targetEmail) {
                    snap = await getDocs(query(collection(db, "users"), where("studentId", "==", loginId)));
                }

                if (!snap.empty && !targetEmail) {
                    const d = snap.docs[0].data();
                    targetEmail = d.email || "";
                    userRole = (d.role || "student").toLowerCase();
                    canonicalLoginId = String(d.loginId || d.studentId || loginId);
                }

                if (!targetEmail) {
                    throw new Error(`No account found with ID "${loginId}". Please check your Student / Staff ID.`);
                }

                // Authenticate with Firebase Auth
                let userCred;
                try {
                    userCred = await signInWithEmailAndPassword(auth, targetEmail, password);
                } catch (authErr) {
                    console.warn("Auth sign-in note:", authErr.code, authErr.message);

                    // If default password "000000" and user not yet created in Auth, auto-create
                    if (password === "000000" && (authErr.code === "auth/user-not-found" || authErr.code === "auth/invalid-credential")) {
                        try {
                            userCred = await createUserWithEmailAndPassword(auth, targetEmail, "000000");
                        } catch (createErr) {
                            if (createErr.code === "auth/email-already-in-use") {
                                throw new Error("Incorrect password. If you reset your password recently, please enter your new password.");
                            }
                            throw createErr;
                        }
                    } else if (authErr.code === "auth/wrong-password" || authErr.code === "auth/invalid-credential") {
                        throw new Error("Incorrect password. Please try again, or use 'Forgot Password?' below.");
                    } else if (authErr.code === "auth/user-not-found") {
                        throw new Error("No Auth account found for this user. Please contact administrator.");
                    } else if (authErr.code === "auth/too-many-requests") {
                        throw new Error("Too many failed attempts. Please wait a few minutes before trying again.");
                    } else {
                        throw new Error(authErr.message || "Failed to sign in. Please check your credentials.");
                    }
                }

                // Verify user role
                if (userRole !== "student" && userRole !== "staff") {
                    await signOut(auth);
                    permDiv.classList.remove('d-none');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = `<span>Sign In</span> <i class="bi bi-box-arrow-in-right"></i>`;
                    return;
                }

                // Persist login details for instant sidebar display (no async wait flash)
                sessionStorage.setItem('currentLoginEmail', targetEmail);
                sessionStorage.setItem('currentLoginId', canonicalLoginId);

                // Redirect
                window.location.href = redirectUrl;

            } catch (err) {
                console.error("Login error:", err);
                errDiv.innerText = err.message || "Failed to sign in. Please check your ID and password.";
                errDiv.classList.remove('d-none');
                submitBtn.disabled = false;
                submitBtn.innerHTML = `<span>Sign In</span> <i class="bi bi-box-arrow-in-right"></i>`;
            }
        });

        /* ============================================================
           2. FORGOT PASSWORD — handlePasswordReset(email)
           ============================================================ */
        async function handlePasswordReset(email) {
            if (!email || !email.includes('@')) {
                alert("Please enter a valid email address.");
                return;
            }
            try {
                await sendPasswordResetEmail(auth, email);
                alert("Password reset email sent! Please check your inbox (and spam folder).");
                const modalEl = document.getElementById('forgotPasswordModal');
                const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                modal.hide();
            } catch (error) {
                const errorCode = error.code;
                if (errorCode === 'auth/user-not-found') {
                    alert("No user found with this email address.");
                } else if (errorCode === 'auth/invalid-email') {
                    alert("Please enter a valid email address.");
                } else if (errorCode === 'auth/too-many-requests') {
                    alert("Too many requests. Please wait a few minutes before trying again.");
                } else {
                    alert(`An error occurred: ${error.message}`);
                }
            }
        }

        // Link handlePasswordReset to Forgot Password button
        document.getElementById('btn-forgot-password')?.addEventListener('click', () => {
            const emailInput = document.getElementById('fp-email');
            if (emailInput) emailInput.value = "";
            const modalEl = document.getElementById('forgotPasswordModal');
            const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            modal.show();
        });

        document.getElementById('btn-send-reset')?.addEventListener('click', async () => {
            const email = document.getElementById('fp-email').value.trim();
            const btn = document.getElementById('btn-send-reset');
            btn.disabled = true;
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Sending...`;
            await handlePasswordReset(email);
            btn.disabled = false;
            btn.innerHTML = `<i class="bi bi-send me-1"></i>Send Reset Link`;
        });

        // Allow pressing Enter key on email field in modal
        document.getElementById('fp-email')?.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('btn-send-reset')?.click();
            }
        });
    </script>
</body>
</html>
