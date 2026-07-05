<div class="card card-dashboard border-secondary p-0 mb-4">
    <div class="card-header bg-transparent border-secondary py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 text-light fw-bold">Active Routing Matrix</h6>
        <div class="text-muted small">Showing 9 known intents</div>
    </div>
    <div class="table-responsive">
        <table class="table table-dark-custom table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-4">Intent</th>
                    <th>Primary Route</th>
                    <th>Fallback(s)</th>
                    <th>Traffic (Today)</th>
                    <th>Rules</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Row 1 -->
                <tr>
                    <td class="ps-4 fw-bold text-light">general_chat</td>
                    <td>
                        <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-50">Gemini 1.5 Pro</span>
                        <div class="small text-muted mt-1" style="font-size: 0.7rem;">via Google</div>
                    </td>
                    <td>
                        <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50">gpt-4o</span>
                        <div class="small text-muted mt-1" style="font-size: 0.7rem;">via OpenAI</div>
                    </td>
                    <td>
                        <div class="font-monospace text-light mb-1">4,320 reqs</div>
                        <div class="d-flex w-100" style="height: 4px; max-width: 150px;">
                            <div class="bg-primary" style="width: 98.1%" title="Primary: 98.1%"></div>
                            <div class="bg-success" style="width: 1.9%" title="Fallback: 1.9%"></div>
                        </div>
                        <div class="text-muted mt-1" style="font-size: 0.7rem;">$0.0021/req | 720ms avg</div>
                    </td>
                    <td>
                        <span class="badge bg-dark border border-secondary text-muted rounded-pill px-2">Cost-Optimized</span>
                        <span class="badge bg-dark border border-secondary text-muted rounded-pill px-2">Standard-Latency</span>
                    </td>
                    <td class="text-end pe-4">
                        <button class="btn btn-sm btn-link text-info p-0 mx-1" data-bs-toggle="offcanvas" data-bs-target="#routeRuleDrawer"><i class="fa-solid fa-pen-to-square"></i></button>
                        <button class="btn btn-sm btn-link text-muted p-0 mx-1" onclick="document.getElementById('logs-tab').click()"><i class="fa-solid fa-list-check"></i></button>
                    </td>
                </tr>
                
                <!-- Row 2 -->
                <tr>
                    <td class="ps-4 fw-bold text-light">data_extraction</td>
                    <td>
                        <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50">gpt-4o-mini</span>
                        <div class="small text-muted mt-1" style="font-size: 0.7rem;">via OpenAI</div>
                    </td>
                    <td>
                        <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-50">gemini-1.5-flash</span>
                        <div class="small text-muted mt-1" style="font-size: 0.7rem;">via Google</div>
                    </td>
                    <td>
                        <div class="font-monospace text-light mb-1">8,105 reqs</div>
                        <div class="d-flex w-100" style="height: 4px; max-width: 150px;">
                            <div class="bg-success" style="width: 99.8%" title="Primary: 99.8%"></div>
                            <div class="bg-primary" style="width: 0.2%" title="Fallback: 0.2%"></div>
                        </div>
                        <div class="text-muted mt-1" style="font-size: 0.7rem;">$0.0003/req | 410ms avg</div>
                    </td>
                    <td>
                        <span class="badge bg-dark border border-secondary text-muted rounded-pill px-2">JSON-Forced</span>
                    </td>
                    <td class="text-end pe-4">
                        <button class="btn btn-sm btn-link text-info p-0 mx-1" data-bs-toggle="offcanvas" data-bs-target="#routeRuleDrawer"><i class="fa-solid fa-pen-to-square"></i></button>
                        <button class="btn btn-sm btn-link text-muted p-0 mx-1" onclick="document.getElementById('logs-tab').click()"><i class="fa-solid fa-list-check"></i></button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
