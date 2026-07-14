<div class="row g-3 mb-4">
    <!-- Timeline Chart -->
    <div class="col-md-8">
        <div class="tasks-glass-card p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0 font-inter text-light" style="font-size: 0.85rem;"><i class="fa-solid fa-chart-line me-2 text-muted"></i>Execution Timeline</h6>
                <div class="btn-group btn-group-sm" role="group">
                    <input type="radio" class="btn-check" name="btnradio" id="btnradio1" autocomplete="off" checked>
                    <label class="btn btn-outline-secondary" for="btnradio1" style="font-size: 0.7rem;">7D</label>
                    <input type="radio" class="btn-check" name="btnradio" id="btnradio2" autocomplete="off">
                    <label class="btn btn-outline-secondary" for="btnradio2" style="font-size: 0.7rem;">30D</label>
                </div>
            </div>
            <div style="height: 250px;">
                <canvas id="chart-timeline"></canvas>
            </div>
        </div>
    </div>

    <!-- Status Donut -->
    <div class="col-md-4">
        <div class="tasks-glass-card p-3 h-100">
            <h6 class="mb-3 font-inter text-light" style="font-size: 0.85rem;"><i class="fa-solid fa-chart-pie me-2 text-muted"></i>Status Distribution</h6>
            <div style="height: 200px; display: flex; justify-content: center;">
                <canvas id="chart-status"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- Type Breakdown Bar -->
    <div class="col-md-6">
        <div class="tasks-glass-card p-3 h-100">
            <h6 class="mb-3 font-inter text-light" style="font-size: 0.85rem;"><i class="fa-solid fa-chart-bar me-2 text-muted"></i>Tasks by Type</h6>
            <div style="height: 200px;">
                <canvas id="chart-type"></canvas>
            </div>
        </div>
    </div>

    <!-- Agent Performance -->
    <div class="col-md-6">
        <div class="tasks-glass-card p-3 h-100">
            <h6 class="mb-3 font-inter text-light" style="font-size: 0.85rem;"><i class="fa-solid fa-robot me-2 text-muted"></i>Agent Performance</h6>
            <div style="height: 200px;">
                <canvas id="chart-agent"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Chart.js defaults
    Chart.defaults.color = 'rgba(255, 255, 255, 0.5)';
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(15, 23, 42, 0.9)';
    Chart.defaults.plugins.tooltip.titleColor = '#fff';
    Chart.defaults.plugins.tooltip.bodyColor = '#ccc';
    Chart.defaults.plugins.tooltip.borderColor = 'rgba(255,255,255,0.1)';
    Chart.defaults.plugins.tooltip.borderWidth = 1;

    let charts = {};

    function initCharts() {
        // Timeline Chart
        const ctxTimeline = document.getElementById('chart-timeline').getContext('2d');
        charts.timeline = new Chart(ctxTimeline, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [
                    { label: 'Completed', data: [12, 19, 3, 5, 2, 3, 9], borderColor: '#10B981', backgroundColor: 'rgba(16, 185, 129, 0.1)', fill: true, tension: 0.4 },
                    { label: 'Failed', data: [2, 3, 1, 0, 1, 0, 2], borderColor: '#EF4444', backgroundColor: 'transparent', tension: 0.4 }
                ]
            },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' } }, x: { grid: { display: false } } }, plugins: { legend: { position: 'top', labels: { boxWidth: 10, font: { size: 10 } } } } }
        });

        // Status Donut
        const ctxStatus = document.getElementById('chart-status').getContext('2d');
        charts.status = new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: ['Todo', 'Running', 'Blocked', 'Completed', 'Failed', 'Cancelled'],
                datasets: [{
                    data: [10, 5, 2, 50, 3, 1],
                    backgroundColor: ['rgba(255,255,255,0.1)', 'rgba(99, 102, 241, 0.8)', '#F59E0B', '#10B981', '#EF4444', 'gray'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, cutout: '75%', plugins: { legend: { position: 'right', labels: { boxWidth: 10, font: { size: 10 } } } } }
        });

        // Type Breakdown Bar
        const ctxType = document.getElementById('chart-type').getContext('2d');
        charts.type = new Chart(ctxType, {
            type: 'bar',
            data: {
                labels: ['Manual', 'Agent', 'System', 'Code', 'API', 'Terminal', 'Tool'],
                datasets: [{
                    label: 'Tasks',
                    data: [20, 45, 15, 10, 30, 5, 8],
                    backgroundColor: 'rgba(99, 102, 241, 0.6)',
                    borderRadius: 4
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' } }, x: { grid: { display: false } } }, plugins: { legend: { display: false } } }
        });

        // Agent Performance
        const ctxAgent = document.getElementById('chart-agent').getContext('2d');
        charts.agent = new Chart(ctxAgent, {
            type: 'bar',
            data: {
                labels: ['AutoAgent', 'SpecAgent', 'ReflectAgent', 'Supervisor'],
                datasets: [{
                    label: 'Success Rate %',
                    data: [95, 88, 100, 92],
                    backgroundColor: 'rgba(16, 185, 129, 0.6)',
                    borderRadius: 4
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y', scales: { x: { max: 100, beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' } }, y: { grid: { display: false } } }, plugins: { legend: { display: false } } }
        });
    }

    // Delay init to ensure Chart.js is fully parsed and canvas is visible
    setTimeout(initCharts, 500);

    // Poll data for charts
    function pollChartData() {
        if (!window.TaskAPI || !charts.status) return;

        TaskAPI.get('/tasks/stats').done(function(res) {
            if(res.data) {
                // Update Donut
                charts.status.data.datasets[0].data = [
                    res.data.todo || 0,
                    res.data.in_progress || 0,
                    res.data.blocked || 0,
                    res.data.completed || 0,
                    res.data.failed || 0,
                    res.data.cancelled || 0
                ];
                charts.status.update();
            }
        });

        TaskAPI.get('/tasks/stats/by-type').done(function(res) {
            if(res.data) {
                // Assuming res.data is key-value: { 'manual': 10, 'agent': 20, ... }
                let types = ['manual', 'agent', 'system', 'code', 'api', 'terminal', 'tool'];
                let data = types.map(t => res.data[t] ? res.data[t].total || 0 : 0);
                charts.type.data.datasets[0].data = data;
                charts.type.update();
            }
        });
    }

    setInterval(function() {
        if ($('#content-dashboard').hasClass('active')) {
            pollChartData();
        }
    }, 30000); // 30s for charts
});
</script>
