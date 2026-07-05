@extends('layouts.app')

@section('page_title', 'AI Models Hub')

@push('styles')
<style>
    /* Custom styles for AI Hub */
    .ai-hub-tabs .nav-link {
        color: var(--text-secondary);
        border: none;
        border-bottom: 2px solid transparent;
        border-radius: 0;
        padding: 0.75rem 1rem;
        font-weight: 500;
        transition: all 0.2s ease;
    }
    .ai-hub-tabs .nav-link:hover {
        color: var(--text-primary);
        border-bottom-color: rgba(255, 255, 255, 0.2);
    }
    .ai-hub-tabs .nav-link.active {
        color: var(--nexus-blue);
        border-bottom-color: var(--nexus-blue);
        background: transparent;
    }
    
    .metric-value {
        font-size: 1.5rem;
        font-weight: 700;
        font-family: 'JetBrains Mono', monospace;
    }
    
    .card-dashboard {
        background: rgba(22, 27, 34, 0.5) !important;
        backdrop-filter: blur(10px);
        border: 1px solid var(--nexus-border);
        transition: transform 0.2s, box-shadow 0.2s;
        cursor: pointer;
    }
    .card-dashboard:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        border-color: rgba(255,255,255,0.1);
    }
    
    .progress-thin {
        height: 4px;
        background-color: var(--nexus-border);
        border-radius: 2px;
    }
    
    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
    }
    
    .table-dark-custom {
        --bs-table-bg: transparent;
        --bs-table-color: var(--text-secondary);
        --bs-table-border-color: var(--nexus-border);
    }
    
    .table-dark-custom th {
        color: var(--text-primary);
        font-weight: 600;
        border-bottom-width: 1px;
    }
    
    .nav-pills-custom .nav-link {
        color: var(--text-secondary);
        border-radius: 6px;
        padding: 0.4rem 1rem;
    }
    .nav-pills-custom .nav-link.active {
        background-color: rgba(255,255,255,0.1);
        color: var(--text-primary);
    }
</style>
@endpush

@section('content')
<!-- Top Health Ribbon -->
<div class="bg-dark border-bottom border-secondary px-4 py-2 d-flex justify-content-between align-items-center animate-fade-in">
    <div class="d-flex align-items-center gap-4">
        <div class="d-flex align-items-center gap-2">
            <span class="status-dot bg-success shadow-sm" style="box-shadow: 0 0 8px var(--bs-success) !important;" id="global-health-dot"></span>
            <span class="fw-bold text-light" style="font-size: 0.85rem;" id="global-health-text">System Healthy</span>
        </div>
        <div class="d-none d-md-flex align-items-center gap-2 text-muted" style="font-size: 0.8rem; font-family: 'JetBrains Mono';">
            <i class="fa-solid fa-bolt text-warning"></i>
            <span id="active-requests-counter">0</span> Active Req
        </div>
        <div class="d-none d-lg-flex align-items-center gap-2 text-muted" style="font-size: 0.8rem; font-family: 'JetBrains Mono';">
            <i class="fa-solid fa-microchip text-info"></i>
            <span id="tpm-counter">0</span> Tokens/Min
        </div>
        <div class="d-none d-xl-flex align-items-center gap-2 text-muted" style="font-size: 0.8rem; font-family: 'JetBrains Mono';">
            <i class="fa-solid fa-sack-dollar text-success"></i>
            $<span id="est-cost-today">0.00</span> Today
        </div>
    </div>
    <div>
        <button class="btn btn-sm btn-outline-danger d-flex align-items-center gap-2 px-3 py-1" onclick="showKillSwitchModal()" style="border-radius: 20px; font-size: 0.75rem; font-weight: 600;">
            <i class="fa-solid fa-skull"></i> KILL SWITCH
        </button>
    </div>
</div>

