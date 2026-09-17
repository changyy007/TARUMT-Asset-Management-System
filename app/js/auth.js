import { auth, db } from './config.js';
import { onAuthStateChanged, signOut } from "https://www.gstatic.com/firebasejs/10.10.0/firebase-auth.js";
import { doc, getDoc, collection, query, where, getDocs } from "https://www.gstatic.com/firebasejs/10.10.0/firebase-firestore.js";

// Check if current page is guest-accessible
const currentFile = window.location.pathname.split("/").pop() || "index.php";
const urlParams = new URLSearchParams(window.location.search);
const isPublicPage = currentFile === "login.php" || 
                     currentFile === "info.php" || 
                     currentFile === "report.php";

// 15-Minute Inactivity Auto-Logout Tracker
let lastActivity = Date.now();
const INACTIVITY_LIMIT_MS = 15 * 60 * 1000; // 15 minutes

function resetActivityTimer() {
    lastActivity = Date.now();
}

['mousemove', 'mousedown', 'keydown', 'touchstart', 'scroll', 'click'].forEach(evt => {
    window.addEventListener(evt, resetActivityTimer, { passive: true });
});

// Periodic inactivity check
setInterval(() => {
    if (auth.currentUser && (Date.now() - lastActivity > INACTIVITY_LIMIT_MS)) {
        signOut(auth).then(() => {
            window.location.href = "login.php?timeout=1";
        }).catch(console.error);
    }
}, 20000);

// Auth State Monitor
onAuthStateChanged(auth, async (user) => {
    const userAvatar = document.getElementById('userAvatar');
    const sidebarEmail = document.getElementById('sidebarEmail');
    const logoutBtn = document.getElementById('logoutBtn');
    const authActionArea = document.getElementById('authActionArea');

    if (user) {
        const initial = (user.email || 'U').charAt(0).toUpperCase();
        const savedId = sessionStorage.getItem('currentLoginId');
        const displayLabel = savedId || user.email || "User";
        const initial = displayLabel.charAt(0).toUpperCase();
        if (userAvatar) userAvatar.innerText = initial;
        
        // Show current user gmail / email account cleanly as requested
        if (sidebarEmail) {
            sidebarEmail.innerText = user.email || "No Email";
            sidebarEmail.innerText = displayLabel;
            sidebarEmail.title = user.email || "";
        }

        // Fetch User Role from Firestore
        let role = "student";
        try {
            const userDoc = await getDoc(doc(db, "users", user.uid));
            if (userDoc.exists() && userDoc.data().role) {
                role = userDoc.data().role.toLowerCase();
            } else {
                const q = query(collection(db, "users"), where("email", "==", user.email));
                const snap = await getDocs(q);
                if (!snap.empty && snap.docs[0].data().role) {
                    role = snap.docs[0].data().role.toLowerCase();
                } else if (user.email && (user.email.includes("admin") || user.email.includes("staff"))) {
                    role = "staff";
                } else {
                    role = "staff"; // Default to staff so features are available
                }
            }
        } catch (err) {
            console.warn("Could not fetch user role:", err);
            role = "staff";
        }

        window.currentUserRole = role;
        if (logoutBtn) logoutBtn.classList.remove('d-none');
        if (authActionArea) authActionArea.innerHTML = "";

        // Trigger event for page components
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

        // If on a protected page, immediately redirect to login.php
        if (!isPublicPage) {
            const returnUrl = encodeURIComponent(window.location.href);
            window.location.href = `login.php?redirect=${returnUrl}`;
        }
    }
});

// Logout Handler
document.addEventListener('DOMContentLoaded', () => {
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', () => {
            signOut(auth).then(() => {
                window.location.href = "login.php";
            }).catch(console.error);
        });
    }
});