<!-- Edit Budget Modal -->
<div class="modal fade" id="editBudgetModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border-secondary text-light">
            <div class="modal-header border-bottom border-secondary">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-wallet me-2"></i>Edit Budget: Global Account</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form>
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <label class="form-label small text-muted">Monthly Cap ($) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control bg-dark border-secondary text-light fs-5 font-monospace" value="250.00">
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted">Daily Soft Cap ($)</label>
                            <input type="number" class="form-control bg-dark border-secondary text-light font-monospace" value="15.00">
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted">Per-Request Max ($)</label>
                            <input type="number" step="0.01" class="form-control bg-dark border-secondary text-light font-monospace" value="0.10">
                        </div>
                    </div>
                    
                    <h6 class="text-light border-bottom border-secondary pb-2 mb-3">Enforcement Policies</h6>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Action on Exceed</label>
                        <select class="form-select bg-dark border-secondary text-light">
                            <option>Alert Only (Do not block)</option>
                            <option>Route to Cheapest Fallback</option>
                            <option selected>Block Requests (Return 429)</option>
                        </select>
                    </div>
                    
                    <div class="mb-1">
                        <label class="form-label small text-muted">Alert Thresholds (Email & In-App)</label>
                        <div class="d-flex gap-4 mt-1">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" checked id="gbAlert50">
                                <label class="form-check-label small" for="gbAlert50">50%</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" checked id="gbAlert75">
                                <label class="form-check-label small" for="gbAlert75">75%</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" checked id="gbAlert90">
                                <label class="form-check-label small" for="gbAlert90">90%</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" checked id="gbAlert100">
                                <label class="form-check-label small text-danger" for="gbAlert100">100%</label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-top border-secondary">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="window.Nexus.notify('Budget saved successfully', 'success')">Save Budget</button>
            </div>
        </div>
    </div>
</div>
