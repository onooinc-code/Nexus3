@extends('TasksHub.layout')
@section('page_title', 'Task Details')

@section('tasks_content')
<div class="container-fluid py-4">
    <div class="row g-4">
        <div class="col-12 col-xl-8">
            <div class="card bg-dark border-secondary mb-4">
                <div class="card-header border-secondary d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 text-light"><i class="fa-solid fa-layer-group me-2 text-primary"></i>Task #{{ $task->id }}</h5>
                    <span class="badge bg-{{ $task->status === 'completed' ? 'success' : ($task->status === 'failed' ? 'danger' : 'warning') }}">{{ strtoupper($task->status) }}</span>
                </div>
                <div class="card-body text-light">
                    <h4>{{ $task->title }}</h4>
                    <p class="text-muted">{{ $task->description }}</p>
                    <hr class="border-secondary">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block text-uppercase" style="font-size: 0.7rem; letter-spacing: 1px;">Type</small>
                            <span class="fw-bold">{{ $task->type }}</span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block text-uppercase" style="font-size: 0.7rem; letter-spacing: 1px;">Priority</small>
                            <span class="fw-bold">{{ $task->priority_name ?? $task->priority }}</span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block text-uppercase" style="font-size: 0.7rem; letter-spacing: 1px;">Created At</small>
                            <span class="fw-bold">{{ $task->created_at->format('Y-m-d H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Terminal for Live Logs -->
            <div class="card bg-dark border-secondary">
                <div class="card-header border-secondary">
                    <h5 class="card-title mb-0 text-light"><i class="fa-solid fa-terminal me-2 text-info"></i>Execution Logs</h5>
                </div>
                <div class="card-body p-0">
                    <div id="live-log-viewer-container"></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="card bg-dark border-secondary">
                <div class="card-header border-secondary">
                    <h5 class="card-title mb-0 text-light"><i class="fa-solid fa-code me-2 text-info"></i>Payload Data</h5>
                </div>
                <div class="card-body p-0">
                    <pre class="m-0 p-3 text-success" style="font-family: 'JetBrains Mono'; font-size: 0.8rem; background: #0f172a; overflow-x: auto; min-height: 200px;">
{{ json_encode($task->payload_data, JSON_PRETTY_PRINT) }}
                    </pre>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            if (window.mountLiveLogViewer) {
                window.mountLiveLogViewer({{ $task->id }});
            } else {
                console.error("LiveLogViewer mount function not found in window.");
            }
        }, 500);
    });
</script>
@endsection
