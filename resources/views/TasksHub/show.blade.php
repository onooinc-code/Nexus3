@extends('TasksHub.layout')

@section('page_title', 'Task Details #' . $task->id)

@push('styles')
<style>
/* State Machine Visualizer (F16) */
.state-machine-node {
    width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
    font-size: 0.8rem; background: rgba(255,255,255,0.05); border: 2px solid rgba(255,255,255,0.1);
    color: var(--text-muted); transition: all 0.3s;
}
.state-machine-node.active {
    background: var(--tasks-primary); border-color: var(--tasks-primary); color: #fff;
    box-shadow: 0 0 15px rgba(99, 102, 241, 0.5);
}
.state-machine-node.completed {
    background: var(--tasks-success); border-color: var(--tasks-success); color: #fff;
}
.state-machine-line {
    flex-grow: 1; height: 2px; background: rgba(255,255,255,0.1); margin: 0 10px; transition: all 0.3s;
}
.state-machine-line.active {
    background: var(--tasks-success);
}

/* Detail Tabs */
.task-detail-nav .nav-link {
    color: var(--text-muted); font-family: var(--font-mono); font-size: 0.8rem; text-transform: uppercase;
    border-radius: 0; border-bottom: 2px solid transparent;
}
.task-detail-nav .nav-link.active {
    color: var(--tasks-primary); border-bottom: 2px solid var(--tasks-primary); background: transparent;
}
</style>
@endpush

