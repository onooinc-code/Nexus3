{{-- Providers Health Summary Strip --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="bg-dark border border-secondary rounded p-3 d-flex align-items-center gap-3">
            <div class="bg-primary bg-opacity-25 rounded p-2 text-primary" style="font-size: 1.5rem;"><i class="fa-solid fa-server"></i></div>
            <div>
                <div class="text-muted small">Total Active</div>
                <div class="fw-bold text-light" style="font-size: 1.25rem;">{{ $healthSummary['active'] ?? 0 }} <span class="text-muted" style="font-size: 0.8rem;">/ {{ $healthSummary['total'] ?? 0 }}</span></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="bg-dark border border-secondary rounded p-3 d-flex align-items-center gap-3">
            <div class="bg-danger bg-opacity-25 rounded p-2 text-danger" style="font-size: 1.5rem;"><i class="fa-solid fa-network-wired"></i></div>
            <div>
                <div class="text-muted small">Unreachable</div>
                <div class="fw-bold text-light" style="font-size: 1.25rem;">{{ $healthSummary['unreachable'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="bg-dark border border-secondary rounded p-3 d-flex align-items-center gap-3">
            <div class="bg-warning bg-opacity-25 rounded p-2 text-warning" style="font-size: 1.5rem;"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <div>
                <div class="text-muted small">Missing Keys</div>
                <div class="fw-bold text-light" style="font-size: 1.25rem;">{{ $healthSummary['no_key'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="bg-dark border border-secondary rounded p-3 d-flex align-items-center gap-3">
            <div class="bg-info bg-opacity-25 rounded p-2 text-info" style="font-size: 1.5rem;"><i class="fa-solid fa-rotate"></i></div>
            <div>
                <div class="text-muted small">Last Sync</div>
                <div class="fw-bold text-light" style="font-size: 0.9rem;">
                    {{ isset($healthSummary['last_sync_at']) ? \Carbon\Carbon::parse($healthSummary['last_sync_at'])->diffForHumans(null, true) : 'Never' }}
                </div>
            </div>
        </div>
    </div>
</div>

@include('hubs.partials.ai-hub.providers.filter-bar')
@include('hubs.partials.ai-hub.providers.cards-grid')
@include('hubs.partials.ai-hub.providers.drawers.add-edit')
@include('hubs.partials.ai-hub.providers.drawers.details-drawer')
