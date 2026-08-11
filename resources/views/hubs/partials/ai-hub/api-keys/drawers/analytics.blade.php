<!-- Per-Key Analytics Drawer -->
<div class="offcanvas offcanvas-end bg-dark border-start border-secondary text-light" tabindex="-1" id="keyAnalyticsDrawer" style="width: 720px;">
    <div class="offcanvas-header border-bottom border-secondary pb-3">
        <div>
            <h5 class="offcanvas-title fw-bold mb-1"><i class="fa-solid fa-chart-pie me-2 text-primary"></i>Key Telemetry & Analytics</h5>
            <div class="d-flex align-items-center gap-2" style="font-size: 0.8rem;">
                <span class="fw-bold text-light" id="key-drawer-name">Key Name</span>
                <span class="text-muted font-monospace border border-secondary rounded px-2 py-0" id="key-drawer-prefix">sk-...****</span>
                <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-25" id="key-drawer-provider">Provider</span>
            </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-4">
        <ul class="nav nav-tabs ai-hub-tabs mb-4" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#key-overview"><i class="fa-solid fa-gauge me-1"></i> Overview</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#key-requests"><i class="fa-solid fa-list me-1"></i> Recent Requests</button></li>
        </ul>
        
        <div class="tab-content">
            <div class="tab-pane fade show active" id="key-overview">
                <!-- Stat Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-sm-6 col-md-3">
                        <div class="bg-dark border border-secondary rounded p-2 text-center h-100">
                            <div class="text-muted small mb-1">Total Reqs</div>
                            <div class="fw-bold fs-5 text-light" id="key-drawer-total-reqs">0</div>
                            <div class="text-success" style="font-size: 0.7rem;"><i class="fa-solid fa-bolt me-1"></i>30 Days</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="bg-dark border border-secondary rounded p-2 text-center h-100">
                            <div class="text-muted small mb-1">Success Rate</div>
                            <div class="fw-bold fs-5 text-success" id="key-drawer-success-rate">100%</div>
                            <div class="text-success" style="font-size: 0.7rem;"><i class="fa-solid fa-shield-check me-1"></i>Verified</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="bg-dark border border-secondary rounded p-2 text-center h-100">
                            <div class="text-muted small mb-1">Total Spend</div>
                            <div class="fw-bold fs-5 text-warning" id="key-drawer-total-cost">$0.00</div>
                            <div class="text-muted" style="font-size: 0.7rem;">Cumulative</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="bg-dark border border-secondary rounded p-2 text-center h-100">
                            <div class="text-muted small mb-1">Avg Cost/Req</div>
                            <div class="fw-bold fs-5 text-info" id="key-drawer-avg-cost">$0.0000</div>
                            <div class="text-info" style="font-size: 0.7rem;">per request</div>
                        </div>
                    </div>
                </div>
                
                <h6 class="text-light mb-3"><i class="fa-solid fa-chart-area me-1 text-primary"></i> Daily Cost Trend (30 Days)</h6>
                <div class="bg-dark border border-secondary rounded p-3 mb-4" style="height: 200px;">
                    <canvas id="keyCostChart"></canvas>
                </div>
                
                <div class="row g-4">
                    <div class="col-md-6">
                        <h6 class="text-light mb-3"><i class="fa-solid fa-chart-pie me-1 text-warning"></i> Error Breakdown</h6>
                        <div class="bg-dark border border-secondary rounded p-3 d-flex justify-content-center" style="height: 200px;">
                            <canvas id="keyErrorChart"></canvas>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-light mb-3"><i class="fa-solid fa-chart-bar me-1 text-info"></i> Token Volume</h6>
                        <div class="bg-dark border border-secondary rounded p-3 d-flex justify-content-center" style="height: 200px;">
                            <canvas id="keyTokenChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="tab-pane fade" id="key-requests">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted small">Showing recent request logs for this key</span>
                    <button class="btn btn-sm btn-outline-secondary py-0" onclick="if(window.currentAnalyticsKeyId) loadKeyAnalyticsData(window.currentAnalyticsKeyId);"><i class="fa-solid fa-rotate me-1"></i> Refresh</button>
                </div>
                <div class="table-responsive bg-dark border border-secondary rounded">
                    <table class="table table-dark-custom table-hover align-middle mb-0 font-mono small">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Model</th>
                                <th>Tokens (In/Out)</th>
                                <th>Cost ($)</th>
                            </tr>
                        </thead>
                        <tbody id="key-recent-requests-body">
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No request logs found for this key.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
