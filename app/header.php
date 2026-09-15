<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TARUMT Campus Facility Management System</title>
    <meta name="description" content="TARUMT Asset Management System for managing campus inventory, maintenance, financial reports, utilities, and user directory.">
    <!-- Bootstrap 5.3.0 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <!-- Bootstrap 5.3.0 JS loaded in head for instant mobile offcanvas responsiveness -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        :root {
            --gemini-sidebar-width: 270px;
            --gemini-bg-dark: #1a1c1e;
            --gemini-bg-surface: #26292d;
            --gemini-border: #35383d;
        }
        body {
            background-color: #f4f6f9;
            color: #212529;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            min-height: 100vh;
        }
        /* Gemini Sidebar Drawer */
        .gemini-sidebar {
            width: var(--gemini-sidebar-width) !important;
            background-color: var(--gemini-bg-dark) !important;
            border-right: 1px solid var(--gemini-border);
        }
        @media (min-width: 992px) {
            .gemini-sidebar.offcanvas-lg {
                position: fixed;
                top: 57px; /* Below top navbar */
                bottom: 0;
                left: 0;
                z-index: 1020;
                visibility: visible !important;
                transform: none !important;
            }
            .main-wrapper {
                margin-left: var(--gemini-sidebar-width);
                min-height: calc(100vh - 57px);
                display: flex;
                flex-direction: column;
                transition: margin-left 0.25s ease;
            }
            body.sidebar-collapsed .gemini-sidebar.offcanvas-lg {
                transform: translateX(-100%) !important;
                visibility: hidden !important;
            }
            body.sidebar-collapsed .main-wrapper {
                margin-left: 0 !important;
            }
        }
        @media (max-width: 991.98px) {
            .main-wrapper {
                margin-left: 0 !important;
                min-height: calc(100vh - 57px);
                display: flex;
                flex-direction: column;
            }
        }
        .gemini-nav-item .nav-link {
            border-radius: 50rem;
            padding: 0.65rem 1.15rem;
            color: #c4c7c5;
            transition: all 0.2s ease;
            font-size: 0.95rem;
        }
        .gemini-nav-item .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.08);
            color: #ffffff;
        }
        .gemini-nav-item .nav-link.active {
            background-color: #0d6efd;
            color: #ffffff !important;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.35);
        }
        .user-avatar-circle {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0d6efd, #7952b3);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1rem;
            flex-shrink: 0;
        }
        /* Top Navigation */
        .gemini-topbar {
            background-color: var(--gemini-bg-dark);
            border-bottom: 1px solid var(--gemini-border);
            height: 57px;
        }
    </style>
</head>
<body>

<!-- Top Navigation Bar -->
<nav class="navbar navbar-dark gemini-topbar sticky-top px-3 py-2 shadow-sm">
    <div class="container-fluid p-0 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <!-- Mobile Toggle: triggers Offcanvas sidebar on phone -->
            <button class="btn btn-outline-secondary border-0 text-white p-2 rounded-circle me-2 d-lg-none" type="button" onclick="toggleMobileSidebar()" aria-label="Open sidebar menu">
                <i class="bi bi-list fs-3"></i>
            </button>
            <!-- Desktop Toggle: collapses/expands docked sidebar -->
            <button class="btn btn-outline-secondary border-0 text-white p-2 rounded-circle me-2 d-none d-lg-inline-flex align-items-center justify-content-center" type="button" onclick="document.body.classList.toggle('sidebar-collapsed')" title="Toggle Sidebar">
                <i class="bi bi-list fs-4"></i>
            </button>
            <a href="index.php" class="navbar-brand mb-0 fw-bold fs-6 d-flex align-items-center text-white text-decoration-none">
                <i class="bi bi-boxes text-primary me-2 fs-5"></i>
                <span>TARUMT Asset Management</span>
            </a>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-secondary bg-opacity-25 text-light rounded-pill px-3 py-2 small d-none d-md-inline-block">
                <i class="bi bi-check-circle-fill text-success me-1"></i>System Online
            </span>
        </div>
    </div>
</nav>

