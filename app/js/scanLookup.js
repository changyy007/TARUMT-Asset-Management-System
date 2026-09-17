import { normalizeTagId } from './tagId.js';

let lookupQrCode = null;
let isLookupScannerRunning = false;

export function goToAssetInfo(tagInput) {
    const rawText = (tagInput || '').trim();
    const tagId = normalizeTagId(rawText);
    if (!tagId) {
        alert('Please enter or scan a valid Tag ID.');
        return;
    }
    window.location.href = `info.php?id=${encodeURIComponent(tagId)}`;
}

export async function stopLookupScanner() {
    if (lookupQrCode) {
        try {
            if (isLookupScannerRunning || lookupQrCode.isScanning) {
                await lookupQrCode.stop();
            }
        } catch (e) {}
        try {
            await lookupQrCode.clear();
        } catch (e) {}
        lookupQrCode = null;
        isLookupScannerRunning = false;
    }
    const qrContainer = document.getElementById('lookup-qr-reader');
    if (qrContainer) {
        qrContainer.querySelectorAll('video').forEach(vid => {
            if (vid.srcObject?.getTracks) {
                vid.srcObject.getTracks().forEach(track => {
                    try { track.stop(); } catch (e) {}
                });
                vid.srcObject = null;
            }
        });
        qrContainer.innerHTML = '';
    }
}

export async function startPublicQrScan() {
    const manual = document.getElementById('manual-lookup-id');
    if (manual) manual.value = '';
    await stopLookupScanner();
    await initLookupScanner();
}

async function initLookupScanner() {
    const qrContainer = document.getElementById('lookup-qr-reader');
    if (!qrContainer || typeof Html5Qrcode === 'undefined') return;
    qrContainer.innerHTML = '';

    try {
        lookupQrCode = new Html5Qrcode('lookup-qr-reader');
        const config = { fps: 10, qrbox: { width: 220, height: 220 } };
        const onScan = (decodedText) => {
            lookupQrCode.stop().then(() => {
                isLookupScannerRunning = false;
                goToAssetInfo(decodedText);
            }).catch(() => goToAssetInfo(decodedText));
        };
        try {
            await lookupQrCode.start({ facingMode: 'environment' }, config, onScan, () => {});
            isLookupScannerRunning = true;
        } catch (cameraErr) {
            await lookupQrCode.start({ facingMode: 'user' }, config, onScan, () => {});
            isLookupScannerRunning = true;
        }
    } catch (err) {
        isLookupScannerRunning = false;
        qrContainer.innerHTML = `
            <div class="p-4 text-center text-light small d-flex flex-column align-items-center justify-content-center h-100" style="min-height: 220px;">
                <i class="bi bi-camera-video-off fs-1 text-warning mb-2"></i>
                <span>Camera unavailable. Please enter the Tag ID manually below.</span>
            </div>`;
    }
}

export function initScanLookupModal() {
    window.startPublicQrScan = startPublicQrScan;
    window.stopLookupScanner = stopLookupScanner;

    const lookupModalEl = document.getElementById('scanLookupModal');
    if (lookupModalEl) {
        lookupModalEl.addEventListener('shown.bs.modal', () => startPublicQrScan());
        lookupModalEl.addEventListener('hidden.bs.modal', () => stopLookupScanner());
    }

    document.getElementById('btn-go-lookup')?.addEventListener('click', () => {
        goToAssetInfo(document.getElementById('manual-lookup-id')?.value);
    });
    document.getElementById('manual-lookup-id')?.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') goToAssetInfo(document.getElementById('manual-lookup-id')?.value);
    });
}
