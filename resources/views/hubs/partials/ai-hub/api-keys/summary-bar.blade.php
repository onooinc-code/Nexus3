<div class="bg-dark border border-secondary rounded p-3 mb-4 d-flex justify-content-between align-items-center">
    <div class="d-flex gap-4" style="font-family: 'JetBrains Mono', monospace; font-size: 0.85rem;">
        <span class="text-light">Total Keys: <span class="fw-bold">{{ $apiKeys->count() }}</span></span>
        <span class="text-success">Active: <span class="fw-bold">{{ $apiKeys->where('is_active', true)->count() }}</span></span>
        <span class="text-warning">Cooldown: <span class="fw-bold">{{ $apiKeys->whereNotNull('cooldown_until')->count() }}</span></span>
        <span class="text-danger">Errors: <span class="fw-bold">{{ $apiKeys->sum('error_count') }}</span></span>
        <span class="text-muted">Revoked: <span class="fw-bold">{{ $apiKeys->where('is_active', false)->count() }}</span></span>
    </div>
    <button class="btn btn-sm btn-primary fw-bold px-3" data-bs-toggle="modal" data-bs-target="#addKeyModal">
        <i class="fa-solid fa-plus me-1"></i> Generate New Key
    </button>
</div>
