<!-- Add/Edit Provider Drawer -->
<div class="offcanvas offcanvas-end bg-dark border-start border-secondary text-light" tabindex="-1" id="addProviderOffcanvas" style="width: 500px;">
    <div class="offcanvas-header border-bottom border-secondary">
        <h5 class="offcanvas-title fw-bold"><i class="fa-solid fa-server me-2"></i>Add Provider</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-4">
        <form id="addProviderForm">
            <h6 class="text-muted fw-bold mb-3">1. Identity</h6>
            <div class="mb-3">
                <label class="form-label small">Provider Preset</label>
                <div class="d-flex flex-wrap gap-2 mb-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary active">OpenAI</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary">Anthropic</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary">Google</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary">Custom</button>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label small">Provider Name</label>
                <input type="text" class="form-control bg-dark border-secondary text-light" value="OpenAI">
            </div>
            
            <div class="mb-3">
                <label class="form-label small">Base URL</label>
                <div class="input-group">
                    <input type="text" class="form-control bg-dark border-secondary text-light" value="https://api.openai.com/v1">
                    <button class="btn btn-outline-secondary" type="button">Test</button>
                </div>
            </div>
            
            <hr class="border-secondary my-4">
            
            <h6 class="text-muted fw-bold mb-3">2. Authentication</h6>
            <div class="mb-3">
                <label class="form-label small">Auth Format</label>
                <select class="form-select bg-dark border-secondary text-light">
                    <option>Bearer {key}</option>
                    <option>x-api-key: {key}</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label small">API Key <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="password" class="form-control bg-dark border-secondary text-light" placeholder="sk-...">
                    <button class="btn btn-outline-secondary" type="button"><i class="fa-solid fa-eye"></i></button>
                </div>
            </div>
            
            <hr class="border-secondary my-4">
            
            <h6 class="text-muted fw-bold mb-3">3. Advanced</h6>
            <div class="mb-3 form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" id="autoSync" checked>
                <label class="form-check-label small" for="autoSync">Auto-sync models daily</label>
            </div>
            
            <div class="mb-3">
                <label class="form-label small">Circuit Breaker Threshold</label>
                <input type="number" class="form-control bg-dark border-secondary text-light" value="5">
                <div class="form-text text-muted" style="font-size: 0.7rem;">Number of consecutive failures before opening circuit.</div>
            </div>
        </form>
    </div>
    <div class="offcanvas-footer border-top border-secondary p-3 bg-dark d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="offcanvas">Cancel</button>
        <button type="button" class="btn btn-primary" onclick="window.Nexus.notify('Provider saved successfully', 'success'); bootstrap.Offcanvas.getInstance(document.getElementById('addProviderOffcanvas')).hide();">Test & Save</button>
    </div>
</div>
