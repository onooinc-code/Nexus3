<div class="card card-dashboard border-secondary p-4">
    <h6 class="text-light fw-bold mb-3"><i class="fa-solid fa-shuffle me-2"></i>A/B Traffic Split Experiment</h6>
    <div class="row align-items-center mb-3">
        <div class="col-md-4">
            <div class="text-muted small mb-1">Purpose</div>
            <div class="text-light">Evaluate new Gemini Pro model performance</div>
        </div>
        <div class="col-md-4">
            <div class="text-muted small mb-1">Goal Metric</div>
            <div class="d-flex gap-2">
                <span class="badge bg-primary">Lowest Cost</span>
                <span class="badge bg-dark border border-secondary text-muted">Lowest Latency</span>
                <span class="badge bg-dark border border-secondary text-muted">Highest Success</span>
            </div>
        </div>
        <div class="col-md-4 text-end">
            <button class="btn btn-sm btn-outline-warning">End Experiment</button>
        </div>
    </div>
    
    <div class="bg-dark rounded p-3 border border-secondary">
        <div class="row align-items-center mb-2">
            <div class="col-md-3 text-light fw-bold">Gemini 1.5 Pro</div>
            <div class="col-md-7">
                <div class="progress" style="height: 12px; background-color: var(--bs-gray-800);">
                    <div class="progress-bar bg-primary" style="width: 70%"></div>
                </div>
            </div>
            <div class="col-md-2 text-end">
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-secondary py-0 px-2">-</button>
                    <span class="btn btn-dark disabled text-light py-0 px-2">70%</span>
                    <button class="btn btn-outline-secondary py-0 px-2">+</button>
                </div>
            </div>
        </div>
        
        <div class="row align-items-center">
            <div class="col-md-3 text-light fw-bold">GPT-4o</div>
            <div class="col-md-7">
                <div class="progress" style="height: 12px; background-color: var(--bs-gray-800);">
                    <div class="progress-bar bg-success" style="width: 30%"></div>
                </div>
            </div>
            <div class="col-md-2 text-end">
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-secondary py-0 px-2">-</button>
                    <span class="btn btn-dark disabled text-light py-0 px-2">30%</span>
                    <button class="btn btn-outline-secondary py-0 px-2">+</button>
                </div>
            </div>
        </div>
    </div>
</div>
