<div class="row g-4 mb-4">
    <!-- Daily Cost Trend 30 Days -->
    <div class="col-lg-8">
        <div class="card card-dashboard border-secondary h-100">
            <div class="card-header bg-transparent border-secondary py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 text-light fw-bold">Daily Cost Trend (30 Days)</h6>
            </div>
            <div class="card-body">
                <canvas id="cost-trend-chart" height="250"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Cost Breakdown by Provider -->
    <div class="col-lg-4">
        <div class="card card-dashboard border-secondary h-100">
            <div class="card-header bg-transparent border-secondary py-3">
                <h6 class="mb-0 text-light fw-bold">Spend by Provider</h6>
            </div>
            <div class="card-body d-flex justify-content-center align-items-center position-relative">
                <canvas id="cost-provider-doughnut" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Cost by Intent -->
    <div class="col-lg-6">
        <div class="card card-dashboard border-secondary h-100">
            <div class="card-header bg-transparent border-secondary py-3">
                <h6 class="mb-0 text-light fw-bold">Cost by Intent</h6>
            </div>
            <div class="card-body">
                <canvas id="cost-intent-chart" height="250"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Unit Economics Table -->
    <div class="col-lg-6">
        <div class="card card-dashboard border-secondary h-100">
            <div class="card-header bg-transparent border-secondary py-3">
                <h6 class="mb-0 text-light fw-bold">Unit Economics (Cost per 1K Tokens)</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-dark-custom table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">Model</th>
                            <th class="text-end">Input Cost</th>
                            <th class="text-end pe-3">Output Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="ps-3 text-light">gpt-4o</td>
                            <td class="text-end font-monospace text-warning">$0.0050</td>
                            <td class="text-end font-monospace text-warning pe-3">$0.0150</td>
                        </tr>
                        <tr>
                            <td class="ps-3 text-light">gemini-1.5-pro</td>
                            <td class="text-end font-monospace text-warning">$0.0035</td>
                            <td class="text-end font-monospace text-warning pe-3">$0.0105</td>
                        </tr>
                        <tr>
                            <td class="ps-3 text-light">gpt-4o-mini</td>
                            <td class="text-end font-monospace text-success">$0.00015</td>
                            <td class="text-end font-monospace text-success pe-3">$0.0006</td>
                        </tr>
                        <tr>
                            <td class="ps-3 text-light">gemini-1.5-flash</td>
                            <td class="text-end font-monospace text-success">$0.000075</td>
                            <td class="text-end font-monospace text-success pe-3">$0.0003</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
