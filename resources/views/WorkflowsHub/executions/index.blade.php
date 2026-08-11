<!-- Workflow Executions Module -->
<div class="row h-100 g-3 wf-animate-in position-relative">
    
    <!-- DataTables Area -->
    <div class="col-12 h-100 d-flex flex-column" id="wf-executions-main">
        <div class="wf-glass-panel h-100 d-flex flex-column p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="text-light fw-bold m-0"><i class="fa-solid fa-list-check text-success me-2"></i> Execution History</h4>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-info"><i class="fa-solid fa-filter me-1"></i> Filters</button>
                    <button class="btn btn-sm btn-outline-secondary" onclick="WfExecutionsTable.ajax.reload()"><i class="fa-solid fa-rotate-right"></i></button>
                </div>
            </div>

            <div class="flex-grow-1 overflow-auto">
                <table id="wf-executions-table" class="table table-dark table-hover w-100 align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Workflow Name</th>
                            <th>Status</th>
                            <th>Duration</th>
                            <th>Triggered By</th>
                            <th>Started At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be loaded via AJAX DataTables -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Quick View Sidebar (Hidden by default) -->
    <div class="wf-glass-panel position-absolute top-0 end-0 h-100 d-flex flex-column shadow-lg" id="wf-quick-view-panel" style="width: 450px; transform: translateX(110%); transition: transform 0.3s ease; z-index: 1050;">
        
        <!-- Header -->
        <div class="wf-glass-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="text-light m-0 fw-bold" id="qv-exec-id">#EX-0000</h5>
                <small class="text-muted" id="qv-wf-name">Workflow Name</small>
            </div>
            <button class="btn btn-sm btn-outline-secondary rounded-circle" id="btn-close-qv"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <!-- Body -->
        <div class="p-3 flex-grow-1 overflow-auto">
            
            <div class="d-flex justify-content-between mb-4">
                <div>
                    <span class="text-muted d-block small text-uppercase">Status</span>
                    <span class="badge bg-success" id="qv-status">Completed</span>
                </div>
                <div class="text-end">
                    <span class="text-muted d-block small text-uppercase">Duration</span>
                    <span class="text-light fw-bold" id="qv-duration">0.0s</span>
                </div>
            </div>

            <!-- Steps Trace -->
            <h6 class="text-light border-bottom border-secondary pb-2 mb-3"><i class="fa-solid fa-shoe-prints text-primary me-2"></i> Execution Trace</h6>
            
            <div class="d-flex flex-column gap-3 position-relative" id="qv-trace-container">
                <!-- Vertical Line -->
                <div class="position-absolute h-100" style="left: 14px; top: 10px; width: 2px; background: var(--wf-panel-border); z-index: 0;"></div>

                <!-- Step Mock 1 -->
                <div class="d-flex position-relative z-1">
                    <div class="rounded-circle bg-dark border border-success text-success d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px;">
                        <i class="fa-solid fa-check fs-6"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-light">Trigger: Webhook</div>
                        <small class="text-muted">Payload received & parsed. (45ms)</small>
                    </div>
                </div>

                <!-- Step Mock 2 -->
                <div class="d-flex position-relative z-1">
                    <div class="rounded-circle bg-dark border border-success text-success d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px;">
                        <i class="fa-solid fa-check fs-6"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-light">Action: Extract Invoice</div>
                        <small class="text-muted">Agent Gem-Flash successfully extracted JSON. (1.2s)</small>
                    </div>
                </div>

                <!-- Step Mock 3 -->
                <div class="d-flex position-relative z-1">
                    <div class="rounded-circle bg-dark border border-danger text-danger d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px;">
                        <i class="fa-solid fa-xmark fs-6"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-light">Action: Save to DB</div>
                        <small class="text-danger">SQLSTATE[23000]: Integrity constraint violation.</small>
                    </div>
                </div>

            </div>

            <div class="mt-4">
                <h6 class="text-light border-bottom border-secondary pb-2 mb-2"><i class="fa-solid fa-code text-warning me-2"></i> Final Payload</h6>
                <pre class="bg-black text-info p-2 rounded small" id="qv-payload-dump" style="border: 1px solid var(--wf-panel-border); white-space: pre-wrap; word-break: break-all;">
                </pre>
            </div>
        </div>

        <!-- Footer -->
        <div class="p-3 border-top border-secondary d-flex gap-2">
            <button class="btn btn-warning flex-fill"><i class="fa-solid fa-rotate-right me-1"></i> Force Retry</button>
        </div>
    </div>

