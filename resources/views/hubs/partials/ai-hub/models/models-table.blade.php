<div class="card card-dashboard border-secondary p-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-dark-custom table-hover align-middle mb-0" id="modelsTable">
            <thead>
                <tr>
                    <th class="ps-4" style="width: 28%;">Model Name</th>
                    <th style="width: 14%;">Provider</th>
                    <th style="width: 18%;">Context Window</th>
                    <th style="width: 12%;">Input Cost (1M)</th>
                    <th style="width: 12%;">Output Cost (1M)</th>
                    <th style="width: 10%;">Tier</th>
                    <th class="text-center" style="width: 8%;">Status</th>
                    <th class="text-end pe-4" style="width: 14%;">Actions</th>
                </tr>
            </thead>
            <tbody id="models-table-body">
                @forelse($models as $model)
                    <tr data-model-id="{{ $model->id }}">
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fa-regular fa-star text-muted cursor-pointer favorite-model-star" 
                                   data-id="{{ $model->id }}"
                                   onclick="toggleFavoriteModel('{{ $model->id }}', this)" 
                                   title="Toggle Favorite"></i>
                                <div>
                                    <span class="fw-bold text-light d-block">{{ $model->name }}</span>
                                    <span class="text-muted small font-mono" style="font-size: 0.72rem;">{{ $model->model_id ?? $model->id }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            @php
                                $providerName = $model->provider?->name ?? 'N/A';
                                $pLower = strtolower($providerName);
                                $providerBadgeClass = 'bg-primary text-primary border-primary';
                                if (str_contains($pLower, 'openai')) {
                                    $providerBadgeClass = 'bg-success text-success border-success';
                                } elseif (str_contains($pLower, 'google') || str_contains($pLower, 'gemini')) {
                                    $providerBadgeClass = 'bg-info text-info border-info';
                                } elseif (str_contains($pLower, 'anthropic') || str_contains($pLower, 'claude')) {
                                    $providerBadgeClass = 'bg-warning text-warning border-warning';
                                } elseif (str_contains($pLower, 'groq')) {
                                    $providerBadgeClass = 'bg-danger text-danger border-danger';
                                }
                            @endphp
                            <span class="badge bg-opacity-25 border border-opacity-50 model-provider-name {{ $providerBadgeClass }}">
                                {{ $providerName }}
                            </span>
                        </td>
                        <td>
                            @php
                                $windowVal = (int) ($model->context_window ?? 0);
                                $progressWidth = 0;
                                $labelText = 'N/A';
                                if ($windowVal > 0) {
                                    if ($windowVal >= 1000000) {
                                        $labelText = ($windowVal / 1000000) . 'M';
                                    } elseif ($windowVal >= 1000) {
                                        $labelText = ($windowVal / 1000) . 'K';
                                    } else {
                                        $labelText = (string) $windowVal;
                                    }
                                    $progressWidth = min(100, max(12, round(($windowVal / 2000000) * 100)));
                                }
                            @endphp
                            <div class="d-flex align-items-center gap-2" style="width: 140px;">
                                <div class="progress-thin flex-grow-1 bg-secondary bg-opacity-50">
                                    <div class="progress-bar {{ $windowVal > 0 ? 'bg-info' : 'bg-secondary' }}" style="width: {{ $progressWidth }}%"></div>
                                </div>
                                <span class="small font-mono {{ $windowVal > 0 ? 'text-light' : 'text-muted' }}">{{ $labelText }}</span>
                            </div>
                        </td>
                        <td class="font-monospace text-muted">
                            ${{ number_format((float) ($model->input_cost_per_m ?? 0.0), 2) }}
                        </td>
                        <td class="font-monospace text-muted">
                            ${{ number_format((float) ($model->output_cost_per_m ?? 0.0), 2) }}
                        </td>
                        <td>
                            @php
                                $tier = strtolower($model->quality_tier ?? '');
                                if (! $tier) {
                                    $mName = strtolower($model->name ?? '');
                                    if (str_contains($mName, 'pro') || str_contains($mName, 'opus') || str_contains($mName, 'ultra') || str_contains($mName, 'gpt-4o') || str_contains($mName, 'r1')) {
                                        $tier = 'premium';
                                    } elseif (str_contains($mName, 'flash') || str_contains($mName, 'mini') || str_contains($mName, 'haiku') || str_contains($mName, 'turbo') || str_contains($mName, 'medium')) {
                                        $tier = 'standard';
                                    } else {
                                        $tier = 'basic';
                                    }
                                }
                                $tierStyle = 'color:#b57edd;border-color:#b57edd;background-color:rgba(181,126,221,0.25);';
                                if ($tier === 'standard') {
                                    $tierStyle = 'color:#2ed573;border-color:#2ed573;background-color:rgba(46,213,115,0.20);';
                                } elseif ($tier === 'basic') {
                                    $tierStyle = 'color:#a4b0be;border-color:#747d8c;background-color:rgba(116,125,140,0.20);';
                                }
                            @endphp
                            <span class="badge border font-mono" style="{{ $tierStyle }}">
                                {{ ucfirst($tier) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="form-check form-switch d-flex justify-content-center m-0">
                                <input class="form-check-input toggle-model-checkbox" type="checkbox" role="switch" 
                                       data-id="{{ $model->id }}" {{ $model->is_active ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td class="text-end pe-4">
                            <button type="button" class="btn btn-sm btn-outline-info py-1 px-2 mx-1" 
                                    onclick="event.preventDefault(); event.stopPropagation(); testModelPrompt('{{ $model->provider_id ?? $model->provider?->id }}', '{{ $model->id }}', '{{ addslashes($model->name) }}', '{{ addslashes($model->provider?->name ?? '') }}'); return false;" 
                                    title="Quick Test Model Prompt">
                                <i class="fa-solid fa-vial me-1"></i> Test
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-1 mx-1" 
                                    onclick="navigator.clipboard.writeText('{{ $model->id }}'); if(window.Nexus&&window.Nexus.notify) window.Nexus.notify('Model ID copied!', 'info');" 
                                    title="Copy Model ID">
                                <i class="fa-solid fa-copy"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">No models configured. Click "Sync All Providers" to fetch remote catalogs.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        if ($.fn.DataTable && !$.fn.DataTable.isDataTable('#modelsTable')) {
            $('#modelsTable').DataTable({
                paging: true,
                searching: true,
                info: true,
                lengthChange: false,
                pageLength: 10,
                dom: '<"top">rt<"bottom d-flex justify-content-between p-3"ip><"clear">',
                language: {
                    info: "Showing _START_ to _END_ of _TOTAL_ models",
                    paginate: {
                        previous: "<i class='fa-solid fa-chevron-left'></i>",
                        next: "<i class='fa-solid fa-chevron-right'></i>"
                    }
                }
            });
        }

        initFavoriteModels();
    });

    function initFavoriteModels() {
        try {
            const favorites = JSON.parse(localStorage.getItem('nexus_favorite_models') || '[]');
            $('.favorite-model-star').each(function() {
                const id = $(this).data('id');
                if (favorites.includes(id)) {
                    $(this).removeClass('fa-regular text-muted').addClass('fa-solid text-warning');
                }
            });
        } catch (e) {}
    }

    window.toggleFavoriteModel = function(id, el) {
        try {
            let favorites = JSON.parse(localStorage.getItem('nexus_favorite_models') || '[]');
            const star = $(el);
            if (favorites.includes(id)) {
                favorites = favorites.filter(favId => favId !== id);
                star.removeClass('fa-solid text-warning').addClass('fa-regular text-muted');
            } else {
                favorites.push(id);
                star.removeClass('fa-regular text-muted').addClass('fa-solid text-warning');
            }
            localStorage.setItem('nexus_favorite_models', JSON.stringify(favorites));
        } catch (e) {}
    };

    window.toggleModelStatus = function(id, forceState = null) {
        const checkbox = $(`.toggle-model-checkbox[data-id="${id}"]`);
        const currentState = checkbox.is(':checked');
        const newState = forceState !== null ? forceState : !currentState;

        const url = '{{ route("hub.models.toggle", ["id" => "__ID__"]) }}'.replace('__ID__', id);
        if (window.Nexus && window.Nexus.showTaskLoader) {
            window.Nexus.showTaskLoader('Updating...', 'Toggling model status...');
        }
        $.ajax({
            url: url,
            method: 'POST',
            data: { 
                _token: '{{ csrf_token() }}',
                is_active: newState ? 1 : 0
            },
            success: function(res) {
                if (window.Nexus && window.Nexus.hideTaskLoader) window.Nexus.hideTaskLoader();
                checkbox.prop('checked', newState);
                if (window.Nexus && window.Nexus.notify) {
                    window.Nexus.notify(res.message || `Model status updated to ${newState ? 'Active' : 'Inactive'}!`, 'success');
                }
            },
            error: function(xhr) {
                if (window.Nexus && window.Nexus.hideTaskLoader) window.Nexus.hideTaskLoader();
                checkbox.prop('checked', currentState);
                const msg = xhr.responseJSON?.message || 'Failed to toggle model status';
                if (window.Nexus && window.Nexus.notify) {
                    window.Nexus.notify(msg, 'error');
                }
            }
        });
    };

    $(document).on('change', '.toggle-model-checkbox', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        const isChecked = $(this).is(':checked');
        window.toggleModelStatus(id, isChecked);
    });

    window.testModelPrompt = function(providerId, modelId, name, providerName = '') {
        if (typeof window.openTestPromptModal === 'function') {
            window.openTestPromptModal(providerId, modelId, name, providerName);
        } else if (window.Nexus && window.Nexus.notify) {
            window.Nexus.notify('Test modal initialization error', 'error');
        }
    };
</script>
@endpush