<!-- Sidebar Drawer (Offcanvas on mobile, fixed docked on desktop) -->
<div class="offcanvas-lg offcanvas-start gemini-sidebar text-white" tabindex="-1" id="geminiSidebar" aria-labelledby="geminiSidebarLabel">
    <div class="offcanvas-header border-bottom border-secondary border-opacity-25 px-4 py-3 d-lg-none">
        <div class="d-flex align-items-center">
            <i class="bi bi-boxes text-primary fs-4 me-2"></i>
            <h5 class="offcanvas-title fw-bold text-white mb-0" id="geminiSidebarLabel">Menu</h5>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#geminiSidebar" aria-label="Close" onclick="closeMobileSidebar()"></button>
    </div>

    <div class="offcanvas-body d-flex flex-column p-3 h-100">
        <!-- Navigation Links: Home, Inventory, Maintenance, Financial, Utility, People -->
        <ul class="nav nav-pills flex-column mb-auto gap-1">
            <li class="nav-item gemini-nav-item">
                <a href="index.php" class="nav-link d-flex align-items-center <?= ($currentPage == 'index.php') ? 'active' : '' ?>">
                    <i class="bi bi-house-door me-3 fs-5"></i>
                    <span>Home</span>
                </a>
            </li>
            <li class="nav-item gemini-nav-item">
                <a href="inventory.php" class="nav-link d-flex align-items-center <?= ($currentPage == 'inventory.php') ? 'active' : '' ?>">
                    <i class="bi bi-collection me-3 fs-5"></i>
                    <span>Inventory</span>
                </a>
            </li>
            <li class="nav-item gemini-nav-item">
                <a href="maintenance.php" class="nav-link d-flex align-items-center <?= ($currentPage == 'maintenance.php' || $currentPage == 'report_detail.php') ? 'active' : '' ?>">
                    <i class="bi bi-tools me-3 fs-5"></i>
                    <span>Maintenance</span>
                </a>
            </li>
            <li class="nav-item gemini-nav-item">
                <a href="financial.php" class="nav-link d-flex align-items-center <?= ($currentPage == 'financial.php') ? 'active' : '' ?>">
                    <i class="bi bi-cash-stack me-3 fs-5"></i>
                    <span>Financial</span>
                </a>
            </li>
            <li class="nav-item gemini-nav-item">
                <a href="utility.php" class="nav-link d-flex align-items-center <?= ($currentPage == 'utility.php') ? 'active' : '' ?>">
                    <i class="bi bi-building me-3 fs-5"></i>
                    <span>Utility</span>
                </a>
            </li>
            <li class="nav-item gemini-nav-item">
                <a href="people.php" class="nav-link d-flex align-items-center <?= ($currentPage == 'people.php') ? 'active' : '' ?>">
                    <i class="bi bi-people me-3 fs-5"></i>
                    <span>People</span>
                </a>
            </li>
        </ul>

        <!-- Bottom User Profile Section: Shows current user Gmail account cleanly -->
        <div class="border-top border-secondary border-opacity-25 pt-3 mt-auto">
            <div class="d-flex align-items-center p-2 rounded-3" style="background-color: var(--gemini-bg-surface);">
                <div class="user-avatar-circle me-2" id="userAvatar">?</div>
                <div class="flex-grow-1 overflow-hidden">
                    <div class="fw-semibold text-truncate small text-white" id="sidebarEmail">User</div>
                </div>
                <script>
                // Immediately populate sidebar from sessionStorage – no async wait needed
                (function() {
                    var em = sessionStorage.getItem('currentLoginEmail') || '';
                    var id = sessionStorage.getItem('currentLoginId') || '';
                    var label = id || em || 'User';
                    var el = document.getElementById('sidebarEmail');
                    var av = document.getElementById('userAvatar');
                    if (el && label !== 'User') {
                        el.innerText = label;
                        el.title = em;
                    }
                    if (av && label !== 'User') {
                        av.innerText = label.charAt(0).toUpperCase();
                    }
                })();
                </script>
                <button type="button" id="logoutBtn" class="btn btn-outline-danger btn-sm p-1 px-2 rounded-circle ms-1 d-none" title="Sign Out">
                    <i class="bi bi-box-arrow-right"></i>
                </button>
            </div>
            <div id="authActionArea"></div>
        </div>
    </div>
</div>

<script>
// Robust Mobile Sidebar Open/Close Handler for Phone Browsers
function toggleMobileSidebar() {
    const sidebar = document.getElementById('geminiSidebar');
    if (!sidebar) return;
    try {
        const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(sidebar);
        offcanvas.toggle();
    } catch (e) {
        sidebar.classList.toggle('show');
    }
}

function closeMobileSidebar() {
    const sidebar = document.getElementById('geminiSidebar');
    if (!sidebar) return;
    try {
        const offcanvas = bootstrap.Offcanvas.getInstance(sidebar);
        if (offcanvas) offcanvas.hide();
    } catch (e) {
        sidebar.classList.remove('show');
    }
}
</script>

<!-- Main Content Area Wrapper -->
<div class="main-wrapper">
    <main class="main-content flex-grow-1 p-3 p-md-4">