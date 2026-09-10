import { auth, db } from './config.js';
import { onAuthStateChanged, signOut } from "https://www.gstatic.com/firebasejs/10.10.0/firebase-auth.js";
import { doc, getDoc } from "https://www.gstatic.com/firebasejs/10.10.0/firebase-firestore.js";

// Auth State Monitor
onAuthStateChanged(auth, async (user) => {
    const userAvatar = document.getElementById('userAvatar');
    const topbarUserAvatar = document.getElementById('topbarUserAvatar');
    const sidebarEmail = document.getElementById('sidebarEmail');
    const sidebarRole = document.getElementById('sidebarRole');
    const logoutBtn = document.getElementById('logoutBtn');
    const authActionArea = document.getElementById('authActionArea');

    if (user) {
        const initial = (user.email || 'U').charAt(0).toUpperCase();
        if (userAvatar) userAvatar.innerText = initial;
        if (topbarUserAvatar) topbarUserAvatar.innerText = initial;
        if (sidebarEmail) sidebarEmail.innerText = user.email;

        // Fetch User Role from Firestore
        try {
            const userDoc = await getDoc(doc(db, "users", user.uid));
            const role = (userDoc.exists() && userDoc.data().role) ? userDoc.data().role : "Staff / Student";
            if (sidebarRole) sidebarRole.innerText = role;
            window.currentUserRole = role;
        } catch (err) {
            console.warn("Could not fetch user role:", err);
            if (sidebarRole) sidebarRole.innerText = "Member";
        }

        if (logoutBtn) logoutBtn.classList.remove('d-none');
    } else {
        if (userAvatar) userAvatar.innerText = "?";
        if (topbarUserAvatar) topbarUserAvatar.innerText = "?";
        if (sidebarEmail) sidebarEmail.innerText = "Guest User";
        if (sidebarRole) sidebarRole.innerText = "Visitor";
        if (logoutBtn) logoutBtn.classList.add('d-none');
        if (authActionArea) {
            authActionArea.innerHTML = `<a href="login.php" class="btn btn-sm btn-outline-light rounded-pill w-100 mt-2"><i class="bi bi-box-arrow-in-right me-1"></i> Sign In</a>`;
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