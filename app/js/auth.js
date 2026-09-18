import { auth, db } from './config.js';
import { onAuthStateChanged, signOut } from "https://www.gstatic.com/firebasejs/10.10.0/firebase-auth.js";
import { doc, getDoc, collection, query, where, getDocs } from "https://www.gstatic.com/firebasejs/10.10.0/firebase-firestore.js";
import { isPublicPage, applyNavVisibility, redirectIfUnauthorized, currentPageName } from './permissions.js';

// 15-Minute Inactivity Auto-Logout Tracker
let lastActivity = Date.now();
const INACTIVITY_LIMIT_MS = 15 * 60 * 1000;

function resetActivityTimer() {
    lastActivity = Date.now();
}

['mousemove', 'mousedown', 'keydown', 'touchstart', 'scroll', 'click'].forEach(evt => {
    window.addEventListener(evt, resetActivityTimer, { passive: true });
});

setInterval(() => {
    if (auth.currentUser && (Date.now() - lastActivity > INACTIVITY_LIMIT_MS)) {
        signOut(auth).then(() => {
            window.location.href = "login.php?timeout=1";
        }).catch(console.error);
    }
}, 20000);

function wireProtectedLinks() {
    document.querySelectorAll('[data-requires-login]').forEach(el => {
        el.addEventListener('click', (e) => {
            if (window.currentUserRole) return;
            e.preventDefault();
            e.stopPropagation();
            const href = el.getAttribute('href') || el.dataset.href || currentPageName();
            const target = el.dataset.href || href;
            window.location.href = `login.php?redirect=${encodeURIComponent(target.startsWith('http') ? target : new URL(target, window.location.href).href)}`;
        });
    });
}

async function resolveUserRole(user) {
    let role = "student";
    try {
        const userDoc = await getDoc(doc(db, "users", user.uid));
        if (userDoc.exists() && userDoc.data().role) {
            return userDoc.data().role.toLowerCase();
        }
        const q = query(collection(db, "users"), where("email", "==", user.email));
        const snap = await getDocs(q);
        if (!snap.empty && snap.docs[0].data().role) {
            return snap.docs[0].data().role.toLowerCase();
        }
        if (user.email && (user.email.includes("admin") || user.email.includes("staff"))) {
            return "staff";
        }
    } catch (err) {
        console.warn("Could not fetch user role:", err);
    }
    return role;
}

onAuthStateChanged(auth, async (user) => {
    const userAvatar = document.getElementById('userAvatar');
    const sidebarEmail = document.getElementById('sidebarEmail');
    const logoutBtn = document.getElementById('logoutBtn');
    const authActionArea = document.getElementById('authActionArea');

    if (user) {
        const savedId = sessionStorage.getItem('currentLoginId');
        const displayLabel = savedId || user.email || "User";
        const initial = displayLabel.charAt(0).toUpperCase();
        if (userAvatar) userAvatar.innerText = initial;

        if (sidebarEmail) {
            sidebarEmail.innerText = displayLabel;
            sidebarEmail.title = user.email || "";
        }

        const role = await resolveUserRole(user);
        window.currentUserRole = role;
        if (logoutBtn) logoutBtn.classList.remove('d-none');
        if (authActionArea) authActionArea.innerHTML = "";

        applyNavVisibility(role);
        redirectIfUnauthorized(role);
        wireProtectedLinks();
        window.dispatchEvent(new CustomEvent("userRoleReady", { detail: { role, user } }));

    } else {
        sessionStorage.removeItem('currentLoginEmail');
        sessionStorage.removeItem('currentLoginId');
        window.currentUserRole = null;
        if (userAvatar) userAvatar.innerText = "?";
        if (sidebarEmail) sidebarEmail.innerText = "Guest User";
        if (logoutBtn) logoutBtn.classList.add('d-none');
        if (authActionArea) {
            authActionArea.innerHTML = `<a href="login.php" class="btn btn-sm btn-outline-light rounded-pill w-100 mt-2"><i class="bi bi-box-arrow-in-right me-1"></i> Sign In</a>`;
        }

        applyNavVisibility(null);
        wireProtectedLinks();

        //bypass
        const path = window.location.pathname.toLowerCase();
        const isHomePage = path.includes('index.php') || path.endsWith('/');

        if (!isHomePage && !isPublicPage(currentPageName())) {
            console.warn("Redirecting to login. Path:", path);
            const returnUrl = encodeURIComponent(window.location.href);
            window.location.href = `login.php?redirect=${returnUrl}`;
        } else {
            console.log("Allowed public access to:", path);
            window.dispatchEvent(new CustomEvent("userRoleReady", { detail: { role: null, user: null } }));
        }
    }
});

document.addEventListener('DOMContentLoaded', () => {
    wireProtectedLinks();
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', () => {
            signOut(auth).then(() => {
                window.location.href = "login.php";
            }).catch(console.error);
        });
    }
});
