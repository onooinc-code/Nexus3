<!-- Cache Inspector -->
<div class="tab-pane fade" id="log-cache">
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card card-dashboard border-secondary h-100 text-center py-3">
                <div class="text-muted small mb-1">Hit Rate (7 Days)</div>
                <div class="display-6 fw-bold text-success mb-2">43.2%</div>
                <div class="text-muted small">Target: > 50%</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-dashboard border-secondary h-100 text-center py-3">
                <div class="text-muted small mb-1">Total Hits</div>
                <div class="display-6 fw-bold text-light mb-2">8,420</div>
                <div class="text-success small"><i class="fa-solid fa-arrow-trend-up"></i> +412 today</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-dashboard border-secondary h-100 text-center py-3">
                <div class="text-muted small mb-1">Cost Saved Estimate</div>
                <div class="display-6 fw-bold text-warning mb-2">$14.20</div>
                <div class="text-muted small">This Month</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-dashboard border-secondary h-100 text-center py-3">
                <div class="text-muted small mb-1">Active Cache Entries</div>
                <div class="display-6 fw-bold text-light mb-2">1,204</div>
                <div class="text-muted small">Avg TTL: 24h</div>
            </div>
        </div>
    </div>
    
    <div class="card border-secondary bg-dark">
        <div class="card-header border-secondary d-flex justify-content-between align-items-center">
            <h6 class="mb-0 text-light fw-bold">Recent Cache Entries</h6>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-danger">Flush All Cache</button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-dark-custom table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">MD5 Key</th>
                        <th>Intent</th>
                        <th style="width: 40%">Prompt Preview</th>
                        <th>Hits</th>
                        <th>Expires In</th>
                        <th class="text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="ps-4 font-monospace text-muted small">a8f9c2e...</td>
                        <td><span class="badge bg-dark border border-secondary text-muted">general_chat</span></td>
                        <td class="text-truncate" style="max-width: 250px;">"What are the store hours for the New York location?"</td>
                        <td><span class="badge bg-success">14</span></td>
                        <td class="text-warning small">4h 20m</td>
                        <td class="text-end pe-4">
                            <button class="btn btn-sm btn-link text-danger p-0"><i class="fa-solid fa-trash"></i></button>
                        </td>
                    </tr>
                    <tr>
                        <td class="ps-4 font-monospace text-muted small">b3d1f4a...</td>
                        <td><span class="badge bg-dark border border-secondary text-muted">data_extraction</span></td>
                        <td class="text-truncate" style="max-width: 250px;">"Extract invoice number from: INV-2026-004 total $400..."</td>
                        <td><span class="badge bg-success">2</span></td>
                        <td class="text-warning small">12h 45m</td>
                        <td class="text-end pe-4">
                            <button class="btn btn-sm btn-link text-danger p-0"><i class="fa-solid fa-trash"></i></button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
