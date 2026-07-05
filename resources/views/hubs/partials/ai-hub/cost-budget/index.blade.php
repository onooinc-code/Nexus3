@include('hubs.partials.ai-hub.cost-budget.summary-cards')
@include('hubs.partials.ai-hub.cost-budget.cost-charts')
@include('hubs.partials.ai-hub.cost-budget.budget-manager')

@include('hubs.partials.ai-hub.cost-budget.modals.edit-budget')

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const tabEl = document.getElementById('budget-tab');
        if (tabEl) {
            tabEl.addEventListener('shown.bs.tab', function () {
                
                // Cost Trend Line Chart
                const ctxTrend = document.getElementById('cost-trend-chart');
                if (ctxTrend && !ctxTrend.dataset.initialized) {
                    new Chart(ctxTrend, {
                        type: 'line',
                        data: {
                            labels: Array.from({length: 30}, (_, i) => i+1),
                            datasets: [
                                {
                                    label: 'Actual Cost',
                                    data: Array.from({length: 15}, () => Math.random() * 8 + 2),
                                    borderColor: '#10b981',
                                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                    borderWidth: 2,
                                    fill: true,
                                    tension: 0.3
                                },
                                {
                                    label: 'Projected Cost',
                                    data: [...Array(14).fill(null), 7, ...Array.from({length: 15}, () => Math.random() * 8 + 2)],
                                    borderColor: '#94a3b8',
                                    borderDash: [5, 5],
                                    borderWidth: 2,
                                    tension: 0.3
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { labels: { color: '#94a3b8' } } },
                            scales: {
                                x: { grid: { display: false }, ticks: { color: '#94a3b8' } },
                                y: { grid: { color: '#334155' }, ticks: { color: '#94a3b8' } }
                            }
                        }
                    });
                    ctxTrend.dataset.initialized = 'true';
                }
                
                // Cost Provider Doughnut
                const ctxDoughnut = document.getElementById('cost-provider-doughnut');
                if (ctxDoughnut && !ctxDoughnut.dataset.initialized) {
                    new Chart(ctxDoughnut, {
                        type: 'doughnut',
                        data: {
                            labels: ['OpenAI', 'Anthropic', 'Google', 'Ollama'],
                            datasets: [{
                                data: [112.50, 15.30, 18.40, 2.00],
                                backgroundColor: ['#10b981', '#f59e0b', '#3b82f6', '#8b5cf6'],
                                borderWidth: 0
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '70%',
                            plugins: {
                                legend: { position: 'right', labels: { color: '#94a3b8', boxWidth: 12 } }
                            }
                        }
                    });
                    ctxDoughnut.dataset.initialized = 'true';
                }
                
                // Cost Intent Bar
                const ctxIntent = document.getElementById('cost-intent-chart');
                if (ctxIntent && !ctxIntent.dataset.initialized) {
                    new Chart(ctxIntent, {
                        type: 'bar',
                        data: {
                            labels: ['general_chat', 'reasoning', 'data_extraction', 'embedding', 'summarization'],
                            datasets: [{
                                label: 'Spend ($)',
                                data: [42.80, 38.50, 22.10, 15.30, 8.50],
                                backgroundColor: '#3b82f6',
                                borderRadius: 4
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                x: { grid: { color: '#334155' }, ticks: { color: '#94a3b8' } },
                                y: { grid: { display: false }, ticks: { color: '#94a3b8' } }
                            }
                        }
                    });
                    ctxIntent.dataset.initialized = 'true';
                }
                
            });
        }
    });
</script>
@endpush
