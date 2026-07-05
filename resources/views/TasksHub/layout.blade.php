@extends('layouts.app')
@section('page_title', 'Tasks Hub')

@push('styles')
<style>
/* Tasks Hub specific styles */
:root {
    --tasks-header-height: 70px;
    --tasks-status-bar-height: 40px;
}
.tasks-hub-container {
    display: flex;
    flex-direction: column;
    height: calc(100vh - var(--navbar-height, 60px));
    overflow: hidden;
}
.tasks-hub-content {
    flex: 1;
    overflow-y: auto;
    padding-bottom: var(--tasks-status-bar-height);
}
.tasks-status-bar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    height: var(--tasks-status-bar-height);
    background: rgba(15,23,42,0.9);
    border-top: 1px solid var(--glass-border);
    backdrop-filter: blur(10px);
    z-index: 1000;
}
</style>
@endpush

@section('content')
<div class="tasks-hub-container">
    @include('TasksHub.components.header')
    
    <div class="tasks-hub-content p-4">
        @yield('tasks_content')
    </div>

    <div class="tasks-status-bar">
        @include('TasksHub.components.status-bar')
    </div>
</div>

@vite(['resources/js/app.js'])
@endsection
