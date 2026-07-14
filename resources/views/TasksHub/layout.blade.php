@extends('layouts.app')
@section('page_title', 'Tasks Hub')

@push('styles')
    @include('TasksHub.styles')
@endpush

@section('content')
<div class="tasks-hub-container">
    <!-- Hub Top Navigation (F03) -->
    @include('TasksHub.components.topnav')
    
    <!-- Main Content Area -->
    <div class="tasks-main-content">
        @yield('tasks_content')
    </div>

    <!-- Bottom Status Bar (F04) -->
    @include('TasksHub.components.bottombar')
</div>
@endsection

@push('scripts')
    @include('TasksHub.scripts')
    @yield('hub_scripts')
@endpush
