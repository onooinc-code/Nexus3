{{-- Add Provider Drawer --}}
<div class="offcanvas offcanvas-end bg-dark border-start border-secondary text-light" tabindex="-1" id="addProviderOffcanvas" style="width: 500px;">
    <div class="offcanvas-header border-bottom border-secondary">
        <h5 class="offcanvas-title fw-bold"><i class="fa-solid fa-server me-2"></i>Add Provider</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-4">
        <form id="addProviderForm">

            {{-- Step 1: Presets --}}
            <h6 class="text-muted fw-bold mb-3">1. Quick Preset</h6>
            <div class="mb-3">
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary provider-preset-btn active" data-preset="openai">
                        <i class="fa-brands fa-openai me-1"></i>OpenAI
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary provider-preset-btn" data-preset="anthropic">
                        Anthropic
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary provider-preset-btn" data-preset="google">
                        <i class="fa-brands fa-google me-1"></i>Google
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary provider-preset-btn" data-preset="mistral">
                        Mistral
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary provider-preset-btn" data-preset="custom">
                        <i class="fa-solid fa-code me-1"></i>Custom
                    </button>
                </div>
            </div>

            <hr class="border-secondary my-3">

            {{-- Step 2: Identity --}}
            <h6 class="text-muted fw-bold mb-3">2. Identity</h6>
            <div class="mb-3">
                <label class="form-label small">Provider Name <span class="text-danger">*</span></label>
                <input type="text" id="providerName" class="form-control bg-dark border-secondary text-light" value="OpenAI" placeholder="e.g. My Custom Provider">
            </div>

            <div class="mb-3">
                <label class="form-label small">Base URL <span class="text-danger">*</span></label>
                <input type="text" id="providerBaseUrl" class="form-control bg-dark border-secondary text-light" value="https://api.openai.com/v1">
            </div>

            <div class="mb-3">
                <label class="form-label small">Models Fetch Endpoint</label>
                <div class="input-group">
                    <input type="text" id="providerModelsEndpoint" class="form-control bg-dark border-secondary text-light" value="/models">
                    <button class="btn btn-outline-info" type="button" id="btnTestDrawerConnection" title="Test connection">
                        <i class="fa-solid fa-plug me-1"></i>Test
                    </button>
                </div>
                <div class="form-text text-muted" style="font-size: 0.7rem;">Relative path appended to Base URL (e.g. /models)</div>
            </div>
            <div id="test-connection-result" class="alert py-2 d-none" style="font-size: 0.8rem;"></div>

            <hr class="border-secondary my-3">

            {{-- Step 3: Authentication --}}
            <h6 class="text-muted fw-bold mb-3">3. Authentication</h6>
            <div class="mb-3">
                <label class="form-label small">Auth Format</label>
                <select id="providerAuthFormat" class="form-select bg-dark border-secondary text-light">
                    <option value="Bearer {key}" selected>Bearer {key} (OpenAI / Standard)</option>
                    <option value="x-api-key: {key}">x-api-key: {key} (Anthropic)</option>
                    <option value="x-goog-api-key: {key}">x-goog-api-key: {key} (Google AI Studio)</option>
                    <option value="Authorization: Bearer {key}">Authorization: Bearer {key}</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label small">API Key <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="password" id="providerApiKey" class="form-control bg-dark border-secondary text-light" placeholder="sk-...">
                    <button class="btn btn-outline-secondary toggle-eye-btn" type="button" data-target="providerApiKey">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" id="providerApiKeyDefault" checked>
                    <label class="form-check-label text-muted small" for="providerApiKeyDefault">
                        Set as default API key for this provider
                    </label>
                </div>
            </div>

        </form>
    </div>
    <div class="offcanvas-footer border-top border-secondary p-3 bg-dark d-flex justify-content-end gap-2" style="padding-bottom: 60px !important;">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="offcanvas">Cancel</button>
        <button type="button" id="btnSaveProvider" class="btn btn-primary">
            <i class="fa-solid fa-floppy-disk me-1"></i>Save Provider
        </button>
    </div>
</div>

@push('scripts')
<script>
// ─── Provider Presets ─────────────────────────────────────────────────────────
const PROVIDER_PRESETS = {
    openai: {
        name: 'OpenAI',
        base_url: 'https://api.openai.com/v1',
        models_endpoint: '/models',
        auth_format: 'Bearer {key}',
    },
    anthropic: {
        name: 'Anthropic',
        base_url: 'https://api.anthropic.com/v1',
        models_endpoint: '/models',
        auth_format: 'x-api-key: {key}',
    },
    google: {
        name: 'Google AI Studio',
        base_url: 'https://generativelanguage.googleapis.com/v1beta',
        models_endpoint: '/models',
        auth_format: 'x-goog-api-key: {key}',
    },
    mistral: {
        name: 'Mistral AI',
        base_url: 'https://api.mistral.ai/v1',
        models_endpoint: '/models',
        auth_format: 'Bearer {key}',
    },
    custom: {
        name: '',
        base_url: '',
        models_endpoint: '/models',
        auth_format: 'Bearer {key}',
    },
};