@section('tasks_content')
<div class="h-100 d-flex flex-column pe-2 pb-3">
    
    <!-- Top Actions & Navigation -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="{{ route('hub.tasks.index') }}" class="btn btn-sm btn-outline-secondary font-mono" style="font-size: 0.75rem;">
            <i class="fa-solid fa-arrow-left me-2"></i>Back to Hub
        </a>
        <div class="d-flex gap-2" id="task-actions-container">
            <button class="btn btn-sm btn-outline-success" id="btn-dtl-execute"><i class="fa-solid fa-play me-1"></i>Execute</button>
            <button class="btn btn-sm btn-outline-warning" id="btn-dtl-pause"><i class="fa-solid fa-pause me-1"></i>Pause</button>
            <button class="btn btn-sm btn-outline-danger" id="btn-dtl-cancel"><i class="fa-solid fa-xmark me-1"></i>Cancel</button>
        </div>
    </div>

    <div class="row g-3 flex-grow-1">
        <!-- Main Content (Left) -->
        <div class="col-lg-8 d-flex flex-column gap-3">
            
            <!-- Header Card (F15) & State Machine (F16) -->
            <div class="tasks-glass-card p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-secondary font-mono">#{{ $task->id }}</span>
                            <span class="badge bg-dark border border-secondary font-mono">{{ strtoupper($task->type) }}</span>
                            <span id="dtl-status-badge" class="badge bg-primary">{{ strtoupper($task->status) }}</span>
                        </div>
                        <h4 class="text-light mb-1">{{ $task->title }}</h4>
                        <p class="text-muted font-mono mb-0" style="font-size: 0.75rem;">Created: {{ $task->created_at->format('Y-m-d H:i') }} | Agent: {{ $task->agent_id ?? 'None' }}</p>
                    </div>
                    <div class="text-end">
                        <span class="text-muted font-mono" style="font-size: 0.65rem; text-transform: uppercase;">Priority</span>
                        <h3 class="text-light mb-0">{{ $task->priority }}/10</h3>
                    </div>
                </div>

                <!-- Live Progress Bar (F17) -->
                <div class="mb-4 mt-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="font-mono text-muted" style="font-size: 0.7rem;"><i class="fa-solid fa-bars-progress me-2"></i>Execution Progress</span>
                        <span id="dtl-progress-text" class="font-mono text-light" style="font-size: 0.7rem;">{{ $task->progress ?? 0 }}%</span>
                    </div>
                    <div class="progress" style="height: 8px; background: rgba(255,255,255,0.05);">
                        <div id="dtl-progress-bar" class="progress-bar" role="progressbar" style="width: {{ $task->progress ?? 0 }}%; background: var(--tasks-primary); transition: width 0.4s ease;"></div>
                    </div>
                </div>

                <!-- State Machine Visualizer (F16) -->
                <div class="d-flex align-items-center mt-3 p-3 rounded" style="background: rgba(0,0,0,0.2);">
                    <div class="state-machine-node active" title="Todo" data-state="todo"><i class="fa-solid fa-clipboard-list"></i></div>
                    <div class="state-machine-line" data-line="todo-inprogress"></div>
                    <div class="state-machine-node" title="In Progress" data-state="in-progress"><i class="fa-solid fa-person-running"></i></div>
                    <div class="state-machine-line" data-line="inprogress-completed"></div>
                    <div class="state-machine-node" title="Completed" data-state="completed"><i class="fa-solid fa-flag-checkered"></i></div>
                </div>
            </div>

            <!-- Tabs Section -->
            <div class="tasks-glass-card flex-grow-1 d-flex flex-column">
                <ul class="nav nav-pills task-detail-nav p-2 border-bottom border-secondary" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-logs" type="button"><i class="fa-solid fa-terminal me-2"></i>Live Logs</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-payload" type="button"><i class="fa-solid fa-code me-2"></i>Payload & Result</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-trace" type="button"><i class="fa-solid fa-diagram-project me-2"></i>Execution Trace</button>
                    </li>
                </ul>

                <div class="tab-content flex-grow-1 overflow-hidden" style="position: relative;">
                    <!-- Logs Tab -->
                    <div class="tab-pane fade show active h-100 p-3" id="tab-logs" role="tabpanel">
                        <div id="dtl-logs-container" class="h-100 overflow-y-auto font-mono rounded p-3" style="background: #000; border: 1px solid rgba(255,255,255,0.05); font-size: 0.75rem;">
                            <div class="text-muted"><i class="fa-solid fa-circle-notch fa-spin me-2"></i> Connecting to log stream...</div>
                        </div>
                    </div>
                    
                    <!-- Payload Tab -->
                    <div class="tab-pane fade h-100 p-3" id="tab-payload" role="tabpanel">
                        <h6 class="text-light font-mono mb-2" style="font-size: 0.8rem;">Input Payload</h6>
                        <pre class="rounded p-3 text-light" style="background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); font-size: 0.75rem;">{{ $task->payload_data ? json_encode($task->payload_data, JSON_PRETTY_PRINT) : 'None' }}</pre>
                        
                        <h6 class="text-light font-mono mb-2 mt-4" style="font-size: 0.8rem;">Execution Result</h6>
                        <pre id="dtl-result-container" class="rounded p-3 text-light" style="background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); font-size: 0.75rem;">{{ $task->result_data ? json_encode($task->result_data, JSON_PRETTY_PRINT) : 'Pending execution...' }}</pre>
                    </div>

                    <!-- Trace Tab -->
                    <div class="tab-pane fade h-100 p-3" id="tab-trace" role="tabpanel">
                        <div class="text-muted text-center py-5">
                            <i class="fa-solid fa-code-merge mb-3" style="font-size: 2rem; opacity: 0.5;"></i>
                            <p class="font-mono">Trace visualization not yet populated for this task.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Sidebar (Right) -->
        <div class="col-lg-4 d-flex flex-column gap-3">
            
            <!-- Context & Description -->
            <div class="tasks-glass-card p-3">
                <h6 class="text-light font-mono mb-3" style="font-size: 0.8rem;"><i class="fa-solid fa-align-left text-muted me-2"></i>Description</h6>
                <p class="text-muted mb-0" style="font-size: 0.85rem; line-height: 1.5;">
                    {{ $task->description ?? 'No description provided.' }}
                </p>
                
                @if($task->tags)
                <div class="mt-3">
                    @foreach(explode(',', $task->tags) as $tag)
                        <span class="badge bg-dark border border-secondary text-muted font-mono me-1">{{ trim($tag) }}</span>
                    @endforeach
                </div>
                @endif
            </div>

            <!-- Retry Management Panel (F18) -->
            <div class="tasks-glass-card p-3 border-warning" style="border-left: 3px solid var(--tasks-warning);">
                <h6 class="text-light font-mono mb-3" style="font-size: 0.8rem;"><i class="fa-solid fa-rotate-right text-warning me-2"></i>Retry Management</h6>
                
                <div class="d-flex justify-content-between align-items-center mb-3 p-2 rounded" style="background: rgba(0,0,0,0.2);">
                    <span class="text-muted font-mono" style="font-size: 0.7rem;">Attempts</span>
                    <span class="text-light font-mono"><span id="dtl-attempts">{{ $task->attempts ?? 0 }}</span> / {{ $task->max_attempts ?? 3 }}</span>
                </div>

                <div class="mb-3 p-2 rounded" style="background: rgba(0,0,0,0.2);">
                    <span class="text-muted font-mono d-block mb-1" style="font-size: 0.7rem;">Last Error</span>
                    <span class="text-danger font-mono" style="font-size: 0.7rem; word-break: break-all;" id="dtl-last-error">
                        {{ $task->last_error ?? 'None' }}
                    </span>
                </div>

                <button class="btn btn-sm btn-outline-warning w-100" id="btn-dtl-force-retry">
                    <i class="fa-solid fa-hammer me-2"></i>Force Manual Retry
                </button>
            </div>

            <!-- Attributes / Meta -->
            <div class="tasks-glass-card p-3">
                <h6 class="text-light font-mono mb-3" style="font-size: 0.8rem;"><i class="fa-solid fa-database text-muted me-2"></i>Metadata</h6>
                <ul class="list-unstyled mb-0 font-mono text-muted" style="font-size: 0.75rem;">
                    <li class="d-flex justify-content-between mb-2"><span>Due Date:</span> <span class="text-light">{{ $task->due_date ? $task->due_date->format('Y-m-d') : 'None' }}</span></li>
                    <li class="d-flex justify-content-between mb-2"><span>Completed At:</span> <span class="text-light" id="dtl-completed-at">{{ $task->completed_at ? $task->completed_at->format('Y-m-d H:i') : '-' }}</span></li>
                    <li class="d-flex justify-content-between"><span>Updated At:</span> <span class="text-light">{{ $task->updated_at->format('Y-m-d H:i') }}</span></li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@section('hub_scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const taskId = {{ $task->id }};
    let currentStatus = '{{ $task->status }}';
    
    // --- State Machine Visualizer ---
    function updateStateMachine(status) {
        $('.state-machine-node').removeClass('active completed');
        $('.state-machine-line').removeClass('active');

        if(status === 'todo') {
            $('.state-machine-node[data-state="todo"]').addClass('active');
        } else if (status === 'in-progress') {
            $('.state-machine-node[data-state="todo"]').addClass('completed');
            $('.state-machine-line[data-line="todo-inprogress"]').addClass('active');
            $('.state-machine-node[data-state="in-progress"]').addClass('active');
        } else if (status === 'completed') {
            $('.state-machine-node[data-state="todo"]').addClass('completed');
            $('.state-machine-line[data-line="todo-inprogress"]').addClass('active');
            $('.state-machine-node[data-state="in-progress"]').addClass('completed');
            $('.state-machine-line[data-line="inprogress-completed"]').addClass('active');
            $('.state-machine-node[data-state="completed"]').addClass('completed');
        } else if (status === 'failed' || status === 'blocked') {
            $('.state-machine-node[data-state="in-progress"]').addClass('active').css('background', 'var(--tasks-danger)').css('border-color', 'var(--tasks-danger)');
        }
    }
    updateStateMachine(currentStatus);

    // --- Actions ---
    $('#btn-dtl-execute, #btn-dtl-force-retry').on('click', function() {
        TaskAPI.post(`/tasks/${taskId}/execute`).done(res => Nexus.notify('Execution triggered', 'success'));
    });
    $('#btn-dtl-pause').on('click', function() {
        TaskAPI.post(`/tasks/${taskId}/pause`).done(res => Nexus.notify('Task paused', 'warning'));
    });
    $('#btn-dtl-cancel').on('click', function() {
        TaskAPI.post(`/tasks/${taskId}/cancel`).done(res => Nexus.notify('Task cancelled', 'secondary'));
    });

    // --- Live Updates via Polling & WebSocket ---
    function fetchLogs() {
        TaskAPI.get(`/tasks/${taskId}/logs`, { limit: 50 }).done(function(res) {
            let container = $('#dtl-logs-container');
            container.empty();
            let logs = res.data || [];
            if(logs.length === 0) {
                container.html('<div class="text-muted">No logs recorded yet.</div>');
                return;
            }
            logs.reverse().forEach(log => appendLog(log, container));
            container.scrollTop(container[0].scrollHeight);
        });
    }

    function appendLog(log, container = $('#dtl-logs-container')) {
        let color = 'text-muted';
        if(log.level === 'error' || log.level === 'critical') color = 'text-danger';
        else if(log.level === 'warning') color = 'text-warning';
        else if(log.level === 'info') color = 'text-info';
        
        let time = new Date(log.created_at).toLocaleTimeString();
        container.append(`<div class="mb-1"><span class="text-secondary">[${time}]</span> <span class="${color}">${log.level.toUpperCase()}</span>: <span class="text-light">${log.message}</span></div>`);
    }

    fetchLogs();

    function fetchTaskDetails() {
        TaskAPI.get(`/tasks/${taskId}`).done(function(res) {
            let t = res.data;
            if(t.status !== currentStatus) {
                currentStatus = t.status;
                updateStateMachine(currentStatus);
                let badgeColor = (t.status==='completed')?'success':(t.status==='failed'?'danger':(t.status==='in-progress'?'primary':'secondary'));
                $('#dtl-status-badge').text(t.status.toUpperCase()).removeClass().addClass('badge bg-' + badgeColor);
            }
            
            $('#dtl-progress-text').text((t.progress || 0) + '%');
            $('#dtl-progress-bar').css('width', (t.progress || 0) + '%');
            if(t.progress === 100) $('#dtl-progress-bar').css('background', 'var(--tasks-success)');
            
            $('#dtl-attempts').text(t.attempts || 0);
            if(t.last_error) $('#dtl-last-error').text(t.last_error);
            if(t.result_data) $('#dtl-result-container').text(JSON.stringify(t.result_data, null, 2));
        });
    }

    setInterval(fetchTaskDetails, 5000);
    setInterval(fetchLogs, 10000); // Polling fallback for logs

    // Listen for WebSocket events if Echo is available
    if (window.Echo) {
        window.Echo.channel(`tasks.${taskId}`)
            .listen('TaskLogCreated', (e) => {
                appendLog(e.log);
                let c = $('#dtl-logs-container');
                c.scrollTop(c[0].scrollHeight);
            })
            .listen('TaskProgressUpdated', (e) => {
                $('#dtl-progress-text').text(e.progress + '%');
                $('#dtl-progress-bar').css('width', e.progress + '%');
            });
    }
});
</script>
@endsection
