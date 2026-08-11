<div class="row g-3 h-100 wf-animate-in">
    <!-- 1. Node Library (Left Sidebar) -->
    <div class="col-md-2 h-100 d-flex flex-column">
        <div class="wf-glass-panel h-100 d-flex flex-column">
            <div class="wf-glass-header text-light">
                <i class="fa-solid fa-cubes text-primary me-2"></i> Nodes
            </div>
            <div class="p-2 flex-grow-1 overflow-auto">
                <div class="mb-3">
                    <small class="text-muted text-uppercase fw-bold mb-2 d-block">Triggers</small>
                    <div class="drag-drawflow" draggable="true" ondragstart="drag(event)" data-node="trigger_webhook">
                        <i class="fa-solid fa-globe text-primary me-2"></i> Webhook
                    </div>
                    <div class="drag-drawflow" draggable="true" ondragstart="drag(event)" data-node="trigger_schedule">
                        <i class="fa-solid fa-clock text-warning me-2"></i> Schedule
                    </div>
                </div>

                <div class="mb-3">
                    <small class="text-muted text-uppercase fw-bold mb-2 d-block">Agents & AI</small>
                    <div class="drag-drawflow" draggable="true" ondragstart="drag(event)" data-node="action_agent">
                        <i class="fa-solid fa-robot text-success me-2"></i> Call Agent
                    </div>
                    <div class="drag-drawflow" draggable="true" ondragstart="drag(event)" data-node="action_extract">
                        <i class="fa-solid fa-magnifying-glass-chart text-info me-2"></i> Extract Data
                    </div>
                </div>

                <div class="mb-3">
                    <small class="text-muted text-uppercase fw-bold mb-2 d-block">Logic</small>
                    <div class="drag-drawflow" draggable="true" ondragstart="drag(event)" data-node="logic_if">
                        <i class="fa-solid fa-code-branch text-warning me-2"></i> If / Else
                    </div>
                    <div class="drag-drawflow" draggable="true" ondragstart="drag(event)" data-node="logic_loop">
                        <i class="fa-solid fa-rotate-right text-secondary me-2"></i> Loop
                    </div>
                </div>

                <div class="mb-3">
                    <small class="text-muted text-uppercase fw-bold mb-2 d-block">Integrations</small>
                    <div class="drag-drawflow" draggable="true" ondragstart="drag(event)" data-node="action_api">
                        <i class="fa-solid fa-cloud-arrow-up text-danger me-2"></i> HTTP Request
                    </div>
                    <div class="drag-drawflow" draggable="true" ondragstart="drag(event)" data-node="action_email">
                        <i class="fa-solid fa-envelope text-primary me-2"></i> Send Email
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Workflow Canvas (Middle) -->
    <div class="col-md-7 h-100 d-flex flex-column gap-3">
        <!-- Toolbar -->
        <div class="wf-glass-panel p-2 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <select class="form-select form-select-sm bg-dark text-light border-secondary" id="workflow-selector" style="width: 250px;">
                    <option value="" disabled selected>-- Select Workflow --</option>
                    @foreach($workflows as $wf)
                        <option value="{{ $wf->id }}" data-steps="{{ json_encode($wf->steps ?? []) }}" data-metadata="{{ json_encode($wf->metadata ?? []) }}">{{ $wf->name }}</option>
                    @endforeach
                </select>
                <span class="badge bg-secondary ms-2" id="wf-status-badge">Draft</span>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-secondary" onclick="editor.zoom_in()"><i class="fa-solid fa-magnifying-glass-plus"></i></button>
                <button class="btn btn-sm btn-outline-secondary" onclick="editor.zoom_out()"><i class="fa-solid fa-magnifying-glass-minus"></i></button>
                <button class="btn btn-sm btn-outline-danger" onclick="editor.clear()"><i class="fa-solid fa-trash"></i></button>
                <button class="btn btn-sm btn-primary" id="btn-save-workflow"><i class="fa-solid fa-floppy-disk me-1"></i> Save</button>
                <button class="btn btn-sm btn-success" id="btn-run-workflow"><i class="fa-solid fa-play me-1"></i> Run</button>
            </div>
        </div>

        <!-- Canvas -->
        <div class="wf-glass-panel flex-grow-1 position-relative overflow-hidden" id="canvas-wrapper">
            <div id="drawflow" ondrop="drop(event)" ondragover="allowDrop(event)"></div>
        </div>

        <!-- Live Terminal (Bottom) -->
        <div class="wf-glass-panel" style="height: 150px;">
            <div class="wf-glass-header py-1 px-2 d-flex justify-content-between align-items-center">
                <small class="text-light"><i class="fa-solid fa-terminal text-success me-1"></i> Console</small>
                <button class="btn btn-link text-muted p-0 btn-sm" onclick="document.getElementById('workflow-logs').innerHTML=''"><i class="fa-solid fa-eraser"></i></button>
            </div>
            <div class="p-2 bg-black h-100 overflow-auto" id="workflow-logs" style="font-family: monospace; font-size: 0.8rem; border-radius: 0 0 12px 12px;">
                <div class="text-muted">> Workflow Builder Initialized...</div>
            </div>
        </div>
    </div>

    <!-- 3. Node Configuration (Right Sidebar) -->
    <div class="col-md-3 h-100 d-flex flex-column">
        <div class="wf-glass-panel h-100 d-flex flex-column">
            <div class="wf-glass-header text-light d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-sliders text-info me-2"></i> Node Config</span>
                <span class="badge bg-dark border border-secondary" id="selected-node-id">None</span>
            </div>
            <div class="p-3 flex-grow-1 overflow-auto" id="node-config-panel">
                <div class="text-center text-muted mt-5">
                    <i class="fa-solid fa-hand-pointer fs-1 mb-3 opacity-50"></i>
                    <p>Click on any node in the canvas<br>to configure its properties.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Drawflow CSS specifically for this tab -->
