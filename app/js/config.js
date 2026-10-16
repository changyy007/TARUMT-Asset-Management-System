import { initializeApp, getApps, getApp } from "https://www.gstatic.com/firebasejs/10.10.0/firebase-app.js";
import { getFirestore, doc, getDoc, collection, query, where, getDocs } from "https://www.gstatic.com/firebasejs/10.10.0/firebase-firestore.js";
import { getAuth, setPersistence, browserSessionPersistence, onAuthStateChanged, signOut } from "https://www.gstatic.com/firebasejs/10.10.0/firebase-auth.js";
import { getStorage } from "https://www.gstatic.com/firebasejs/10.10.0/firebase-storage.js";

const configResponse = await fetch(new URL("../api/firebase_config.php", import.meta.url));
if (!configResponse.ok) {
  throw new Error("Could not load Firebase configuration");
}
export const firebaseConfig = await configResponse.json();

// Singleton — avoids "Firebase App named '[DEFAULT]' already exists" error
export const app = getApps().length === 0 ? initializeApp(firebaseConfig) : getApp();
export const db  = getFirestore(app);
export const auth = getAuth(app);
export const storage = getStorage(app);

// Enforce session-only persistence so closing the browser window logs the user out
try {
  setPersistence(auth, browserSessionPersistence);
} catch (e) {
  console.warn("Could not set session persistence:", e);
}

// Make configs globally accessible if needed by non-module scripts
window.firebaseConfig  = firebaseConfig;
window.firebaseApp     = app;
window.firebaseDb      = db;
window.firebaseAuth    = auth;
window.firebaseStorage = storage;

// Role checking helper
export async function getCurrentUserRole() {
  return new Promise((resolve) => {
    onAuthStateChanged(auth, async (user) => {
      if (!user) { resolve(null); return; }
      try {
        const userDoc = await getDoc(doc(db, "users", user.uid));
        if (userDoc.exists() && userDoc.data().role) {
          resolve(userDoc.data().role.toLowerCase()); return;
        }
        const q = query(collection(db, "users"), where("email", "==", user.email));
        const snap = await getDocs(q);
        if (!snap.empty && snap.docs[0].data().role) {
          resolve(snap.docs[0].data().role.toLowerCase()); return;
        }
        resolve("student");
      } catch (e) {
        console.warn("Could not fetch user role:", e);
        resolve("student");
      }
    });
  });
}

// Global logout helper
window.logout = () => signOut(auth).then(() => window.location.href = "login.php");