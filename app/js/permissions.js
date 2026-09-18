/** Pages anyone (including guests) may open without signing in. */
export const PUBLIC_PAGES = new Set(['login.php', 'index.php', 'info.php', 'report.php']);

/** Pages students may view (read-only). Staff-only: financial.php, people.php */
export const STUDENT_ALLOWED_PAGES = new Set([
    'index.php', 'inventory.php', 'maintenance.php', 'utility.php',
    'report_detail.php', 'info.php', 'report.php', 'login.php'
]);

export const STAFF_ONLY_PAGES = new Set(['financial.php', 'people.php']);

export function currentPageName() {
    const page = window.location.pathname.split('/').pop();
    
    // If the URL is just a folder name or doesn't end in .php, default to index.php
    if (page === '' || !page.endsWith('.php')) {
        return 'index.php';
    }
    
    return page;
}

export function isPublicPage(page = currentPageName()) {
    return PUBLIC_PAGES.has(page);
}

export function canStudentAccess(page = currentPageName()) {
    return STUDENT_ALLOWED_PAGES.has(page);
}

export function applyNavVisibility(role) {
    const isGuest = !role;
    const isStaff = role === 'staff';

    document.querySelectorAll('[data-nav-financial], [data-nav-people]').forEach(el => {
        el.classList.toggle('d-none', !isStaff);
    });

    document.querySelectorAll('[data-requires-auth]').forEach(el => {
        el.classList.toggle('d-none', isGuest);
    });

    document.querySelectorAll('[data-staff-only]').forEach(el => {
        el.classList.toggle('d-none', !isStaff);
    });
}

export function redirectIfUnauthorized(role) {
    const page = currentPageName();
    if (isPublicPage(page)) return;

    if (!role) {
        const returnUrl = encodeURIComponent(window.location.href);
        window.location.href = `login.php?redirect=${returnUrl}`;
        return;
    }

    if (role === 'student' && STAFF_ONLY_PAGES.has(page)) {
        alert('This module is restricted to staff accounts.');
        window.location.href = 'index.php';
    }
}
