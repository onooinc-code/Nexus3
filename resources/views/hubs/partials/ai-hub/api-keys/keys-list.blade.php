<div class="d-flex flex-column gap-3">
    <!-- Key Card 1 -->
    <div class="card card-dashboard border-secondary">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-3">
                    <i class="fa-solid fa-star text-warning cursor-pointer"></i>
                    <h5 class="mb-0 text-light fw-bold">Production Key #1</h5>
                    <div class="bg-dark border border-secondary rounded px-2 py-1 font-monospace text-muted" style="font-size: 0.8rem;">
                        sk-...****8f2a
                    </div>
                    <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50">OpenAI</span>
                    <div class="text-success small fw-bold"><i class="fa-solid fa-circle me-1" style="font-size: 0.5rem;"></i>Active</div>
                </div>
                <div class="dropdown">
                    <button class="btn btn-sm btn-link text-muted p-0" data-bs-toggle="dropdown"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                    <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow">
                        <li><a class="dropdown-item" href="#"><i class="fa-solid fa-pen-to-square fa-fw me-2"></i>Edit Limits</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fa-solid fa-rotate fa-fw me-2"></i>Rotate Key</a></li>
                        <li><hr class="dropdown-divider border-secondary"></li>
                        <li><a class="dropdown-item text-danger" href="#"><i class="fa-solid fa-ban fa-fw me-2"></i>Revoke</a></li>
                    </ul>
                </div>
            </div>
            
            <hr class="border-secondary my-3 opacity-25">
            
            <div class="row g-4">
                <div class="col-md-5">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between text-muted mb-1" style="font-size: 0.75rem;">
                            <span>Budget Usage</span>
                            <span class="font-monospace">$48.20 / $80.00 (60.3%)</span>
                        </div>
                        <div class="progress" style="height: 6px; background-color: var(--bs-gray-800);">
                            <div class="progress-bar bg-warning" style="width: 60.3%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between text-muted mb-1" style="font-size: 0.75rem;">
                            <span>Token Usage (Month)</span>
                            <span class="font-monospace">3.2M / 5.0M (64.0%)</span>
                        </div>
                        <div class="progress" style="height: 6px; background-color: var(--bs-gray-800);">
                            <div class="progress-bar bg-warning" style="width: 64%"></div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-7">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="bg-dark rounded p-2 border border-secondary text-center h-100">
                                <div class="text-muted mb-1" style="font-size: 0.7rem;">Today's Activity</div>
                                <div class="font-monospace text-light" style="font-size: 0.85rem;">1,240 reqs</div>
                                <div class="font-monospace text-info" style="font-size: 0.85rem;">890K tokens</div>
                                <div class="font-monospace text-warning" style="font-size: 0.85rem;">$8.20</div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="bg-dark rounded p-2 border border-secondary text-center h-100 d-flex flex-column justify-content-center">
                                <div class="text-muted mb-2" style="font-size: 0.7rem;">Success Rate</div>
                                <div class="text-success fw-bold">98.4%</div>
                                <div class="text-danger mt-1" style="font-size: 0.7rem;">Errors: 23 (429: 18)</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top border-secondary opacity-75">
                <div class="text-muted font-monospace" style="font-size: 0.75rem;">
                    Created: 2026-05-01 &nbsp;|&nbsp; Last Used: 3 mins ago
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-secondary py-1" style="font-size: 0.75rem;"><i class="fa-solid fa-pen-to-square me-1"></i> Edit Budget</button>
                    <button class="btn btn-sm btn-outline-info py-1" style="font-size: 0.75rem;" data-bs-toggle="offcanvas" data-bs-target="#keyAnalyticsDrawer"><i class="fa-solid fa-chart-pie me-1"></i> Analytics</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Key Card 2 -->
    <div class="card card-dashboard border-danger border-opacity-50" style="background: rgba(220, 53, 69, 0.02) !important;">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-3">
                    <i class="fa-regular fa-star text-muted cursor-pointer"></i>
                    <h5 class="mb-0 text-light fw-bold">Research Dept Fallback</h5>
                    <div class="bg-dark border border-secondary rounded px-2 py-1 font-monospace text-muted" style="font-size: 0.8rem;">
                        sk-...****9b1c
                    </div>
                    <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-50">Google</span>
                    <div class="text-danger small fw-bold"><i class="fa-solid fa-circle me-1" style="font-size: 0.5rem;"></i>Exhausted</div>
                </div>
                <div class="dropdown">
                    <button class="btn btn-sm btn-link text-muted p-0" data-bs-toggle="dropdown"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                    <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow">
                        <li><a class="dropdown-item" href="#"><i class="fa-solid fa-pen-to-square fa-fw me-2"></i>Edit Limits</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fa-solid fa-rotate fa-fw me-2"></i>Rotate Key</a></li>
                        <li><hr class="dropdown-divider border-secondary"></li>
                        <li><a class="dropdown-item text-danger" href="#"><i class="fa-solid fa-ban fa-fw me-2"></i>Revoke</a></li>
                    </ul>
                </div>
            </div>
            
            <hr class="border-secondary my-3 opacity-25">
            
            <div class="row g-4">
                <div class="col-md-5">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between text-muted mb-1" style="font-size: 0.75rem;">
                            <span class="text-danger fw-bold">Budget Usage (LIMIT REACHED)</span>
                            <span class="font-monospace text-danger">$50.00 / $50.00 (100%)</span>
                        </div>
                        <div class="progress" style="height: 6px; background-color: var(--bs-gray-800);">
                            <div class="progress-bar bg-danger" style="width: 100%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between text-muted mb-1" style="font-size: 0.75rem;">
                            <span>Token Usage (Month)</span>
                            <span class="font-monospace">1.2M / Unlimited</span>
                        </div>
                        <div class="progress" style="height: 6px; background-color: var(--bs-gray-800);">
                            <div class="progress-bar bg-info" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-7">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="bg-dark rounded p-2 border border-secondary text-center h-100 opacity-50">
                                <div class="text-muted mb-1" style="font-size: 0.7rem;">Today's Activity</div>
                                <div class="font-monospace text-light" style="font-size: 0.85rem;">0 reqs</div>
                                <div class="font-monospace text-info" style="font-size: 0.85rem;">0 tokens</div>
                                <div class="font-monospace text-warning" style="font-size: 0.85rem;">$0.00</div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="bg-dark rounded p-2 border border-secondary text-center h-100 d-flex flex-column justify-content-center">
                                <div class="text-muted mb-2" style="font-size: 0.7rem;">Success Rate</div>
                                <div class="text-warning fw-bold">82.1%</div>
                                <div class="text-danger mt-1" style="font-size: 0.7rem;">Errors: 145 (429: 145)</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top border-secondary opacity-75">
                <div class="text-muted font-monospace" style="font-size: 0.75rem;">
                    Created: 2026-06-10 &nbsp;|&nbsp; Last Used: 2 days ago
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-secondary py-1" style="font-size: 0.75rem;"><i class="fa-solid fa-pen-to-square me-1"></i> Increase Budget</button>
                    <button class="btn btn-sm btn-outline-info py-1" style="font-size: 0.75rem;"><i class="fa-solid fa-chart-pie me-1"></i> Analytics</button>
                </div>
            </div>
        </div>
    </div>
</div>
