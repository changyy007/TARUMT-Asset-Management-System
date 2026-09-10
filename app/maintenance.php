<?php include 'header.php'; ?>
<div class="container-fluid px-0 px-md-2">
    <div class="mb-4">
        <h1 class="h3 fw-bold text-dark mb-1">Maintenance Management</h1>
        <p class="text-muted small mb-0">Scheduled campus maintenance schedules, service logs, and work orders.</p>
    </div>

    <div class="card border-0 shadow-sm rounded-4 p-4 text-center">
        <div class="py-5">
            <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-3" style="width: 64px; height: 64px;">
                <i class="bi bi-tools fs-2"></i>
            </div>
            <h4 class="fw-bold text-dark mb-2">Maintenance Schedules</h4>
            <p class="text-muted small mx-auto" style="max-width: 420px;">
                Routine maintenance inspections and vendor service tickets for campus facilities are tracked here.
            </p>
            <a href="inventory.php" class="btn btn-outline-primary rounded-pill px-4 btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Return to Inventory
            </a>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>