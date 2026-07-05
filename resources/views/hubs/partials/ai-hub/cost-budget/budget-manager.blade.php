<div class="card card-dashboard border-secondary">
    <div class="card-header bg-transparent border-secondary py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 text-light fw-bold">Budget Manager</h6>
        <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#editBudgetModal"><i class="fa-solid fa-pen-to-square me-1"></i> Edit Global Budget</button>
    </div>
    <div class="table-responsive">
        <table class="table table-dark-custom table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-4">Scope</th>
                    <th>Spent / Monthly Cap</th>
                    <th style="width: 25%">Progress</th>
                    <th>Action on Exceed</th>
                    <th class="text-end pe-4">Edit</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="ps-4 fw-bold text-light"><i class="fa-solid fa-globe text-muted me-2"></i>Global Account</td>
                    <td class="font-monospace text-muted">$148.20 / <span class="text-light">$250.00</span></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height: 6px; background-color: var(--bs-gray-800);">
                                <div class="progress-bar bg-warning" style="width: 59%"></div>
                            </div>
                            <span class="small text-muted font-monospace">59%</span>
                        </div>
                    </td>
                    <td><span class="badge bg-danger">Block Requests</span></td>
                    <td class="text-end pe-4">
                        <button class="btn btn-sm btn-link text-info p-0" data-bs-toggle="modal" data-bs-target="#editBudgetModal"><i class="fa-solid fa-pen-to-square"></i></button>
                    </td>
                </tr>
                <tr>
                    <td class="ps-4 text-light"><i class="fa-solid fa-server text-muted me-2"></i>Provider: OpenAI</td>
                    <td class="font-monospace text-muted">$112.50 / <span class="text-light">$150.00</span></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height: 6px; background-color: var(--bs-gray-800);">
                                <div class="progress-bar bg-warning" style="width: 75%"></div>
                            </div>
                            <span class="small text-muted font-monospace">75%</span>
                        </div>
                    </td>
                    <td><span class="badge bg-secondary">Alert Only</span></td>
                    <td class="text-end pe-4">
                        <button class="btn btn-sm btn-link text-info p-0"><i class="fa-solid fa-pen-to-square"></i></button>
                    </td>
                </tr>
                <tr>
                    <td class="ps-4 text-light"><i class="fa-solid fa-route text-muted me-2"></i>Intent: reasoning</td>
                    <td class="font-monospace text-muted">$29.70 / <span class="text-light">$30.00</span></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height: 6px; background-color: var(--bs-gray-800);">
                                <div class="progress-bar bg-danger progress-bar-striped progress-bar-animated" style="width: 99%"></div>
                            </div>
                            <span class="small text-danger fw-bold font-monospace">99%</span>
                        </div>
                    </td>
                    <td><span class="badge bg-warning text-dark">Route to Cheapest</span></td>
                    <td class="text-end pe-4">
                        <button class="btn btn-sm btn-link text-info p-0"><i class="fa-solid fa-pen-to-square"></i></button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