@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/jerosoler/Drawflow/dist/drawflow.min.css">
    <style>
        .drag-drawflow {
            padding: 10px;
            margin-bottom: 8px;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--wf-panel-border);
            border-radius: 6px;
            cursor: grab;
            transition: all 0.2s;
            font-size: 0.85rem;
        }
        .drag-drawflow:hover {
            background: rgba(255,255,255,0.1);
            border-color: var(--wf-primary);
        }
        #drawflow {
            width: 100%;
            height: 100%;
            background: transparent;
            background-size: 20px 20px;
            background-image: radial-gradient(rgba(255,255,255,0.1) 1px, transparent 1px);
        }
        /* Override Drawflow Default Theme */
        .drawflow .drawflow-node {
            background: #1e293b;
            border: 1px solid #334155;
            color: #f8fafc;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            padding: 15px;
            min-width: 160px;
        }
        .drawflow .drawflow-node.selected {
            background: #0f172a;
            border: 2px solid var(--wf-primary);
        }
        .drawflow .drawflow-node .inputs .input {
            background: #10b981;
            border: 2px solid #0f111a;
            width: 14px;
            height: 14px;
            border-radius: 50%;
        }
        .drawflow .drawflow-node .outputs .output {
            background: var(--wf-primary);
            border: 2px solid #0f111a;
            width: 14px;
            height: 14px;
            border-radius: 50%;
        }
        .drawflow .connection .main-path {
            stroke: #475569;
            stroke-width: 3px;
        }
    </style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/gh/jerosoler/Drawflow/dist/drawflow.min.js"></script>
