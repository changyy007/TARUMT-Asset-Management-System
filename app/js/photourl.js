import { getStorage, ref, uploadBytes, getDownloadURL } from "firebase/storage";
import { getFirestore, collection, addDoc } from "firebase/firestore";

const storage = getStorage();
const db = getFirestore();

async function uploadAsset(imageFile, assetMetadata) {
  try {
    // 1. Create a unique path for the image in Cloud Storage
    const storageRef = ref(storage, `asset-photos/${Date.now()}_${imageFile.name}`);

    // 2. Upload the file to Cloud Storage
    const uploadResult = await uploadBytes(storageRef, imageFile);

    // 3. Retrieve the short public download URL
    const downloadURL = await getDownloadURL(uploadResult.ref);

    // 4. Save the asset document to Firestore with the image URL
    const newAsset = {
      ...assetMetadata,
      photourl: downloadURL // This is now a lightweight URL string!
    };

    const docRef = await addDoc(collection(db, "assets"), newAsset);
    console.log("Asset saved successfully with ID: ", docRef.id);
  } catch (error) {
    console.error("Error saving asset: ", error);
  }
}
