<div class="d-flex flex-column gap-3" id="api-keys-container">
    @forelse($apiKeys as $key)
        @php
            $kReqs = (int) ($key->stats->month_requests ?? 0);
            $kTokens = (int) ($key->stats->month_tokens ?? 0);
            $kCost = (float) ($key->stats->month_cost ?? 0.0);
            $isCooldown = !empty($key->cooldown_until) && \Carbon\Carbon::parse($key->cooldown_until)->isFuture();
            $errorCount = (int) ($key->error_count ?? 0);
            
            $healthClass = 'text-success';
            $healthText = 'Healthy';
            $healthIcon = 'fa-shield-check';
            if (!$key->is_active) {
                $healthClass = 'text-muted';
                $healthText = 'Revoked';
                $healthIcon = 'fa-ban';
            } elseif ($isCooldown) {
                $healthClass = 'text-warning';
                $healthText = 'Cooldown Mode';
                $healthIcon = 'fa-hourglass-half';
            } elseif ($errorCount > 5) {
                $healthClass = 'text-danger';
                $healthText = 'High Errors';
                $healthIcon = 'fa-triangle-exclamation';
            }
        @endphp
        <div class="card card-dashboard api-key-card-item {{ $key->is_active ? 'border-secondary' : 'border-danger border-opacity-50' }}" 
             data-id="{{ $key->id }}"
             data-active="{{ $key->is_active ? 1 : 0 }}"
             data-cooldown="{{ $isCooldown ? 1 : 0 }}"
             data-default="{{ $key->is_default ? 1 : 0 }}"
             data-errors="{{ $errorCount }}"
             data-provider="{{ $key->provider?->name ?? 'Universal' }}"
             data-cost="{{ $kCost }}"
             data-reqs="{{ $kReqs }}"
             data-priority="{{ $key->priority ?? 1 }}"
             data-created="{{ $key->created_at?->timestamp ?? 0 }}"
             @if(!$key->is_active) style="background: rgba(220, 53, 69, 0.02) !important;" @endif>
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <i class="fa-solid fa-key text-warning fs-5"></i>
                        <h5 class="mb-0 text-light fw-bold">{{ $key->name }}</h5>
                        <div class="bg-dark border border-secondary rounded px-2 py-1 font-monospace text-muted" style="font-size: 0.8rem;">
                            {{ $key->key_prefix ?? 'sk-••••' }}...
                        </div>
                        <span class="badge bg-opacity-25 border border-opacity-50 bg-primary text-primary border-primary">
                            {{ $key->provider?->name ?? 'Universal' }}
                        </span>
                        @if($key->is_active)
                            @if($isCooldown)
                                <span class="badge bg-warning bg-opacity-25 text-warning border border-warning"><i class="fa-solid fa-hourglass-half me-1"></i>Cooldown</span>
                            @else
                                <span class="badge bg-success bg-opacity-25 text-success border border-success"><i class="fa-solid fa-circle me-1" style="font-size: 0.5rem;"></i>Active</span>
                            @endif
                        @else
                            <span class="badge bg-danger bg-opacity-25 text-danger border border-danger"><i class="fa-solid fa-ban me-1"></i>Revoked</span>
                        @endif
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-primary py-1" onclick="openKeyAnalytics('{{ $key->id }}', '{{ addslashes($key->name) }}', '{{ $key->key_prefix ?? 'sk-••••' }}')" title="View Key Analytics & Telemetry">
                            <i class="fa-solid fa-chart-line me-1"></i> Analytics
                        </button>
                        <button class="btn btn-sm btn-outline-info py-1" onclick="pingSingleKey('{{ $key->id }}')" title="Ping API Provider Key">
                            <i class="fa-solid fa-plug me-1"></i> Ping
                        </button>
                        <button class="btn btn-sm btn-outline-danger py-1 revoke-key-btn" data-id="{{ $key->id }}" title="Delete API Key">
                            <i class="fa-solid fa-trash me-1"></i> Delete
                        </button>
                    </div>
                </div>
                
                <hr class="border-secondary my-3 opacity-25">
                
                <div class="row g-4">
                    <div class="col-md-5">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between text-muted mb-1" style="font-size: 0.75rem;">
                                <span>Month Spend</span>
                                <span class="font-monospace text-success fw-bold">${{ number_format($kCost, 4) }}</span>
                            </div>
                            <div class="progress" style="height: 6px; background-color: var(--bs-gray-800);">
                                <div class="progress-bar bg-success" style="width: {{ min(100, max(5, $kCost * 10)) }}%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="d-flex justify-content-between text-muted mb-1" style="font-size: 0.75rem;">
                                <span>Token Usage (Month)</span>
                                <span class="font-monospace text-info">{{ number_format($kTokens) }} tokens</span>
                            </div>
                            <div class="progress" style="height: 6px; background-color: var(--bs-gray-800);">
                                <div class="progress-bar bg-info" style="width: {{ min(100, max(5, $kTokens / 1000)) }}%"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-7">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="bg-dark rounded p-2 border border-secondary text-center h-100">
                                    <div class="text-muted mb-1" style="font-size: 0.7rem;">Activity (Month)</div>
                                    <div class="font-monospace text-light" style="font-size: 0.85rem;">{{ number_format($kReqs) }} reqs</div>
                                    <div class="font-monospace text-info" style="font-size: 0.85rem;">{{ number_format($kTokens) }} tokens</div>
                                    <div class="font-monospace text-warning" style="font-size: 0.85rem;">${{ number_format($kCost, 4) }}</div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="bg-dark rounded p-2 border border-secondary text-center h-100 d-flex flex-column justify-content-center">
                                    <div class="text-muted mb-2" style="font-size: 0.7rem;">Key Health & Status</div>
                                    <div class="{{ $healthClass }} fw-bold"><i class="fa-solid {{ $healthIcon }} me-1"></i>{{ $healthText }}</div>
                                    <div class="text-muted mt-1" style="font-size: 0.7rem;">Errors: {{ $errorCount }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top border-secondary opacity-75">
                    <div class="text-muted font-monospace" style="font-size: 0.75rem;">
                        Created: {{ $key->created_at?->format('Y-m-d') ?? 'N/A' }} | Priority: {{ $key->priority ?? 1 }}
                    </div>
                    <div>
                        @if(!$key->is_default)
                            <button class="btn btn-sm btn-link text-info p-0 text-decoration-none" onclick="setDefaultApiKey('{{ $key->id }}')">Set Primary Default</button>
                        @else
                            <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-25"><i class="fa-solid fa-star me-1 text-warning"></i>Primary Default</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="card card-dashboard border-secondary p-5 text-center text-muted">
            <i class="fa-solid fa-key mb-3 text-warning" style="font-size: 3rem;"></i>
            <h5 class="text-light fw-bold">No API Keys Configured</h5>
            <p class="mb-3">Add a new API key to link with your AI providers.</p>
            <div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addKeyModal"><i class="fa-solid fa-plus me-1"></i> Add First API Key</button>
            </div>
        </div>
    @endforelse
</div>

@push('scripts')
<script>
    window.pingSingleKey = function(keyId) {
        if (window.Nexus && window.Nexus.showTaskLoader) {
            window.Nexus.showTaskLoader('Testing API key...', 'Pinging provider...');
        }
        $.ajax({
            url: `/hub/models/api-keys/${keyId}/ping`,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function(res) {
                if (window.Nexus && window.Nexus.hideTaskLoader) window.Nexus.hideTaskLoader();
                if (window.Nexus && window.Nexus.notify) {
                    window.Nexus.notify(res.message || 'Key ping successful!', 'success');
                }
            },
            error: function(xhr) {
                if (window.Nexus && window.Nexus.hideTaskLoader) window.Nexus.hideTaskLoader();
                if (window.Nexus && window.Nexus.notify) {
                    window.Nexus.notify(xhr.responseJSON ? xhr.responseJSON.message : 'Key ping failed', 'error');
                }
            }
        });
    };

    window.setDefaultApiKey = function(keyId) {
        if (window.Nexus && window.Nexus.showTaskLoader) {
            window.Nexus.showTaskLoader('Updating default key...', 'Setting primary key...');
        }
        $.ajax({
            url: `/hub/models/api-keys/${keyId}/set-default`,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function(res) {
                if (window.Nexus && window.Nexus.hideTaskLoader) window.Nexus.hideTaskLoader();
                if (window.Nexus && window.Nexus.notify) {
                    window.Nexus.notify('Primary default key updated!', 'success');
                }
                setTimeout(() => location.reload(), 800);
            },
            error: function(xhr) {
                if (window.Nexus && window.Nexus.hideTaskLoader) window.Nexus.hideTaskLoader();
                if (window.Nexus && window.Nexus.notify) {
                    window.Nexus.notify('Failed to update primary default key', 'error');
                }
            }
        });
    };

    $(document).ready(function() {
        $(document).on('click', '.revoke-key-btn', function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            if(!confirm('Are you sure you want to delete this API key?')) return;
            
            if (window.Nexus && window.Nexus.showTaskLoader) {
                window.Nexus.showTaskLoader('Deleting Key...', 'Removing API key record...');
            }
            $.ajax({
                url: `/hub/models/api-keys/${id}`,
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                success: function(res) {
                    if (window.Nexus && window.Nexus.hideTaskLoader) window.Nexus.hideTaskLoader();
                    if (window.Nexus && window.Nexus.notify) {
                        window.Nexus.notify('API key deleted successfully', 'success');
                    }
                    $(`.api-key-card-item[data-id="${id}"]`).fadeOut(400, function() { $(this).remove(); });
                },
                error: function(xhr) {
                    if (window.Nexus && window.Nexus.hideTaskLoader) window.Nexus.hideTaskLoader();
                    if (window.Nexus && window.Nexus.notify) {
                        window.Nexus.notify('Failed to delete API key', 'error');
                    }
                }
            });
        });
    });
</script>
@endpush
