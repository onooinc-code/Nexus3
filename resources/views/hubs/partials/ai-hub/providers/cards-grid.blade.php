<div class="row g-4">
    <!-- Provider Card 1 -->
    <div class="col-md-6 col-xl-4">
        <div class="card card-dashboard h-100 border-success border-opacity-25">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded bg-white p-2" style="width: 40px; height: 40px;">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/0/04/ChatGPT_logo.svg" alt="OpenAI" class="img-fluid">
                        </div>
                        <div>
                            <h6 class="mb-0 text-light fw-bold">OpenAI</h6>
                            <div class="text-success small" style="font-size: 0.75rem;"><i class="fa-solid fa-check-circle me-1"></i>Active</div>
                        </div>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-link text-muted p-0" data-bs-toggle="dropdown"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                        <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow">
                            <li><a class="dropdown-item" href="#"><i class="fa-solid fa-pen fa-fw me-2"></i>Edit</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fa-solid fa-rotate fa-fw me-2"></i>Sync Models</a></li>
                            <li><hr class="dropdown-divider border-secondary"></li>
                            <li><a class="dropdown-item text-danger" href="#"><i class="fa-solid fa-ban fa-fw me-2"></i>Disable</a></li>
                        </ul>
                    </div>
                </div>
                
                <div class="text-muted small mb-3" style="font-size: 0.8rem; font-family: 'JetBrains Mono';">
                    <i class="fa-solid fa-link me-1"></i> https://api.openai.com/v1
                </div>
                
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="bg-dark rounded p-2 border border-secondary text-center">
                            <div class="text-muted" style="font-size: 0.7rem;">Models</div>
                            <div class="text-light fw-bold">14</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-dark rounded p-2 border border-secondary text-center">
                            <div class="text-muted" style="font-size: 0.7rem;">Active Keys</div>
                            <div class="text-light fw-bold">3</div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between text-muted mb-1" style="font-size: 0.75rem;">
                        <span>Budget ($80.00 / $100.00)</span>
                        <span>80%</span>
                    </div>
                    <div class="progress" style="height: 6px; background-color: var(--bs-gray-800);">
                        <div class="progress-bar bg-warning" style="width: 80%"></div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between border-top border-secondary pt-3 mt-auto">
                    <button class="btn btn-sm btn-outline-secondary" onclick="pingProvider(1)"><i class="fa-solid fa-network-wired me-1"></i>Ping</button>
                    <button class="btn btn-sm btn-outline-info"><i class="fa-solid fa-chart-simple me-1"></i>Details</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Provider Card 2 -->
    <div class="col-md-6 col-xl-4">
        <div class="card card-dashboard h-100 border-success border-opacity-25">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded bg-white p-2" style="width: 40px; height: 40px;">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/8/8a/Google_Gemini_logo.svg" alt="Google" class="img-fluid">
                        </div>
                        <div>
                            <h6 class="mb-0 text-light fw-bold">Google Gemini</h6>
                            <div class="text-success small" style="font-size: 0.75rem;"><i class="fa-solid fa-check-circle me-1"></i>Active</div>
                        </div>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-link text-muted p-0" data-bs-toggle="dropdown"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                        <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow">
                            <li><a class="dropdown-item" href="#"><i class="fa-solid fa-pen fa-fw me-2"></i>Edit</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fa-solid fa-rotate fa-fw me-2"></i>Sync Models</a></li>
                            <li><hr class="dropdown-divider border-secondary"></li>
                            <li><a class="dropdown-item text-danger" href="#"><i class="fa-solid fa-ban fa-fw me-2"></i>Disable</a></li>
                        </ul>
                    </div>
                </div>
                
                <div class="text-muted small mb-3" style="font-size: 0.8rem; font-family: 'JetBrains Mono';">
                    <i class="fa-solid fa-link me-1"></i> https://generativelanguage.googleapis.com
                </div>
                
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="bg-dark rounded p-2 border border-secondary text-center">
                            <div class="text-muted" style="font-size: 0.7rem;">Models</div>
                            <div class="text-light fw-bold">8</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-dark rounded p-2 border border-secondary text-center">
                            <div class="text-muted" style="font-size: 0.7rem;">Active Keys</div>
                            <div class="text-light fw-bold">1</div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between text-muted mb-1" style="font-size: 0.75rem;">
                        <span>Budget ($12.40 / $50.00)</span>
                        <span>24%</span>
                    </div>
                    <div class="progress" style="height: 6px; background-color: var(--bs-gray-800);">
                        <div class="progress-bar bg-success" style="width: 24%"></div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between border-top border-secondary pt-3 mt-auto">
                    <button class="btn btn-sm btn-outline-secondary" onclick="pingProvider(2)"><i class="fa-solid fa-network-wired me-1"></i>Ping</button>
                    <button class="btn btn-sm btn-outline-info"><i class="fa-solid fa-chart-simple me-1"></i>Details</button>
                </div>
            </div>
        </div>
    </div>
</div>
