<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 p-4 border-bottom" style="border-color: var(--glass-border) !important;">
    <div class="d-flex align-items-center gap-3">
        <div style="width: 42px; height: 42px; background: var(--nexus-teal-dim); border: 1px solid hsla(174,90%,41%,0.3); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
            <i class="fa-solid fa-list-check" style="color: var(--nexus-teal); font-size: 1.1rem;"></i>
        </div>
        <div>
            <h1 class="mb-0" style="font-size: 1.4rem; font-weight: 700; letter-spacing: -0.02em;">Task Objectives</h1>
            <p class="text-muted mb-0" style="font-size: 0.8rem;">AI agent task management & execution monitoring</p>
        </div>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <!-- Vue App Mount Point for Notification Hub (if any specific to tasks) -->
        <!-- Global Notification Hub is already in app layout -->

        <div class="dropdown">
            <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" style="font-size: 0.78rem; padding: 6px 16px; border-radius: 8px;">
                <i class="fa-solid fa-plus me-1"></i> New Task
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="background: var(--bg-dark); border: 1px solid var(--glass-border);">
                <li><a class="dropdown-item text-light" href="#" data-bs-toggle="modal" data-bs-target="#newTaskModal" data-task-type="manual"><i class="fa-solid fa-user me-2"></i>Manual Task</a></li>
                <li><a class="dropdown-item text-light" href="#" data-bs-toggle="modal" data-bs-target="#newTaskModal" data-task-type="agent"><i class="fa-solid fa-robot me-2 text-info"></i>Agent Task</a></li>
                <li><a class="dropdown-item text-light" href="#" data-bs-toggle="modal" data-bs-target="#newTaskModal" data-task-type="system"><i class="fa-solid fa-microchip me-2 text-warning"></i>System Task</a></li>
            </ul>
        </div>
    </div>
</div>

{{-- ═══ NEW TASK MODAL ═══ --}}
<div class="modal fade" id="newTaskModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center gap-2">
                    <i class="fa-solid fa-plus" style="color: var(--nexus-teal);"></i> <span id="newTaskTitleText">New Task</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="new-task-form">
                    @csrf
                    <input type="hidden" name="type" id="taskTypeInput" value="manual">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); font-family: 'JetBrains Mono';">Task Title *</label>
                            <input type="text" name="title" class="form-control" placeholder="Describe the task..." required>
                        </div>
                        <div class="col-12">
                            <label class="form-label" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); font-family: 'JetBrains Mono';">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Task details and objectives..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); font-family: 'JetBrains Mono';">Priority</label>
                            <select name="priority" class="form-select">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); font-family: 'JetBrains Mono';">Due Date</label>
                            <input type="datetime-local" name="due_at" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); font-family: 'JetBrains Mono';">Payload (JSON)</label>
                            <textarea name="payload" class="form-control" rows="3" placeholder='{"key": "value"}' style="font-family: 'JetBrains Mono'; font-size: 0.75rem;"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: var(--text-secondary); border-radius: 7px;" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-primary" id="btn-create-task" style="border-radius: 7px;">
                    <i class="fa-solid fa-check me-1"></i> Create Task
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const newTaskModal = document.getElementById('newTaskModal')
    if(newTaskModal) {
        newTaskModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget
            const type = button.getAttribute('data-task-type')
            document.getElementById('taskTypeInput').value = type;
            
            let typeText = 'Manual Task';
            if(type === 'agent') typeText = 'Agent Task';
            if(type === 'system') typeText = 'System Task';
            
            document.getElementById('newTaskTitleText').innerText = 'New ' + typeText;
        });
    }

    $('#btn-create-task').on('click', function() {
        const form = $('#new-task-form');
        const data = form.serializeArray();
        let payload = {};
        data.forEach(item => { payload[item.name] = item.value; });
        
        const $btn = $(this);
        $btn.html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Creating...').prop('disabled', true);

        // Map due_at to due_date and payload to payload_data for standard store
        let postData = {
            title: payload.title,
            description: payload.description,
            type: payload.type,
            priority: payload.priority,
            due_date: payload.due_at,
            payload_data: payload.payload
        };
        
        // CSRF Token
        postData._token = form.find('input[name="_token"]').val();

        $.ajax({
            url: '/api/tasks',
            method: 'POST',
            data: postData,
            success: function(res) {
                if(window.Nexus && window.Nexus.notify) {
                    Nexus.notify('Task created successfully!', 'success');
                }
                bootstrap.Modal.getInstance(newTaskModal).hide();
                $btn.html('<i class="fa-solid fa-check me-1"></i> Create Task').prop('disabled', false);
                form[0].reset();
                // Kanban board should auto-update via Echo or we can reload data
                // Dispatch a custom event to tell Vue to refresh
                window.dispatchEvent(new CustomEvent('task-created'));
            },
            error: function(err) {
                console.error(err);
                if(window.Nexus && window.Nexus.notify) {
                    Nexus.notify('Failed to create task. Check the form.', 'error');
                } else {
                    alert('Failed to create task');
                }
                $btn.html('<i class="fa-solid fa-check me-1"></i> Create Task').prop('disabled', false);
            }
        });
    });
});
</script>
