{{-- Providers Cards Grid --}}
<div id="providers-cards-view">
    <div class="row g-4" id="sortable-providers-list">
        @forelse($providers as $provider)
            @php
                $logoUrl = 'https://ui-avatars.com/api/?name='.urlencode($provider->name).'&background=1a1f2e&color=58a6ff&size=40&font-size=0.4&bold=true';
                $nameLower = strtolower($provider->name);
                if (str_contains($nameLower, 'openai') || str_contains($nameLower, 'gpt')) {
                    $logoUrl = 'https://upload.wikimedia.org/wikipedia/commons/0/04/ChatGPT_logo.svg';
                } elseif (str_contains($nameLower, 'google') || str_contains($nameLower, 'gemini') || str_contains($nameLower, 'aistudio')) {
                    $logoUrl = 'https://www.gstatic.com/lamda/images/gemini_sparkle_v002_d4735304ff6292a690345.svg';
                } elseif (str_contains($nameLower, 'anthropic') || str_contains($nameLower, 'claude')) {
                    $logoUrl = 'https://upload.wikimedia.org/wikipedia/commons/thumb/7/7f/Anthropic_logo.svg/200px-Anthropic_logo.svg.png';
                } elseif (str_contains($nameLower, 'mistral')) {
                    $logoUrl = 'https://mistral.ai/images/news/mistral-logo-black.png';
                }

                // Enriched properties from HubController
                $healthStatus = $provider->health_status ?? 'no_ping'; // 'healthy', 'degraded', 'offline', 'no_ping', 'disabled'
                $healthColor = match($healthStatus) {
                    'healthy'  => 'success',
                    'degraded' => 'warning',
                    'offline'  => 'danger',
                    'disabled' => 'secondary',
                    default    => 'secondary'
                };
                $healthLabel = match($healthStatus) {
                    'healthy'  => 'Healthy',
                    'degraded' => 'Degraded',
                    'offline'  => 'Unreachable',
                    'disabled' => 'Disabled',
                    default    => 'No Ping Yet'
                };

                $monthCost = $provider->month_stats->month_cost ?? 0;
                $budgetCap = $provider->budget->monthly_budget ?? $provider->monthly_budget_cap ?? 0;
                $budgetPct = $budgetCap > 0 ? min(100, ($monthCost / $budgetCap) * 100) : 0;
                $budgetColor = $budgetPct > 90 ? 'danger' : ($budgetPct > 75 ? 'warning' : 'info');
                
                $isFav = $provider->is_favorite ? 1 : 0;
            @endphp
            <div class="col-md-6 col-xl-4 provider-card-wrapper"
                 data-id="{{ $provider->id }}"
                 data-status="{{ $provider->is_active ? 'active' : 'disabled' }}"
                 data-health="{{ $healthStatus }}"
                 data-favorite="{{ $isFav }}"
                 data-name="{{ $provider->name }}"
                 data-cost="{{ $monthCost }}"
                 data-models="{{ $provider->models_count ?? 0 }}"
                 data-synced="{{ $provider->last_synced_at ? strtotime($provider->last_synced_at) : 0 }}"
                 data-sort_order="{{ $provider->sort_order ?? 999 }}">
                
                <div class="card h-100 border-0 shadow-lg position-relative" style="background: rgba(26, 31, 46, 0.6); backdrop-filter: blur(10px); border-radius: 16px; transition: transform 0.3s ease, box-shadow 0.3s ease; border: 1px solid {{ $provider->is_active ? 'rgba(var(--bs-'.$healthColor.'-rgb), 0.3)' : 'rgba(255,255,255,0.05)' }} !important;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 1rem 3rem rgba(0,0,0,0.175)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 .5rem 1rem rgba(0,0,0,0.15)';">
                    <div class="card-body p-4 d-flex flex-column">
                        {{-- Header with Drag Handle & Bulk Checkbox --}}
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="text-muted drag-handle fs-5 opacity-50 hover-opacity-100" style="cursor: grab;" title="Drag to reorder">
                                    <i class="fa-solid fa-grip-vertical"></i>
                                </div>
                                <div class="bg-dark rounded-circle d-flex align-items-center justify-content-center p-2 shadow-sm border border-secondary" style="width: 52px; height: 52px;">
                                    <img src="{{ $logoUrl }}" alt="{{ $provider->name }}" style="max-height: 28px; max-width: 28px; object-fit: contain;">
                                </div>
                                <div>
                                    <h5 class="mb-1 text-white fw-bold d-flex align-items-center gap-2">
                                        {{ $provider->name }}
                                        <button class="btn btn-sm p-0 text-{{ $isFav ? 'warning' : 'secondary' }} toggle-fav-btn" data-id="{{ $provider->id }}" title="Toggle Favorite" style="transition: color 0.2s;">
                                            <i class="fa-{{ $isFav ? 'solid' : 'regular' }} fa-star"></i>
                                        </button>
                                    </h5>
                                    <div class="d-flex align-items-center gap-2" style="font-size: 0.75rem;">
                                        <span class="badge bg-{{ $healthColor }} bg-opacity-25 text-{{ $healthColor }} border border-{{ $healthColor }} border-opacity-25 rounded-pill px-2 py-1">
                                            <i class="fa-solid fa-circle text-{{ $healthColor }} me-1" style="font-size: 0.4rem;"></i>{{ $healthLabel }}
                                        </span>
                                        @if($provider->last_ping)
                                            <span class="text-muted"><i class="fa-solid fa-bolt text-warning opacity-75 me-1"></i>{{ $provider->last_ping->latency_ms }}ms</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Kebab Menu --}}
                            <div class="dropdown">
                                <button class="btn btn-sm btn-link text-muted p-0 fs-5" data-bs-toggle="dropdown" aria-label="Provider options">
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow-lg" style="min-width: 180px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1);">
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center py-2" href="#" onclick="openProviderDetails('{{ $provider->id }}'); return false;">
                                            <i class="fa-solid fa-sliders fa-fw me-3 text-primary"></i> Configure
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center py-2" href="#" onclick="syncModels('{{ $provider->id }}'); return false;">
                                            <i class="fa-solid fa-rotate fa-fw me-3 text-info"></i> Sync Models
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center py-2" href="#" onclick="pingProvider('{{ $provider->id }}'); return false;">
                                            <i class="fa-solid fa-network-wired fa-fw me-3 text-success"></i> Ping Provider
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider border-secondary opacity-25"></li>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center py-2 toggle-provider-btn"
                                           href="#"
                                           data-id="{{ $provider->id }}"
                                           data-active="{{ $provider->is_active ? '0' : '1' }}">
                                            @if($provider->is_active)
                                                <i class="fa-solid fa-pause fa-fw me-3 text-warning"></i> Disable
                                            @else
                                                <i class="fa-solid fa-play fa-fw me-3 text-success"></i> Enable
                                            @endif
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider border-secondary opacity-25"></li>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center py-2 text-danger" href="#" onclick="deleteProvider('{{ $provider->id }}', '{{ addslashes($provider->name) }}'); return false;">
                                            <i class="fa-solid fa-trash fa-fw me-3"></i> Delete
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        {{-- Stats Row --}}
                        <div class="d-flex justify-content-between align-items-center mb-4 bg-dark bg-opacity-25 rounded-4 p-3 border border-light border-opacity-10 shadow-inner">
                            <div class="text-center w-100 border-end border-light border-opacity-10">
                                <div class="text-muted text-uppercase mb-1 fw-semibold" style="font-size: 0.65rem; letter-spacing: 0.5px;">Models</div>
                                <div class="text-white fw-bold fs-5">{{ $provider->models_count ?? 0 }}</div>
                            </div>
                            <div class="text-center w-100 border-end border-light border-opacity-10">
                                <div class="text-muted text-uppercase mb-1 fw-semibold" style="font-size: 0.65rem; letter-spacing: 0.5px;">Reqs (24h)</div>
                                <div class="text-white fw-bold fs-5">{{ number_format($provider->today_stats->today_requests ?? 0) }}</div>
                            </div>
                            <div class="text-center w-100">
                                <div class="text-muted text-uppercase mb-1 fw-semibold" style="font-size: 0.65rem; letter-spacing: 0.5px;">Last Synced</div>
                                <div class="text-white fw-semibold" style="font-size: 0.85rem; margin-top: 5px;">{{ $provider->last_synced_at ? \Carbon\Carbon::parse($provider->last_synced_at)->shortAbsoluteDiffForHumans() : 'Never' }}</div>
                            </div>
                        </div>

                        {{-- Sparkline Area --}}
                        <div class="mb-4 position-relative rounded-3" style="height: 60px; overflow: hidden; background: linear-gradient(180deg, rgba(255,255,255,0.03) 0%, rgba(255,255,255,0) 100%); border: 1px solid rgba(255,255,255,0.05);">
                            <canvas id="sparkline-{{ $provider->id }}" style="width:100%; height:100%;"></canvas>
                            @if($healthStatus === 'no_ping')
                                <div class="position-absolute top-50 start-50 translate-middle w-100 text-center">
                                    <div class="text-muted" style="font-size: 0.75rem;">
                                        <i class="fa-solid fa-chart-line opacity-25 d-block fs-4 mb-1"></i> No Latency Data
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Budget Progress --}}
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2" style="font-size: 0.8rem;">
                                <span class="text-muted">Usage: <strong class="text-white fs-6">${{ number_format($monthCost, 2) }}</strong></span>
                                <span class="text-muted">Cap: <strong class="text-white">{{ $budgetCap > 0 ? '$'.number_format($budgetCap,2) : 'None' }}</strong></span>
                            </div>
                            <div class="progress" style="height: 6px; background-color: rgba(255,255,255,0.05); border-radius: 10px; overflow: visible;">
                                <div class="progress-bar bg-{{ $budgetColor }} progress-bar-striped progress-bar-animated rounded-pill position-relative" role="progressbar" style="width: {{ $budgetPct }}%;" aria-valuenow="{{ $budgetPct }}" aria-valuemin="0" aria-valuemax="100">
                                    <div class="position-absolute top-50 start-100 translate-middle bg-{{ $budgetColor }} border border-2 border-dark rounded-circle shadow" style="width: 12px; height: 12px;"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="d-flex justify-content-between align-items-center mt-auto pt-2">
                            <div class="d-flex align-items-center">
                                <div class="form-check m-0 me-3">
                                    <input class="form-check-input provider-bulk-checkbox bg-dark border-secondary shadow-sm" type="checkbox" value="{{ $provider->id }}" aria-label="Select provider" style="cursor: pointer; transform: scale(1.3);">
                                </div>
                                @if($provider->api_keys_count > 0)
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-2"><i class="fa-solid fa-key me-1"></i> Key Configured</span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-3 py-2"><i class="fa-solid fa-triangle-exclamation me-1"></i> Missing Key</span>
                                @endif
                            </div>
                            <button class="btn btn-sm btn-primary rounded-pill px-4 py-2 fw-semibold shadow-sm" onclick="openProviderDetails('{{ $provider->id }}')" style="transition: all 0.2s ease;">
                                Configure <i class="fa-solid fa-arrow-right ms-2" style="font-size: 0.75rem;"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <i class="fa-solid fa-server text-muted mb-3" style="font-size: 3rem;"></i>
                <h5 class="text-light">No Providers Configured</h5>
                <p class="text-muted mb-4">Add your first AI provider to get started.</p>
                <button class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#addProviderOffcanvas">
                    <i class="fa-solid fa-plus me-2"></i>Add Provider
                </button>
            </div>
        @endforelse
    </div>
