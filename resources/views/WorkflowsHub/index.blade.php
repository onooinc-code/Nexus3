@extends('layouts.app')

@push('styles')
    @include('WorkflowsHub.styles')
@endpush

@section('content')
<div class="workflows-hub-wrapper">
    <!-- Top Navigation Tabs -->
    <div class="workflows-topnav">
        <div class="d-flex align-items-center gap-3">
            <h4 class="mb-0 fw-bold text-light"><i class="fa-solid fa-network-wired text-primary me-2"></i> WorkflowsHub</h4>
            <div class="wf-nav-tabs ms-4">
                <button class="wf-nav-btn active" data-target="#content-dashboard">
                    <i class="fa-solid fa-chart-line"></i> Dashboard
                </button>
                <button class="wf-nav-btn" data-target="#content-builder">
                    <i class="fa-solid fa-diagram-project"></i> Builder
                </button>
                <button class="wf-nav-btn" data-target="#content-executions">
                    <i class="fa-solid fa-list-check"></i> Executions
                </button>
                <button class="wf-nav-btn" data-target="#content-schedules">
                    <i class="fa-solid fa-clock"></i> Schedules
                </button>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-secondary text-light">
                <i class="fa-solid fa-book me-1"></i> Docs
            </button>
            <button class="btn btn-sm btn-primary">
                <i class="fa-solid fa-plus me-1"></i> New Workflow
            </button>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="workflows-main-content p-3 p-md-4">
        <div class="tab-content h-100">
            <!-- Dashboard Tab -->
            <div class="tab-pane fade show active h-100 wf-tab-pane" id="content-dashboard" role="tabpanel">
                @include('WorkflowsHub.dashboard.index')
            </div>

            <!-- Builder Tab -->
            <div class="tab-pane fade h-100 wf-tab-pane" id="content-builder" role="tabpanel">
                @include('WorkflowsHub.builder.index')
            </div>

            <!-- Executions Tab -->
            <div class="tab-pane fade h-100 wf-tab-pane" id="content-executions" role="tabpanel">
                @include('WorkflowsHub.executions.index')
            </div>

            <!-- Schedules Tab -->
            <div class="tab-pane fade h-100 wf-tab-pane" id="content-schedules" role="tabpanel">
                @include('WorkflowsHub.schedules.index')
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @include('WorkflowsHub.scripts')
@endpush
