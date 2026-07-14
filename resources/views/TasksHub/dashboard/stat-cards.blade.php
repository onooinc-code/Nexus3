<div class="row g-3 mb-4" id="dashboard-stat-cards">
    <!-- Stat Card Template -->
    @php
        $cards = [
            ['id' => 'stat-total', 'title' => 'Total Tasks', 'icon' => 'fa-bars-staggered', 'color' => 'var(--tasks-primary)'],
            ['id' => 'stat-running', 'title' => 'Running', 'icon' => 'fa-play', 'color' => 'var(--tasks-primary)'],
            ['id' => 'stat-pending', 'title' => 'Pending', 'icon' => 'fa-clock', 'color' => 'var(--tasks-warning)'],
            ['id' => 'stat-blocked', 'title' => 'Blocked', 'icon' => 'fa-ban', 'color' => 'var(--tasks-warning)'],
            ['id' => 'stat-completed', 'title' => 'Completed', 'icon' => 'fa-check-double', 'color' => 'var(--tasks-success)'],
            ['id' => 'stat-failed', 'title' => 'Failed', 'icon' => 'fa-triangle-exclamation', 'color' => 'var(--tasks-danger)'],
            ['id' => 'stat-cancelled', 'title' => 'Cancelled', 'icon' => 'fa-xmark', 'color' => 'gray'],
            ['id' => 'stat-queued', 'title' => 'Queue Depth', 'icon' => 'fa-layer-group', 'color' => 'var(--tasks-info)'],
            ['id' => 'stat-dlq', 'title' => 'DLQ', 'icon' => 'fa-skull', 'color' => '#4B5563'],
            ['id' => 'stat-sla', 'title' => 'SLA Breached', 'icon' => 'fa-stopwatch', 'color' => 'var(--tasks-danger)'],
        ];
    @endphp

    @foreach($cards as $card)
    <div class="col-6 col-md-3 col-xl">
        <div class="tasks-glass-card p-3 h-100 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="text-muted font-mono" style="font-size: 0.65rem; text-transform: uppercase;">{{ $card['title'] }}</span>
                <i class="fa-solid {{ $card['icon'] }}" style="color: {{ $card['color'] }}; font-size: 0.8rem; opacity: 0.8;"></i>
            </div>
            <div>
                <h3 class="mb-0 fw-bold font-mono" id="{{ $card['id'] }}" style="font-size: 1.5rem; letter-spacing: -1px;">0</h3>
                <div class="d-flex align-items-center mt-1" style="font-size: 0.65rem;">
                    <span class="text-success"><i class="fa-solid fa-arrow-trend-up me-1"></i>0%</span>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function pollDashboardStats() {
        if (!window.TaskAPI) return;
        
        // Fetch stats
        TaskAPI.get('/tasks/stats').done(function(res) {
            if(res.data) {
                animateValue('stat-total', res.data.total || 0);
                animateValue('stat-running', res.data.in_progress || 0);
                animateValue('stat-pending', res.data.todo || 0);
                animateValue('stat-blocked', res.data.blocked || 0);
                animateValue('stat-completed', res.data.completed || 0);
                animateValue('stat-failed', res.data.failed || 0);
                animateValue('stat-cancelled', res.data.cancelled || 0);
                // Placeholder SLA for now
                animateValue('stat-sla', Math.floor((res.data.failed || 0) * 0.1));
            }
        });

        // Fetch queue stats
        TaskAPI.get('/tasks/queue-stats').done(function(res) {
            if(res.data) {
                animateValue('stat-queued', res.data.queued || 0);
                animateValue('stat-dlq', res.data.dlq_count || 0); // Assuming DLQ count might come here or separate
            }
        });
    }

    function animateValue(id, end, duration = 1000) {
        let obj = document.getElementById(id);
        if (!obj) return;
        let start = parseInt(obj.innerHTML) || 0;
        if (start === end) return;
        let range = end - start;
        let current = start;
        let increment = end > start ? 1 : -1;
        let stepTime = Math.abs(Math.floor(duration / range));
        if(stepTime < 10) stepTime = 10;
        let timer = setInterval(function() {
            current += increment;
            obj.innerHTML = current;
            if (current == end) {
                clearInterval(timer);
            }
        }, stepTime);
    }

    // Polling every 10s if Dashboard is active tab
    pollDashboardStats();
    setInterval(function() {
        if ($('#content-dashboard').hasClass('active')) {
            pollDashboardStats();
        }
    }, 10000);
});
</script>
