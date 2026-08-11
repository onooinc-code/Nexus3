@extends('layouts.app')

@push('styles')
<style>
    .studio-shell {
        background: rgba(11, 14, 20, 0.75);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 16px;
        backdrop-filter: blur(20px);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);
        overflow: hidden;
    }
    .studio-header {
        background: rgba(255, 255, 255, 0.03);
        border-bottom: 1px solid rgba(255, 255, 255, 0.07);
        padding: 20px 28px;
    }
    .nav-tabs-custom .nav-link {
        color: rgba(255, 255, 255, 0.5);
        border: none;
        border-bottom: 2px solid transparent;
        padding: 12px 24px;
        font-weight: 600;
        transition: all 0.2s ease;
    }
    .nav-tabs-custom .nav-link:hover {
        color: rgba(255, 255, 255, 0.85);
        background: rgba(255, 255, 255, 0.02);
    }
    .nav-tabs-custom .nav-link.active {
        color: #8b5cf6;
        background: transparent;
        border-bottom: 2px solid #8b5cf6;
    }
    .glass-card {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.07);
        border-radius: 12px;
        padding: 24px;
    }
    .fallback-node {
        background: linear-gradient(135deg, rgba(30, 41, 59, 0.8), rgba(15, 23, 42, 0.9));
        border: 1px solid rgba(139, 92, 246, 0.3);
        border-radius: 12px;
        padding: 18px;
        position: relative;
        transition: all 0.3s ease;
    }
    .fallback-node:hover {
        border-color: rgba(139, 92, 246, 0.7);
        box-shadow: 0 4px 20px rgba(139, 92, 246, 0.15);
    }
    .fallback-connector {
        width: 2px;
        height: 30px;
        background: linear-gradient(180deg, #8b5cf6, #3b82f6);
        margin: 0 auto;
    }
    .key-item {
        background: rgba(0, 0, 0, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 10px;
        padding: 14px 18px;
        transition: background 0.2s ease;
    }
    .key-item:hover {
        background: rgba(255, 255, 255, 0.04);
    }
    .badge-cooldown {
        background: rgba(239, 68, 68, 0.15);
        color: #f87171;
        border: 1px solid rgba(239, 68, 68, 0.3);
    }
    .badge-active-key {
        background: rgba(16, 185, 129, 0.15);
        color: #34d399;
        border: 1px solid rgba(16, 185, 129, 0.3);
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <a href="{{ route('hub.people-connect') }}" class="text-muted text-decoration-none small hover-light">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Live Hub
            </a>
            <span class="text-muted">&bull;</span>
            <span class="badge bg-purple-500 bg-opacity-10 text-purple-400 border border-purple-500 border-opacity-25">AI COMMAND CENTER</span>
        </div>
        <h2 class="h3 text-light fw-bold mb-0">
            <i class="fa-solid fa-microchip text-indigo me-2" style="color:#8b5cf6;"></i> AI Agent Settings &amp; Studio
        </h2>
    </div>
    <div class="d-flex gap-2">
        <button type="submit" form="agentSettingsForm" class="btn btn-primary px-4 fw-bold shadow-sm" style="background:linear-gradient(135deg, #6366f1, #8b5cf6); border:none;">
            <i class="fa-solid fa-floppy-disk me-2"></i> Save All Settings
        </button>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success bg-success bg-opacity-10 text-success border-success border-opacity-25 d-flex align-items-center mb-4 rounded-3" role="alert">
    <i class="fa-solid fa-circle-check fs-5 me-3"></i>
    <div>{{ session('success') }}</div>
</div>
@endif

<div class="studio-shell">
    <div class="studio-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-indigo-500 bg-opacity-25 p-3 rounded-4 border border-indigo-500 border-opacity-25 text-indigo-400">
                <i class="fa-solid fa-robot fs-3" style="color:#a78bfa;"></i>
            </div>
            <div>
                <h4 class="text-light fw-bold mb-1">{{ $agent ? $agent->name : 'Default AI Agent' }}</h4>
                <div class="text-muted small">ID: <code>{{ $agent ? $agent->id : 'N/A' }}</code> &bull; Role: PeopleConnect Autonomous Engine</div>
            </div>
        </div>
        <div>
            <span class="badge {{ ($agent && $agent->status === 'active') ? 'bg-success bg-opacity-25 text-success border border-success border-opacity-25' : 'bg-warning bg-opacity-25 text-warning border border-warning border-opacity-25' }} px-3 py-2 rounded-pill fw-bold">
                <i class="fa-solid fa-circle me-1" style="font-size:0.5rem;"></i> {{ $agent ? strtoupper($agent->status) : 'STANDBY' }}
            </span>
        </div>
    </div>

    <!-- Tabs Header -->
    <ul class="nav nav-tabs nav-tabs-custom px-3 border-bottom border-secondary border-opacity-10" id="studioTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="persona-tab" data-bs-toggle="tab" data-bs-target="#persona" type="button" role="tab" aria-controls="persona" aria-selected="true">
                <i class="fa-solid fa-user-astronaut me-2"></i> Persona &amp; Behavior Studio
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="providers-tab" data-bs-toggle="tab" data-bs-target="#providers" type="button" role="tab" aria-controls="providers" aria-selected="false">
                <i class="fa-solid fa-server me-2"></i> Provider &amp; Model Inspector
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="fallbacks-tab" data-bs-toggle="tab" data-bs-target="#fallbacks" type="button" role="tab" aria-controls="fallbacks" aria-selected="false">
                <i class="fa-solid fa-shield-halved me-2"></i> 3-Tier Fallback Pipeline
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="keys-tab" data-bs-toggle="tab" data-bs-target="#keys" type="button" role="tab" aria-controls="keys" aria-selected="false">
                <i class="fa-solid fa-key me-2"></i> Multi-Key Rotation Engine
            </button>
        </li>
    </ul>

    <form id="agentSettingsForm" action="{{ route('hub.people-connect.agent-settings.save') }}" method="POST">
        @csrf
        <input type="hidden" name="agent_id" value="{{ $agent ? $agent->id : '' }}">

        <div class="tab-content p-4" id="studioTabsContent">
            
            {{-- ===== TAB 1: PERSONA & BEHAVIOR STUDIO ===== --}}
            <div class="tab-pane fade show active" id="persona" role="tabpanel" aria-labelledby="persona-tab">
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="glass-card mb-4">
                            <h5 class="text-light fw-bold mb-3"><i class="fa-solid fa-comment-dots text-purple me-2" style="color:#c084fc;"></i> System Prompt &amp; Persona Definition</h5>
                            <p class="text-muted small mb-3">Define how the AI agent communicates in WhatsApp conversations, its tone of voice, greeting habits, and internal logic constraints.</p>
                            <div class="mb-3">
                                <label class="form-label text-muted small fw-semibold">AGENT NAME</label>
                                <input type="text" name="name" class="form-control bg-dark border-secondary border-opacity-25 text-light" value="{{ $agent ? $agent->name : 'Souly AI' }}" required>
                            </div>
                            <div>
                                <label class="form-label text-muted small fw-semibold">SYSTEM PROMPT INSTRUCTIONS</label>
                                <textarea name="system_prompt" rows="12" class="form-control bg-dark border-secondary border-opacity-25 text-light font-monospace" style="font-size:0.88rem;">{{ $agent ? $agent->system_prompt : 'You are a professional, highly responsive AI executive assistant running inside PeopleConnect Hub...' }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="glass-card">
                            <h5 class="text-light fw-bold mb-3"><i class="fa-solid fa-sliders text-info me-2"></i> Hyperparameters &amp; Limits</h5>
                            <div class="mb-4">
                                <label class="form-label text-muted small fw-semibold d-flex justify-content-between">
                                    <span>TEMPERATURE (CREATIVITY)</span>
                                    <span id="tempValue" class="text-info fw-bold">{{ $agent ? ($agent->settings['temperature'] ?? '0.7') : '0.7' }}</span>
                                </label>
                                <input type="range" class="form-range" min="0" max="2" step="0.1" name="temperature" id="tempSlider" value="{{ $agent ? ($agent->settings['temperature'] ?? 0.7) : 0.7 }}" oninput="document.getElementById('tempValue').innerText=this.value">
                                <div class="d-flex justify-content-between text-muted" style="font-size:0.75rem;">
                                    <span>Precise (0.0)</span>
                                    <span>Balanced (0.7)</span>
                                    <span>Creative (2.0)</span>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label text-muted small fw-semibold">MAX GENERATION TOKENS</label>
                                <input type="number" name="max_tokens" class="form-control bg-dark border-secondary border-opacity-25 text-light" value="{{ $agent ? ($agent->settings['max_tokens'] ?? 2048) : 2048 }}" min="100" max="32000">
                                <div class="text-muted small mt-1" style="font-size:0.75rem;">Controls the upper limit of response length per WhatsApp message.</div>
                            </div>
                            <hr class="border-secondary border-opacity-10 my-4">
                            <h6 class="text-light fw-bold mb-2"><i class="fa-solid fa-toolbox me-2" style="color:#34d399;"></i> Active Agent Capabilities</h6>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" role="switch" id="toolWeb" checked disabled>
                                <label class="form-check-label text-muted small" for="toolWeb">Real-Time Firestore Sync Pipeline</label>
                            </div>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" role="switch" id="toolRotation" checked disabled>
                                <label class="form-check-label text-muted small" for="toolRotation">Auto-Exhaustion API Key Rotation</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="toolFallback" checked disabled>
                                <label class="form-check-label text-muted small" for="toolFallback">3-Tier Fallback Resilience Engine</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== TAB 2: PROVIDER & MODEL INSPECTOR ===== --}}
            <div class="tab-pane fade" id="providers" role="tabpanel" aria-labelledby="providers-tab">
                <div class="glass-card mb-4">
                    <h5 class="text-light fw-bold mb-3"><i class="fa-solid fa-circle-nodes text-primary me-2"></i> Connected AI Providers &amp; Active Models</h5>
                    <p class="text-muted small mb-4">Inspect configured LLM service providers, Base API URLs, active endpoint protocols, and available models currently registered in Nexus3.</p>
                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle mb-0" style="border-color: rgba(255,255,255,0.07);">
                            <thead>
                                <tr class="text-muted small">
                                    <th style="width:20%;">PROVIDER</th>
                                    <th style="width:25%;">API ENDPOINT URL</th>
                                    <th style="width:15%;">PROTOCOL TYPE</th>
                                    <th style="width:25%;">REGISTERED MODELS</th>
                                    <th style="width:15%;">STATUS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($providers as $prov)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-light">{{ $prov->name }}</div>
                                        <div class="text-muted small">{{ $prov->slug ?? $prov->id }}</div>
                                    </td>
                                    <td><code class="text-info bg-dark px-2 py-1 rounded">{{ $prov->api_url ?? 'Default Cloud URL' }}</code></td>
                                    <td><span class="badge bg-secondary bg-opacity-25 text-secondary border border-secondary border-opacity-25">{{ strtoupper($prov->driver_type ?? 'REST / OPENAI') }}</span></td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            @forelse($prov->models as $m)
                                            <span class="badge bg-dark border border-secondary border-opacity-25 text-light small">{{ $m->name }}</span>
                                            @empty
                                            <span class="text-muted small">No active models</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td>
                                        @if(($prov->status ?? 'active') === 'active')
                                            <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-25"><i class="fa-solid fa-check me-1"></i> ACTIVE</span>
                                        @else
                                            <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-25">INACTIVE</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No AI Providers found in system database.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ===== TAB 3: 3-TIER FALLBACK PIPELINE ===== --}}
            <div class="tab-pane fade" id="fallbacks" role="tabpanel" aria-labelledby="fallbacks-tab">
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <div class="glass-card mb-4 text-center">
                            <h5 class="text-light fw-bold mb-2"><i class="fa-solid fa-shield-cat text-warning me-2"></i> 3-Tier Sequential Fallback Resilience Pipeline</h5>
                            <p class="text-muted small mx-auto mb-4" style="max-width: 650px;">If the primary AI model hits API rate limits, quota depletion, or regional outages, Nexus automatically redirects the execution request to subsequent fallback tiers with zero downtime.</p>
                            
                            <!-- Tier 0: Primary Model -->
                            <div class="fallback-node text-start mx-auto mb-2" style="max-width: 650px; border-color: rgba(16, 185, 129, 0.5);">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-success text-dark fw-bold">PRIMARY TIER (DEFAULT)</span>
                                    <span class="text-muted small"><i class="fa-solid fa-star text-warning"></i> Highest Priority</span>
                                </div>
                                <label class="form-label text-muted small fw-semibold">PRIMARY AI MODEL</label>
                                <select name="primary_model_id" class="form-select bg-dark border-secondary border-opacity-50 text-light fw-bold">
                                    <option value="">-- Use Default System Resolved Model --</option>
                                    @foreach($models as $model)
                                        <option value="{{ $model->id }}" {{ ($agent && ($agent->settings['model_id'] ?? '') == $model->id) ? 'selected' : '' }}>
                                            {{ $model->name }} (Provider: {{ $model->provider->name ?? 'None' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="fallback-connector"></div>

                            <!-- Tier 1: First Fallback -->
                            <div class="fallback-node text-start mx-auto mb-2" style="max-width: 650px; border-color: rgba(59, 130, 246, 0.4);">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-primary text-light fw-bold">FALLBACK TIER #1</span>
                                    <span class="text-muted small">Triggered on 429 / 500 Provider Failure</span>
                                </div>
                                <label class="form-label text-muted small fw-semibold">SELECT 1ST FALLBACK MODEL</label>
                                <select name="fallback_models[]" class="form-select bg-dark border-secondary border-opacity-50 text-light">
                                    <option value="">-- None (Disable Tier 1 Fallback) --</option>
                                    @foreach($models as $model)
                                        <option value="{{ $model->id }}" {{ ($agent && !empty($agent->settings['fallback_models'][0]) && $agent->settings['fallback_models'][0] == $model->id) ? 'selected' : '' }}>
                                            {{ $model->name }} (Provider: {{ $model->provider->name ?? 'None' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="fallback-connector"></div>

                            <!-- Tier 2: Second Fallback -->
                            <div class="fallback-node text-start mx-auto mb-2" style="max-width: 650px; border-color: rgba(139, 92, 246, 0.4);">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-indigo-500 bg-opacity-75 text-light fw-bold" style="background:#8b5cf6;">FALLBACK TIER #2</span>
                                    <span class="text-muted small">Triggered if Tier #1 fails</span>
                                </div>
                                <label class="form-label text-muted small fw-semibold">SELECT 2ND FALLBACK MODEL</label>
                                <select name="fallback_models[]" class="form-select bg-dark border-secondary border-opacity-50 text-light">
                                    <option value="">-- None (Disable Tier 2 Fallback) --</option>
                                    @foreach($models as $model)
                                        <option value="{{ $model->id }}" {{ ($agent && !empty($agent->settings['fallback_models'][1]) && $agent->settings['fallback_models'][1] == $model->id) ? 'selected' : '' }}>
                                            {{ $model->name }} (Provider: {{ $model->provider->name ?? 'None' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="fallback-connector"></div>

                            <!-- Tier 3: Third Fallback -->
                            <div class="fallback-node text-start mx-auto" style="max-width: 650px; border-color: rgba(239, 68, 68, 0.4);">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-danger text-light fw-bold">FALLBACK TIER #3 (LAST RESORT)</span>
                                    <span class="text-muted small">Final safety net before error dispatch</span>
                                </div>
                                <label class="form-label text-muted small fw-semibold">SELECT 3RD FALLBACK MODEL</label>
                                <select name="fallback_models[]" class="form-select bg-dark border-secondary border-opacity-50 text-light">
                                    <option value="">-- None (Disable Tier 3 Fallback) --</option>
                                    @foreach($models as $model)
                                        <option value="{{ $model->id }}" {{ ($agent && !empty($agent->settings['fallback_models'][2]) && $agent->settings['fallback_models'][2] == $model->id) ? 'selected' : '' }}>
                                            {{ $model->name }} (Provider: {{ $model->provider->name ?? 'None' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== TAB 4: MULTI-KEY ROTATION ENGINE ===== --}}
            <div class="tab-pane fade" id="keys" role="tabpanel" aria-labelledby="keys-tab">
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="glass-card">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="text-light fw-bold mb-0"><i class="fa-solid fa-key text-success me-2"></i> Active Rotation Pools &amp; Cooldown Tracking</h5>
                                <span class="badge bg-secondary bg-opacity-25 text-light border border-secondary border-opacity-25">Round-Robin LRU Algorithm</span>
                            </div>
                            <p class="text-muted small mb-4">When a key encounters rate limits (HTTP 429), budget exhaustion (402), or auth blocks (403), the engine tags it with a temporary cooldown and seamlessly promotes the next available key in the pool.</p>
                            
                            <div class="d-flex flex-column gap-3">
                                @forelse($apiKeys as $key)
                                    @php
                                        $inCooldown = $key->cooldown_until && \Carbon\Carbon::parse($key->cooldown_until)->isFuture();
                                    @endphp
                                    <div class="key-item d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="fs-4 text-muted"><i class="fa-solid fa-shield-halved"></i></div>
                                            <div>
                                                <div class="d-flex align-items-center gap-2 mb-1">
                                                    <span class="text-light fw-bold">{{ $key->provider->name ?? 'Unknown Provider' }}</span>
                                                    <span class="text-muted small">(&bull;&bull;&bull;&bull;&bull;&bull;{{ substr($key->api_key, -4) }})</span>
                                                    @if($inCooldown)
                                                        <span class="badge badge-cooldown small"><i class="fa-solid fa-clock me-1"></i> COOLDOWN UNTIL {{ \Carbon\Carbon::parse($key->cooldown_until)->format('H:i') }}</span>
                                                    @else
                                                        <span class="badge badge-active-key small"><i class="fa-solid fa-check me-1"></i> ACTIVE IN POOL</span>
                                                    @endif
                                                </div>
                                                <div class="text-muted" style="font-size:0.75rem;">
                                                    Errors Logged: <strong class="text-warning">{{ $key->error_count ?? 0 }}</strong> &bull; 
                                                    Last Used: <strong>{{ $key->last_used_at ? \Carbon\Carbon::parse($key->last_used_at)->diffForHumans() : 'Never' }}</strong>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2">
                                            @if($inCooldown)
                                            <button type="button" class="btn btn-sm btn-outline-success" onclick="manageKeyRotation('release_key', '{{ $key->provider_id }}', '{{ $key->id }}')" title="Release Cooldown Now">
                                                <i class="fa-solid fa-unlock"></i> Release
                                            </button>
                                            @else
                                            <button type="button" class="btn btn-sm btn-outline-warning" onclick="manageKeyRotation('set_cooldown', '{{ $key->provider_id }}', '{{ $key->id }}', 60)" title="Simulate 60m Cooldown Flag">
                                                <i class="fa-solid fa-clock"></i> Pause 60m
                                            </button>
                                            @endif
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="manageKeyRotation('revoke_key', '{{ $key->provider_id }}', '{{ $key->id }}')" title="Revoke &amp; Delete Key">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-5 text-muted">No API Keys registered in the encrypted store. Use the sidebar to add keys to the pool!</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="glass-card">
                            <h5 class="text-light fw-bold mb-3"><i class="fa-solid fa-plus-circle text-success me-2"></i> Add Key to Rotation Pool</h5>
                            <p class="text-muted small mb-3">Add secondary or fallback API keys for any connected provider to ensure uninterruptible 24/7 AI uptime.</p>
                            <div class="mb-3">
                                <label class="form-label text-muted small fw-semibold">TARGET AI PROVIDER</label>
                                <select id="addKeyProviderId" class="form-select bg-dark border-secondary border-opacity-50 text-light">
                                    @foreach($providers as $prov)
                                        <option value="{{ $prov->id }}">{{ $prov->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted small fw-semibold">KEY LABEL / IDENTIFIER</label>
                                <input type="text" id="addKeyName" class="form-control bg-dark border-secondary border-opacity-50 text-light" placeholder="e.g. Gemini Backup Key #2">
                            </div>
                            <div class="mb-4">
                                <label class="form-label text-muted small fw-semibold">ENCRYPTED API KEY STRING</label>
                                <input type="password" id="addKeySecret" class="form-control bg-dark border-secondary border-opacity-50 text-light" placeholder="Paste secret key starting with AIza... or sk-...">
                            </div>
                            <button type="button" class="btn btn-success w-100 fw-bold" onclick="addNewApiKey()">
                                <i class="fa-solid fa-plus me-1"></i> Encrypt &amp; Add to Pool
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

@push('scripts')
<script>
function manageKeyRotation(action, providerId, keyId = '', cooldownMinutes = 60) {
    if (action === 'revoke_key' && !confirm('Are you sure you want to delete this API key from the rotation pool?')) return;
    
    fetch('{{ route("hub.people-connect.agent-settings.key-rotation") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            provider_id: providerId,
            action: action,
            key_id: keyId,
            cooldown_minutes: cooldownMinutes
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Action failed'));
        }
    })
    .catch(e => {
        alert('Request error: ' + e.message);
    });
}

function addNewApiKey() {
    const providerId = document.getElementById('addKeyProviderId').value;
    const keyName = document.getElementById('addKeyName').value.trim();
    const apiKey = document.getElementById('addKeySecret').value.trim();
    
    if (!apiKey) {
        alert('Please enter an API Key secret string.');
        return;
    }
    
    fetch('{{ route("hub.people-connect.agent-settings.key-rotation") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            provider_id: providerId,
            action: 'add_key',
            key_name: keyName || 'Backup Rotation Key',
            api_key: apiKey
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error adding key: ' + (data.message || 'Unknown error'));
        }
    });
}
</script>
@endpush
@endsection
