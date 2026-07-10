{{-- Provider Details & Settings Drawer --}}
<div class="offcanvas offcanvas-end bg-dark border-start border-secondary text-light" tabindex="-1" id="providerDetailsOffcanvas" style="width: 700px;">
    <div class="offcanvas-header border-bottom border-secondary pb-3">
        <div class="d-flex align-items-center gap-3">
            <div id="drawerProviderLogo" class="rounded bg-dark border border-secondary p-1 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; overflow: hidden;">
                <img src="" alt="Provider Logo" class="img-fluid" style="max-height: 32px; max-width: 32px; object-fit: contain;">
            </div>
            <div>
                <h5 class="offcanvas-title fw-bold mb-0" id="drawerProviderName">Provider Name</h5>
                <div class="d-flex align-items-center gap-2 mt-1">
                    <span id="drawerProviderStatus" class="badge bg-secondary">Status</span>
                    <span id="drawerProviderHealth" class="badge bg-secondary"><i class="fa-solid fa-circle me-1" style="font-size: 0.5rem;"></i>Health</span>
                </div>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-secondary" onclick="syncModels($('#drawerProviderId').val())" title="Sync Models"><i class="fa-solid fa-rotate"></i></button>
            <button class="btn btn-sm btn-outline-secondary" onclick="pingProvider($('#drawerProviderId').val())" title="Ping"><i class="fa-solid fa-network-wired"></i></button>
            <button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
    </div>
    
    <div class="offcanvas-body p-0">
        <input type="hidden" id="drawerProviderId">
        
        <ul class="nav nav-tabs nav-tabs-custom px-4 pt-3 border-bottom border-secondary" role="tablist" style="border-bottom-color: var(--bs-secondary) !important;">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#drawer-tab-config" type="button" role="tab">Configuration</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#drawer-tab-keys" type="button" role="tab">API Keys</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#drawer-tab-usage" type="button" role="tab">Usage & Budget</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#drawer-tab-apis" type="button" role="tab">Nexus APIs</button>
            </li>
        </ul>

        <div class="tab-content p-4">
            
            {{-- Tab 1: Configuration --}}
            <div class="tab-pane fade show active" id="drawer-tab-config" role="tabpanel">
                <form id="drawerConfigForm">
                    <div class="mb-3">
                        <label class="form-label small text-muted">Provider Name</label>
                        <input type="text" id="drawerInputName" class="form-control bg-dark border-secondary text-light">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Base URL</label>
                        <input type="text" id="drawerInputBaseUrl" class="form-control bg-dark border-secondary text-light" style="font-family: 'JetBrains Mono';">
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label small text-muted">Models Endpoint</label>
                            <input type="text" id="drawerInputModelsEndpoint" class="form-control bg-dark border-secondary text-light">
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted">Auth Format</label>
                            <input type="text" id="drawerInputAuthFormat" class="form-control bg-dark border-secondary text-light">
                        </div>
                    </div>
                    
                    <h6 class="text-muted fw-bold mt-4 mb-3">Advanced Settings</h6>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label small text-muted">Auto-Sync Interval</label>
                            <select id="drawerInputAutoSync" class="form-select bg-dark border-secondary text-light">
                                <option value="never">Never</option>
                                <option value="6h">Every 6 Hours</option>
                                <option value="12h">Every 12 Hours</option>
                                <option value="24h">Daily</option>
                                <option value="weekly">Weekly</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted">Request Timeout (ms)</label>
                            <input type="number" id="drawerInputTimeout" class="form-control bg-dark border-secondary text-light">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small text-muted">Notes / Documentation Link</label>
                        <textarea id="drawerInputNotes" class="form-control bg-dark border-secondary text-light" rows="3"></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-end mt-4">
                        <button type="button" class="btn btn-primary" onclick="saveProviderConfig()">Save Configuration</button>
                    </div>
                </form>
            </div>
            
            {{-- Tab 2: API Keys --}}
            <div class="tab-pane fade" id="drawer-tab-keys" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0 text-muted">Configured Keys</h6>
                    <button class="btn btn-sm btn-outline-success" onclick="$('#addKeyFormContainer').slideToggle()">
                        <i class="fa-solid fa-plus me-1"></i> Add Key
                    </button>
                </div>
                
                <div id="addKeyFormContainer" class="bg-black bg-opacity-25 border border-secondary rounded p-3 mb-4" style="display: none;">
                    <form id="addKeyForm">
                        <div class="mb-2">
                            <label class="form-label small">Key Name (e.g., Production, Dev)</label>
                            <input type="text" id="newKeyName" class="form-control form-control-sm bg-dark border-secondary text-light">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">API Key Value</label>
                            <input type="password" id="newKeyValue" class="form-control form-control-sm bg-dark border-secondary text-light">
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-sm btn-secondary me-2" onclick="$('#addKeyFormContainer').slideUp()">Cancel</button>
                            <button type="button" class="btn btn-sm btn-success" onclick="saveNewApiKey()">Save Key</button>
                        </div>
                    </form>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-dark table-hover table-sm align-middle" style="font-size: 0.85rem;">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Key Prefix</th>
                                <th>Last Used</th>
                                <th>Default</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="drawerKeysList">
                            {{-- Populated via JS --}}
                        </tbody>
                    </table>
                </div>
            </div>
            
            {{-- Tab 3: Usage & Budget --}}
            <div class="tab-pane fade" id="drawer-tab-usage" role="tabpanel">
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="bg-black bg-opacity-25 rounded p-3 border border-secondary text-center">
                            <div class="text-muted small mb-1">Today's Cost</div>
                            <h4 class="mb-0 text-light fw-bold" id="drawerUsageToday">$0.00</h4>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-black bg-opacity-25 rounded p-3 border border-secondary text-center">
                            <div class="text-muted small mb-1">Month's Cost</div>
                            <h4 class="mb-0 text-light fw-bold" id="drawerUsageMonth">$0.00</h4>
                        </div>
                    </div>
                </div>
                
                <h6 class="text-muted fw-bold mb-3">Budget Limits</h6>
                <div class="mb-3">
                    <label class="form-label small">Monthly Budget Cap ($)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark border-secondary text-muted">$</span>
                        <input type="number" id="drawerInputBudgetCap" class="form-control bg-dark border-secondary text-light" step="0.01">
                        <button class="btn btn-outline-primary" type="button" onclick="saveProviderMeta({ monthly_budget_cap: $('#drawerInputBudgetCap').val() })">Save</button>
                    </div>
                    <div class="form-text text-muted" style="font-size: 0.7rem;">Alerts will be sent when approaching this cap.</div>
                </div>
                
                <div class="mt-4">
                    <div class="d-flex justify-content-between text-muted mb-1" style="font-size: 0.75rem;">
                        <span>Budget Utilization</span>
                        <span id="drawerBudgetPctText">0%</span>
                    </div>
                    <div class="progress" style="height: 6px; background-color: #2b303b;">
                        <div id="drawerBudgetBar" class="progress-bar bg-info" role="progressbar" style="width: 0%"></div>
                    </div>
                </div>
            </div>
            
            {{-- Tab 4: Nexus APIs --}}
            <div class="tab-pane fade" id="drawer-tab-apis" role="tabpanel">
                <div class="alert alert-dark border-secondary d-flex align-items-center gap-3">
                    <i class="fa-solid fa-code text-info" style="font-size: 1.5rem;"></i>
                    <div>
                        <h6 class="mb-1 text-light">Auto-Discovered Endpoints</h6>
                        <p class="mb-0 text-muted small">These REST APIs are mapped to this provider's configuration within Nexus Hub.</p>
                    </div>
                </div>
                
                <div class="list-group list-group-flush border border-secondary rounded" id="drawerApisList">
                    {{-- Populated via JS --}}
                </div>
            </div>
            
        </div>
    </div>