</div>

@push('scripts')
<!-- Add DataTables CSS/JS if not already present -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        
        let WfExecutionsTable = $('#wf-executions-table').DataTable({
            ajax: {
                url: '/hub/workflows/executions-data',
                type: 'GET'
            },
            columns: [
                { data: 'id' },
                { data: 'workflow_name', className: 'fw-bold text-light' },
                { 
                    data: 'status',
                    render: function(data) {
                        if(data === 'completed') return `<span class="badge bg-success bg-opacity-10 text-success border border-success">Completed</span>`;
                        if(data === 'failed') return `<span class="badge bg-danger bg-opacity-10 text-danger border border-danger">Failed</span>`;
                        return `<span class="badge bg-warning bg-opacity-10 text-warning border border-warning">${data}</span>`;
                    }
                },
                { data: 'duration' },
                { data: 'trigger_source' },
                { data: 'started_at' },
                { 
                    data: 'raw_id',
                    render: function(data) {
                        return `<button class="btn btn-sm btn-link text-info btn-view-exec" data-id="${data}"><i class="fa-solid fa-eye"></i> View</button>`;
                    }
                }
            ],
            order: [[0, 'desc']],
            pageLength: 15,
            lengthChange: false,
            dom: '<"d-flex justify-content-between align-items-center mb-3"f>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
            language: {
                search: "",
                searchPlaceholder: "Search executions..."
            }
        });

        // Make it accessible globally for the reload button
        window.WfExecutionsTable = WfExecutionsTable;

        // Open Quick View
        $('#wf-executions-table').on('click', '.btn-view-exec', function() {
            const execId = $(this).data('id');
            $('#qv-exec-id').text('#EX-' + execId);
            
            // Slide in immediately with loading state
            $('#qv-trace-container').html('<div class="text-center text-muted py-5"><i class="fa-solid fa-spinner fa-spin fs-3"></i></div>');
            $('#wf-quick-view-panel').css('transform', 'translateX(0)');

            // Fetch Real Trace
            window.WorkflowAPI.request(`/hub/workflows/executions/${execId}`, 'GET')
                .then(resp => {
                    const exec = resp.execution;
                    $('#qv-wf-name').text(exec.workflow.name);
                    $('#qv-status').text(exec.status);
                    $('#qv-duration').text(exec.duration || 'N/A');
                    
                    let traceHtml = '';
                    if (exec.step_logs && exec.step_logs.length > 0) {
                        traceHtml += `<div class="position-absolute h-100" style="left: 14px; top: 10px; width: 2px; background: var(--wf-panel-border); z-index: 0;"></div>`;
                        exec.step_logs.forEach(log => {
                            const isError = log.status === 'failed';
                            traceHtml += `
                            <div class="d-flex position-relative z-1 mb-3">
                                <div class="rounded-circle bg-dark border border-${isError ? 'danger text-danger' : 'success text-success'} d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px;">
                                    <i class="fa-solid fa-${isError ? 'xmark' : 'check'} fs-6"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-light">${log.step_name || 'Step'}</div>
                                    <small class="${isError ? 'text-danger' : 'text-muted'}">${log.message || ''}</small>
                                </div>
                            </div>`;
                        });
                    } else {
                        traceHtml = '<div class="text-muted small">No trace logs available.</div>';
                    }
                    
                    $('#qv-trace-container').html(traceHtml);
                    
                    let payloadJson = exec.output || exec.input_payload || 'No payload attached.';
                    if (typeof payloadJson === 'object') payloadJson = JSON.stringify(payloadJson, null, 2);
                    $('#qv-payload-dump').text(payloadJson);
                });
        });

        // Close Quick View
        $('#btn-close-qv').on('click', function() {
            $('#wf-quick-view-panel').css('transform', 'translateX(110%)');
        });
    });
</script>
@endpush
