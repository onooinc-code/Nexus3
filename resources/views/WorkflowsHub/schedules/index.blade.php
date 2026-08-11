<!-- Workflow Schedules Module -->
<div class="row g-3 h-100 wf-animate-in">
    <!-- CRON Schedules -->
    <div class="col-md-7 h-100 d-flex flex-column">
        <div class="wf-glass-panel h-100 d-flex flex-column p-3">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="text-light fw-bold m-0"><i class="fa-solid fa-clock text-warning me-2"></i> CRON Schedules</h5>
                <button class="btn btn-sm btn-primary"><i class="fa-solid fa-plus me-1"></i> New Schedule</button>
            </div>
            
            <div class="flex-grow-1 overflow-auto">
                <table class="table table-dark table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Workflow</th>
                            <th>Expression (CRON)</th>
                            <th>Next Run</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="schedules-tbody">
                        <!-- Loaded via JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Webhook Endpoints -->
    <div class="col-md-5 h-100 d-flex flex-column">
        <div class="wf-glass-panel h-100 d-flex flex-column p-3">
            <h5 class="text-light fw-bold mb-4"><i class="fa-solid fa-globe text-primary me-2"></i> Webhook Triggers</h5>
            
            <p class="text-muted small mb-4">
                You can trigger workflows externally by sending a <code>POST</code> request to the generated webhook URLs below.
            </p>

            <div class="flex-grow-1 overflow-auto" id="webhooks-container">
                <!-- Loaded via JS -->
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        
        function loadSchedules() {
            window.WorkflowAPI.request('/hub/workflows/schedules-data', 'GET')
                .then(data => {
                    renderSchedules(data.schedules);
                    renderWebhooks(data.webhooks);
                });
        }

        function renderSchedules(schedules) {
            let html = '';
            if(schedules.length === 0) {
                html = '<tr><td colspan="5" class="text-center text-muted py-4">No schedules found.</td></tr>';
            } else {
                schedules.forEach(s => {
                    html += `
                    <tr>
                        <td class="fw-bold">${s.workflow_name}</td>
                        <td><code class="bg-dark text-warning border border-secondary px-2 py-1 rounded">${s.cron_expression}</code></td>
                        <td><small class="text-muted">${s.next_run_at}</small></td>
                        <td>
                            <div class="form-check form-switch">
                                <input class="form-check-input toggle-schedule" type="checkbox" data-id="${s.id}" ${s.is_active ? 'checked' : ''}>
                            </div>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-link text-danger"><i class="fa-solid fa-trash"></i></button>
                        </td>
                    </tr>`;
                });
            }
            $('#schedules-tbody').html(html);
        }

        function renderWebhooks(webhooks) {
            let html = '';
            if(webhooks.length === 0) {
                html = '<div class="text-center text-muted py-4">No active workflows to generate webhooks for.</div>';
            } else {
                webhooks.forEach(w => {
                    html += `
                    <div class="card bg-dark border-secondary mb-3">
                        <div class="card-body p-2">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold text-light">${w.name}</span>
                                <span class="badge bg-success">Active</span>
                            </div>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-secondary border-secondary text-light">POST</span>
                                <input type="text" class="form-control bg-black text-info border-secondary" readonly value="${w.url || 'https://n.soulyeg.online/api/workflows/trigger/' + w.id}">
                                <button class="btn btn-outline-secondary" onclick="navigator.clipboard.writeText('${w.url || 'https://n.soulyeg.online/api/workflows/trigger/' + w.id}'); window.Nexus.notify('Copied!', 'success');"><i class="fa-solid fa-copy"></i></button>
                            </div>
                        </div>
                    </div>`;
                });
            }
            $('#webhooks-container').html(html);
        }

        // Handle Toggle
        $(document).on('change', '.toggle-schedule', function() {
            const id = $(this).data('id');
            const isActive = $(this).is(':checked');
            
            window.WorkflowAPI.request(`/hub/workflows/schedules/${id}/toggle`, 'POST', { is_active: isActive })
                .then(resp => window.Nexus.notify(resp.message, 'success'))
                .catch(err => {
                    // revert UI toggle on error
                    $(this).prop('checked', !isActive);
                });
        });

        // Init
        loadSchedules();
    });
</script>
@endpush
