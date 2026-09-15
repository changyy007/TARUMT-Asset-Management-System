import { initializeApp, getApps, getApp } from "https://www.gstatic.com/firebasejs/10.10.0/firebase-app.js";
import { getFirestore, doc, getDoc, collection, query, where, getDocs } from "https://www.gstatic.com/firebasejs/10.10.0/firebase-firestore.js";
import { getAuth, setPersistence, browserSessionPersistence, onAuthStateChanged, signOut } from "https://www.gstatic.com/firebasejs/10.10.0/firebase-auth.js";
import { getStorage } from "https://www.gstatic.com/firebasejs/10.10.0/firebase-storage.js";

export const firebaseConfig = {
  apiKey: "AIzaSyDuvpuStG61ywn1eOx-Q-XHHe866-t_5GQ",
  authDomain: "assetmanagementsystem-86fcf.firebaseapp.com",
  projectId: "assetmanagementsystem-86fcf",
  storageBucket: "assetmanagementsystem-86fcf.firebasestorage.app",
  messagingSenderId: "710144417947",
  appId: "1:710144417947:web:1ba59a6f0d34fc2cd1263e",
  measurementId: "G-D645QJSQ51"
};

export const app = getApps().length === 0 ? initializeApp(firebaseConfig) : getApp();
export const db = getFirestore(app);
export const auth = getAuth(app);
export const storage = getStorage(app);

// Enforce session-only persistence so closing the window logs the user out
try {
  setPersistence(auth, browserSessionPersistence);
} catch (e) {
  console.warn("Could not set session persistence:", e);
}

// Make configs globally accessible if needed by non-module scripts
window.firebaseConfig = firebaseConfig;
window.firebaseApp = app;
window.firebaseDb = db;
window.firebaseAuth = auth;
window.firebaseStorage = storage;

// Role checking helper
export async function getCurrentUserRole() {
  return new Promise((resolve) => {
    onAuthStateChanged(auth, async (user) => {
      if (!user) {
        resolve(null);
        return;
      }
      try {
        // 1. Check users doc by UID
        const userDoc = await getDoc(doc(db, "users", user.uid));
        if (userDoc.exists() && userDoc.data().role) {
          resolve(userDoc.data().role.toLowerCase());
          return;
        }
        // 2. Check users collection by email
        const q = query(collection(db, "users"), where("email", "==", user.email));
        const snap = await getDocs(q);
        if (!snap.empty && snap.docs[0].data().role) {
          resolve(snap.docs[0].data().role.toLowerCase());
          return;
        }
        // 3. Fallback: email hint or default to staff if admin
        if (user.email && (user.email.includes("admin") || user.email.includes("staff"))) {
          resolve("staff");
        } else {
          resolve("student");
        }
      } catch (e) {
        console.warn("Could not fetch user role from Firestore:", e);
        if (user.email && (user.email.includes("admin") || user.email.includes("staff"))) {
          resolve("staff");
        } else {
          resolve("staff"); // Default to staff during development so features aren't blocked
        }
      }
    });
  });
}

// Global logout helper
window.logout = () => signOut(auth).then(() => window.location.href = "login.php");