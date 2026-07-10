<div class="d-flex flex-column gap-3">
    @forelse($apiKeys as $key)
        <div class="card card-dashboard {{ $key->is_active ? 'border-secondary' : 'border-danger border-opacity-50' }}" 
             @if(!$key->is_active) style="background: rgba(220, 53, 69, 0.02) !important;" @endif>
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <i class="fa-regular fa-star text-muted cursor-pointer" onclick="$(this).toggleClass('fa-regular fa-solid text-warning text-muted')"></i>
                        <h5 class="mb-0 text-light fw-bold">{{ $key->name }}</h5>
                        <div class="bg-dark border border-secondary rounded px-2 py-1 font-monospace text-muted" style="font-size: 0.8rem;">
                            {{ substr($key->key_hash ?? 'sk-****************', 0, 16) }}...
                        </div>
                        <span class="badge bg-opacity-25 border border-opacity-50 
                            {{ str_contains(strtolower($key->provider?->name ?? ''), 'openai') ? 'bg-success text-success border-success' : 'bg-primary text-primary border-primary' }}">
                            {{ $key->provider?->name ?? 'N/A' }}
                        </span>
                        @if($key->is_active)
                            <div class="text-success small fw-bold"><i class="fa-solid fa-circle me-1" style="font-size: 0.5rem;"></i>Active</div>
                        @else
                            <div class="text-danger small fw-bold"><i class="fa-solid fa-circle me-1" style="font-size: 0.5rem;"></i>Revoked</div>
                        @endif
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-link text-muted p-0" data-bs-toggle="dropdown"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                        <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow">
                            <li><a class="dropdown-item" href="#"><i class="fa-solid fa-pen-to-square fa-fw me-2"></i>Edit Limits</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fa-solid fa-rotate fa-fw me-2"></i>Rotate Key</a></li>
                            @if($key->is_active)
                                <li><hr class="dropdown-divider border-secondary"></li>
                                <li>
                                    <a class="dropdown-item text-danger revoke-key-btn" href="#" data-id="{{ $key->id }}">
                                        <i class="fa-solid fa-ban fa-fw me-2"></i>Revoke
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
                
                <hr class="border-secondary my-3 opacity-25">
                
                <div class="row g-4">
                    <div class="col-md-5">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between text-muted mb-1" style="font-size: 0.75rem;">
                                <span>Budget Usage</span>
                                <span class="font-monospace">$0.00 / Managed</span>
                            </div>
                            <div class="progress" style="height: 6px; background-color: var(--bs-gray-800);">
                                <div class="progress-bar bg-success" style="width: 0%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="d-flex justify-content-between text-muted mb-1" style="font-size: 0.75rem;">
                                <span>Token Usage (Month)</span>
                                <span class="font-monospace">0 / Unlimited</span>
                            </div>
                            <div class="progress" style="height: 6px; background-color: var(--bs-gray-800);">
                                <div class="progress-bar bg-info" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-7">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="bg-dark rounded p-2 border border-secondary text-center h-100">
                                    <div class="text-muted mb-1" style="font-size: 0.7rem;">Today's Activity</div>
                                    <div class="font-monospace text-light" style="font-size: 0.85rem;">0 reqs</div>
                                    <div class="font-monospace text-info" style="font-size: 0.85rem;">0 tokens</div>
                                    <div class="font-monospace text-warning" style="font-size: 0.85rem;">$0.00</div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="bg-dark rounded p-2 border border-secondary text-center h-100 d-flex flex-column justify-content-center">
                                    <div class="text-muted mb-2" style="font-size: 0.7rem;">Success Rate</div>
                                    <div class="text-success fw-bold">100%</div>
                                    <div class="text-danger mt-1" style="font-size: 0.7rem;">Errors: 0</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top border-secondary opacity-75">
                    <div class="text-muted font-monospace" style="font-size: 0.75rem;">
                        Created: {{ $key->created_at?->format('Y-m-d') ?? 'N/A' }}
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-secondary py-1" style="font-size: 0.75rem;"><i class="fa-solid fa-pen-to-square me-1"></i> Edit Budget</button>
                        <button class="btn btn-sm btn-outline-info py-1" style="font-size: 0.75rem;" data-bs-toggle="offcanvas" data-bs-target="#keyAnalyticsDrawer"><i class="fa-solid fa-chart-pie me-1"></i> Analytics</button>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="card card-dashboard border-secondary p-5 text-center text-muted">
            <i class="fa-solid fa-key mb-3" style="font-size: 3rem;"></i>
            <h5 class="text-light">No API Keys Configured</h5>
            <p>Add a new API key to link with your providers.</p>
        </div>
    @endforelse
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('.revoke-key-btn').on('click', function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            const url = '{{ route("hub.models.api-keys.revoke", ["id" => "__ID__"]) }}'.replace('__ID__', id);
            
            if(!confirm('Are you sure you want to revoke this API key?')) {
                return;
            }
            
            window.Nexus.showTaskLoader();
            $.ajax({
                url: url,
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    window.Nexus.hideTaskLoader();
                    if(res.success) {
                        window.Nexus.notify(res.message, 'success');
                        setTimeout(() => location.reload(), 500);
                    }
                },
                error: function(xhr) {
                    window.Nexus.hideTaskLoader();
                    window.Nexus.notify('Failed to revoke API key', 'error');
                }
            });
        });
    });
</script>
@endpush
