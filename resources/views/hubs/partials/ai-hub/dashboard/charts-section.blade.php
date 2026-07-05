<div class="row g-4 mb-4">
    <!-- Main Charts -->
    <div class="col-lg-8">
        <div class="card card-dashboard h-100">
            <div class="card-header bg-transparent border-secondary d-flex justify-content-between align-items-center py-3">
                <h6 class="mb-0 text-light fw-bold">Token Consumption (7 Days)</h6>
                <select class="form-select form-select-sm bg-dark text-light border-secondary w-auto">
                    <option>7 Days</option>
                    <option>30 Days</option>
                    <option>This Month</option>
                </select>
            </div>
            <div class="card-body">
                <canvas id="chart-tokens-timeline" height="250"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card card-dashboard h-100">
            <div class="card-header bg-transparent border-secondary py-3">
                <h6 class="mb-0 text-light fw-bold">Cost by Provider</h6>
            </div>
            <div class="card-body">
                <canvas id="chart-cost-provider" height="250"></canvas>
            </div>
        </div>
    </div>
</div>
