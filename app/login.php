<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TARUMT Asset System</title>
    <!-- Bootstrap 5.3.0 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #1a1c1e;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.25rem;
        }
        .login-card {
            max-width: 400px;
            width: 100%;
        }
    </style>
</head>
<body>

    <div class="card login-card border-0 shadow-lg rounded-4 p-3 p-sm-4 bg-white">
        <div class="text-center mb-4 mt-2">
            <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-3" style="width: 56px; height: 56px;">
                <i class="bi bi-shield-lock-fill fs-3"></i>
            </div>
            <h2 class="h4 fw-bold text-dark mb-1">TARUMT Asset Login</h2>
            <p class="text-muted small mb-0">Sign in to manage campus assets & reports</p>
        </div>
        
        <div id="login-error" class="alert alert-danger py-2 px-3 small rounded-3 d-none mb-3" role="alert"></div>

        <form id="login-form">
            <div class="mb-3">
                <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Email</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                    <input type="email" id="email" required placeholder="admin@tarumt.edu.my" class="form-control border-start-0 ps-0">
                </div>
            </div>
            
            <div class="mb-4">
                <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                    <input type="password" id="password" required placeholder="••••••••" class="form-control border-start-0 ps-0">
                </div>
            </div>
            
            <button type="submit" id="btn-login-submit" class="btn btn-primary w-100 rounded-pill py-2 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2">
                <span>Sign In</span>
                <i class="bi bi-box-arrow-in-right"></i>
            </button>
            
            <div class="text-center mt-3">
                <a href="inventory.php" class="text-decoration-none small text-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back to Campus Inventory
                </a>
            </div>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script type="module">
        import { auth } from './js/config.js';
        import { signInWithEmailAndPassword } from "https://www.gstatic.com/firebasejs/10.10.0/firebase-auth.js";

        document.getElementById('login-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const errDiv = document.getElementById('login-error');
            const submitBtn = document.getElementById('btn-login-submit');

            errDiv.classList.add('d-none');
            submitBtn.disabled = true;
            submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Signing in...`;

            try {
                await signInWithEmailAndPassword(auth, email, password);
                window.location.href = "inventory.php";
            } catch (err) {
                console.error("Login error:", err);
                errDiv.innerText = err.message || "Failed to sign in. Please check credentials.";
                errDiv.classList.remove('d-none');
                submitBtn.disabled = false;
                submitBtn.innerHTML = `<span>Sign In</span> <i class="bi bi-box-arrow-in-right"></i>`;
            }
        });
    </script>
</body>
</html>