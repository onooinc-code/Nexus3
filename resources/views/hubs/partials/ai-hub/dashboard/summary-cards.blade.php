<div class="row g-4 mb-4">
    <!-- 6 Summary Cards -->
    <div class="col-md-4 col-lg-2">
        <div class="card card-dashboard h-100" onclick="document.getElementById('logs-tab').click()">
            <div class="card-body p-3">
                <div class="text-muted small mb-1">Total Requests (24h)</div>
                <div class="d-flex justify-content-between align-items-end">
                    <div class="metric-value text-light">18.4k</div>
                    <div class="text-success small"><i class="fa-solid fa-arrow-trend-up"></i> 12%</div>
                </div>
                <div class="mt-2"><canvas id="sparkline-requests" height="30"></canvas></div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 col-lg-2">
        <div class="card card-dashboard h-100">
            <div class="card-body p-3 text-center d-flex flex-column justify-content-center">
                <div class="text-muted small mb-2">Success Rate</div>
                <div class="position-relative d-inline-block mx-auto" style="width: 60px; height: 60px;">
                    <canvas id="chart-success-rate"></canvas>
                    <div class="position-absolute top-50 start-50 translate-middle text-light fw-bold" style="font-size: 0.85rem;">98.7%</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 col-lg-2">
        <div class="card card-dashboard h-100">
            <div class="card-body p-3 text-center d-flex flex-column justify-content-center">
                <div class="text-muted small mb-2">Avg Latency P50</div>
                <div class="metric-value text-info mb-1">820<span class="fs-6 text-muted">ms</span></div>
                <div class="progress-thin w-75 mx-auto">
                    <div class="progress-bar bg-info" style="width: 40%"></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 col-lg-2">
        <div class="card card-dashboard h-100" onclick="document.getElementById('budget-tab').click()">
            <div class="card-body p-3">
                <div class="text-muted small mb-1">Total Cost (Month)</div>
                <div class="metric-value text-light">$148.20</div>
                <div class="d-flex justify-content-between text-muted mt-2" style="font-size: 0.7rem;">
                    <span>$0</span><span>$250</span>
                </div>
                <div class="progress-thin mt-1">
                    <div class="progress-bar bg-warning" style="width: 59.3%"></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 col-lg-2">
        <div class="card card-dashboard h-100" onclick="document.getElementById('providers-tab').click()">
            <div class="card-body p-3 text-center d-flex flex-column justify-content-center">
                <div class="text-muted small mb-2">Active Providers</div>
                <div class="metric-value text-light">4<span class="fs-6 text-muted">/5</span></div>
                <div class="d-flex gap-1 justify-content-center mt-2">
                    <span class="status-dot bg-success"></span>
                    <span class="status-dot bg-success"></span>
                    <span class="status-dot bg-success"></span>
                    <span class="status-dot bg-success"></span>
                    <span class="status-dot bg-secondary opacity-25"></span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 col-lg-2">
        <div class="card card-dashboard h-100">
            <div class="card-body p-3 text-center d-flex flex-column justify-content-center">
                <div class="text-muted small mb-2">Cache Hit Rate</div>
                <div class="position-relative d-inline-block mx-auto" style="width: 60px; height: 60px;">
                    <canvas id="chart-cache-rate"></canvas>
                    <div class="position-absolute top-50 start-50 translate-middle text-light fw-bold" style="font-size: 0.85rem;">43%</div>
                </div>
            </div>
        </div>
    </div>
</div>
