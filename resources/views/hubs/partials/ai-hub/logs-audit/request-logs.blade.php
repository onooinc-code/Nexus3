<!-- Request Logs -->
<div class="tab-pane fade show active" id="log-requests">
    <!-- Advanced Filter Bar -->
    <div class="card bg-dark border-secondary mb-4">
        <div class="card-body p-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-dark border-secondary text-muted"><i class="fa-solid fa-search"></i></span>
                        <input type="text" class="form-control bg-dark border-secondary text-light" placeholder="Search logs...">
                    </div>
                </div>
                <div class="col-md-2">
                    <select class="form-select form-select-sm bg-dark text-light border-secondary">
                        <option>All Providers</option>
                        <option>OpenAI</option>
                        <option>Google</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select form-select-sm bg-dark text-light border-secondary">
                        <option>All Intents</option>
                        <option>general_chat</option>
                        <option>data_extraction</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select form-select-sm bg-dark text-light border-secondary">
                        <option>Date: Today</option>
                        <option>Date: Last 7 Days</option>
                        <option>Custom Range...</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-sm btn-primary w-100">Apply Filter</button>
                    <button class="btn btn-sm btn-outline-secondary px-3"><i class="fa-solid fa-filter"></i></button>
                </div>
            </div>
            
            <!-- Expanded Filters (Hidden by default) -->
            <div class="row g-2 mt-2 pt-2 border-top border-secondary" style="display:none;">
                <div class="col-md-2">
                    <select class="form-select form-select-sm bg-dark text-light border-secondary">
                        <option>Status: All</option>
                        <option>Status: 200 OK</option>
                        <option>Status: Error (4xx/5xx)</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select form-select-sm bg-dark text-light border-secondary">
                        <option>Latency: All</option>
                        <option>< 500ms</option>
                        <option>500ms - 2s</option>
                        <option>> 2s</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select form-select-sm bg-dark text-light border-secondary">
                        <option>Routing: All</option>
                        <option>Primary Only</option>
                        <option>Fallback Triggered</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select form-select-sm bg-dark text-light border-secondary">
                        <option>Cache: All</option>
                        <option>Cache Hit</option>
                        <option>Cache Miss</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Log List -->
    <div class="d-flex flex-column gap-2">
        
        <!-- Log Row 1 -->
        <div class="card card-dashboard border-secondary p-3">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="d-flex align-items-center gap-3">
                    <span class="text-muted font-monospace small">2026-07-04 14:32:15</span>
                    <span class="badge bg-dark border border-secondary text-light">general_chat</span>
                    <span class="text-light fw-bold">gpt-4o-mini <span class="text-muted fw-normal">(OpenAI)</span></span>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-link text-info p-0" data-bs-toggle="offcanvas" data-bs-target="#logDetailDrawer"><i class="fa-solid fa-magnifying-glass me-1"></i> Details</button>
                </div>
            </div>
            
            <div class="d-flex flex-wrap gap-4 align-items-center mb-2 font-monospace" style="font-size: 0.8rem;">
                <div class="text-success"><i class="fa-solid fa-circle-check me-1"></i>200 OK</div>
                <div class="text-light"><i class="fa-solid fa-stopwatch text-muted me-1"></i>820ms</div>
                <div class="text-light"><i class="fa-solid fa-file-lines text-muted me-1"></i>123/456 tokens</div>
                <div class="text-warning"><i class="fa-solid fa-coins text-muted me-1"></i>$0.00042</div>
                <div class="text-muted"><i class="fa-solid fa-rotate text-muted me-1"></i>No fallback</div>
                <div class="text-muted"><i class="fa-solid fa-box text-muted me-1"></i>Cache: MISS</div>
            </div>
            
            <div class="bg-dark rounded p-2 border border-secondary text-muted" style="font-size: 0.75rem;">
                <span class="text-info fw-bold">Decision:</span> Primary route selected. Cost profile matched. Circuit: CLOSED.
            </div>
        </div>
        
        <!-- Log Row 2 (Fallback) -->
        <div class="card card-dashboard border-warning border-opacity-50 p-3" style="background: rgba(245, 158, 11, 0.02) !important;">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="d-flex align-items-center gap-3">
                    <span class="text-muted font-monospace small">2026-07-04 14:30:12</span>
                    <span class="badge bg-dark border border-secondary text-light">data_extraction</span>
                    <span class="text-light fw-bold">gemini-1.5-flash <span class="text-muted fw-normal">(Google)</span></span>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-link text-info p-0" data-bs-toggle="offcanvas" data-bs-target="#logDetailDrawer"><i class="fa-solid fa-magnifying-glass me-1"></i> Details</button>
                </div>
            </div>
            
            <div class="d-flex flex-wrap gap-4 align-items-center mb-2 font-monospace" style="font-size: 0.8rem;">
                <div class="text-success"><i class="fa-solid fa-circle-check me-1"></i>200 OK</div>
                <div class="text-warning"><i class="fa-solid fa-stopwatch text-muted me-1"></i>2,410ms</div>
                <div class="text-light"><i class="fa-solid fa-file-lines text-muted me-1"></i>8,400/210 tokens</div>
                <div class="text-warning"><i class="fa-solid fa-coins text-muted me-1"></i>$0.00210</div>
                <div class="text-warning fw-bold"><i class="fa-solid fa-rotate text-warning me-1"></i>Fallback Triggered</div>
                <div class="text-muted"><i class="fa-solid fa-box text-muted me-1"></i>Cache: MISS</div>
            </div>
            
            <div class="bg-dark rounded p-2 border border-secondary text-muted" style="font-size: 0.75rem;">
                <span class="text-warning fw-bold">Decision:</span> Primary (gpt-4o) returned 429 Rate Limit. Fallback 1 (gemini-1.5-flash) selected. Circuit: OPEN for OpenAI.
            </div>
        </div>
        
    </div>
    
    <!-- Pagination Placeholder -->
    <div class="d-flex justify-content-between align-items-center mt-4 text-muted small">
        <div>Showing 1-20 of 18,430 logs</div>
        <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-secondary disabled">Previous</button>
            <button class="btn btn-outline-secondary">Next</button>
        </div>
    </div>
</div>
