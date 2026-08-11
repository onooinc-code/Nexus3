@extends('layouts.app')

@section('title', 'Provider Details - ' . $provider->name)

@section('content')
<div class="container-fluid py-4">
    <!-- Header Breadcrumb & Actions -->
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('hub.models') }}" class="text-info text-decoration-none"><i class="fa-solid fa-server me-1"></i> AI Models Hub</a></li>
                    <li class="breadcrumb-item active text-light" aria-current="page">{{ $provider->name }}</li>
                </ol>
            </nav>
            <h3 class="text-light fw-bold mb-0 d-flex align-items-center gap-3">
                {{ $provider->name }}
                @if($provider->is_active)
                    <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-25 fs-6 fw-normal px-3 py-1" id="provider-status-badge">
                        <i class="fa-solid fa-circle me-1" style="font-size: 0.5rem;"></i>Active
                    </span>
                @else
                    <span class="badge bg-secondary bg-opacity-25 text-secondary border border-secondary fs-6 fw-normal px-3 py-1" id="provider-status-badge">
                        <i class="fa-solid fa-circle me-1" style="font-size: 0.5rem;"></i>Disabled
                    </span>
                @endif
            </h3>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-info px-3" id="btn-sync-models" onclick="syncModels('{{ $provider->id }}')">
                <i class="fa-solid fa-rotate me-1"></i> Sync Models
            </button>
            <button class="btn btn-outline-success px-3" id="btn-ping-provider" onclick="pingProvider('{{ $provider->id }}')">
                <i class="fa-solid fa-network-wired me-1"></i> Ping Provider
            </button>
            <a href="{{ route('hub.models') }}" class="btn btn-secondary px-3">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Hub
            </a>
        </div>
    </div>

    <!-- Overview Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card card-dashboard border-secondary p-3 h-100 shadow-sm">
                <div class="text-muted small mb-1"><i class="fa-solid fa-dollar-sign text-success me-1"></i> Month's Cost</div>
                <h3 class="text-light fw-bold mb-0">${{ number_format($provider->month_cost ?? 0, 2) }}</h3>
                <div class="text-muted small mt-2">Today: ${{ number_format($provider->today_cost ?? 0, 2) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-dashboard border-secondary p-3 h-100 shadow-sm">
                <div class="text-muted small mb-1"><i class="fa-solid fa-robot text-info me-1"></i> Configured Models</div>
                <h3 class="text-light fw-bold mb-0">{{ $provider->models->count() }}</h3>
                <div class="text-muted small mt-2">Active: {{ $provider->models->where('is_active', true)->count() }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-dashboard border-secondary p-3 h-100 shadow-sm">
                <div class="text-muted small mb-1"><i class="fa-solid fa-key text-warning me-1"></i> API Keys</div>
                <h3 class="text-light fw-bold mb-0">{{ $provider->apiKeys->count() }}</h3>
                <div class="text-muted small mt-2">Default Key: {{ $provider->apiKeys->where('is_default', true)->first()?->name ?? 'None' }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-dashboard border-secondary p-3 h-100 shadow-sm">
                <div class="text-muted small mb-1"><i class="fa-solid fa-chart-line text-purple me-1" style="color:#b57edd;"></i> Requests (24h)</div>
                <h3 class="text-light fw-bold mb-0">{{ number_format($provider->today_requests ?? 0) }}</h3>
                <div class="text-muted small mt-2">Last Synced: {{ $provider->last_synced_at ? \Carbon\Carbon::parse($provider->last_synced_at)->diffForHumans() : 'Never' }}</div>
            </div>
        </div>
    </div>

    <!-- Provider Details Grid -->
    <div class="row g-4 mb-4">
        <!-- Configuration Card -->
        <div class="col-lg-6">
            <div class="card card-dashboard border-secondary p-4 h-100 shadow">
                <h5 class="text-light fw-bold mb-3 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-sliders text-primary"></i> Configuration & Auth Credentials
                </h5>
                <form id="provider-details-form">
                    @csrf
                    <input type="hidden" name="provider_id" id="provider_id" value="{{ $provider->id }}">
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">Provider Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control bg-dark text-light border-secondary" id="config-name" name="name" value="{{ $provider->name }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">Base URL <span class="text-danger">*</span></label>
                        <input type="text" class="form-control bg-dark text-light border-secondary font-mono" id="config-base-url" name="base_url" value="{{ $provider->base_url }}" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-bold">Models Fetch Endpoint</label>
                            <input type="text" class="form-control bg-dark text-light border-secondary" id="config-models-endpoint" name="models_fetch_endpoint" value="{{ $provider->models_fetch_endpoint ?? '/models' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-bold">Auth Format</label>
                            <select class="form-select bg-dark text-light border-secondary" id="config-auth-format" name="auth_header_format">
                                <option value="Bearer {key}" {{ ($provider->auth_header_format == 'Bearer {key}') ? 'selected' : '' }}>Bearer {key} (Standard)</option>
                                <option value="x-api-key: {key}" {{ ($provider->auth_header_format == 'x-api-key: {key}') ? 'selected' : '' }}>x-api-key: {key} (Anthropic)</option>
                                <option value="x-goog-api-key: {key}" {{ ($provider->auth_header_format == 'x-goog-api-key: {key}') ? 'selected' : '' }}>x-goog-api-key: {key} (Google)</option>
                                <option value="Authorization: Bearer {key}" {{ ($provider->auth_header_format == 'Authorization: Bearer {key}') ? 'selected' : '' }}>Authorization: Bearer {key}</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">Update API Key <span class="text-muted">(leave blank to keep current)</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control bg-dark text-light border-secondary" id="config-api-key" name="api_key" placeholder="Enter new API key to save & encrypt...">
                            <button class="btn btn-outline-secondary toggle-eye-btn" type="button" data-target="config-api-key">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-primary px-4 fw-bold" id="btn-save-config">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Save Configuration
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Health & Latency Logs Card -->
        <div class="col-lg-6">
            <div class="card card-dashboard border-secondary p-4 h-100 shadow">
                <h5 class="text-light fw-bold mb-3 d-flex justify-content-between align-items-center">
                    <span><i class="fa-solid fa-pulse text-success me-2"></i> Health & Latency Metrics</span>
                    <span class="badge bg-dark border border-secondary text-info fs-6 fw-normal" id="metric-count-badge">{{ count($provider->health_metrics) }} checks</span>
                </h5>
                <div class="table-responsive" style="max-height: 330px; overflow-y: auto;">
                    <table class="table table-dark table-hover table-sm align-middle small" id="health-metrics-table">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Latency</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($provider->health_metrics as $metric)
                                <tr>
                                    <td>
                                        @if($metric->status === 'healthy')
                                            <span class="badge bg-success bg-opacity-25 text-success"><i class="fa-solid fa-circle me-1" style="font-size: 0.4rem;"></i>Healthy</span>
                                        @else
                                            <span class="badge bg-danger bg-opacity-25 text-danger"><i class="fa-solid fa-circle me-1" style="font-size: 0.4rem;"></i>{{ $metric->status }}</span>
                                        @endif
                                    </td>
                                    <td class="font-mono text-info fw-bold">{{ $metric->latency_ms }} ms</td>
                                    <td class="text-muted">{{ \Carbon\Carbon::parse($metric->created_at)->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">No ping metrics recorded yet. Click "Ping Provider" to execute a health check.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Provider API Keys Card -->
    <div class="card card-dashboard border-secondary p-4 mb-4 shadow">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="text-light fw-bold mb-0"><i class="fa-solid fa-key text-warning me-2"></i> Configured API Keys & Rotation</h5>
            <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#addKeyModal">
                <i class="fa-solid fa-plus me-1"></i> Add API Key
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle">
                <thead>
                    <tr>
                        <th>Key Label</th>
                        <th>Key Prefix</th>
                        <th class="text-center">Priority</th>
                        <th class="text-center">Last Used</th>
                        <th class="text-center">Default Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($provider->apiKeys as $key)
                        <tr>
                            <td class="fw-semibold text-light"><i class="fa-solid fa-key text-warning me-2"></i>{{ $key->name }}</td>
                            <td class="text-muted font-mono small">{{ $key->key_prefix ?? '••••' }}...</td>
                            <td class="text-center font-mono text-info fw-bold">{{ $key->priority ?? 1 }}</td>
                            <td class="text-center text-muted small">{{ $key->last_used_at ? \Carbon\Carbon::parse($key->last_used_at)->diffForHumans() : 'Never' }}</td>
                            <td class="text-center">
                                @if($key->is_default)
                                    <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-25">Primary Default</span>
                                @else
                                    <button class="btn btn-sm btn-link text-muted p-0 text-decoration-none" onclick="setDefaultApiKey('{{ $key->id }}')">Set as Default</button>
                                @endif
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-info py-1 px-2 me-1" onclick="pingSingleKey('{{ $key->id }}')" title="Ping Key">
                                    <i class="fa-solid fa-plug me-1"></i> Ping
                                </button>
                                <button class="btn btn-sm btn-outline-danger py-1 px-2" onclick="deleteApiKey('{{ $key->id }}')" title="Delete Key">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No API keys configured for this provider yet. Click "Add API Key" above.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Models Table Card with Live Statistics -->
    <div class="card card-dashboard border-secondary p-4 mb-4 shadow">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="text-light fw-bold mb-0"><i class="fa-solid fa-robot text-info me-2"></i> Configured Provider Models & Live Usage</h5>
            <button class="btn btn-sm btn-outline-info" onclick="syncModels('{{ $provider->id }}')"><i class="fa-solid fa-rotate me-1"></i> Sync Latest Models</button>
        </div>
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle">
                <thead>
                    <tr>
                        <th>Model Name</th>
                        <th>Model ID</th>
                        <th class="text-center">Requests (Month)</th>
                        <th class="text-center">Tokens Used</th>
                        <th class="text-center">Month Cost ($)</th>
                        <th class="text-center">Context Window</th>
                        <th class="text-center">Active Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($provider->models as $m)
                        @php
                            $mReqs = $m->stats->model_requests ?? 0;
                            $mTokens = $m->stats->model_tokens ?? 0;
                            $mCost = $m->stats->model_month_cost ?? 0;
                        @endphp
                        <tr>
                            <td class="fw-semibold text-light">
                                <i class="fa-solid fa-microchip text-info me-2"></i>{{ $m->name }}
                            </td>
                            <td class="text-muted font-mono small">{{ $m->model_id }}</td>
                            <td class="text-center fw-bold text-light">{{ number_format($mReqs) }}</td>
                            <td class="text-center text-info font-mono small">{{ number_format($mTokens) }}</td>
                            <td class="text-center text-success fw-bold font-mono">${{ number_format($mCost, 4) }}</td>
                            <td class="text-center text-secondary small">{{ number_format($m->context_window ?? 0) }} tokens</td>
                            <td class="text-center">
                                <div class="form-check form-switch d-flex justify-content-center m-0">
                                    <input class="form-check-input toggle-model-checkbox" type="checkbox" role="switch"
                                           data-id="{{ $m->id }}" {{ $m->is_active ? 'checked' : '' }}>
                                </div>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-info py-1 px-3" onclick="openTestPromptModal('{{ $provider->id }}', '{{ $m->id }}', '{{ addslashes($m->name) }}', '{{ addslashes($provider->name) }}')" title="Quick Modal Test">
                                    <i class="fa-solid fa-vial me-1"></i> Test
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No models configured for this provider. Click "Sync Models" to auto-discover models.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('hubs.partials.ai-hub.modals.prompt-test-modal')
@include('hubs.partials.ai-hub.api-keys.modals.add-key', ['providers' => [$provider]])

@endsection

@push('scripts')
<script>
    // ─── Toggle Eye (Show/Hide Password) ─────────────────────────────────────
    $(document).on('click', '.toggle-eye-btn', function() {
        const targetId = $(this).data('target');
        const input = $('#' + targetId);
        const isPassword = input.attr('type') === 'password';
        input.attr('type', isPassword ? 'text' : 'password');
        $(this).find('i').toggleClass('fa-eye fa-eye-slash');
    });

    // ─── Ping Provider Function ───────────────────────────────────────────────
    window.pingProvider = function(id) {
        const btn = $('#btn-ping-provider');
        btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Pinging...');
        
        if (window.Nexus && window.Nexus.showTaskLoader) {
            window.Nexus.showTaskLoader('Pinging provider...', 'Testing connection...');
        }

        $.ajax({
            url: `/api/v1/ai/providers/${id}/test`,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            contentType: 'application/json',
            data: JSON.stringify({}),
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fa-solid fa-network-wired me-1"></i> Ping Provider');
                if (window.Nexus && window.Nexus.hideTaskLoader) window.Nexus.hideTaskLoader();

                if (res.success) {
                    const latency = res.data && res.data.latency ? ` (${res.data.latency}ms)` : '';
                    if (window.Nexus && window.Nexus.notify) {
                        window.Nexus.notify((res.message || 'Ping successful') + latency, 'success');
                    }
                    setTimeout(() => location.reload(), 1200);
                } else {
                    if (window.Nexus && window.Nexus.notify) {
                        window.Nexus.notify(res.message || 'Ping failed.', 'error');
                    }
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fa-solid fa-network-wired me-1"></i> Ping Provider');
                if (window.Nexus && window.Nexus.hideTaskLoader) window.Nexus.hideTaskLoader();
                const res = xhr.responseJSON;
                if (window.Nexus && window.Nexus.notify) {
                    window.Nexus.notify((res && res.message) ? res.message : 'Ping execution failed.', 'error');
                }
            }
        });
    };

    // ─── Sync Models Function ──────────────────────────────────────────────────
    window.syncModels = function(id) {
        const btn = $('#btn-sync-models');
        btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Syncing...');

        if (window.Nexus && window.Nexus.showTaskLoader) {
            window.Nexus.showTaskLoader('Syncing models...', 'Fetching available models...');
        }

        $.ajax({
            url: `/api/v1/ai/providers/${id}/sync-models`,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            contentType: 'application/json',
            data: JSON.stringify({}),
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fa-solid fa-rotate me-1"></i> Sync Models');
                if (window.Nexus && window.Nexus.hideTaskLoader) window.Nexus.hideTaskLoader();

                if (res.success) {
                    if (window.Nexus && window.Nexus.notify) {
                        window.Nexus.notify(res.message || 'Models synced successfully!', 'success');
                    }
                    setTimeout(() => location.reload(), 1200);
                } else {
                    if (window.Nexus && window.Nexus.notify) {
                        window.Nexus.notify(res.message || 'Sync failed.', 'error');
                    }
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fa-solid fa-rotate me-1"></i> Sync Models');
                if (window.Nexus && window.Nexus.hideTaskLoader) window.Nexus.hideTaskLoader();
                const res = xhr.responseJSON;
                if (window.Nexus && window.Nexus.notify) {
                    window.Nexus.notify((res && res.message) ? res.message : 'Sync execution failed.', 'error');
                }
            }
        });
    };

    // ─── Key Action Handlers ──────────────────────────────────────────────────
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
        $.ajax({
            url: `/api/v1/ai/api-keys/${keyId}/set-default`,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function(res) {
                if (window.Nexus && window.Nexus.notify) {
                    window.Nexus.notify('Primary default key updated!', 'success');
                }
                setTimeout(() => location.reload(), 1000);
            }
        });
    };

    window.deleteApiKey = function(keyId) {
        if (!confirm('Are you sure you want to revoke/delete this API key?')) return;
        $.ajax({
            url: `/api/v1/ai/api-keys/${keyId}`,
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function() {
                if (window.Nexus && window.Nexus.notify) {
                    window.Nexus.notify('API key deleted successfully.', 'success');
                }
                setTimeout(() => location.reload(), 1000);
            }
        });
    };

    // ─── AJAX Save Configuration Form ────────────────────────────────────────
    $(document).ready(function() {
        $('#provider-details-form').on('submit', function(e) {
            e.preventDefault();
            const btn = $('#btn-save-config');
            btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Saving...');

            const pId = $('#provider_id').val();
            const payload = {
                name: $('#config-name').val().trim(),
                base_url: $('#config-base-url').val().trim(),
                models_fetch_endpoint: $('#config-models-endpoint').val().trim(),
                auth_header_format: $('#config-auth-format').val(),
            };

            const newKey = $('#config-api-key').val().trim();
            if (newKey) {
                payload.api_key = newKey;
            }

            $.ajax({
                url: `/api/v1/ai/providers/${pId}`,
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                data: JSON.stringify(payload),
                success: function(res) {
                    btn.prop('disabled', false).html('<i class="fa-solid fa-floppy-disk me-1"></i> Save Configuration');
                    if (res.success) {
                        if (window.Nexus && window.Nexus.notify) {
                            window.Nexus.notify(res.message || 'Configuration saved successfully!', 'success');
                        }
                        setTimeout(() => location.reload(), 1200);
                    } else {
                        if (window.Nexus && window.Nexus.notify) {
                            window.Nexus.notify(res.message || 'Failed to save configuration.', 'error');
                        }
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="fa-solid fa-floppy-disk me-1"></i> Save Configuration');
                    const res = xhr.responseJSON;
                    let msg = 'Failed to save configuration.';
                    if (res && res.errors) {
                        msg = Object.values(res.errors).flat().join(' ');
                    } else if (res && res.message) {
                        msg = res.message;
                    }
                    if (window.Nexus && window.Nexus.notify) {
                        window.Nexus.notify(msg, 'error');
                    }
                }
            });
        });

        // ─── Toggle Model Active Checkbox ─────────────────────────────────────
        $('.toggle-model-checkbox').on('change', function() {
            const id = $(this).data('id');
            const url = '{{ route("hub.models.toggle", ["id" => "__ID__"]) }}'.replace('__ID__', id);
            
            $.ajax({
                url: url,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    if (window.Nexus && window.Nexus.notify) {
                        window.Nexus.notify(res.message || 'Model status updated successfully', 'success');
                    }
                },
                error: function(xhr) {
                    if (window.Nexus && window.Nexus.notify) {
                        window.Nexus.notify('Failed to toggle model status', 'error');
                    }
                }
            });
        });
    });
</script>
@endpush
