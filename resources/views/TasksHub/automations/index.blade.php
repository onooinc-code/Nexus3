<div class="row g-3 h-100 pb-3 pe-2">
    
    <!-- Left Panel: Task Templates (F23) -->
    <div class="col-lg-6 d-flex flex-column">
        <div class="tasks-glass-card p-0 flex-grow-1 d-flex flex-column overflow-hidden">
            <div class="p-3 border-bottom border-secondary d-flex justify-content-between align-items-center bg-dark bg-opacity-50">
                <h6 class="mb-0 text-light font-mono"><i class="fa-solid fa-copy me-2 text-info"></i>Task Templates</h6>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#newTemplateModal"><i class="fa-solid fa-plus me-1"></i>New Template</button>
            </div>
            
            <div class="flex-grow-1 p-3 overflow-auto" id="templates-list-container">
                <!-- Example Template Card -->
                <div class="tasks-glass-panel p-3 mb-3 rounded" style="background: rgba(255,255,255,0.03);">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="text-light font-mono mb-0">Daily Database Backup</h6>
                        <span class="badge bg-secondary">System</span>
                    </div>
                    <p class="text-muted font-mono mb-3" style="font-size: 0.75rem;">Triggers a backup sequence. Variables: <code>{environment}</code></p>
                    <div class="d-flex justify-content-end gap-2">
                        <button class="btn btn-sm btn-outline-info btn-spawn-template" data-name="Daily Database Backup" data-vars="environment"><i class="fa-solid fa-play me-1"></i>Spawn</button>
                    </div>
                </div>

                <div class="tasks-glass-panel p-3 mb-3 rounded" style="background: rgba(255,255,255,0.03);">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="text-light font-mono mb-0">Code Review Request</h6>
                        <span class="badge bg-primary">Agent</span>
                    </div>
                    <p class="text-muted font-mono mb-3" style="font-size: 0.75rem;">Analyzes a PR. Variables: <code>{pr_url}</code>, <code>{branch}</code></p>
                    <div class="d-flex justify-content-end gap-2">
                        <button class="btn btn-sm btn-outline-info btn-spawn-template" data-name="Code Review Request" data-vars="pr_url,branch"><i class="fa-solid fa-play me-1"></i>Spawn</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Panel: Automation Rules (F24) -->
    <div class="col-lg-6 d-flex flex-column">
        <div class="tasks-glass-card p-0 flex-grow-1 d-flex flex-column overflow-hidden">
            <div class="p-3 border-bottom border-secondary d-flex justify-content-between align-items-center bg-dark bg-opacity-50">
                <h6 class="mb-0 text-light font-mono"><i class="fa-solid fa-bolt me-2 text-warning"></i>Automation Rules (If/Then)</h6>
                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#newRuleModal"><i class="fa-solid fa-plus me-1"></i>New Rule</button>
            </div>
            
            <div class="flex-grow-1 p-3 overflow-auto" id="rules-list-container">
                <!-- Example Rule Card -->
                <div class="tasks-glass-panel p-3 mb-3 rounded border-start border-warning" style="background: rgba(255,255,255,0.02); border-left-width: 3px !important;">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h6 class="text-light font-mono mb-0">Notify Admin on Critical Failure</h6>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" checked>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3 font-mono" style="font-size: 0.75rem;">
                        <div class="bg-dark p-2 rounded border border-secondary text-light">
                            <span class="text-muted d-block" style="font-size: 0.6rem;">IF (Trigger)</span>
                            Task Status = <span class="text-danger">Failed</span> AND Priority >= 8
                        </div>
                        <i class="fa-solid fa-arrow-right text-muted"></i>
                        <div class="bg-dark p-2 rounded border border-secondary text-light">
                            <span class="text-muted d-block" style="font-size: 0.6rem;">THEN (Action)</span>
                            Send Waha Message to <span class="text-success">AdminGroup</span>
                        </div>
                    </div>
                </div>

                <div class="tasks-glass-panel p-3 mb-3 rounded border-start border-warning" style="background: rgba(255,255,255,0.02); border-left-width: 3px !important;">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h6 class="text-light font-mono mb-0">Auto-Retry API Rate Limits</h6>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" checked>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3 font-mono" style="font-size: 0.75rem;">
                        <div class="bg-dark p-2 rounded border border-secondary text-light">
                            <span class="text-muted d-block" style="font-size: 0.6rem;">IF (Trigger)</span>
                            Last Error contains <span class="text-warning">"429 Too Many Requests"</span>
                        </div>
                        <i class="fa-solid fa-arrow-right text-muted"></i>
                        <div class="bg-dark p-2 rounded border border-secondary text-light">
                            <span class="text-muted d-block" style="font-size: 0.6rem;">THEN (Action)</span>
                            Increase Backoff to <span class="text-info">Exp(5m)</span> AND Requeue
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Template Spawn Modal -->
<div class="modal fade" id="spawnTemplateModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content tasks-glass-panel" style="background: rgba(10, 14, 26, 0.98);">
            <div class="modal-header border-secondary">
                <h6 class="modal-title font-mono text-light"><i class="fa-solid fa-play text-info me-2"></i>Spawn: <span id="spawn-tmpl-name"></span></h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="spawn-tmpl-form">
                    <p class="text-muted font-mono mb-3" style="font-size: 0.75rem;">Please provide values for the template variables.</p>
                    <div id="spawn-vars-container"></div>
                </form>
            </div>
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-info" id="btn-confirm-spawn">Create Task</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentTemplateId = null;
    
    // Dynamic fetching of task templates
    function loadTaskTemplates() {
        if (!window.TaskAPI) return;
        TaskAPI.get('/task-templates').done(function(res) {
            if (res.data && res.data.length > 0) {
                let container = $('#templates-list-container');
                container.empty();
                res.data.forEach(tmpl => {
                    let varsStr = Array.isArray(tmpl.variables) ? tmpl.variables.join(',') : (tmpl.variables || '');
                    container.append(`
                        <div class="tasks-glass-panel p-3 mb-3 rounded" style="background: rgba(255,255,255,0.03);">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="text-light font-mono mb-0">${tmpl.name}</h6>
                                <span class="badge ${tmpl.type === 'system' ? 'bg-secondary' : 'bg-primary'}">${tmpl.type || 'Template'}</span>
                            </div>
                            <p class="text-muted font-mono mb-3" style="font-size: 0.75rem;">${tmpl.description || ''}</p>
                            <div class="d-flex justify-content-end gap-2">
                                <button class="btn btn-sm btn-outline-info btn-spawn-template" data-id="${tmpl.id}" data-name="${tmpl.name}" data-vars="${varsStr}"><i class="fa-solid fa-play me-1"></i>Spawn</button>
                            </div>
                        </div>
                    `);
                });
            }
        });
    }

    if ($('#content-automations').hasClass('active')) {
        loadTaskTemplates();
    }
    $('a[data-bs-toggle="tab"], button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        if ($(e.target).attr('data-bs-target') === '#content-automations') {
            loadTaskTemplates();
        }
    });

    // Template Spawning Logic (F23) - Delegated click event
    $(document).on('click', '.btn-spawn-template', function() {
        currentTemplateId = $(this).data('id') || null;
        let name = $(this).data('name');
        let varsStr = $(this).data('vars');
        let vars = varsStr ? varsStr.toString().split(',') : [];
        
        $('#spawn-tmpl-name').text(name);
        let container = $('#spawn-vars-container');
        container.empty();

        if(vars.length === 0) {
            container.append('<div class="text-success font-mono" style="font-size: 0.8rem;">No variables required. Ready to spawn.</div>');
        } else {
            vars.forEach(v => {
                let cleanVar = v.trim();
                if(cleanVar) {
                    container.append(`
                        <div class="mb-3">
                            <label class="form-label font-mono text-muted" style="font-size: 0.7rem;">${cleanVar}</label>
                            <input type="text" class="form-control form-control-sm bg-dark border-secondary text-light tmpl-var-input" data-var="${cleanVar}" required>
                        </div>
                    `);
                }
            });
        }
        
        $('#spawnTemplateModal').modal('show');
    });

    $('#btn-confirm-spawn').on('click', function() {
        let btn = $(this);
        let inputs = $('.tmpl-var-input');
        let payload = {};
        let valid = true;

        inputs.each(function() {
            if(!$(this).val()) valid = false;
            payload[$(this).data('var')] = $(this).val();
        });

        if(inputs.length > 0 && !valid) {
            alert('Please fill all variables.');
            return;
        }

        btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>');

        if (currentTemplateId && window.TaskAPI) {
            TaskAPI.post(`/task-templates/${currentTemplateId}/spawn`, { variables: payload })
                .done(function() {
                    Nexus.notify('Task spawned from template successfully!', 'success');
                    $('#spawnTemplateModal').modal('hide');
                })
                .fail(function() {
                    Nexus.notify('Failed to spawn task template', 'error');
                })
                .always(function() {
                    btn.prop('disabled', false).text('Create Task');
                });
        } else {
            setTimeout(() => {
                Nexus.notify('Task spawned from template successfully!', 'success');
                $('#spawnTemplateModal').modal('hide');
                btn.prop('disabled', false).text('Create Task');
            }, 500);
        }
    });

});
</script>
