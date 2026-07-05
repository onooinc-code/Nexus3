<!-- Route Rule Builder Drawer -->
<div class="offcanvas offcanvas-end bg-dark border-start border-secondary text-light" tabindex="-1" id="routeRuleDrawer" style="width: 600px;">
    <div class="offcanvas-header border-bottom border-secondary">
        <h5 class="offcanvas-title fw-bold"><i class="fa-solid fa-route me-2"></i>Edit Route: general_chat</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-4">
        
        <h6 class="text-muted fw-bold mb-3">Fallback Chain Editor</h6>
        <div class="bg-dark rounded border border-secondary p-3 mb-4 d-flex align-items-center gap-2 overflow-auto text-nowrap" style="scrollbar-width: thin;">
            <div class="badge bg-primary p-2 px-3 fs-6 rounded border border-primary border-opacity-50 text-light shadow-sm">
                <div class="small text-white-50 mb-1 fw-normal" style="font-size: 0.65rem;">PRIMARY</div>
                Gemini 1.5 Pro
            </div>
            
            <i class="fa-solid fa-arrow-right text-muted mx-1"></i>
            
            <div class="badge bg-success p-2 px-3 fs-6 rounded border border-success border-opacity-50 text-light shadow-sm">
                <div class="small text-white-50 mb-1 fw-normal" style="font-size: 0.65rem;">FALLBACK 1</div>
                gpt-4o
            </div>
            
            <i class="fa-solid fa-arrow-right text-muted mx-1"></i>
            
            <button class="btn btn-sm btn-outline-secondary border-dashed rounded text-muted p-2 px-3 h-100">
                <i class="fa-solid fa-plus d-block mb-1"></i> Add Node
            </button>
        </div>
        
        <h6 class="text-muted fw-bold mb-3">Conditional Logic Rules</h6>
        <div class="bg-dark rounded border border-secondary p-3 mb-3">
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge bg-info text-dark">IF</span>
                <select class="form-select form-select-sm bg-dark text-light border-secondary w-auto">
                    <option selected>prompt_length</option>
                    <option>cost_profile</option>
                </select>
                <select class="form-select form-select-sm bg-dark text-light border-secondary w-auto">
                    <option selected>&gt;</option>
                    <option>&lt;</option>
                    <option>=</option>
                </select>
                <input type="number" class="form-control form-control-sm bg-dark border-secondary text-light w-auto" value="8000">
                <span class="text-muted small">tokens</span>
            </div>
            <div class="d-flex align-items-center gap-2 mb-2 ms-4">
                <span class="badge bg-secondary">AND</span>
                <select class="form-select form-select-sm bg-dark text-light border-secondary w-auto">
                    <option>prompt_length</option>
                    <option selected>cost_profile</option>
                </select>
                <select class="form-select form-select-sm bg-dark text-light border-secondary w-auto">
                    <option selected>=</option>
                </select>
                <select class="form-select form-select-sm bg-dark text-light border-secondary w-auto">
                    <option selected>budget</option>
                </select>
            </div>
            
            <div class="border-top border-secondary my-3"></div>
            
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge bg-success">THEN</span>
                <span class="text-muted small">Route to:</span>
                <select class="form-select form-select-sm bg-dark text-light border-secondary w-auto">
                    <option selected>Anthropic</option>
                </select>
                <i class="fa-solid fa-arrow-right text-muted" style="font-size:0.7rem;"></i>
                <select class="form-select form-select-sm bg-dark text-light border-secondary w-auto">
                    <option selected>claude-3-haiku</option>
                </select>
            </div>
        </div>
        
        <button class="btn btn-sm btn-outline-secondary w-100 border-dashed py-2 text-muted">
            <i class="fa-solid fa-plus me-1"></i> Add Rule
        </button>
        
    </div>
    <div class="offcanvas-footer border-top border-secondary p-3 bg-dark d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="offcanvas">Cancel</button>
        <button type="button" class="btn btn-primary" onclick="window.Nexus.notify('Route updated successfully', 'success'); bootstrap.Offcanvas.getInstance(document.getElementById('routeRuleDrawer')).hide();">Save Route</button>
    </div>
</div>