<script>
    let editor = null;
    let currentWorkflowId = null;
    let echoChannel = null;

    function initDrawflow() {
        const id = document.getElementById("drawflow");
        editor = new Drawflow(id);
        editor.reroute = true;
        editor.start();

        // Node Selection Event for Right Sidebar
        editor.on('nodeSelected', function(id) {
            const node = editor.getNodeFromId(id);
            renderNodeConfig(node);
        });

        editor.on('nodeUnselected', function(id) {
            document.getElementById('node-config-panel').innerHTML = `
                <div class="text-center text-muted mt-5">
                    <i class="fa-solid fa-hand-pointer fs-1 mb-3 opacity-50"></i>
                    <p>Click on any node in the canvas<br>to configure its properties.</p>
                </div>
            `;
            document.getElementById('selected-node-id').innerText = 'None';
        });
    }

    function renderNodeConfig(node) {
        $('#selected-node-id').text('Node #' + node.id);
        const panel = $('#node-config-panel');
        
        let configHtml = `
            <div class="mb-3">
                <label class="form-label text-muted small text-uppercase">Node Name</label>
                <input type="text" class="form-control form-control-sm bg-dark text-light border-secondary node-config-input" 
                       data-key="name" data-id="${node.id}" value="${node.data.name || ''}">
            </div>
        `;

        if(node.name === 'trigger_webhook') {
            configHtml += `
                <div class="mb-3">
                    <label class="form-label text-muted small text-uppercase">Webhook URL Path</label>
                    <input type="text" class="form-control form-control-sm bg-dark text-light border-secondary node-config-input" 
                           data-key="path" data-id="${node.id}" value="${node.data.path || '/webhook/incoming'}">
                </div>
            `;
        } else if(node.name === 'action_agent') {
            configHtml += `
                <div class="mb-3">
                    <label class="form-label text-muted small text-uppercase">Select Agent</label>
                    <select class="form-select form-select-sm bg-dark text-light border-secondary node-config-input" data-key="agent_id" data-id="${node.id}">
                        <option value="1" ${node.data.agent_id == 1 ? 'selected' : ''}>Support Agent</option>
                        <option value="2" ${node.data.agent_id == 2 ? 'selected' : ''}>Sales Agent</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small text-uppercase">Task Prompt</label>
                    <textarea class="form-control form-control-sm bg-dark text-light border-secondary node-config-input" rows="4" 
                              data-key="prompt" data-id="${node.id}">${node.data.prompt || ''}</textarea>
                </div>
            `;
        } else if(node.name === 'action_api') {
            configHtml += `
                <div class="mb-3">
                    <label class="form-label text-muted small text-uppercase">Method</label>
                    <select class="form-select form-select-sm bg-dark text-light border-secondary node-config-input" data-key="method" data-id="${node.id}">
                        <option value="GET" ${node.data.method == 'GET' ? 'selected' : ''}>GET</option>
                        <option value="POST" ${node.data.method == 'POST' ? 'selected' : ''}>POST</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small text-uppercase">URL</label>
                    <input type="text" class="form-control form-control-sm bg-dark text-light border-secondary node-config-input" 
                           data-key="url" data-id="${node.id}" value="${node.data.url || 'https://'}">
                </div>
            `;
        }

        panel.html(configHtml);

        // Bind auto-save to node data
        $('.node-config-input').on('change', function() {
            const key = $(this).data('key');
            const val = $(this).val();
            const id = $(this).data('id');
            const targetNode = editor.getNodeFromId(id);
            targetNode.data[key] = val;
            editor.updateNodeDataFromId(id, targetNode.data);
            logToTerminal(`Updated config '${key}' for Node #${id}`);
        });
    }

    // Drag and Drop Logic
    let draggedNode = null;
    function drag(ev) {
        if (ev.type === "touchstart") {
            // mobile touch logic can go here
        } else {
            ev.dataTransfer.setData("node", ev.target.getAttribute('data-node'));
        }
    }

    function allowDrop(ev) {
        ev.preventDefault();
    }

    function drop(ev) {
        ev.preventDefault();
        const nodeType = ev.dataTransfer.getData("node");
        addNodeToCanvas(nodeType, ev.clientX, ev.clientY);
    }

    function addNodeToCanvas(name, pos_x, pos_y) {
        if(editor.editor_mode === 'fixed') return false;
        
        // Offset correction based on canvas wrapper position
        pos_x = pos_x * ( editor.precanvas.clientWidth / (editor.precanvas.clientWidth * editor.zoom)) - (editor.precanvas.getBoundingClientRect().x * ( editor.precanvas.clientWidth / (editor.precanvas.clientWidth * editor.zoom)));
        pos_y = pos_y * ( editor.precanvas.clientHeight / (editor.precanvas.clientHeight * editor.zoom)) - (editor.precanvas.getBoundingClientRect().y * ( editor.precanvas.clientHeight / (editor.precanvas.clientHeight * editor.zoom)));
        
        let html = '';
        let inputs = 1;
        let outputs = 1;
        let data = { name: name.replace('_', ' ').toUpperCase() };
        
        if (name === 'trigger_webhook' || name === 'trigger_schedule') {
            inputs = 0;
            html = `<div class="fw-bold"><i class="fa-solid fa-bolt text-primary me-2"></i> ${data.name}</div>`;
        } else if (name.startsWith('action_')) {
            html = `<div class="fw-bold"><i class="fa-solid fa-robot text-success me-2"></i> ${data.name}</div>`;
        } else if (name.startsWith('logic_')) {
            html = `<div class="fw-bold"><i class="fa-solid fa-code-branch text-warning me-2"></i> ${data.name}</div>`;
            outputs = 2; // e.g. true/false paths
        }

        editor.addNode(name, inputs, outputs, pos_x, pos_y, name, data, html);
        logToTerminal(`Added node: ${data.name}`);
    }

    function logToTerminal(message, type = 'info') {
        const logsContainer = document.getElementById('workflow-logs');
        const timestamp = new Date().toLocaleTimeString();
        let colorClass = 'text-light';
        if (type === 'success') colorClass = 'text-success';
        if (type === 'error') colorClass = 'text-danger';
        if (type === 'warning') colorClass = 'text-warning';
        if (type === 'info') colorClass = 'text-info';

        logsContainer.innerHTML += `<div class="${colorClass} mt-1">> [${timestamp}] ${message}</div>`;
        logsContainer.scrollTop = logsContainer.scrollHeight;
    }

    $(document).ready(function() {
        initDrawflow();
        
        // Load Workflow from Dropdown
        $('#workflow-selector').on('change', function() {
            currentWorkflowId = $(this).val();
            const rawSteps = $(this).find(':selected').data('steps');
            const metadata = $(this).find(':selected').data('metadata');
            const name = $(this).find(':selected').text();
            
            $('#wf-status-badge').removeClass('bg-secondary').addClass('bg-success').text('Active');
            logToTerminal(`Loaded workflow: ${name}`);
            
            editor.clear();
            
            // Try loading exact Drawflow JSON if it exists in metadata
            if (metadata && metadata.drawflow) {
                editor.import(metadata.drawflow);
            } 
            // Fallback to plotting basic steps if no drawflow JSON exists
            else if (rawSteps && rawSteps.length > 0) {
                rawSteps.forEach((s, i) => {
                    editor.addNode(`step_${i}`, (i===0?0:1), (i===rawSteps.length-1?0:1), 150 + (i*300), 200, 'action', {name: s.name}, `<div>${s.name}</div>`);
                    if(i > 0) editor.addConnection(i, i+1, 'output_1', 'input_1');
                });
            }
        });

        // Run Workflow
        $('#btn-run-workflow').click(function() {
            if (!currentWorkflowId) {
                window.Nexus.notify('Please select a workflow to run.', 'error');
                return;
            }
            logToTerminal('Triggering workflow...', 'info');
            $(this).prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>');
            
            window.WorkflowAPI.request(`/hub/workflows/${currentWorkflowId}/execute`, 'POST')
                .then(resp => {
                    logToTerminal(`Execution Queued! ID: ${resp.execution_id}`, 'success');
                    $(this).prop('disabled', false).html('<i class="fa-solid fa-play me-1"></i> Run');
                })
                .catch(err => {
                    logToTerminal(`Execution failed.`, 'error');
                    $(this).prop('disabled', false).html('<i class="fa-solid fa-play me-1"></i> Run');
                });
        });

        // Save Workflow
        $('#btn-save-workflow').click(function() {
            if (!currentWorkflowId) {
                window.Nexus.notify('Please select a workflow to save.', 'error');
                return;
            }
            
            $(this).prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>');
            const data = editor.export();
            
            window.WorkflowAPI.request(`/hub/workflows/${currentWorkflowId}/save`, 'POST', { drawflow: data })
                .then(resp => {
                    logToTerminal(resp.message, 'success');
                    $(this).prop('disabled', false).html('<i class="fa-solid fa-floppy-disk me-1"></i> Save');
                })
                .catch(err => {
                    logToTerminal('Failed to save workflow.', 'error');
                    $(this).prop('disabled', false).html('<i class="fa-solid fa-floppy-disk me-1"></i> Save');
                });
        });
    });
</script>
@endpush