<!-- Tabs Navigation -->
<div class="px-4 pt-3 border-bottom border-secondary animate-fade-in stagger-1">
    <ul class="nav nav-tabs ai-hub-tabs" id="aiHubTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="dashboard-tab" data-bs-toggle="tab" data-bs-target="#dashboard-pane" type="button" role="tab"><i class="fa-solid fa-chart-line me-2"></i>Dashboard</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="providers-tab" data-bs-toggle="tab" data-bs-target="#providers-pane" type="button" role="tab"><i class="fa-solid fa-server me-2"></i>Providers</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="models-tab" data-bs-toggle="tab" data-bs-target="#models-pane" type="button" role="tab"><i class="fa-solid fa-robot me-2"></i>Models</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="keys-tab" data-bs-toggle="tab" data-bs-target="#keys-pane" type="button" role="tab"><i class="fa-solid fa-key me-2"></i>API Keys</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="routing-tab" data-bs-toggle="tab" data-bs-target="#routing-pane" type="button" role="tab"><i class="fa-solid fa-route me-2"></i>Intent Routing</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="budget-tab" data-bs-toggle="tab" data-bs-target="#budget-pane" type="button" role="tab"><i class="fa-solid fa-wallet me-2"></i>Cost & Budget</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="playground-tab" data-bs-toggle="tab" data-bs-target="#playground-pane" type="button" role="tab"><i class="fa-solid fa-flask me-2"></i>Playground</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="logs-tab" data-bs-toggle="tab" data-bs-target="#logs-pane" type="button" role="tab"><i class="fa-solid fa-list-check me-2"></i>Logs & Audit</button>
        </li>
    </ul>
</div>

<!-- Tabs Content -->
<div class="tab-content flex-grow-1 overflow-auto bg-dark p-4" id="aiHubTabsContent">
    
    <!-- 1. Dashboard -->
    <div class="tab-pane fade show active" id="dashboard-pane" role="tabpanel" tabindex="0">
        @include('hubs.partials.ai-hub.dashboard.index')
    </div>
    
    <!-- 2. Providers -->
    <div class="tab-pane fade" id="providers-pane" role="tabpanel" tabindex="0">
        @include('hubs.partials.ai-hub.providers.index')
    </div>
    
    <!-- 3. Models -->
    <div class="tab-pane fade" id="models-pane" role="tabpanel" tabindex="0">
        @include('hubs.partials.ai-hub.models.index')
    </div>
    
    <!-- 4. API Keys -->
    <div class="tab-pane fade" id="keys-pane" role="tabpanel" tabindex="0">
        @include('hubs.partials.ai-hub.api-keys.index')
    </div>
    
    <!-- 5. Intent Routing -->
    <div class="tab-pane fade" id="routing-pane" role="tabpanel" tabindex="0">
        @include('hubs.partials.ai-hub.intent-routing.index')
    </div>
    
    <!-- 6. Cost & Budget -->
    <div class="tab-pane fade" id="budget-pane" role="tabpanel" tabindex="0">
        @include('hubs.partials.ai-hub.cost-budget.index')
    </div>
    
    <!-- 7. Playground -->
    <div class="tab-pane fade" id="playground-pane" role="tabpanel" tabindex="0">
        @include('hubs.partials.ai-hub.playground.index')
    </div>
    
    <!-- 8. Logs & Audit -->
    <div class="tab-pane fade" id="logs-pane" role="tabpanel" tabindex="0">
        @include('hubs.partials.ai-hub.logs-audit.index')
    </div>

</div>

<!-- Global Modals -->
<!-- Kill Switch Modal -->
<div class="modal fade" id="killSwitchModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border-danger">
            <div class="modal-header border-bottom border-danger">
                <h5 class="modal-title text-danger fw-bold"><i class="fa-solid fa-triangle-exclamation me-2"></i> EMERGENCY KILL SWITCH</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-light">
                <p>You are about to engage the emergency kill switch.</p>
                <p class="text-muted">This will immediately block <strong>ALL</strong> outbound requests to AI providers. Any running processes may fail.</p>
                <p>Are you sure you want to proceed?</p>
            </div>
            <div class="modal-footer border-top border-secondary">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="executeKillSwitch()">ENGAGE KILL SWITCH</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function showKillSwitchModal() {
        new bootstrap.Modal(document.getElementById('killSwitchModal')).show();
    }
    
    function executeKillSwitch() {
        // Implement kill switch logic here
        window.Nexus.notify('Kill switch engaged! All requests blocked.', 'error');
        $('#killSwitchModal').modal('hide');
        $('#global-health-dot').removeClass('bg-success bg-warning').addClass('bg-danger');
        $('#global-health-text').text('SYSTEM HALTED').addClass('text-danger');
    }

    $(document).ready(function() {
        // Fetch global telemetry for top ribbon
        function fetchRibbonTelemetry() {
            // Mock data for now, replace with actual API call
            $('#active-requests-counter').text(Math.floor(Math.random() * 50));
            $('#tpm-counter').text(Math.floor(Math.random() * 10000 + 2000).toLocaleString());
            $('#est-cost-today').text((Math.random() * 15 + 5).toFixed(2));
        }
        
        setInterval(fetchRibbonTelemetry, 3000);
        fetchRibbonTelemetry();
    });
</script>
@endpush
