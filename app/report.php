<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Broken Asset - TARUMT Asset Management</title>
    <!-- Bootstrap 5.3.0 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f4f6f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem 1rem;
            font-family: system-ui, -apple-system, sans-serif;
        }
        .report-card {
            max-width: 520px;
            width: 100%;
        }
        .form-control:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.15);
        }
    </style>
</head>
<body>

    <div class="card report-card border-0 shadow-lg rounded-4 overflow-hidden my-3 bg-white">
        <!-- Card Header -->
        <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0 text-center">
            <div class="d-inline-flex align-items-center justify-content-center bg-danger bg-opacity-10 text-danger rounded-circle mb-3" style="width: 56px; height: 56px;">
                <i class="bi bi-exclamation-triangle-fill fs-3"></i>
            </div>
            <h2 class="h4 fw-bold text-dark mb-1">Report Broken Asset</h2>
            <p class="text-muted small mb-0" id="asset-target-label">Submit a maintenance work order with fault photo</p>
        </div>
        
        <div class="card-body p-4">
            <div id="ai-badge-notification" class="alert alert-info d-none small py-2 px-3 rounded-3 mb-3 text-center">
                <div class="spinner-border spinner-border-sm text-info me-2" role="status"></div>
                <span>Analyzing defect image with Gemini AI...</span>
            </div>

            <form id="report-form">
                <!-- Asset ID -->
                <div class="mb-3" id="asset-id-group">
                    <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Asset Tag ID <span class="text-danger">*</span></label>
                    <input type="text" id="report-asset-id" class="form-control bg-light" placeholder="e.g. ASSET-00123" required>
                </div>

                <!-- Fault Photo (Required) -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Photo of Fault / Defect <span class="text-danger">*</span></label>
                    <input type="file" id="fault-photo" accept="image/*" capture="environment" class="form-control" required>
                    <div class="form-text small">Take a clear photo with smartphone camera or select an image.</div>
                    <div id="photo-preview-container" class="mt-2 text-center d-none">
                        <img id="photo-preview" src="#" alt="" class="img-fluid rounded-3 border shadow-sm" style="max-height: 180px;">
                    </div>
                </div>

                <!-- Reporter Email -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Your Email <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-envelope text-muted"></i></span>
                        <input type="email" id="reporter-email" required placeholder="e.g. student@tarumt.edu.my" class="form-control">
                    </div>
                </div>

                <!-- Issue Description -->
                <div class="mb-4">
                    <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Fault Description <span class="text-danger">*</span></label>
                    <textarea id="issue-desc" required rows="3" placeholder="Describe what is broken (e.g. Broken desk leg, exposed wires near switch, air conditioner leaking water...)" class="form-control"></textarea>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" id="btn-submit-report" class="btn btn-danger rounded-pill py-2 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-send-fill"></i>
                        <span>Submit Fault Report</span>
                    </button>
                    <a id="btn-cancel-return" href="inventory.php" class="btn btn-outline-secondary rounded-pill py-2 small text-center">
                        Cancel & Return
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script type="module">
        import { auth, db, storage } from './js/config.js';
        import { onAuthStateChanged } from "https://www.gstatic.com/firebasejs/10.10.0/firebase-auth.js";
        import { collection, addDoc, doc, updateDoc, serverTimestamp } from "https://www.gstatic.com/firebasejs/10.10.0/firebase-firestore.js";
        import { ref, uploadBytes, getDownloadURL } from "https://www.gstatic.com/firebasejs/10.10.0/firebase-storage.js";

        const urlParams = new URLSearchParams(window.location.search);
        const assetId = urlParams.get('id');
        const assetInput = document.getElementById('report-asset-id');
        const targetLabel = document.getElementById('asset-target-label');
        const cancelBtn = document.getElementById('btn-cancel-return');
        const photoInput = document.getElementById('fault-photo');
        const previewImg = document.getElementById('photo-preview');
        const previewContainer = document.getElementById('photo-preview-container');

        if (assetId) {
            assetInput.value = assetId;
            assetInput.readOnly = true;
            targetLabel.innerHTML = `Reporting for Asset: <strong class="text-primary font-monospace">${assetId}</strong>`;
            cancelBtn.href = `info.php?id=${encodeURIComponent(assetId)}`;
        }

        // Auto-fill reporter email if logged in
        onAuthStateChanged(auth, (user) => {
            if (user && user.email) {
                const emailInput = document.getElementById('reporter-email');
                if (emailInput && !emailInput.value) {
                    emailInput.value = user.email;
                }
            }
        });

        // Helper: Compress image to max 900px canvas & return JPEG Blob + base64
        function compressImage(file, maxDimension = 900, quality = 0.75) {
            return new Promise((resolve) => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const img = new Image();
                    img.onload = () => {
                        let w = img.width;
                        let h = img.height;
                        if (w > maxDimension || h > maxDimension) {
                            if (w > h) {
                                h = Math.round((h * maxDimension) / w);
                                w = maxDimension;
                            } else {
                                w = Math.round((w * maxDimension) / h);
                                h = maxDimension;
                            }
                        }
                        const canvas = document.createElement('canvas');
                        canvas.width = w;
                        canvas.height = h;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, w, h);
                        const base64 = canvas.toDataURL('image/jpeg', quality);
                        canvas.toBlob((blob) => {
                            resolve({ blob, base64 });
                        }, 'image/jpeg', quality);
                    };
                    img.src = e.target.result;
                };
                reader.readAsDataURL(file);
            });
        }

        let compressedImageData = null;

        photoInput.addEventListener('change', async (e) => {
            const file = e.target.files[0];
            if (file) {
                compressedImageData = await compressImage(file);
                previewImg.src = compressedImageData.base64;
                previewContainer.classList.remove('d-none');
            }
        });

        // Form Submission
        document.getElementById('report-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = document.getElementById('btn-submit-report');
            const aiBadge = document.getElementById('ai-badge-notification');
            const finalAssetId = assetInput.value.trim();
            const description = document.getElementById('issue-desc').value.trim();
            const email = document.getElementById('reporter-email').value.trim();
            const file = photoInput.files ? photoInput.files[0] : null;

            if (!finalAssetId || !file) {
                alert("Please provide an Asset Tag ID and photo.");
                return;
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Submitting...`;
            aiBadge.classList.remove('d-none');

            try {
                // 1. Ensure image is compressed
                if (!compressedImageData) {
                    compressedImageData = await compressImage(file);
                }

                // 2. Upload photo to get a short URL (< 150 bytes)
                let shortPhotoUrl = "";

                // Attempt A: Firebase Storage upload
                try {
                    const storageRef = ref(storage, `faults/${finalAssetId}_${Date.now()}.jpg`);
                    const snap = await uploadBytes(storageRef, compressedImageData.blob, { contentType: 'image/jpeg' });
                    shortPhotoUrl = await getDownloadURL(snap.ref);
                } catch (storageErr) {
                    console.warn("Firebase storage error, attempting server upload:", storageErr);
                }

                // Attempt B: Server-side PHP upload (returns short path like picture/uploads/...)
                if (!shortPhotoUrl) {
                    try {
                        const formData = new FormData();
                        formData.append('image_base64', compressedImageData.base64);
                        const srvRes = await fetch('api/upload_image.php', {
                            method: 'POST',
                            body: formData
                        });
                        const srvData = await srvRes.json();
                        if (srvData.success && srvData.url) {
                            shortPhotoUrl = srvData.url;
                        }
                    } catch (srvErr) {
                        console.warn("Server upload note:", srvErr);
                    }
                }

                // Attempt C: Use compressed base64 (only ~60KB, guaranteed safe below Firestore 1MB limit)
                if (!shortPhotoUrl) {
                    shortPhotoUrl = compressedImageData.base64;
                }

                // 3. Classify Priority via Gemini API
                let classifiedPriority = "MODERATE";
                try {
                    const aiRes = await fetch("api/classify.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({
                            description: description,
                            image: compressedImageData.base64,
                            mimeType: "image/jpeg"
                        })
                    });
                    if (aiRes.ok) {
                        const aiData = await aiRes.json();
                        if (aiData.priority) classifiedPriority = aiData.priority;
                    }
                } catch (aiErr) {
                    // Local heuristic fallback
                    const lower = description.toLowerCase();
                    if (lower.includes("wire") || lower.includes("spark") || lower.includes("fire") || lower.includes("smoke") || lower.includes("leak") || lower.includes("shock")) {
                        classifiedPriority = "CRITICAL";
                    } else if (lower.includes("ac") || lower.includes("aircond") || lower.includes("projector") || lower.includes("trip")) {
                        classifiedPriority = "MODERATE";
                    } else {
                        classifiedPriority = "MINOR";
                    }
                }

                // 4. Save Ticket to Firestore
                const todayDateStr = new Date().toISOString().split('T')[0];
                await addDoc(collection(db, "tickets"), {
                    assetId: finalAssetId,
                    description: description,
                    photoUrl: shortPhotoUrl,
                    priority: classifiedPriority,
                    status: "Pending",
                    reporterEmail: email,
                    reportDate: todayDateStr,
                    isArchived: false,
                    createdAt: serverTimestamp()
                });

                // 5. Update Asset status in assets collection
                try {
                    await updateDoc(doc(db, "assets", finalAssetId), {
                        status: "Under Maintenance"
                    });
                } catch (ae) {}

                alert(`Report submitted successfully!\n\nAI Priority: ${classifiedPriority}\nMaintenance staff has been notified.`);

                if (assetId) {
                    window.location.href = `info.php?id=${encodeURIComponent(assetId)}`;
                } else {
                    window.location.href = "maintenance.php";
                }

            } catch (err) {
                console.error("Report error:", err);
                alert("Failed to submit report: " + err.message);
                submitBtn.disabled = false;
                submitBtn.innerHTML = `<i class="bi bi-send-fill"></i> <span>Submit Fault Report</span>`;
                aiBadge.classList.add('d-none');
            }
        });
    </script>
</body>
</html>