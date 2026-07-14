<div class="tasks-status-bar tasks-glass-panel d-flex align-items-center justify-content-between px-3" style="height: var(--tasks-bottombar-height);">
    <!-- Left: Quick Actions -->
    <div class="d-flex align-items-center gap-2">
        <button class="btn btn-sm btn-primary d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#newTaskModal" style="border-radius: 6px; padding: 4px 12px;">
            <i class="fa-solid fa-plus" style="font-size: 0.75rem;"></i> Task
        </button>
        <div style="width: 1px; height: 20px; background: rgba(255,255,255,0.1); margin: 0 4px;"></div>
        <button class="btn btn-sm text-light d-flex align-items-center gap-1 opacity-50" disabled style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 6px; font-size: 0.8rem;">
            <i class="fa-solid fa-play" style="color: var(--tasks-success);"></i> Run
        </button>
        <button class="btn btn-sm text-light d-flex align-items-center gap-1 opacity-50" disabled style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 6px; font-size: 0.8rem;">
            <i class="fa-solid fa-pause" style="color: var(--tasks-warning);"></i> Pause
        </button>
    </div>

    <!-- Center: Live Status Indicators -->
    <div class="d-none d-md-flex align-items-center gap-4 font-mono text-muted" style="font-size: 0.75rem;">
        <div class="d-flex align-items-center gap-2" title="Running Tasks">
            <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: var(--tasks-primary); animation: pulse-opacity 2s infinite;"></span>
            <span id="bb-count-running">--</span>
        </div>
        <div class="d-flex align-items-center gap-2" title="Queued Tasks">
            <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: var(--tasks-warning);"></span>
            <span id="bb-count-queued">--</span>
        </div>
        <div class="d-flex align-items-center gap-2" title="Failed Tasks">
            <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: var(--tasks-danger);"></span>
            <span id="bb-count-failed">--</span>
        </div>
        <div class="d-flex align-items-center gap-2" title="Dead Letter Queue">
            <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #4B5563;"></span>
            <span id="bb-count-dlq">--</span>
        </div>
    </div>

    <!-- Right: Monitor Tools -->
    <div class="d-flex align-items-center gap-3 font-mono" style="font-size: 0.75rem;">
        <div id="bb-backpressure-alert" class="badge bg-danger rounded-pill d-none" style="animation: pulse-opacity 1s infinite;">
            <i class="fa-solid fa-triangle-exclamation me-1"></i> HIGH LOAD
        </div>
        
        <div class="d-flex flex-column align-items-end">
            <span class="text-muted" style="font-size: 0.65rem;">QUEUE DEPTH</span>
            <div class="d-flex align-items-center gap-2">
                <div style="width: 60px; height: 4px; background: rgba(255,255,255,0.1); border-radius: 2px; overflow: hidden;">
                    <div id="bb-queue-bar" style="width: 0%; height: 100%; background: var(--tasks-success); transition: width 0.3s ease;"></div>
                </div>
                <span id="bb-queue-text" class="text-light">0%</span>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function pollBottomBar() {
        if (!window.TaskAPI) return;
        
        // Fetch standard stats
        TaskAPI.get('/tasks/stats').done(function(res) {
            if(res.data) {
                $('#bb-count-running').text(res.data.in_progress || 0);
                $('#bb-count-failed').text(res.data.failed || 0);
            }
        });

        // Fetch queue stats
        TaskAPI.get('/tasks/queue-stats').done(function(res) {
            if(res.data) {
                let queued = res.data.queued || 0;
                $('#bb-count-queued').text(queued);
                
                // Assuming threshold is 100 for gauge
                let threshold = 100; 
                let pct = Math.min((queued / threshold) * 100, 100);
                $('#bb-queue-bar').css('width', pct + '%');
                $('#bb-queue-text').text(Math.round(pct) + '%');
                
                if (pct > 80) {
                    $('#bb-queue-bar').css('background', 'var(--tasks-danger)');
                    $('#bb-backpressure-alert').removeClass('d-none');
                } else if (pct > 50) {
                    $('#bb-queue-bar').css('background', 'var(--tasks-warning)');
                    $('#bb-backpressure-alert').addClass('d-none');
                } else {
                    $('#bb-queue-bar').css('background', 'var(--tasks-success)');
                    $('#bb-backpressure-alert').addClass('d-none');
                }
            }
        });
    }

    // Start polling every 5s
    pollBottomBar();
    setInterval(pollBottomBar, 5000);
});
</script>
