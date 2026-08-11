@include('hubs.partials.ai-hub.api-keys.summary-bar')
@include('hubs.partials.ai-hub.api-keys.filter-bar')
@include('hubs.partials.ai-hub.api-keys.keys-list')

@include('hubs.partials.ai-hub.api-keys.drawers.analytics')
@include('hubs.partials.ai-hub.api-keys.modals.add-key')

@push('scripts')
<script>
    window.currentAnalyticsKeyId = null;
    let costChartInstance = null;
    let errorChartInstance = null;
    let tokenChartInstance = null;

    window.openKeyAnalytics = function(keyId, name, prefix) {
        window.currentAnalyticsKeyId = keyId;
        $('#key-drawer-name').text(name || 'API Key');
        $('#key-drawer-prefix').text(prefix || 'sk-••••');

        const offcanvasEl = document.getElementById('keyAnalyticsDrawer');
        if (offcanvasEl) {
            const drawer = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
            drawer.show();
            loadKeyAnalyticsData(keyId);
        }
    };

    function loadKeyAnalyticsData(keyId) {
        $.ajax({
            url: `/hub/models/api-keys/${keyId}/analytics`,
            method: 'GET',
            success: function(res) {
                if (res.success && res.data) {
                    const d = res.data;
                    $('#key-drawer-provider').text(d.provider_name || 'Universal');
                    $('#key-drawer-total-reqs').text(Number(d.total_requests || 0).toLocaleString());
                    $('#key-drawer-success-rate').text(`${d.success_rate}%`);
                    $('#key-drawer-total-cost').text(`$${Number(d.total_cost || 0).toFixed(4)}`);
                    $('#key-drawer-avg-cost').text(`$${Number(d.avg_cost_per_req || 0).toFixed(6)}`);

                    renderKeyAnalyticsCharts(d);
                    renderRecentRequestsTable(d.recent_requests || []);
                }
            },
            error: function(err) {
                console.error('Failed to load key analytics telemetry:', err);
            }
        });
    }

    function renderKeyAnalyticsCharts(data) {
        // 1. Cost Chart
        const ctxCost = document.getElementById('keyCostChart');
        if (ctxCost) {
            if (costChartInstance) costChartInstance.destroy();
            costChartInstance = new Chart(ctxCost, {
                type: 'line',
                data: {
                    labels: data.chart_labels || [],
                    datasets: [{
                        label: 'Daily Spend ($)',
                        data: data.cost_series || [],
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
                        x: { grid: { display: false }, ticks: { color: '#64748b', maxTicksLimit: 7 } },
                        y: { grid: { color: '#334155' }, ticks: { color: '#94a3b8' } }
                    }
                }
            });
        }

        // 2. Error Chart
        const ctxError = document.getElementById('keyErrorChart');
        if (ctxError) {
            if (errorChartInstance) errorChartInstance.destroy();
            const errors = data.error_count || 0;
            const success = Math.max(0, (data.total_requests || 0) - errors);
            errorChartInstance = new Chart(ctxError, {
                type: 'doughnut',
                data: {
                    labels: ['Success', 'Errors / Cooldown'],
                    datasets: [{
                        data: [success, errors],
                        backgroundColor: ['#10b981', '#f59e0b'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: { legend: { position: 'bottom', labels: { color: '#94a3b8', boxWidth: 10 } } }
                }
            });
        }

        // 3. Token Chart
        const ctxToken = document.getElementById('keyTokenChart');
        if (ctxToken) {
            if (tokenChartInstance) tokenChartInstance.destroy();
            tokenChartInstance = new Chart(ctxToken, {
                type: 'bar',
                data: {
                    labels: ['Input', 'Output'],
                    datasets: [{
                        data: [data.input_tokens || 0, data.output_tokens || 0],
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
        }
    }

    function renderRecentRequestsTable(logs) {
        const tbody = $('#key-recent-requests-body');
        if (!logs || !logs.length) {
            tbody.html('<tr><td colspan="4" class="text-center py-4 text-muted">No request logs recorded for this key.</td></tr>');
            return;
        }

        let html = '';
        logs.forEach(function(log) {
            const dateStr = log.created_at || log.timestamp || 'N/A';
            const modelName = log.model_name || log.intent_name || 'N/A';
            const tokensStr = `${Number(log.input_tokens || 0).toLocaleString()} in / ${Number(log.output_tokens || 0).toLocaleString()} out`;
            const costStr = `$${Number(log.total_cost || 0).toFixed(6)}`;

            html += `
                <tr>
                    <td class="text-muted">${dateStr}</td>
                    <td class="fw-bold text-light">${modelName}</td>
                    <td class="text-info">${tokensStr}</td>
                    <td class="text-success font-mono">${costStr}</td>
                </tr>
            `;
        });
        tbody.html(html);
    }
</script>
@endpush
