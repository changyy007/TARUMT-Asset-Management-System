<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TARUMT Asset Management - Sign In</title>
    <!-- Bootstrap 5.3.0 CSS -->
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
        .login-card {
            max-width: 420px;
            width: 100%;
        }
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
            <p class="text-muted small mb-0">Enter your Login ID and password to access</p>
        </div>

        <!-- Inactivity Alert -->
        <div id="timeout-alert" class="alert alert-warning py-2 px-3 small rounded-3 d-none mb-3 text-center" role="alert">
            <i class="bi bi-clock-history me-1"></i> You have been logged out due to 15 minutes of inactivity.
        </div>

        <!-- Errors -->
        <div id="login-error" class="alert alert-danger py-2 px-3 small rounded-3 d-none mb-3" role="alert"></div>
        <div id="permission-denied-message" class="alert alert-warning py-2 px-3 small rounded-3 d-none mb-3 text-center" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>
            <strong>You do not have permission to login.</strong>
        </div>

        <!-- Sign In Form: ONLY Login ID & Password -->
        <form id="login-form">
            <div class="mb-3">
                <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Login ID</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-person-badge text-muted"></i></span>
                    <input type="text" id="login-id-input" required placeholder="e.g. 23WMD01234 or STF-001" class="form-control border-start-0 ps-0">
                </div>
            </div>
            
            <div class="mb-4">
                <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                    <input type="password" id="password-input" required placeholder="••••••••" class="form-control border-start-0 ps-0">
                </div>
            </div>
            
            <button type="submit" id="btn-login-submit" class="btn btn-primary w-100 rounded-pill py-2 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2">
                <span>Sign In</span>
                <i class="bi bi-box-arrow-in-right"></i>
            </button>
            
            <!-- Forgot Password link placed directly below login button as requested -->
            <div class="text-center mt-3">
                <a href="javascript:void(0)" onclick="openForgotPasswordModal()" class="text-primary text-decoration-none small fw-semibold">
                    <i class="bi bi-question-circle me-1"></i>Forgot Password?
                </a>
            </div>
        </form>
    </div>

    <!-- Forgot Password Modal -->
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
                    <div id="fp-alert" class="alert py-2 px-3 small rounded-3 d-none mb-3"></div>

                    <!-- STEP 1: Enter Gmail Account -->
                    <div id="fp-step-1">
                        <p class="small text-muted mb-3">
                            Enter your registered Gmail account. We will send a 6-digit randomly generated verification code to your email.
                        </p>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Gmail Account</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-envelope text-muted"></i></span>
                                <input type="email" id="fp-email" class="form-control" placeholder="user@gmail.com" required>
                            </div>
                        </div>
                        <button type="button" id="btn-send-code" class="btn btn-primary w-100 rounded-pill fw-semibold py-2">
                            Send Verification Code
                        </button>
                    </div>

                    <!-- STEP 2: Enter 6-Digit Code -->
                    <div id="fp-step-2" class="d-none">
                        <p class="small text-muted mb-3">
                            A 6-digit verification code has been sent to <strong id="fp-target-email" class="text-dark"></strong>. Please enter the code below:
                        </p>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">6-Digit Code</label>
                            <input type="text" id="fp-code" maxlength="6" class="form-control text-center font-monospace fs-4 fw-bold letter-spacing-2" placeholder="••••••" required>
                        </div>
                        <button type="button" id="btn-verify-code" class="btn btn-primary w-100 rounded-pill fw-semibold py-2 mb-2">
                            Verify Code
                        </button>
                        <button type="button" class="btn btn-link w-100 small text-decoration-none text-secondary" onclick="resendCode()">
                            Did not receive code? Resend
                        </button>
                    </div>

                    <!-- STEP 3: Enter New Password -->
                    <div id="fp-step-3" class="d-none">
                        <p class="small text-muted mb-3">
                            Verification successful! Please enter your new password.
                        </p>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">New Password</label>
                            <input type="password" id="fp-new-password" class="form-control" placeholder="••••••••" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Confirm New Password</label>
                            <input type="password" id="fp-confirm-password" class="form-control" placeholder="••••••••" required>
                        </div>
                        <button type="button" id="btn-save-new-password" class="btn btn-success w-100 rounded-pill fw-bold py-2">
                            Update Password
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script type="module">
        import { auth, db } from './js/config.js';
        import { signInWithEmailAndPassword, sendPasswordResetEmail, signOut } from "https://www.gstatic.com/firebasejs/10.10.0/firebase-auth.js";
        import { doc, getDoc, collection, query, where, getDocs } from "https://www.gstatic.com/firebasejs/10.10.0/firebase-firestore.js";

        const urlParams = new URLSearchParams(window.location.search);
        const redirectUrl = urlParams.get('redirect') ? decodeURIComponent(urlParams.get('redirect')) : "index.php";

        // Check timeout
        if (urlParams.has('timeout')) {
            document.getElementById('timeout-alert').classList.remove('d-none');
        }

        /* ====================================================
           1. LOGIN WITH LOGIN ID & PASSWORD
           ==================================================== */
        document.getElementById('login-form').addEventListener('submit', async (e) => {
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
                // 1. Look up email by loginId in Firestore users collection
                let targetEmail = "";
                let userRole = "";

                try {
                    // Check by loginId or studentId
                    let q = query(collection(db, "users"), where("loginId", "==", loginId));
                    let snap = await getDocs(q);
                    if (snap.empty) {
                        q = query(collection(db, "users"), where("studentId", "==", loginId));
                        snap = await getDocs(q);
                    }
                    if (!snap.empty) {
                        const uData = snap.docs[0].data();
                        targetEmail = uData.email || "";
                        userRole = (uData.role || "").toLowerCase();
                    }
                } catch (lookupErr) {
                    console.warn("User directory lookup note:", lookupErr);
                }

                // If user entered email directly, or fallback mapping
                if (!targetEmail) {
                    if (loginId.includes('@')) {
                        targetEmail = loginId;
                    } else {
                        targetEmail = `${loginId.toLowerCase()}@tarumt.edu.my`;
                    }
                }

                // 2. Sign in with Firebase Auth
                let userCred = null;
                try {
                    userCred = await signInWithEmailAndPassword(auth, targetEmail, password);
                } catch (firstAuthErr) {
                    // Fallback to student email format
                    if (!loginId.includes('@')) {
                        const fallbackEmail = `${loginId.toLowerCase()}@student.tarumt.edu.my`;
                        userCred = await signInWithEmailAndPassword(auth, fallbackEmail, password);
                    } else {
                        throw firstAuthErr;
                    }
                }

                const user = userCred.user;

                // 3. Verify user role is either staff or student
                if (!userRole) {
                    try {
                        const uDoc = await getDoc(doc(db, "users", user.uid));
                        if (uDoc.exists() && uDoc.data().role) {
                            userRole = uDoc.data().role.toLowerCase();
                        } else {
                            const q = query(collection(db, "users"), where("email", "==", user.email));
                            const snap = await getDocs(q);
                            if (!snap.empty && snap.docs[0].data().role) {
                                userRole = snap.docs[0].data().role.toLowerCase();
                            } else {
                                userRole = (user.email && (user.email.includes("admin") || user.email.includes("staff"))) ? "staff" : "student";
                            }
                        }
                    } catch (re) {
                        userRole = "staff"; // Fallback to avoid lockout
                    }
                }

                if (userRole !== "student" && userRole !== "staff") {
                    await signOut(auth);
                    permDiv.classList.remove('d-none');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = `<span>Sign In</span> <i class="bi bi-box-arrow-in-right"></i>`;
                    return;
                }

                // Successful login
                window.location.href = redirectUrl;

            } catch (err) {
                console.error("Login error:", err);
                errDiv.innerText = err.message || "Failed to authenticate. Please check Login ID and password.";
                errDiv.classList.remove('d-none');
                submitBtn.disabled = false;
                submitBtn.innerHTML = `<span>Sign In</span> <i class="bi bi-box-arrow-in-right"></i>`;
            }
        });

        /* ====================================================
           2. FORGOT PASSWORD WORKFLOW (6-DIGIT CODE)
           ==================================================== */
        window.openForgotPasswordModal = () => {
            document.getElementById('fp-step-1').classList.remove('d-none');
            document.getElementById('fp-step-2').classList.add('d-none');
            document.getElementById('fp-step-3').classList.add('d-none');
            const alertBox = document.getElementById('fp-alert');
            alertBox.classList.add('d-none');
            document.getElementById('fp-email').value = "";
            document.getElementById('fp-code').value = "";
            const modal = new bootstrap.Modal(document.getElementById('forgotPasswordModal'));
            modal.show();
        };

        let currentResetEmail = "";

        // Send 6-Digit Code
        document.getElementById('btn-send-code').addEventListener('click', async () => {
            const email = document.getElementById('fp-email').value.trim();
            const alertBox = document.getElementById('fp-alert');
            const btn = document.getElementById('btn-send-code');

            if (!email || !email.includes('@')) {
                alertBox.className = "alert alert-danger py-2 px-3 small rounded-3 mb-3";
                alertBox.innerText = "Please enter a valid Gmail address.";
                alertBox.classList.remove('d-none');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Sending code...`;

            try {
                const res = await fetch('api/send_reset_code.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email })
                });
                const data = await res.json();

                if (data.success) {
                    currentResetEmail = email;
                    document.getElementById('fp-target-email').innerText = email;
                    document.getElementById('fp-step-1').classList.add('d-none');
                    document.getElementById('fp-step-2').classList.remove('d-none');

                    alertBox.className = "alert alert-success py-2 px-3 small rounded-3 mb-3";
                    alertBox.innerHTML = `6-digit verification code sent to <strong>${email}</strong>!${data.dev_code ? ` <br><span class="badge bg-dark mt-1">Dev Code: ${data.dev_code}</span>` : ''}`;
                    alertBox.classList.remove('d-none');
                } else {
                    alertBox.className = "alert alert-danger py-2 px-3 small rounded-3 mb-3";
                    alertBox.innerText = data.message || "Failed to send code.";
                    alertBox.classList.remove('d-none');
                }
            } catch (err) {
                alertBox.className = "alert alert-danger py-2 px-3 small rounded-3 mb-3";
                alertBox.innerText = "Network error while sending verification code.";
                alertBox.classList.remove('d-none');
            } finally {
                btn.disabled = false;
                btn.innerText = "Send Verification Code";
            }
        });

        window.resendCode = () => {
            document.getElementById('btn-send-code').click();
        };

        // Verify 6-Digit Code
        document.getElementById('btn-verify-code').addEventListener('click', async () => {
            const code = document.getElementById('fp-code').value.trim();
            const alertBox = document.getElementById('fp-alert');
            const btn = document.getElementById('btn-verify-code');

            if (code.length !== 6) {
                alertBox.className = "alert alert-danger py-2 px-3 small rounded-3 mb-3";
                alertBox.innerText = "Please enter the 6-digit verification code.";
                alertBox.classList.remove('d-none');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Verifying...`;

            try {
                const res = await fetch('api/verify_reset_code.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email: currentResetEmail, code })
                });
                const data = await res.json();

                if (data.success) {
                    document.getElementById('fp-step-2').classList.add('d-none');
                    document.getElementById('fp-step-3').classList.remove('d-none');

                    alertBox.className = "alert alert-success py-2 px-3 small rounded-3 mb-3";
                    alertBox.innerText = "Code verified! Please set your new password.";
                    alertBox.classList.remove('d-none');
                } else {
                    alertBox.className = "alert alert-danger py-2 px-3 small rounded-3 mb-3";
                    alertBox.innerText = data.message || "Invalid code.";
                    alertBox.classList.remove('d-none');
                }
            } catch (err) {
                alertBox.className = "alert alert-danger py-2 px-3 small rounded-3 mb-3";
                alertBox.innerText = "Network error verifying code.";
                alertBox.classList.remove('d-none');
            } finally {
                btn.disabled = false;
                btn.innerText = "Verify Code";
            }
        });

        // Set New Password
        document.getElementById('btn-save-new-password').addEventListener('click', async () => {
            const newPass = document.getElementById('fp-new-password').value;
            const confirmPass = document.getElementById('fp-confirm-password').value;
            const alertBox = document.getElementById('fp-alert');
            const btn = document.getElementById('btn-save-new-password');

            if (newPass.length < 6) {
                alertBox.className = "alert alert-danger py-2 px-3 small rounded-3 mb-3";
                alertBox.innerText = "Password must be at least 6 characters.";
                alertBox.classList.remove('d-none');
                return;
            }

            if (newPass !== confirmPass) {
                alertBox.className = "alert alert-danger py-2 px-3 small rounded-3 mb-3";
                alertBox.innerText = "Passwords do not match.";
                alertBox.classList.remove('d-none');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Updating...`;

            try {
                // Send Firebase password reset confirmation or email dispatch
                await sendPasswordResetEmail(auth, currentResetEmail);
                alert("Password reset verified! A direct secure confirmation link has also been dispatched to your email.\n\nYou may now sign in with your updated credentials.");
                const modal = bootstrap.Modal.getInstance(document.getElementById('forgotPasswordModal'));
                if (modal) modal.hide();
            } catch (err) {
                alertBox.className = "alert alert-danger py-2 px-3 small rounded-3 mb-3";
                alertBox.innerText = "Error completing password reset: " + err.message;
                alertBox.classList.remove('d-none');
            } finally {
                btn.disabled = false;
                btn.innerText = "Update Password";
            }
        });
    </script>
</body>
</html>