</div>

<style>
    .nav-tabs-custom .nav-link {
        color: var(--text-secondary);
        border: none;
        background: transparent;
        border-bottom: 2px solid transparent;
        padding-bottom: 0.75rem;
    }
    .nav-tabs-custom .nav-link.active {
        color: var(--bs-primary);
        border-bottom-color: var(--bs-primary);
        background: transparent;
    }
    .nav-tabs-custom .nav-link:hover:not(.active) {
        border-bottom-color: var(--bs-secondary);
    }
</style>

@push('scripts')
<script>
    window.populateProviderDetailsDrawer = function(data) {
        // Identity & Header
        $('#drawerProviderId').val(data.id);
        $('#drawerProviderName').text(data.name);
        
        let logoUrl = 'https://ui-avatars.com/api/?name='+encodeURIComponent(data.name)+'&background=1a1f2e&color=58a6ff&size=40&font-size=0.4&bold=true';
        const nameLower = data.name.toLowerCase();
        if(nameLower.includes('openai') || nameLower.includes('gpt')) logoUrl = 'https://upload.wikimedia.org/wikipedia/commons/0/04/ChatGPT_logo.svg';
        else if(nameLower.includes('google') || nameLower.includes('gemini')) logoUrl = 'https://www.gstatic.com/lamda/images/gemini_sparkle_v002_d4735304ff6292a690345.svg';
        else if(nameLower.includes('anthropic') || nameLower.includes('claude')) logoUrl = 'https://upload.wikimedia.org/wikipedia/commons/thumb/7/7f/Anthropic_logo.svg/200px-Anthropic_logo.svg.png';
        
        $('#drawerProviderLogo img').attr('src', logoUrl);

        // Status badge
        if(data.is_active) {
            $('#drawerProviderStatus').removeClass('bg-secondary').addClass('bg-success bg-opacity-25 text-success border border-success').text('Active');
        } else {
            $('#drawerProviderStatus').removeClass('bg-success bg-opacity-25 text-success border border-success').addClass('bg-secondary text-light').text('Disabled');
        }

        // Configuration Tab
        $('#drawerInputName').val(data.name);
        $('#drawerInputBaseUrl').val(data.base_url);
        $('#drawerInputModelsEndpoint').val(data.models_fetch_endpoint);
        $('#drawerInputAuthFormat').val(data.auth_header_format);
        $('#drawerInputAutoSync').val(data.auto_sync_interval || 'never');
        $('#drawerInputTimeout').val(data.request_timeout_ms || 30000);
        $('#drawerInputNotes').val(data.notes || '');

        // Usage & Budget Tab
        $('#drawerUsageToday').text('$' + Number(data.usage?.today_cost || 0).toFixed(2));
        $('#drawerUsageMonth').text('$' + Number(data.usage?.month_cost || 0).toFixed(2));
        $('#drawerInputBudgetCap').val(data.monthly_budget_cap || '');
        
        if (data.monthly_budget_cap > 0 && data.usage) {
            const pct = Math.min(100, (data.usage.month_cost / data.monthly_budget_cap) * 100);
            $('#drawerBudgetPctText').text(pct.toFixed(1) + '%');
            $('#drawerBudgetBar').css('width', pct + '%');
            $('#drawerBudgetBar').removeClass('bg-info bg-warning bg-danger').addClass(pct > 90 ? 'bg-danger' : (pct > 75 ? 'bg-warning' : 'bg-info'));
        } else {
            $('#drawerBudgetPctText').text('0%');
            $('#drawerBudgetBar').css('width', '0%').removeClass('bg-warning bg-danger').addClass('bg-info');
        }

        // API Keys Tab
        renderKeysList(data.api_keys || []);

        // Nexus APIs Tab
        renderApisList(data.nexus_apis || []);
    };

    function renderKeysList(keys) {
        const tbody = $('#drawerKeysList');
        tbody.empty();
        if(keys.length === 0) {
            tbody.append(`<tr><td colspan="5" class="text-center text-muted py-3">No API keys configured.</td></tr>`);
            return;
        }
        
        keys.forEach(k => {
            const defBadge = k.is_default ? '<span class="badge bg-primary text-light" style="font-size:0.6rem;">Default</span>' : '';
            const lastUsed = k.last_used_at ? new Date(k.last_used_at).toLocaleDateString() : 'Never';
            
            tbody.append(`
                <tr>
                    <td class="text-light fw-semibold">${k.name}</td>
                    <td class="text-muted" style="font-family: monospace;">${k.key_prefix}...</td>
                    <td class="text-muted" style="font-size: 0.75rem;">${lastUsed}</td>
                    <td>${defBadge}</td>
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-link text-muted p-0" data-bs-toggle="dropdown"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                            <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow">
                                ${!k.is_default ? `<li><a class="dropdown-item" href="#" onclick="setDefaultKey('${k.id}'); return false;">Set as Default</a></li>` : ''}
                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteKey('${k.id}'); return false;">Delete</a></li>
                            </ul>
                        </div>
                    </td>
                </tr>
            `);
        });
    }

    function renderApisList(apis) {
        const list = $('#drawerApisList');
        list.empty();
        if(apis.length === 0) {
            list.append(`<div class="list-group-item bg-dark text-muted text-center py-4">No mapped endpoints found.</div>`);
            return;
        }
        
        apis.forEach(api => {
            const methodBadge = `<span class="badge bg-secondary text-light" style="width: 50px;">${api.method}</span>`;
            list.append(`
                <div class="list-group-item bg-dark border-secondary px-3 py-2">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            ${methodBadge}
                            <span class="text-light" style="font-family: 'JetBrains Mono'; font-size: 0.8rem;">${api.uri}</span>
                        </div>
                        <a href="${api.uri}" target="_blank" class="btn btn-sm btn-link text-muted"><i class="fa-solid fa-external-link"></i></a>
                    </div>
                </div>
            `);
        });
    }

    // Handlers
    window.saveProviderConfig = function() {
        const id = $('#drawerProviderId').val();
        const payload = {
            name: $('#drawerInputName').val(),
            base_url: $('#drawerInputBaseUrl').val(),
            models_fetch_endpoint: $('#drawerInputModelsEndpoint').val(),
            auth_header_format: $('#drawerInputAuthFormat').val(),
            auto_sync_interval: $('#drawerInputAutoSync').val(),
            request_timeout_ms: $('#drawerInputTimeout').val(),
            notes: $('#drawerInputNotes').val(),
        };
        
        window.Nexus.showTaskLoader('Saving...');
        $.ajax({
            url: `/api/v1/ai/providers/${id}`,
            method: 'PUT',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
            data: JSON.stringify(payload),
            success: function(res) {
                window.Nexus.hideTaskLoader();
                window.Nexus.notify('Provider configured successfully', 'success');
                setTimeout(() => location.reload(), 1000);
            },
            error: function() {
                window.Nexus.hideTaskLoader();
                window.Nexus.notify('Failed to save configuration', 'error');
            }
        });
    };

    window.saveProviderMeta = function(payload) {
        const id = $('#drawerProviderId').val();
        window.Nexus.showTaskLoader('Updating...');
        $.ajax({
            url: `/api/v1/ai/providers/${id}/meta`,
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
            data: JSON.stringify(payload),
            success: function(res) {
                window.Nexus.hideTaskLoader();
                window.Nexus.notify('Updated successfully', 'success');
            },
            error: function() {
                window.Nexus.hideTaskLoader();
                window.Nexus.notify('Update failed', 'error');
            }
        });
    };

    window.saveNewApiKey = function() {
        const pId = $('#drawerProviderId').val();
        const name = $('#newKeyName').val();
        const val = $('#newKeyValue').val();
        if(!name || !val) return window.Nexus.notify('Name and Value required', 'error');
        
        window.Nexus.showTaskLoader('Saving key...');
        $.ajax({
            url: `/api/v1/ai/providers/${pId}/keys`,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
            data: JSON.stringify({ name: name, key_value: val, is_default: true }),
            success: function(res) {
                window.Nexus.hideTaskLoader();
                window.Nexus.notify('API Key saved', 'success');
                $('#newKeyName, #newKeyValue').val('');
                $('#addKeyFormContainer').slideUp();
                // refresh keys
                $.get(`/api/v1/ai/providers/${pId}/details`, r => renderKeysList(r.data.api_keys));
            }
        });
    };

    window.deleteKey = function(keyId) {
        if(!confirm('Delete this API key?')) return;
        const pId = $('#drawerProviderId').val();
        $.ajax({
            url: `/api/v1/ai/api-keys/${keyId}`,
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function() {
                window.Nexus.notify('API Key deleted', 'success');
                $.get(`/api/v1/ai/providers/${pId}/details`, r => renderKeysList(r.data.api_keys));
            }
        });
    };

    window.setDefaultKey = function(keyId) {
        const pId = $('#drawerProviderId').val();
        $.ajax({
            url: `/api/v1/ai/api-keys/${keyId}/set-default`,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function() {
                window.Nexus.notify('Default key updated', 'success');
                $.get(`/api/v1/ai/providers/${pId}/details`, r => renderKeysList(r.data.api_keys));
            }
        });
    };
</script>
@endpush
