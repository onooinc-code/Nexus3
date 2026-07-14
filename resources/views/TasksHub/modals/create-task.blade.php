<!-- New Task Master Modal -->
<div class="modal fade" id="newTaskModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content tasks-glass-panel" style="background: rgba(10, 14, 26, 0.98);">
            
            <div class="modal-header border-secondary p-4 pb-3">
                <h5 class="modal-title font-display fw-bold text-light d-flex align-items-center gap-2">
                    <i class="fa-solid fa-wand-magic-sparkles text-primary"></i> Create Objective
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body p-0 d-flex" style="min-height: 500px;">
                <!-- Left Sidebar: Task Types -->
                <div class="border-end border-secondary p-4 d-flex flex-column gap-2" style="width: 250px; background: rgba(0,0,0,0.2);">
                    <p class="text-muted font-mono mb-2" style="font-size: 0.65rem; text-transform: uppercase;">Execution Engine</p>
                    
                    <button class="btn task-type-selector text-start px-3 py-2 active" data-type="manual" style="border-radius: 8px;">
                        <i class="fa-solid fa-user me-2 text-secondary"></i> Manual Task
                    </button>
                    <button class="btn task-type-selector text-start px-3 py-2" data-type="agent" style="border-radius: 8px;">
                        <i class="fa-solid fa-robot me-2 text-primary"></i> Agent Task
                    </button>
                    <button class="btn task-type-selector text-start px-3 py-2" data-type="system" style="border-radius: 8px;">
                        <i class="fa-solid fa-microchip me-2 text-warning"></i> System Core
                    </button>
                    <button class="btn task-type-selector text-start px-3 py-2" data-type="code" style="border-radius: 8px;">
                        <i class="fa-solid fa-code me-2 text-info"></i> Code Eval
                    </button>
                    <button class="btn task-type-selector text-start px-3 py-2" data-type="api" style="border-radius: 8px;">
                        <i class="fa-solid fa-network-wired me-2 text-success"></i> API Request
                    </button>
                    <button class="btn task-type-selector text-start px-3 py-2" data-type="terminal" style="border-radius: 8px;">
                        <i class="fa-solid fa-terminal me-2 text-light"></i> Shell Command
                    </button>
                    <button class="btn task-type-selector text-start px-3 py-2" data-type="tool" style="border-radius: 8px;">
                        <i class="fa-solid fa-screwdriver-wrench me-2" style="color: #F472B6;"></i> External Tool
                    </button>
                </div>

                <!-- Right Side: Form Configuration -->
                <div class="p-4 flex-grow-1 overflow-y-auto position-relative" style="max-height: 70vh;">
                    <form id="create-task-form">
                        <input type="hidden" id="frm-task-type" name="type" value="manual">
                        
                        <!-- Core Fields (Always Visible) -->
                        <div class="row g-4 mb-4">
                            <div class="col-12">
                                <label class="form-label font-mono text-muted" style="font-size: 0.7rem;">Objective Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control bg-dark border-secondary text-light form-control-lg" name="title" id="frm-title" placeholder="What needs to be done?" required autocomplete="off">
                                
                                <!-- Smart Routing Preview (Visible for Agent tasks) -->
                                <div id="smart-routing-preview" class="mt-2 d-none p-2 rounded" style="background: rgba(16, 185, 129, 0.1); border: 1px dashed rgba(16, 185, 129, 0.3);">
                                    <span class="font-mono text-muted" style="font-size: 0.7rem;"><i class="fa-solid fa-route text-success me-1"></i>Routing Prediction:</span>
                                    <span id="routing-agent-name" class="badge bg-success ms-2">Waiting for input...</span>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label font-mono text-muted" style="font-size: 0.7rem;">Priority (1-10)</label>
                                <div class="d-flex align-items-center gap-2">
                                    <input type="range" class="form-range flex-grow-1" name="priority" id="frm-priority" min="1" max="10" value="5">
                                    <span class="badge bg-secondary font-mono" id="frm-priority-val" style="width: 30px;">5</span>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label font-mono text-muted" style="font-size: 0.7rem;">Tags</label>
                                <input type="text" class="form-control bg-dark border-secondary text-light form-control-sm" name="tags" placeholder="e.g. urgent, frontend, db">
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label font-mono text-muted" style="font-size: 0.7rem;">Detailed Instructions / Context</label>
                                <textarea class="form-control bg-dark border-secondary text-light" name="description" rows="3" placeholder="Provide context, constraints, and success criteria..."></textarea>
                            </div>
                        </div>

                        <!-- Dynamic Config Sections -->
                        
                        <!-- 1. Agent Config -->
                        <div class="config-section d-none" id="config-agent">
                            <h6 class="text-light mb-3 font-mono border-bottom border-secondary pb-2"><i class="fa-solid fa-robot text-primary me-2"></i>Agent Configuration</h6>
                            <div class="mb-3">
                                <label class="form-label font-mono text-muted" style="font-size: 0.7rem;">Target Agent (Leave empty for Auto-Routing)</label>
                                <select name="agent_id" class="form-select bg-dark border-secondary text-light">
                                    <option value="">-- Auto-Route based on intent --</option>
                                    <option value="Nexus_Architect">Nexus_Architect</option>
                                    <option value="Code_Reviewer">Code_Reviewer</option>
                                    <option value="DB_Admin">DB_Admin</option>
                                </select>
                            </div>
                        </div>

                        <!-- 2. System Core Config -->
                        <div class="config-section d-none" id="config-system">
                            <h6 class="text-light mb-3 font-mono border-bottom border-secondary pb-2"><i class="fa-solid fa-microchip text-warning me-2"></i>System Job Config</h6>
                            <div class="mb-3">
                                <label class="form-label font-mono text-muted" style="font-size: 0.7rem;">Job Payload (JSON)</label>
                                <textarea class="form-control font-mono bg-dark border-secondary text-light" name="system_payload" rows="4" placeholder='{"action": "cleanup_temp", "target": "storage/logs"}'></textarea>
                            </div>
                        </div>

                        <!-- 3. Code Config -->
                        <div class="config-section d-none" id="config-code">
                            <h6 class="text-light mb-3 font-mono border-bottom border-secondary pb-2"><i class="fa-solid fa-code text-info me-2"></i>Code Evaluation</h6>
                            <div class="mb-3">
                                <label class="form-label font-mono text-muted" style="font-size: 0.7rem;">Language</label>
                                <select name="code_lang" class="form-select bg-dark border-secondary text-light mb-2">
                                    <option value="php">PHP (App Context)</option>
                                    <option value="python">Python</option>
                                    <option value="bash">Bash</option>
                                </select>
                                <label class="form-label font-mono text-muted" style="font-size: 0.7rem;">Script</label>
                                <textarea class="form-control font-mono bg-dark border-secondary text-light" name="code_script" rows="6" placeholder="echo 'Hello World';"></textarea>
                            </div>
                        </div>

                        <!-- 4. API Config -->
                        <div class="config-section d-none" id="config-api">
                            <h6 class="text-light mb-3 font-mono border-bottom border-secondary pb-2"><i class="fa-solid fa-network-wired text-success me-2"></i>API Request</h6>
                            <div class="row g-2 mb-3">
                                <div class="col-md-3">
                                    <select name="api_method" class="form-select bg-dark border-secondary text-light">
                                        <option value="GET">GET</option>
                                        <option value="POST">POST</option>
                                        <option value="PUT">PUT</option>
                                        <option value="DELETE">DELETE</option>
                                    </select>
                                </div>
                                <div class="col-md-9">
                                    <input type="url" class="form-control bg-dark border-secondary text-light" name="api_url" placeholder="https://api.example.com/v1/resource">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label font-mono text-muted" style="font-size: 0.7rem;">Headers (JSON)</label>
                                <input type="text" class="form-control font-mono bg-dark border-secondary text-light form-control-sm" name="api_headers" placeholder='{"Authorization": "Bearer token"}'>
                            </div>
                        </div>

                        <!-- 5. Terminal Config -->
                        <div class="config-section d-none" id="config-terminal">
                            <h6 class="text-light mb-3 font-mono border-bottom border-secondary pb-2"><i class="fa-solid fa-terminal text-light me-2"></i>Shell Command</h6>
                            <div class="mb-3">
                                <label class="form-label font-mono text-muted" style="font-size: 0.7rem;">Command</label>
                                <input type="text" class="form-control font-mono bg-dark border-secondary text-light" name="terminal_cmd" placeholder="php artisan optimize:clear">
                            </div>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" role="switch" name="terminal_root" id="terminal_root">
                                <label class="form-check-label font-mono text-muted" for="terminal_root" style="font-size: 0.75rem;">Run as Root (Danger)</label>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
            
            <div class="modal-footer border-secondary p-3 d-flex justify-content-between" style="background: rgba(0,0,0,0.3);">
                <div class="form-check">
                    <input class="form-check-input border-secondary bg-dark" type="checkbox" id="frm-auto-execute" checked>
                    <label class="form-check-label text-muted font-mono" for="frm-auto-execute" style="font-size: 0.75rem;">
                        Auto-Execute on Creation
                    </label>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="btn-submit-task">Deploy Task <i class="fa-solid fa-paper-plane ms-1"></i></button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.task-type-selector {
    color: var(--text-muted);
    border: 1px solid transparent;
    transition: all 0.2s;
    background: transparent;
}
.task-type-selector:hover {
    background: rgba(255,255,255,0.05);
    color: #fff;
}
.task-type-selector.active {
    background: rgba(99, 102, 241, 0.15);
    border: 1px solid rgba(99, 102, 241, 0.5);
    color: #fff;
    box-shadow: 0 0 15px rgba(99, 102, 241, 0.2);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // UI Type Switching
    $('.task-type-selector').on('click', function() {
        $('.task-type-selector').removeClass('active');
        $(this).addClass('active');
        
        let type = $(this).data('type');
        $('#frm-task-type').val(type);
        
        // Toggle Config Sections
        $('.config-section').addClass('d-none');
        if(type !== 'manual' && type !== 'tool') {
            $('#config-' + type).removeClass('d-none');
        }

        // Toggle Smart Routing Preview
        if(type === 'agent') {
            $('#smart-routing-preview').removeClass('d-none');
            triggerRoutingPreview();
        } else {
            $('#smart-routing-preview').addClass('d-none');
        }
    });

    // Priority Slider Value
    $('#frm-priority').on('input', function() {
        let val = $(this).val();
        $('#frm-priority-val').text(val);
        if(val > 7) $('#frm-priority-val').removeClass('bg-secondary bg-success bg-warning').addClass('bg-danger');
        else if (val > 4) $('#frm-priority-val').removeClass('bg-secondary bg-success bg-danger').addClass('bg-warning');
        else $('#frm-priority-val').removeClass('bg-secondary bg-warning bg-danger').addClass('bg-success');
    });

    // Smart Routing Preview Logic
    let routingTimeout;
    function triggerRoutingPreview() {
        if($('#frm-task-type').val() !== 'agent') return;
        
        let title = $('#frm-title').val();
        if(title.length < 5) {
            $('#routing-agent-name').text('Waiting for more input...');
            return;
        }

        clearTimeout(routingTimeout);
        routingTimeout = setTimeout(() => {
            $('#routing-agent-name').html('<i class="fa-solid fa-spinner fa-spin"></i> Analyzing...');
            // In a real scenario, this would be an API call to an LLM or keyword matcher
            // For now, simple keyword simulation for the UI
            let matchedAgent = 'General_Agent';
            let t = title.toLowerCase();
            if(t.includes('code') || t.includes('bug') || t.includes('fix')) matchedAgent = 'Code_Reviewer';
            else if(t.includes('db') || t.includes('database') || t.includes('table')) matchedAgent = 'DB_Admin';
            else if(t.includes('server') || t.includes('deploy')) matchedAgent = 'DevOps_Agent';
            
            setTimeout(() => {
                $('#routing-agent-name').html(`<i class="fa-solid fa-check"></i> ${matchedAgent}`);
            }, 600); // Simulate network latency

        }, 500);
    }
    
    $('#frm-title').on('input', triggerRoutingPreview);

    // Form Submission
    $('#btn-submit-task').on('click', function() {
        let title = $('#frm-title').val();
        if(!title) {
            alert('Title is required');
            return;
        }

        let type = $('#frm-task-type').val();
        let payloadData = {};
        
        // Collect specific payloads
        if (type === 'system') {
            payloadData = $('#create-task-form').find('textarea[name="system_payload"]').val();
        } else if (type === 'code') {
            payloadData = JSON.stringify({
                lang: $('select[name="code_lang"]').val(),
                script: $('textarea[name="code_script"]').val()
            });
        } else if (type === 'api') {
            payloadData = JSON.stringify({
                method: $('select[name="api_method"]').val(),
                url: $('input[name="api_url"]').val(),
                headers: $('input[name="api_headers"]').val()
            });
        } else if (type === 'terminal') {
            payloadData = JSON.stringify({
                cmd: $('input[name="terminal_cmd"]').val(),
                root: $('#terminal_root').is(':checked')
            });
        }

        let data = {
            title: title,
            description: $('textarea[name="description"]').val(),
            type: type,
            priority: $('#frm-priority').val(),
            tags: $('input[name="tags"]').val(),
            auto_execute: $('#frm-auto-execute').is(':checked') ? 1 : 0
        };

        if(type === 'agent') {
            data.agent_id = $('select[name="agent_id"]').val(); // Empty = Auto
        }

        if(Object.keys(payloadData).length > 0 && typeof payloadData !== 'string') {
            // No payload
        } else if (typeof payloadData === 'string' && payloadData.trim() !== '') {
            data.payload_data = payloadData;
        }

        let btn = $(this);
        btn.html('<i class="fa-solid fa-spinner fa-spin"></i>').prop('disabled', true);

        // Map to endpoints based on type or use master /tasks endpoint
        // Assuming standard endpoint handles all via `type` column
        TaskAPI.post('/tasks', data).done(function(res) {
            Nexus.notify('Task Created Successfully', 'success');
            $('#newTaskModal').modal('hide');
            $('#create-task-form')[0].reset();
            
            // Auto trigger execute if checked
            if(data.auto_execute && res.data && res.data.id) {
                TaskAPI.post(`/tasks/${res.data.id}/execute`);
            }
            
            // Refresh tables/boards
            if($('#btn-refresh-table').length) $('#btn-refresh-table').click();
            if($('#btn-refresh-board').length) $('#btn-refresh-board').click();
            
        }).always(function() {
            btn.html('Deploy Task <i class="fa-solid fa-paper-plane ms-1"></i>').prop('disabled', false);
        });
    });
});
</script>
