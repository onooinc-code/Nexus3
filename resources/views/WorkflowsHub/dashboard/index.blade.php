<!-- Workflows Dashboard Module -->
<div class="row g-3 h-100 wf-animate-in align-content-start">
    
    <!-- Header -->
    <div class="col-12">
        <h4 class="fw-bold text-light mb-1"><i class="fa-solid fa-chart-line text-primary me-2"></i> Workflow Analytics</h4>
        <p class="text-muted small">Real-time overview of your automation pipelines.</p>
    </div>

    <!-- Stat Cards -->
    <div class="col-md-3">
        <div class="wf-glass-panel p-3 text-center border-primary">
            <h6 class="text-muted text-uppercase small">Total Workflows</h6>
            <h2 class="text-light fw-bold m-0" id="stat-total-wf">{{ count($workflows ?? []) }}</h2>
        </div>
    </div>
    <div class="col-md-3">
        <div class="wf-glass-panel p-3 text-center border-success">
            <h6 class="text-muted text-uppercase small">Success Rate (24h)</h6>
            <h2 class="text-success fw-bold m-0" id="stat-success">98.5%</h2>
        </div>
    </div>
    <div class="col-md-3">
        <div class="wf-glass-panel p-3 text-center border-warning">
            <h6 class="text-muted text-uppercase small">Active Schedules</h6>
            <h2 class="text-warning fw-bold m-0" id="stat-schedules">4</h2>
        </div>
    </div>
    <div class="col-md-3">
        <div class="wf-glass-panel p-3 text-center border-info">
            <h6 class="text-muted text-uppercase small">Executions Today</h6>
            <h2 class="text-info fw-bold m-0" id="stat-executions">1,204</h2>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="col-md-8">
        <div class="wf-glass-panel p-3 h-100 d-flex flex-column">
            <h6 class="text-light fw-bold border-bottom border-secondary pb-2 mb-3">Execution Volume (Last 7 Days)</h6>
            <div class="flex-grow-1 position-relative" style="min-height: 250px;">
                <canvas id="wf-volume-chart"></canvas>
            </div>
        </div>
    </div>

    <!-- Live Activity Feed -->
    <div class="col-md-4">
        <div class="wf-glass-panel p-3 h-100 d-flex flex-column">
            <h6 class="text-light fw-bold border-bottom border-secondary pb-2 mb-3">
                <i class="fa-solid fa-satellite-dish text-success me-2"></i> Live Feed
            </h6>
            <div class="flex-grow-1 overflow-auto pe-2" id="wf-activity-feed" style="max-height: 300px;">
                
                <div class="d-flex mb-3">
                    <div class="rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px; flex-shrink:0;">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-light small">Lead Sync Completed</div>
                        <div class="text-muted" style="font-size: 0.75rem;">Execution #EX-1042 finished in 2.4s.</div>
                        <div class="text-secondary" style="font-size: 0.7rem;">2 mins ago</div>
                    </div>
                </div>

                <div class="d-flex mb-3">
                    <div class="rounded-circle bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px; flex-shrink:0;">
                        <i class="fa-solid fa-xmark"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-light small">Daily Report Failed</div>
                        <div class="text-muted" style="font-size: 0.75rem;">Execution #EX-1041 failed at node 'Send Email'.</div>
                        <div class="text-secondary" style="font-size: 0.7rem;">1 hour ago</div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function() {
        let volumeChart = null;

        function loadDashboardStats() {
            window.WorkflowAPI.request('/hub/workflows/stats', 'GET')
                .then(data => {
                    // Update Stat Cards
                    $('#stat-total-wf').text(data.totalWorkflows);
                    $('#stat-success').text(data.successRate + '%');
                    $('#stat-schedules').text(data.activeSchedules);
                    $('#stat-executions').text(data.executionsToday);

                    // Render Chart
                    renderChart(data.chartData);
                });
        }

        function renderChart(chartData) {
            const ctx = document.getElementById('wf-volume-chart');
            if (ctx) {
                if (volumeChart) volumeChart.destroy();
                volumeChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            label: 'Successful Executions',
                            data: chartData.success,
                            borderColor: '#10b981', // success green
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Failed Executions',
                            data: chartData.failed,
                            borderColor: '#ef4444', // danger red
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { labels: { color: '#94a3b8' } }
                        },
                        scales: {
                            y: { 
                                grid: { color: 'rgba(255,255,255,0.05)' },
                                ticks: { color: '#94a3b8' }
                            },
                            x: { 
                                grid: { display: false },
                                ticks: { color: '#94a3b8' }
                            }
                        }
                    }
                });
            }
        }

        // Initialize
        loadDashboardStats();
    });
</script>
@endpush
