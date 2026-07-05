@include('hubs.partials.ai-hub.dashboard.summary-cards')
@include('hubs.partials.ai-hub.dashboard.charts-section')

<div class="row g-4">
    @include('hubs.partials.ai-hub.dashboard.leaderboards')
    @include('hubs.partials.ai-hub.dashboard.alerts-panel')
</div>

@push('scripts')
<script>
    // These scripts will run when the tab is shown or the page loads
    document.addEventListener("DOMContentLoaded", function() {
        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } }
        };

        // Sparkline
        if(document.getElementById('sparkline-requests')) {
            new Chart(document.getElementById('sparkline-requests'), {
                type: 'line',
                data: {
                    labels: ['1','2','3','4','5','6','7','8','9','10'],
                    datasets: [{
                        data: [12, 19, 15, 25, 22, 30, 28, 35, 32, 40],
                        borderColor: '#3b82f6',
                        borderWidth: 2,
                        pointRadius: 0,
                        tension: 0.4
                    }]
                },
                options: {
                    ...commonOptions,
                    scales: { x: { display: false }, y: { display: false } },
                    layout: { padding: 0 }
                }
            });
        }

        // Mini Doughnuts
        const doughnutOptions = {
            ...commonOptions,
            cutout: '75%',
            events: []
        };
        
        if(document.getElementById('chart-success-rate')) {
            new Chart(document.getElementById('chart-success-rate'), {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: [98.7, 1.3],
                        backgroundColor: ['#10b981', '#334155'],
                        borderWidth: 0
                    }]
                },
                options: doughnutOptions
            });
        }

        if(document.getElementById('chart-cache-rate')) {
            new Chart(document.getElementById('chart-cache-rate'), {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: [43, 57],
                        backgroundColor: ['#8b5cf6', '#334155'],
                        borderWidth: 0
                    }]
                },
                options: doughnutOptions
            });
        }
        
        // Main Tokens Chart
        if(document.getElementById('chart-tokens-timeline')) {
            new Chart(document.getElementById('chart-tokens-timeline'), {
                type: 'line',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [
                        {
                            label: 'Input Tokens',
                            data: [1.2, 1.9, 1.5, 2.1, 1.8, 2.5, 2.2],
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Output Tokens',
                            data: [0.8, 1.1, 0.9, 1.5, 1.2, 1.7, 1.4],
                            borderColor: '#f59e0b',
                            backgroundColor: 'rgba(245, 158, 11, 0.1)',
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { labels: { color: '#94a3b8' } }
                    },
                    scales: {
                        y: { grid: { color: '#334155' }, ticks: { color: '#94a3b8' } },
                        x: { grid: { display: false }, ticks: { color: '#94a3b8' } }
                    }
                }
            });
        }
        
        // Stacked Bar Cost Provider
        if(document.getElementById('chart-cost-provider')) {
            new Chart(document.getElementById('chart-cost-provider'), {
                type: 'bar',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [
                        {
                            label: 'OpenAI',
                            data: [12, 15, 10, 22, 18, 25, 20],
                            backgroundColor: '#10b981'
                        },
                        {
                            label: 'Anthropic',
                            data: [5, 8, 4, 10, 12, 15, 10],
                            backgroundColor: '#f59e0b'
                        },
                        {
                            label: 'Google',
                            data: [3, 2, 5, 4, 3, 5, 6],
                            backgroundColor: '#3b82f6'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { labels: { color: '#94a3b8', boxWidth: 12 } }
                    },
                    scales: {
                        x: { stacked: true, grid: { display: false }, ticks: { color: '#94a3b8' } },
                        y: { stacked: true, grid: { color: '#334155' }, ticks: { color: '#94a3b8' } }
                    }
                }
            });
        }
    });
</script>
@endpush
