@include('hubs.partials.ai-hub.api-keys.summary-bar')
@include('hubs.partials.ai-hub.api-keys.filter-bar')
@include('hubs.partials.ai-hub.api-keys.keys-list')

@include('hubs.partials.ai-hub.api-keys.drawers.analytics')
@include('hubs.partials.ai-hub.api-keys.modals.add-key')

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const offcanvasEl = document.getElementById('keyAnalyticsDrawer');
        if (offcanvasEl) {
            offcanvasEl.addEventListener('shown.bs.offcanvas', function () {
                // Render charts only when drawer is opened
                
                // Cost line chart
                const ctxCost = document.getElementById('keyCostChart');
                if (ctxCost && !ctxCost.dataset.initialized) {
                    new Chart(ctxCost, {
                        type: 'line',
                        data: {
                            labels: Array.from({length: 30}, (_, i) => i+1),
                            datasets: [{
                                label: 'Cost ($)',
                                data: Array.from({length: 30}, () => Math.random() * 5 + 1),
                                borderColor: '#3b82f6',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.3
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                x: { display: false },
                                y: { grid: { color: '#334155' }, ticks: { color: '#94a3b8' } }
                            }
                        }
                    });
                    ctxCost.dataset.initialized = 'true';
                }
                
                // Error Doughnut
                const ctxError = document.getElementById('keyErrorChart');
                if (ctxError && !ctxError.dataset.initialized) {
                    new Chart(ctxError, {
                        type: 'doughnut',
                        data: {
                            labels: ['429 Rate Limit', '500 Server Error', 'Timeout'],
                            datasets: [{
                                data: [18, 5, 0],
                                backgroundColor: ['#f59e0b', '#ef4444', '#64748b'],
                                borderWidth: 0
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '60%',
                            plugins: { legend: { position: 'right', labels: { color: '#94a3b8', boxWidth: 10 } } }
                        }
                    });
                    ctxError.dataset.initialized = 'true';
                }
                
                // Token Bar
                const ctxToken = document.getElementById('keyTokenChart');
                if (ctxToken && !ctxToken.dataset.initialized) {
                    new Chart(ctxToken, {
                        type: 'bar',
                        data: {
                            labels: ['In', 'Out'],
                            datasets: [{
                                data: [2.1, 1.1],
                                backgroundColor: ['#3b82f6', '#10b981'],
                                borderWidth: 0
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                x: { grid: { display: false }, ticks: { color: '#94a3b8' } },
                                y: { grid: { color: '#334155' }, ticks: { color: '#94a3b8' } }
                            }
                        }
                    });
                    ctxToken.dataset.initialized = 'true';
                }
            });
        }
    });
</script>
@endpush
