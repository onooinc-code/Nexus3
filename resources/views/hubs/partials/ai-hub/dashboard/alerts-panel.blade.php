<div class="col-lg-4">
    <div class="card card-dashboard h-100 border-danger border-opacity-50" style="background: rgba(220, 53, 69, 0.05) !important;">
        <div class="card-header bg-transparent border-secondary py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 text-danger fw-bold"><i class="fa-solid fa-bell me-2"></i>Active Alerts</h6>
            <span class="badge bg-danger rounded-pill">1</span>
        </div>
        <div class="card-body">
            <div class="alert alert-dark border-danger bg-transparent p-2 mb-2">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="text-light fw-bold" style="font-size: 0.85rem;">Budget Warning</div>
                    <span class="text-muted" style="font-size: 0.7rem;">10m ago</span>
                </div>
                <div class="text-muted" style="font-size: 0.8rem;">Intent 'reasoning' has reached 99% of its $30 monthly budget.</div>
                <div class="mt-2 text-end">
                    <button class="btn btn-sm btn-outline-secondary py-0" style="font-size: 0.7rem;">Snooze 15m</button>
                    <button class="btn btn-sm btn-outline-danger py-0" style="font-size: 0.7rem;" onclick="document.getElementById('budget-tab').click()">Investigate</button>
                </div>
            </div>
        </div>
    </div>
</div>