</div>

{{-- Providers Table View (hidden by default) --}}
<div id="providers-table-view" class="d-none">
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle table-dark-custom" style="font-size: 0.85rem;">
            <thead>
                <tr>
                    <th style="width: 40px;"><input class="form-check-input" type="checkbox" id="selectAllProviders"></th>
                    <th>Provider</th>
                    <th>Base URL</th>
                    <th class="text-center">Models</th>
                    <th class="text-center">Health</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Last Synced</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($providers as $provider)
                    @php
                        $healthStatus = $provider->health_status ?? 'no_ping';
                        $healthColor = match($healthStatus) { 'healthy' => 'success', 'degraded' => 'warning', 'offline' => 'danger', 'disabled' => 'secondary', default => 'secondary' };
                        $healthLabel = match($healthStatus) { 'healthy' => 'Healthy', 'degraded' => 'Degraded', 'offline' => 'Unreachable', 'disabled' => 'Disabled', default => 'No Ping' };
                        $isFav = $provider->is_favorite ? 1 : 0;
                    @endphp
                    <tr class="provider-row" 
                        data-id="{{ $provider->id }}" 
                        data-status="{{ $provider->is_active ? 'active' : 'disabled' }}" 
                        data-health="{{ $healthStatus }}" 
                        data-favorite="{{ $isFav }}"
                        data-name="{{ $provider->name }}"
                        data-cost="{{ $provider->month_stats->month_cost ?? 0 }}"
                        data-models="{{ $provider->models_count ?? 0 }}"
                        data-synced="{{ $provider->last_synced_at ? strtotime($provider->last_synced_at) : 0 }}"
                        data-sort_order="{{ $provider->sort_order ?? 999 }}">
                        <td><input class="form-check-input provider-bulk-checkbox" type="checkbox" value="{{ $provider->id }}"></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <button class="btn btn-sm btn-link p-0 text-{{ $isFav ? 'warning' : 'muted' }} toggle-fav-btn" data-id="{{ $provider->id }}">
                                    <i class="fa-{{ $isFav ? 'solid' : 'regular' }} fa-star"></i>
                                </button>
                                <span class="fw-semibold text-light">{{ $provider->name }}</span>
                            </div>
                        </td>
                        <td class="text-muted" style="font-family: 'JetBrains Mono'; font-size: 0.78rem; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $provider->base_url }}">
                            {{ $provider->base_url }}
                        </td>
                        <td class="text-center">{{ $provider->models_count ?? 0 }}</td>
                        <td class="text-center">
                            <span class="text-{{ $healthColor }}">
                                <i class="fa-solid fa-circle text-{{ $healthColor }} me-1" style="font-size: 0.5rem;"></i>{{ $healthLabel }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($provider->is_active)
                                <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-25">Active</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-25 text-secondary border border-secondary">Disabled</span>
                            @endif
                        </td>
                        <td class="text-center text-muted" style="font-size: 0.75rem;">
                            {{ $provider->last_synced_at ? \Carbon\Carbon::parse($provider->last_synced_at)->diffForHumans() : 'Never' }}
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-secondary" onclick="pingProvider('{{ $provider->id }}')" title="Ping">
                                    <i class="fa-solid fa-network-wired"></i>
                                </button>
                                <button class="btn btn-outline-secondary" onclick="syncModels('{{ $provider->id }}')" title="Sync Models">
                                    <i class="fa-solid fa-rotate"></i>
                                </button>
                                <button class="btn btn-outline-info" onclick="openProviderDetails('{{ $provider->id }}')" title="Details">
                                    <i class="fa-solid fa-sliders"></i>
                                </button>
                                <button class="btn btn-outline-danger" onclick="deleteProvider('{{ $provider->id }}', '{{ addslashes($provider->name) }}')" title="Delete">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No providers configured.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Pagination --}}
