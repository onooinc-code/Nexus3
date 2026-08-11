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

        // Mini Doughnuts options
        const doughnutOptions = {
            ...commonOptions,
            cutout: '75%',
            events: []
        };
        
        // Fetch and load dynamic telemetry for dashboard
        fetch('{{ route("hub.models.telemetry") }}')
            .then(res => {
                if (!res.ok) throw new Error('HTTP Error ' + res.status);
                return res.json();
            })
            .then(json => {
                if (json && json.success && json.data) {
                    const data = json.data;

                    // Update Card Values
                    if ($('#card-total-requests').length) $('#card-total-requests').text(Number(data.total_requests_24h || 0).toLocaleString());
                    if ($('#card-success-rate-val').length) $('#card-success-rate-val').text((data.success_rate || 100) + '%');
                    if ($('#card-avg-latency').length) $('#card-avg-latency').html((data.avg_latency || 0) + '<span class="fs-6 text-muted">ms</span>');
                    if ($('#card-total-cost').length) $('#card-total-cost').text('$' + Number(data.total_cost_month || 0).toFixed(2));
                    if ($('#card-cache-hit-val').length) $('#card-cache-hit-val').text((data.cache_hit_rate || 0) + '%');

                    // Success Rate Doughnut
                    if(document.getElementById('chart-success-rate')) {
                        new Chart(document.getElementById('chart-success-rate'), {
                            type: 'doughnut',
                            data: {
                                datasets: [{
                                    data: [data.success_rate, 100 - data.success_rate],
                                    backgroundColor: ['#10b981', '#334155'],
                                    borderWidth: 0
                                }]
                            },
                            options: doughnutOptions
                        });
                    }

                    // Cache Rate Doughnut
                    if(document.getElementById('chart-cache-rate')) {
                        new Chart(document.getElementById('chart-cache-rate'), {
                            type: 'doughnut',
                            data: {
                                datasets: [{
                                    data: [data.cache_hit_rate, 100 - data.cache_hit_rate],
                                    backgroundColor: ['#8b5cf6', '#334155'],
                                    borderWidth: 0
                                }]
                            },
                            options: doughnutOptions
                        });
                    }

                    // Main Tokens Chart (7 Days)
                    if(document.getElementById('chart-tokens-timeline')) {
                        new Chart(document.getElementById('chart-tokens-timeline'), {
                            type: 'line',
                            data: {
                                labels: data.token_timeline.labels,
                                datasets: [
                                    {
                                        label: 'Input Tokens (K)',
                                        data: data.token_timeline.input,
                                        borderColor: '#3b82f6',
                                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                        borderWidth: 2,
                                        tension: 0.4,
                                        fill: true
                                    },
                                    {
                                        label: 'Output Tokens (K)',
                                        data: data.token_timeline.output,
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
                }
            })
            .catch(err => console.error('Failed to load telemetry data:', err));
        
        // Stacked Bar Cost Provider
        if(document.getElementById('chart-cost-provider')) {
            const ctx = document.getElementById('chart-cost-provider');
            let costChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Loading...'],
                    datasets: [{ label: 'Provider Cost', data: [0], backgroundColor: '#334155' }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { labels: { color: '#94a3b8', boxWidth: 12 } } },
                    scales: {
                        x: { stacked: true, grid: { display: false }, ticks: { color: '#94a3b8' } },
                        y: { stacked: true, grid: { color: '#334155' }, ticks: { color: '#94a3b8' } }
                    }
                }
            });

            fetch('{{ route("hub.models.cost-charts") }}')
                .then(res => res.json())
                .then(json => {
                    if (json.success && json.data.dates.length > 0) {
                        const colors = ['#10b981', '#f59e0b', '#3b82f6', '#8b5cf6', '#ef4444'];
                        let datasets = [];
                        let i = 0;
                        for (const [provider, data] of Object.entries(json.data.series)) {
                            datasets.push({
                                label: provider,
                                data: data,
                                backgroundColor: colors[i % colors.length]
                            });
                            i++;
                        }
                        costChart.data.labels = json.data.dates;
                        costChart.data.datasets = datasets;
                        costChart.update();
                    } else if (json.success) {
                        costChart.data.labels = ['No Data'];
                        costChart.data.datasets = [];
                        costChart.update();
                    }
                })
                .catch(err => console.error('Failed to load cost chart:', err));
        }
    });
</script>
@endpush
