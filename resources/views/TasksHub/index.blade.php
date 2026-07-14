@extends('TasksHub.layout')

@section('tasks_content')
<div class="tab-content h-100" id="taskhub-tabContent">
    <!-- Dashboard Tab -->
    <div class="tab-pane fade show active h-100" id="content-dashboard" role="tabpanel">
        <div class="h-100 pe-2 pb-3">
            @include('TasksHub.dashboard.stat-cards')
            @include('TasksHub.dashboard.charts')
            @include('TasksHub.dashboard.insights-feed')
        </div>
    </div>

    <!-- Board Tab -->
    <div class="tab-pane fade h-100" id="content-board" role="tabpanel">
        <div class="h-100 pe-2 pb-3">
            @include('TasksHub.board.index')
        </div>
    </div>

    <!-- List Tab -->
    <div class="tab-pane fade h-100" id="content-list" role="tabpanel">
        <div class="h-100 pe-2 pb-3">
            @include('TasksHub.list.index')
        </div>
    </div>
    
    <!-- Queue Monitor Tab -->
    <div class="tab-pane fade h-100" id="content-queue" role="tabpanel">
        @include('TasksHub.queue.index')
    </div>

    <!-- Automations Tab -->
    <div class="tab-pane fade h-100" id="content-automations" role="tabpanel">
        @include('TasksHub.automations.index')
    </div>
</div>

<!-- Global Modals Container -->
<div id="tasks-modals-container">
    <!-- Task Create Modal (F14) -->
    @include('TasksHub.modals.create-task')
    
    <!-- Quick View Sidebar (F12) -->
    @include('TasksHub.list.quick-view')
</div>
@endsection