@if($providers->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $providers->links('pagination::bootstrap-5') }}
</div>
@endif

{{-- Bulk Actions Strip --}}
<div id="bulk-actions-strip" class="fixed-bottom bg-dark border-top border-secondary py-3 px-4 shadow-lg d-none" style="z-index: 1040;">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <span class="badge bg-primary me-2"><span id="bulk-selected-count">0</span> Selected</span>
            <span class="text-muted small">Bulk Actions</span>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-success" onclick="executeBulkAction('enable')">Enable</button>
            <button class="btn btn-sm btn-outline-secondary" onclick="executeBulkAction('disable')">Disable</button>
            <button class="btn btn-sm btn-outline-info" onclick="executeBulkAction('sync')">Sync Models</button>
            <button class="btn btn-sm btn-outline-danger" onclick="executeBulkAction('delete')">Delete</button>
        </div>
    </div>
</div>

@push('scripts')
{{-- Include SortableJS and Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // ─── Bulk Actions Logic ───────────────────────────────────────────────────
    $(document).ready(function() {
        const updateBulkStrip = () => {
            const count = $('.provider-bulk-checkbox:checked').length;
            $('#bulk-selected-count').text(count);
            if(count > 0) {
                $('#bulk-actions-strip').removeClass('d-none');
            } else {
                $('#bulk-actions-strip').addClass('d-none');
            }
        };

        $(document).on('change', '.provider-bulk-checkbox', updateBulkStrip);
        
        $('#selectAllProviders').on('change', function() {
            const isChecked = $(this).prop('checked');
            $('.provider-bulk-checkbox').prop('checked', isChecked);
            updateBulkStrip();
        });

        window.executeBulkAction = function(action) {
            const ids = $('.provider-bulk-checkbox:checked').map(function(){ return $(this).val(); }).get();
            if(ids.length === 0) return;

            if(action === 'delete' && !confirm(`Are you sure you want to delete ${ids.length} providers?`)) return;

            window.Nexus.showTaskLoader('Executing bulk action...');
            $.post('/api/v1/ai/providers/bulk-action', { action: action, ids: ids }, function(res) {
                window.Nexus.hideTaskLoader();
                window.Nexus.notify(res.message, 'success');
                setTimeout(() => location.reload(), 1000);
            }).fail(function(err) {
                window.Nexus.hideTaskLoader();
                window.Nexus.notify('Bulk action failed.', 'error');
            });
        };
    });

    // ─── SortableJS ───────────────────────────────────────────────────────────
    $(document).ready(function() {
        const sortableGrid = document.getElementById('sortable-providers-list');
        if(sortableGrid) {
            new Sortable(sortableGrid, {
                handle: '.drag-handle',
                animation: 150,
                onEnd: function(evt) {
                    const ids = [];
                    $('#sortable-providers-list .provider-card-wrapper').each(function() {
                        ids.push($(this).data('id'));
                    });
                    
                    $.post('/api/v1/ai/providers/reorder', {
                        ordered_ids: ids
                    }, function(res) {
                        window.Nexus.notify('Sort order saved', 'success');
                    }).fail(function() {
                        window.Nexus.notify('Failed to save sort order', 'error');
                    });
                }
            });
        }
    });

    // ─── Chart.js Sparklines ──────────────────────────────────────────────────
    window.renderProviderSparklines = function() {
        @foreach($providers as $provider)
            $.get(`/api/v1/ai/providers/{{ $provider->id }}/details`, function(res) {
                if(res.data && res.data.recent_latency) {
                    const ctx = document.getElementById('sparkline-{{ $provider->id }}');
                    if(ctx) {
                        new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: res.data.recent_latency.map((_, i) => i),
                                datasets: [{
                                    data: res.data.recent_latency,
                                    borderColor: '#58a6ff',
                                    borderWidth: 2,
                                    tension: 0.4,
                                    pointRadius: 0,
                                    fill: true,
                                    backgroundColor: 'rgba(88, 166, 255, 0.1)'
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: { legend: { display: false }, tooltip: { enabled: false } },
                                scales: { x: { display: false }, y: { display: false, min: 0 } },
                                layout: { padding: 0 }
                            }
                        });
                    }
                }
            });
        @endforeach
    };

    $(document).ready(function() {
        setTimeout(window.renderProviderSparklines, 500);
    });

    // ─── Toggle Favorite ──────────────────────────────────────────────────────
    $(document).on('click', '.toggle-fav-btn', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        const icon = $(this).find('i');
        const isFav = icon.hasClass('fa-solid');
        
        $.ajax({
            url: `/api/v1/ai/providers/${id}/meta`,
            method: 'PATCH',
            data: { is_favorite: !isFav },
            success: function(res) {
                if(res.success) {
                    icon.toggleClass('fa-regular fa-solid');
                    icon.parent().toggleClass('text-muted text-warning');
                    $(`.provider-card-wrapper[data-id='${id}']`).data('favorite', !isFav ? 1 : 0);
                    $(`.provider-row[data-id='${id}']`).data('favorite', !isFav ? 1 : 0);
                }
            }
        });
    });

    // ─── Duplicate Provider ───────────────────────────────────────────────────
    window.duplicateProvider = function(id) {
        window.Nexus.showTaskLoader('Duplicating...');
        $.post(`/api/v1/ai/providers/${id}/duplicate`, {}, function(res) {
            window.Nexus.hideTaskLoader();
            window.Nexus.notify(res.message, 'success');
            setTimeout(() => location.reload(), 1000);
        }).fail(function(err) {
            window.Nexus.hideTaskLoader();
            window.Nexus.notify('Duplication failed.', 'error');
        });
    };

    // ─── Ping Provider ────────────────────────────────────────────────────────
    window.pingProvider = function(id) {
        window.Nexus.showTaskLoader('Pinging provider...', 'Testing API connection...');
        $.ajax({
            url: `/api/v1/ai/providers/${id}/test`,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            contentType: 'application/json',
            data: JSON.stringify({}),
            success: function(res) {
                window.Nexus.hideTaskLoader();
                if (res.success) {
                    const latency = res.data && res.data.latency ? res.data.latency : '';
                    window.Nexus.notify(res.message + (latency ? ` (${latency}ms)` : ''), 'success');
                } else {
                    window.Nexus.notify(res.message || 'Ping failed.', 'error');
                }
            },
            error: function(xhr) {
                window.Nexus.hideTaskLoader();
                const res = xhr.responseJSON;
                const msg = (res && res.message) ? res.message : 'Ping failed: connection error.';
                window.Nexus.notify(msg, 'error');
            }
        });
    };

    // ─── Sync Models ──────────────────────────────────────────────────────────
    window.syncModels = function(id) {
        window.Nexus.showTaskLoader('Syncing models...', 'Fetching from provider API...');
        $.ajax({
            url: `/api/v1/ai/providers/${id}/sync-models`,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            contentType: 'application/json',
            data: JSON.stringify({}),
            success: function(res) {
                window.Nexus.hideTaskLoader();
                if (res.success) {
                    const count = res.synced_count !== undefined ? res.synced_count : 0;
                    window.Nexus.notify(res.message + ` (${count} models synced)`, 'success');
                    setTimeout(() => location.reload(), 1200);
                } else {
                    window.Nexus.notify(res.message || 'Sync failed.', 'error');
                }
            },
            error: function(xhr) {
                window.Nexus.hideTaskLoader();
                const res = xhr.responseJSON;
                const msg = (res && res.message) ? res.message : 'Sync failed: connection error.';
                window.Nexus.notify(msg, 'error');
            }
        });
    };

    // ─── Open Details Drawer ──────────────────────────────────────────────────
    window.openProviderDetails = function(id) {
        window.Nexus.showTaskLoader('Loading...', 'Fetching provider details...');
        $.ajax({
            url: `/api/v1/ai/providers/${id}/details`,
            method: 'GET',
            success: function(res) {
                window.Nexus.hideTaskLoader();
                if (res.success && res.data) {
                    // This function will be defined in the details-drawer.blade.php
                    if(typeof window.populateProviderDetailsDrawer === 'function') {
                        window.populateProviderDetailsDrawer(res.data);
                        const offcanvas = new bootstrap.Offcanvas(document.getElementById('providerDetailsOffcanvas'));
                        offcanvas.show();
                    } else {
                        window.Nexus.notify('Drawer component not ready.', 'error');
                    }
                } else {
                    window.Nexus.notify('Failed to load provider details.', 'error');
                }
            },
            error: function() {
                window.Nexus.hideTaskLoader();
                window.Nexus.notify('Failed to load provider details.', 'error');
            }
        });
    };

    // ─── Delete Provider ──────────────────────────────────────────────────────
    window.deleteProvider = function(id, name) {
        if (!confirm(`Delete provider "${name}"? This will also remove all its models and API keys. This action cannot be undone.`)) {
            return;
        }
        window.Nexus.showTaskLoader('Deleting...', 'Removing provider...');
        $.ajax({
            url: `/api/v1/ai/providers/${id}`,
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function(res) {
                window.Nexus.hideTaskLoader();
                if (res.success) {
                    window.Nexus.notify(res.message || 'Provider deleted.', 'success');
                    setTimeout(() => location.reload(), 800);
                } else {
                    window.Nexus.notify(res.message || 'Delete failed.', 'error');
                }
            },
            error: function(xhr) {
                window.Nexus.hideTaskLoader();
                const res = xhr.responseJSON;
                window.Nexus.notify((res && res.message) ? res.message : 'Delete failed.', 'error');
            }
        });
    };

    // ─── Toggle Provider Status ───────────────────────────────────────────────
    $(document).on('click', '.toggle-provider-btn', function(e) {
        e.preventDefault();
        const id       = $(this).data('id');
        const isActive = parseInt($(this).data('active'));

        window.Nexus.showTaskLoader();
        $.ajax({
            url: `/api/v1/ai/providers/${id}/toggle-active`,
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
            data: JSON.stringify({ is_active: isActive }),
            success: function(res) {
                window.Nexus.hideTaskLoader();
                if (res.success) {
                    window.Nexus.notify(res.message || 'Status updated.', 'success');
                    setTimeout(() => location.reload(), 500);
                }
            },
            error: function(xhr) {
                window.Nexus.hideTaskLoader();
                const res = xhr.responseJSON;
                window.Nexus.notify((res && res.message) ? res.message : 'Failed to toggle status.', 'error');
            }
        });
    });

    // ─── View Toggle (Cards / Table) ──────────────────────────────────────────
    $(document).ready(function() {
        $('#btn-view-cards').on('click', function() {
            $('#providers-cards-view').removeClass('d-none');
            $('#providers-table-view').addClass('d-none');
            $(this).addClass('active').removeClass('btn-outline-secondary').addClass('btn-secondary');
            $('#btn-view-table').removeClass('active').removeClass('btn-secondary').addClass('btn-outline-secondary');
        });

        $('#btn-view-table').on('click', function() {
            $('#providers-table-view').removeClass('d-none');
            $('#providers-cards-view').addClass('d-none');
            $(this).addClass('active').removeClass('btn-outline-secondary').addClass('btn-secondary');
            $('#btn-view-cards').removeClass('active').removeClass('btn-secondary').addClass('btn-outline-secondary');
        });
    });
</script>
@endpush
