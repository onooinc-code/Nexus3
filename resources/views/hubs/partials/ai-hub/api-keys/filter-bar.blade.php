<!-- Quick Filter Tabs -->
<ul class="nav nav-pills nav-pills-custom mb-3 border-bottom border-secondary pb-3 gap-2">
    <li class="nav-item"><a class="nav-link active" href="#">All</a></li>
    <li class="nav-item"><a class="nav-link text-success" href="#">● Active</a></li>
    <li class="nav-item"><a class="nav-link text-warning" href="#">⚠ Near Limit</a></li>
    <li class="nav-item"><a class="nav-link text-danger" href="#">❌ Exhausted</a></li>
    <li class="nav-item"><a class="nav-link text-muted" href="#">🔒 Revoked</a></li>
    <li class="nav-item"><a class="nav-link text-warning" href="#">⭐ Favorites</a></li>
</ul>

<!-- Advanced Filter Bar -->
<div class="d-flex flex-wrap gap-2 mb-4">
    <div class="input-group input-group-sm" style="width: 250px;">
        <span class="input-group-text bg-dark border-secondary text-muted"><i class="fa-solid fa-search"></i></span>
        <input type="text" class="form-control bg-dark border-secondary text-light" placeholder="Search by name or label...">
    </div>
    <select class="form-select form-select-sm bg-dark text-light border-secondary w-auto">
        <option>All Providers</option>
        <option>OpenAI</option>
        <option>Anthropic</option>
        <option>Google</option>
    </select>
    <select class="form-select form-select-sm bg-dark text-light border-secondary w-auto">
        <option>Budget Status</option>
        <option>Under 50%</option>
        <option>50% - 80%</option>
        <option>Over 80%</option>
    </select>
    <select class="form-select form-select-sm bg-dark text-light border-secondary w-auto">
        <option>Sort by: Created Date</option>
        <option>Sort by: Budget %</option>
        <option>Sort by: Requests Today</option>
        <option>Sort by: Last Used</option>
    </select>
</div>
