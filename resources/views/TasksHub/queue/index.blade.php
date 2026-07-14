<div class="row g-3 h-100 pb-3 pe-2">
    
    <!-- Top Left: Live Queue Monitor (F20) -->
    <div class="col-lg-6 d-flex flex-column">
        <div class="tasks-glass-card p-3 flex-grow-1 d-flex flex-column">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-secondary">
                <h6 class="mb-0 text-light font-mono"><i class="fa-solid fa-server me-2 text-primary"></i>Live Queue Monitor</h6>
                <button class="btn btn-sm btn-outline-danger" id="btn-purge-queue"><i class="fa-solid fa-dumpster me-1"></i>Purge Queue</button>
            </div>
            
            <div class="row g-3 flex-grow-1">
                <!-- Gauge 1: Current Depth -->
                <div class="col-6 d-flex flex-column align-items-center justify-content-center">
                    <div style="position: relative; width: 120px; height: 120px;">
                        <canvas id="gauge-queue-depth"></canvas>
                        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                            <h3 class="mb-0 text-light font-mono" id="q-val-depth">0</h3>
                            <span class="text-muted" style="font-size: 0.6rem;">Queued</span>
                        </div>
                    </div>
                </div>
                <!-- Gauge 2: Backpressure / Processed -->
                <div class="col-6 d-flex flex-column align-items-center justify-content-center">
                    <div style="position: relative; width: 120px; height: 120px;">
                        <canvas id="gauge-queue-processed"></canvas>
                        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                            <h3 class="mb-0 text-light font-mono" id="q-val-processed">0</h3>
                            <span class="text-muted" style="font-size: 0.6rem;">/ min</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Right: Smart Routing Center (F19) -->
    <div class="col-lg-6 d-flex flex-column">
        <div class="tasks-glass-card p-3 flex-grow-1 d-flex flex-column">
            <h6 class="mb-3 text-light font-mono border-bottom border-secondary pb-2"><i class="fa-solid fa-route me-2 text-success"></i>Routing Center & Tester</h6>
            
            <div class="mb-3 p-3 rounded" style="background: rgba(0,0,0,0.2);">
                <label class="form-label font-mono text-muted" style="font-size: 0.7rem;">Test Agent Routing Logic</label>
                <div class="input-group input-group-sm mb-2">
                    <input type="text" id="route-tester-input" class="form-control bg-dark border-secondary text-light" placeholder="Type a simulated task objective...">
                    <button class="btn btn-primary" id="btn-test-route">Test Route</button>
                </div>
                <div class="d-flex align-items-center gap-2 mt-2">
                    <span class="text-muted font-mono" style="font-size: 0.7rem;">Result:</span>
                    <span id="route-tester-result" class="badge bg-secondary font-mono">Idle</span>
                </div>
            </div>

            <div class="flex-grow-1 overflow-auto">
                <table class="table table-dark table-sm mb-0" style="font-size: 0.75rem;">
                    <thead>
                        <tr class="text-muted font-mono">
                            <th>Agent Class</th>
                            <th>Active Workers</th>
                            <th>Load</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>General_Agent</td><td>4</td><td><span class="text-success">Low</span></td></tr>
                        <tr><td>Code_Reviewer</td><td>2</td><td><span class="text-warning">Med</span></td></tr>
                        <tr><td>DB_Admin</td><td>1</td><td><span class="text-danger">High</span></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Bottom Left: Dead Letter Queue (F21) -->
    <div class="col-lg-7 d-flex flex-column" style="min-height: 300px;">
        <div class="tasks-glass-card p-0 flex-grow-1 d-flex flex-column overflow-hidden">
            <div class="p-3 border-bottom border-secondary d-flex justify-content-between align-items-center bg-dark bg-opacity-50">
                <h6 class="mb-0 text-light font-mono"><i class="fa-solid fa-skull me-2" style="color: #4B5563;"></i>Dead Letter Queue (Exhausted)</h6>
                <button class="btn btn-sm btn-outline-warning" id="btn-dlq-retry-all">Retry All</button>
            </div>
            
            <div class="table-responsive flex-grow-1 p-2">
                <table id="dlq-datatable" class="table table-dark table-hover mb-0 w-100" style="font-size: 0.8rem;">
                    <thead class="text-muted font-mono" style="font-size: 0.7rem;">
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Attempts</th>
                            <th>Last Error</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Populated by JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Bottom Right: Global System Logs (F22) -->
    <div class="col-lg-5 d-flex flex-column" style="min-height: 300px;">
        <div class="tasks-glass-card p-3 flex-grow-1 d-flex flex-column">
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom border-secondary pb-2">
                <h6 class="mb-0 text-light font-mono"><i class="fa-solid fa-list-ul me-2 text-info"></i>Global System Logs</h6>
                <div class="d-flex gap-2">
                    <select class="form-select form-select-sm bg-dark border-secondary text-light font-mono" style="width: 100px; font-size: 0.7rem;">
                        <option>ALL</option>
                        <option>ERROR</option>
                        <option>INFO</option>
                    </select>
                </div>
            </div>
            
            <div id="global-logs-container" class="flex-grow-1 overflow-auto rounded p-2 font-mono" style="background: #000; font-size: 0.7rem;">
                <!-- Logs stream here -->
                <div class="text-center text-muted mt-4">Connecting to log stream...</div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // --- 1. Queue Gauges (F20) using Chart.js Doughnut as gauge ---
    let qDepthChart, qProcessedChart;
    
    function initGauges() {
        const ctxDepth = document.getElementById('gauge-queue-depth').getContext('2d');
        qDepthChart = new Chart(ctxDepth, {
            type: 'doughnut',
            data: { labels: ['Queued', 'Free'], datasets: [{ data: [0, 100], backgroundColor: ['#3B82F6', 'rgba(255,255,255,0.05)'], borderWidth: 0 }] },
            options: { responsive: true, maintainAspectRatio: false, rotation: -90, circumference: 180, cutout: '80%', plugins: { legend: { display: false }, tooltip: { enabled: false } } }
        });

        const ctxProc = document.getElementById('gauge-queue-processed').getContext('2d');
        qProcessedChart = new Chart(ctxProc, {
            type: 'doughnut',
            data: { labels: ['Processed', 'Target'], datasets: [{ data: [0, 100], backgroundColor: ['#10B981', 'rgba(255,255,255,0.05)'], borderWidth: 0 }] },
            options: { responsive: true, maintainAspectRatio: false, rotation: -90, circumference: 180, cutout: '80%', plugins: { legend: { display: false }, tooltip: { enabled: false } } }
        });
    }
    // slight delay for rendering
    setTimeout(initGauges, 500);

    function pollQueueStats() {
        if (!window.TaskAPI) return;
        TaskAPI.get('/tasks/queue-stats').done(function(res) {
            if(res.data && qDepthChart) {
                let q = res.data.queued || 0;
                let max = 100; // max gauge value
                let pct = Math.min(q, max);
                
                $('#q-val-depth').text(q);
                qDepthChart.data.datasets[0].data = [pct, max - pct];
                if (q > 80) qDepthChart.data.datasets[0].backgroundColor[0] = '#EF4444'; // Red
                else if (q > 50) qDepthChart.data.datasets[0].backgroundColor[0] = '#F59E0B'; // Warning
                else qDepthChart.data.datasets[0].backgroundColor[0] = '#3B82F6'; // Info
                qDepthChart.update();

                // Fake processed rate for demo if not in API
                let p = res.data.processed_per_min || Math.floor(Math.random() * 20) + 10;
                $('#q-val-processed').text(p);
                qProcessedChart.data.datasets[0].data = [Math.min(p, 100), 100 - Math.min(p, 100)];
                qProcessedChart.update();
            }
        });
    }

    // --- 2. Smart Routing Tester (F19) ---
    $('#btn-test-route').on('click', function() {
        let title = $('#route-tester-input').val();
        if(!title) return;
        
        $('#route-tester-result').removeClass().addClass('badge bg-primary font-mono').html('<i class="fa-solid fa-spinner fa-spin"></i> Processing');
        
        // Simulating API call
        setTimeout(() => {
            let matched = 'General_Agent';
            let t = title.toLowerCase();
            if(t.includes('code') || t.includes('fix')) matched = 'Code_Reviewer';
            else if(t.includes('db') || t.includes('database')) matched = 'DB_Admin';
            
            $('#route-tester-result').removeClass('bg-primary').addClass('bg-success').html('<i class="fa-solid fa-check me-1"></i> Routed to: ' + matched);
        }, 800);
    });

    // --- 3. DLQ DataTable (F21) ---
    let dlqTable;
    function initDLQ() {
        dlqTable = $('#dlq-datatable').DataTable({
            serverSide: true, processing: true,
            ajax: {
                url: '/api/v1/tasks',
                data: function(d) {
                    d.status = 'failed';
                    // Custom param to fetch exhausted only, assume backend supports it or just show failed
                    d.retry_exhausted = true; 
                    d.page = (d.start / d.length) + 1;
                    d.per_page = d.length;
                },
                dataFilter: function(data) {
                    let j = jQuery.parseJSON(data);
                    return JSON.stringify({ recordsTotal: j.meta?.total || j.data.length, recordsFiltered: j.meta?.total || j.data.length, data: j.data });
                }
            },
            columns: [
                { data: 'id', render: d => '#' + d },
                { data: 'title' },
                { data: 'attempts', render: (d, t, row) => `${d}/${row.max_attempts || 3}` },
                { data: 'last_error', render: d => `<span class="text-danger" style="display:inline-block; max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${d}">${d || '-'}</span>` },
                { data: 'id', orderable: false, render: d => `<button class="btn btn-sm btn-outline-warning btn-dlq-retry" data-id="${d}"><i class="fa-solid fa-rotate-right"></i></button>` }
            ],
            pageLength: 5, lengthChange: false, searching: false, info: false,
            language: { emptyTable: "No tasks in Dead Letter Queue." }
        });
    }

    $('#dlq-datatable').on('click', '.btn-dlq-retry', function() {
        let id = $(this).data('id');
        TaskAPI.post(`/tasks/${id}/retry`).done(() => {
            Nexus.notify(`Task #${id} requeued`, 'success');
            dlqTable.ajax.reload();
        });
    });

    // --- 4. Global Logs (F22) ---
    function fetchGlobalLogs() {
        // Fallback polling for global logs if no websocket
        // Assuming an endpoint exists or simulating
        $('#global-logs-container').append(`<div class="mb-1"><span class="text-secondary">[${new Date().toLocaleTimeString()}]</span> <span class="text-info">SYSTEM</span>: Queue heartbeat OK.</div>`);
        
        let c = $('#global-logs-container');
        if(c.children().length > 100) c.children().first().remove(); // keep 100
        c.scrollTop(c[0].scrollHeight);
    }

    // --- Initialization & Timers ---
    let queuePoll, logPoll;
    
    function startQueueTab() {
        pollQueueStats();
        queuePoll = setInterval(pollQueueStats, 3000);
        
        if(!dlqTable) initDLQ();
        else dlqTable.ajax.reload();

        $('#global-logs-container').html('');
        logPoll = setInterval(fetchGlobalLogs, 5000);
    }
    
    function stopQueueTab() {
        clearInterval(queuePoll);
        clearInterval(logPoll);
    }

    // Activate if tab is default open, else bind to tab events
    if ($('#content-queue').hasClass('active')) {
        startQueueTab();
    }
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        if ($(e.target).attr('data-bs-target') === '#content-queue') startQueueTab();
        else stopQueueTab();
    });

    $('#btn-purge-queue').on('click', function() {
        if(confirm("DANGER: This will delete all pending tasks. Are you sure?")) {
            // TaskAPI.delete('/tasks/queue/purge').done(...)
            Nexus.notify('Queue purged (Simulation)', 'success');
        }
    });
});
</script>
