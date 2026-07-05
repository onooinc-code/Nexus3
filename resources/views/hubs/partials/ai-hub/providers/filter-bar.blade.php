<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex gap-3">
        <div class="input-group input-group-sm" style="width: 250px;">
            <span class="input-group-text bg-dark border-secondary text-muted"><i class="fa-solid fa-search"></i></span>
            <input type="text" class="form-control bg-dark border-secondary text-light" placeholder="Search providers...">
        </div>
        <select class="form-select form-select-sm bg-dark text-light border-secondary w-auto">
            <option>All Statuses</option>
            <option>Active</option>
            <option>Degraded</option>
            <option>Disabled</option>
        </select>
        <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-secondary active"><i class="fa-solid fa-grid-2"></i></button>
            <button class="btn btn-outline-secondary"><i class="fa-solid fa-list"></i></button>
        </div>
    </div>
    <button class="btn btn-sm btn-primary fw-bold px-3" data-bs-toggle="offcanvas" data-bs-target="#addProviderOffcanvas">
        <i class="fa-solid fa-plus me-1"></i> Add Provider
    </button>
</div>