$(document).ready(function() {
    // Toggle eye (show/hide password)
    $(document).on('click', '.toggle-eye-btn', function() {
        const targetId = $(this).data('target');
        const input = $('#' + targetId);
        const isPassword = input.attr('type') === 'password';
        input.attr('type', isPassword ? 'text' : 'password');
        $(this).find('i').toggleClass('fa-eye fa-eye-slash');
    });

    // ─── Preset Buttons ───────────────────────────────────────────────────────
    $(document).on('click', '.provider-preset-btn', function() {
        $('.provider-preset-btn').removeClass('active btn-secondary').addClass('btn-outline-secondary');
        $(this).addClass('active btn-secondary').removeClass('btn-outline-secondary');

        const preset = $(this).data('preset');
        const config = PROVIDER_PRESETS[preset];
        if (!config) return;

        $('#providerName').val(config.name);
        $('#providerBaseUrl').val(config.base_url);
        $('#providerModelsEndpoint').val(config.models_endpoint);
        $('#providerAuthFormat').val(config.auth_format);

        // Clear test result
        $('#test-connection-result').addClass('d-none').text('');

        if (preset === 'custom') {
            $('#providerName').focus();
        }
    });

    // ─── Test Connection ──────────────────────────────────────────────────────
    $('#btnTestDrawerConnection').on('click', function() {
        const btn = $(this);
        const baseUrl = $('#providerBaseUrl').val().trim();
        const apiKey  = $('#providerApiKey').val().trim();
        const authFmt = $('#providerAuthFormat').val();
        const endpoint= $('#providerModelsEndpoint').val().trim();

        if (!baseUrl) {
            window.Nexus.notify('Please enter a Base URL first.', 'error');
            return;
        }

        btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i>Testing...');
        $('#test-connection-result').addClass('d-none');

        $.ajax({
            url: '{{ route("hub.models.providers.ping") }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            data: {
                base_url: baseUrl,
                api_key: apiKey || null,
                auth_header_format: authFmt,
                models_fetch_endpoint: endpoint,
            },
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fa-solid fa-plug me-1"></i>Test');
                const latency = res.data && res.data.latency ? ` — ${res.data.latency}ms` : '';
                if (res.success) {
                    $('#test-connection-result')
                        .removeClass('d-none alert-danger')
                        .addClass('alert-success')
                        .html(`<i class="fa-solid fa-check-circle me-1"></i>${res.message}${latency}`);
                } else {
                    $('#test-connection-result')
                        .removeClass('d-none alert-success')
                        .addClass('alert-danger')
                        .html(`<i class="fa-solid fa-times-circle me-1"></i>${res.message || 'Connection failed.'}`);
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fa-solid fa-plug me-1"></i>Test');
                const res = xhr.responseJSON;
                let msg = 'Connection test failed.';
                if (res && res.errors) {
                    msg = Object.values(res.errors).flat().join(' ');
                } else if (res && res.message) {
                    msg = res.message;
                }
                $('#test-connection-result')
                    .removeClass('d-none alert-success')
                    .addClass('alert-danger')
                    .html(`<i class="fa-solid fa-times-circle me-1"></i>${msg}`);
            }
        });
    });

    // ─── Save Provider ────────────────────────────────────────────────────────
    $('#btnSaveProvider').on('click', function() {
        const name    = $('#providerName').val().trim();
        const baseUrl = $('#providerBaseUrl').val().trim();
        const apiKey  = $('#providerApiKey').val().trim();

        if (!name) { window.Nexus.notify('Provider Name is required.', 'error'); return; }
        if (!baseUrl) { window.Nexus.notify('Base URL is required.', 'error'); return; }
        if (!apiKey) { window.Nexus.notify('API Key is required.', 'error'); return; }

        window.Nexus.showTaskLoader('Saving...', 'Creating provider...');
        $.ajax({
            url: '/api/v1/ai/providers',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            data: JSON.stringify({
                name: name,
                base_url: baseUrl,
                models_fetch_endpoint: $('#providerModelsEndpoint').val().trim() || '/models',
                auth_header_format: $('#providerAuthFormat').val(),
                api_key: apiKey,
                is_active: true,
            }),
            success: function(res) {
                window.Nexus.hideTaskLoader();
                if (res.success) {
                    window.Nexus.notify('Provider created successfully!', 'success');
                    bootstrap.Offcanvas.getInstance(document.getElementById('addProviderOffcanvas')).hide();
                    setTimeout(() => location.reload(), 800);
                } else {
                    window.Nexus.notify(res.message || 'Failed to save provider.', 'error');
                }
            },
            error: function(xhr) {
                window.Nexus.hideTaskLoader();
                const res = xhr.responseJSON;
                let msg = 'Failed to save provider.';
                if (res && res.errors) {
                    msg = Object.values(res.errors).flat().join(' ');
                } else if (res && res.message) {
                    msg = res.message;
                }
                window.Nexus.notify(msg, 'error');
            }
        });
    });
});
</script>
@endpush
