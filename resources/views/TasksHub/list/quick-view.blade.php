<!-- Quick View Sidebar Overlay -->
<div id="quick-view-overlay" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1040; backdrop-filter: blur(2px);"></div>

<!-- Quick View Sidebar Panel -->
<div id="quick-view-panel" class="tasks-glass-card shadow-lg" style="display: block; position: fixed; top: var(--tasks-topnav-height, 60px); right: -450px; bottom: 0; width: 450px; z-index: 1050; border-right: none; border-top-right-radius: 0; border-bottom-right-radius: 0; transition: right 0.3s cubic-bezier(0.4, 0, 0.2, 1); overflow-y: auto;">
    
    <!-- Header -->
    <div class="p-3 border-bottom border-secondary d-flex justify-content-between align-items-center sticky-top" style="background: rgba(15,23,42,0.95); backdrop-filter: blur(10px);">
        <div>
            <h6 class="mb-0 text-light font-mono d-flex align-items-center gap-2">
                <span id="qv-id">#--</span>
                <span id="qv-status-badge"></span>
            </h6>
        </div>
        <div class="d-flex gap-2">
            <a href="#" id="qv-full-details-link" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>
            <button class="btn btn-sm btn-outline-secondary" id="btn-close-qv"><i class="fa-solid fa-xmark"></i></button>
        </div>
    </div>

    <!-- Content -->
    <div class="p-4" id="qv-content">
        <!-- Title & Basic Info -->
        <h5 id="qv-title" class="text-light mb-3">--</h5>
        
        <div class="row g-3 mb-4">
            <div class="col-6">
                <p class="text-muted font-mono mb-1" style="font-size: 0.65rem; text-transform: uppercase;">Type</p>
                <div id="qv-type">--</div>
            </div>
            <div class="col-6">
                <p class="text-muted font-mono mb-1" style="font-size: 0.65rem; text-transform: uppercase;">Priority</p>
                <div id="qv-priority">--</div>
            </div>
            <div class="col-6">
                <p class="text-muted font-mono mb-1" style="font-size: 0.65rem; text-transform: uppercase;">Agent</p>
                <div id="qv-agent" class="text-light" style="font-size: 0.85rem;">--</div>
            </div>
            <div class="col-6">
                <p class="text-muted font-mono mb-1" style="font-size: 0.65rem; text-transform: uppercase;">Created</p>
                <div id="qv-created" class="text-light" style="font-size: 0.85rem;">--</div>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="mb-4">
            <div class="d-flex justify-content-between mb-1">
                <p class="text-muted font-mono mb-0" style="font-size: 0.65rem; text-transform: uppercase;">Execution Progress</p>
                <span id="qv-progress-text" class="text-light font-mono" style="font-size: 0.75rem;">0%</span>
            </div>
            <div style="height: 6px; background: rgba(255,255,255,0.1); border-radius: 3px; overflow: hidden;">
                <div id="qv-progress-bar" style="width: 0%; height: 100%; background: var(--tasks-primary); transition: width 0.3s ease;"></div>
            </div>
        </div>

        <!-- Actions -->
        <div class="d-flex gap-2 mb-4 pb-4 border-bottom border-secondary">
            <button class="btn btn-sm btn-success flex-grow-1" id="qv-btn-execute"><i class="fa-solid fa-play me-1"></i>Execute</button>
            <button class="btn btn-sm btn-warning flex-grow-1" id="qv-btn-pause"><i class="fa-solid fa-pause me-1"></i>Pause</button>
            <button class="btn btn-sm btn-secondary flex-grow-1" id="qv-btn-cancel"><i class="fa-solid fa-xmark me-1"></i>Cancel</button>
        </div>

        <!-- Recent Logs -->
        <h6 class="text-light mb-3 font-mono" style="font-size: 0.8rem;"><i class="fa-solid fa-terminal me-2 text-muted"></i>Recent Logs</h6>
        <div id="qv-logs-container" class="rounded p-2" style="background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.05); min-height: 100px;">
            <div class="text-center text-muted py-3 font-mono" style="font-size: 0.75rem;">Loading logs...</div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let activeQvTaskId = null;
    let qvPollTimer = null;

    window.openQuickView = function(taskId) {
        activeQvTaskId = taskId;
        $('#quick-view-overlay').fadeIn(200);
        $('#quick-view-panel').css('right', '0');
        
        // Setup Detail Link
        $('#qv-full-details-link').attr('href', '/hub/tasks/' + taskId);

        // Load initial data
        loadQvData();
        
        // Start polling if open
        if(qvPollTimer) clearInterval(qvPollTimer);
        qvPollTimer = setInterval(loadQvData, 5000);
    };

    function closeQuickView() {
        activeQvTaskId = null;
        if(qvPollTimer) clearInterval(qvPollTimer);
        $('#quick-view-overlay').fadeOut(200);
        $('#quick-view-panel').css('right', '-450px');
    }

    $('#btn-close-qv, #quick-view-overlay').on('click', closeQuickView);

    function loadQvData() {
        if(!activeQvTaskId || !window.TaskAPI) return;

        // Fetch task details
        TaskAPI.get(`/tasks/${activeQvTaskId}`).done(function(res) {
            let task = res.data;
            $('#qv-id').text('#' + task.id);
            $('#qv-title').text(task.title);
            $('#qv-type').html(`<span class="badge bg-secondary">${task.type}</span>`);
            $('#qv-priority').text(task.priority);
            $('#qv-agent').text(task.agent_id ? 'Agent ID: ' + task.agent_id : '-');
            $('#qv-created').text(new Date(task.created_at).toLocaleString());
            
            // Progress
            let pct = task.progress || 0;
            $('#qv-progress-text').text(pct + '%');
            $('#qv-progress-bar').css('width', pct + '%');
            if(pct === 100) $('#qv-progress-bar').css('background', 'var(--tasks-success)');
            else $('#qv-progress-bar').css('background', 'var(--tasks-primary)');

            // Status Badge (reuse mapping from list view if possible, else basic)
            let color = 'secondary';
            if(task.status === 'in-progress') color = 'primary';
            if(task.status === 'completed') color = 'success';
            if(task.status === 'failed') color = 'danger';
            if(task.status === 'blocked') color = 'warning';
            $('#qv-status-badge').html(`<span class="badge bg-${color}">${task.status.toUpperCase()}</span>`);
        });

        // Fetch logs
        TaskAPI.get(`/tasks/${activeQvTaskId}/logs`, { limit: 5 }).done(function(res) {
            let logs = res.data;
            let container = $('#qv-logs-container');
            container.empty();
            if(logs.length === 0) {
                container.html('<div class="text-muted font-mono" style="font-size: 0.75rem;">No logs available.</div>');
                return;
            }
            logs.reverse().forEach(log => {
                let color = 'text-muted';
                if(log.level === 'error' || log.level === 'critical') color = 'text-danger';
                else if(log.level === 'warning') color = 'text-warning';
                else if(log.level === 'info') color = 'text-info';
                
                let time = new Date(log.created_at).toLocaleTimeString();
                container.append(`<div class="font-mono mb-1" style="font-size: 0.7rem; line-height: 1.2;">
                    <span class="text-secondary">[${time}]</span> <span class="${color}">${log.level.toUpperCase()}</span>: <span class="text-light">${log.message}</span>
                </div>`);
            });
        });
    }

    // Buttons
    $('#qv-btn-execute').on('click', () => {
        TaskAPI.post(`/tasks/${activeQvTaskId}/execute`).done(() => { Nexus.notify('Execution triggered', 'success'); loadQvData(); });
    });
    $('#qv-btn-pause').on('click', () => {
        TaskAPI.post(`/tasks/${activeQvTaskId}/pause`).done(() => { Nexus.notify('Task paused', 'warning'); loadQvData(); });
    });
    $('#qv-btn-cancel').on('click', () => {
        TaskAPI.post(`/tasks/${activeQvTaskId}/cancel`).done(() => { Nexus.notify('Task cancelled', 'secondary'); loadQvData(); });
    });
});
</script>
