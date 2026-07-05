<!-- Per-Key Analytics Drawer -->
<div class="offcanvas offcanvas-end bg-dark border-start border-secondary text-light" tabindex="-1" id="keyAnalyticsDrawer" style="width: 700px;">
    <div class="offcanvas-header border-bottom border-secondary pb-3">
        <div>
            <h5 class="offcanvas-title fw-bold mb-1"><i class="fa-solid fa-chart-pie me-2"></i>Key Analytics</h5>
            <div class="d-flex align-items-center gap-2" style="font-size: 0.8rem;">
                <span class="text-muted">Production Key #1</span>
                <span class="text-muted font-monospace border border-secondary rounded px-1">sk-...****8f2a</span>
            </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-4">
        <ul class="nav nav-tabs ai-hub-tabs mb-4" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#key-overview">Overview</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#key-requests">Recent Requests</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#key-settings">Settings</button></li>
        </ul>
        
        <div class="tab-content">
            <div class="tab-pane fade show active" id="key-overview">
                <!-- Stat Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-sm-6 col-md-3">
                        <div class="bg-dark border border-secondary rounded p-2 text-center h-100">
                            <div class="text-muted small mb-1">Total Reqs</div>
                            <div class="fw-bold fs-5">18,430</div>
                            <div class="text-success" style="font-size: 0.7rem;"><i class="fa-solid fa-arrow-up"></i> 12%</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="bg-dark border border-secondary rounded p-2 text-center h-100">
                            <div class="text-muted small mb-1">Success</div>
                            <div class="fw-bold fs-5 text-success">18,135</div>
                            <div class="text-success" style="font-size: 0.7rem;">98.4%</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="bg-dark border border-secondary rounded p-2 text-center h-100">
                            <div class="text-muted small mb-1">Total Cost</div>
                            <div class="fw-bold fs-5 text-warning">$48.20</div>
                            <div class="text-danger" style="font-size: 0.7rem;"><i class="fa-solid fa-arrow-up"></i> $5.10</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="bg-dark border border-secondary rounded p-2 text-center h-100">
                            <div class="text-muted small mb-1">Avg Cost/Req</div>
                            <div class="fw-bold fs-5 text-info">$0.002</div>
                            <div class="text-success" style="font-size: 0.7rem;"><i class="fa-solid fa-arrow-down"></i> 2%</div>
                        </div>
                    </div>
                </div>
                
                <h6 class="text-light mb-3">Daily Cost (30 Days)</h6>
                <div class="bg-dark border border-secondary rounded p-3 mb-4" style="height: 200px;">
                    <canvas id="keyCostChart"></canvas>
                </div>
                
                <div class="row g-4">
                    <div class="col-md-6">
                        <h6 class="text-light mb-3">Error Distribution</h6>
                        <div class="bg-dark border border-secondary rounded p-3 d-flex justify-content-center" style="height: 200px;">
                            <canvas id="keyErrorChart"></canvas>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-light mb-3">Token Usage Breakdown</h6>
                        <div class="bg-dark border border-secondary rounded p-3 d-flex justify-content-center" style="height: 200px;">
                            <canvas id="keyTokenChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="tab-pane fade" id="key-requests">
                <p class="text-muted small">Showing last 50 requests</p>
                <!-- Placeholder for datatable -->
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-table fa-3x mb-3 opacity-25"></i>
                    <p>Request logs would load here in a DataTable.</p>
                </div>
            </div>
            
            <div class="tab-pane fade" id="key-settings">
                <!-- Settings Form -->
                <form>
                    <h6 class="text-light border-bottom border-secondary pb-2 mb-3">Budget Limits</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Monthly Cap ($)</label>
                            <input type="number" class="form-control bg-dark border-secondary text-light" value="80.00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Daily Limit ($)</label>
                            <input type="number" class="form-control bg-dark border-secondary text-light" value="10.00">
                        </div>
                    </div>
                    
                    <h6 class="text-light border-bottom border-secondary pb-2 mb-3">Alerts & Actions</h6>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Alert Thresholds</label>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" checked id="alert50">
                                <label class="form-check-label small" for="alert50">50%</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" checked id="alert70">
                                <label class="form-check-label small" for="alert70">70%</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" checked id="alert90">
                                <label class="form-check-label small" for="alert90">90%</label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Action at 100%</label>
                        <select class="form-select bg-dark border-secondary text-light">
                            <option>Just Alert</option>
                            <option selected>Disable Key</option>
                            <option>Route to Fallback Key</option>
                        </select>
                    </div>
                    
                    <div class="d-flex justify-content-end mt-4">
                        <button type="button" class="btn btn-primary px-4">Save Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
