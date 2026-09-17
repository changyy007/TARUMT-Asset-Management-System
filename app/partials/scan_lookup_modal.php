<!-- Scan Asset Tag Lookup Modal (guest + all users) -->
<div class="modal fade" id="scanLookupModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center">
                    <i class="bi bi-qr-code-scan text-primary me-2"></i>Scan Asset QR Tag
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="stopLookupScanner()"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small text-center mb-3">Point your camera at the physical QR tag on the asset to view its details or file a fault report.</p>
                <div id="lookup-qr-reader" class="w-100 bg-dark rounded-3 overflow-hidden border mb-3 position-relative" style="min-height: 250px;"></div>
                <div class="card bg-light border-0 p-3 rounded-3 mb-3">
                    <label class="form-label small fw-semibold text-secondary mb-1">
                        <i class="bi bi-keyboard me-1"></i>Or Enter Tag ID Manually:
                    </label>
                    <div class="input-group">
                        <input type="text" id="manual-lookup-id" class="form-control" placeholder="e.g. TARUMT-a00000002">
                        <button class="btn btn-primary" type="button" id="btn-go-lookup">View Asset</button>
                    </div>
                </div>
                <button type="button" class="btn btn-light border w-100 rounded-pill py-2" data-bs-dismiss="modal" onclick="stopLookupScanner()">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
