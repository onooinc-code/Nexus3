{{-- Edit Provider Drawer --}}
<div class="offcanvas offcanvas-end bg-dark border-start border-secondary text-light" tabindex="-1" id="editProviderOffcanvas" style="width: 500px;">
    <div class="offcanvas-header border-bottom border-secondary">
        <h5 class="offcanvas-title fw-bold"><i class="fa-solid fa-pen-to-square me-2"></i>Edit Provider</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-4">
        <form id="editProviderForm">
            <input type="hidden" id="editProviderId">

            <h6 class="text-muted fw-bold mb-3">1. Identity</h6>
            <div class="mb-3">
                <label class="form-label small">Provider Name <span class="text-danger">*</span></label>
                <input type="text" id="editProviderName" class="form-control bg-dark border-secondary text-light" placeholder="e.g. Google AI Studio">
            </div>

            <div class="mb-3">
                <label class="form-label small">Base URL <span class="text-danger">*</span></label>
                <input type="text" id="editProviderBaseUrl" class="form-control bg-dark border-secondary text-light" placeholder="https://api.openai.com/v1">
            </div>

            <div class="mb-3">
                <label class="form-label small">Models Fetch Endpoint</label>
                <input type="text" id="editProviderModelsEndpoint" class="form-control bg-dark border-secondary text-light" placeholder="/models">
                <div class="form-text text-muted" style="font-size: 0.7rem;">Relative path appended to Base URL (e.g. /models)</div>
            </div>

            <hr class="border-secondary my-4">

            <h6 class="text-muted fw-bold mb-3">2. Authentication</h6>
            <div class="mb-3">
                <label class="form-label small">Auth Format</label>
                <select id="editProviderAuthFormat" class="form-select bg-dark border-secondary text-light">
                    <option value="Bearer {key}">Bearer {key} (OpenAI / Standard)</option>
                    <option value="x-api-key: {key}">x-api-key: {key} (Anthropic)</option>
                    <option value="x-goog-api-key: {key}">x-goog-api-key: {key} (Google AI Studio)</option>
                    <option value="Authorization: Bearer {key}">Authorization: Bearer {key}</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label small">New API Key <span class="text-muted">(leave blank to keep current)</span></label>
                <div class="input-group">
                    <input type="password" id="editProviderApiKey" class="form-control bg-dark border-secondary text-light" placeholder="Enter new key to replace...">
                    <button class="btn btn-outline-secondary toggle-eye-btn" type="button" data-target="editProviderApiKey">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
    <div class="offcanvas-footer border-top border-secondary p-3 bg-dark d-flex justify-content-end gap-2" style="padding-bottom: 60px !important;">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="offcanvas">Cancel</button>
        <button type="button" id="btnUpdateProvider" class="btn btn-primary">
            <i class="fa-solid fa-floppy-disk me-1"></i>Save Changes
        </button>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Toggle eye (show/hide password)
    $(document).on('click', '.toggle-eye-btn', function() {
        const targetId = $(this).data('target');
        const input = $('#' + targetId);
        const isPassword = input.attr('type') === 'password';
        input.attr('type', isPassword ? 'text' : 'password');
        $(this).find('i').toggleClass('fa-eye fa-eye-slash');
    });

    // Save Changes
    $('#btnUpdateProvider').on('click', function() {
        const id = $('#editProviderId').val();
        if (!id) { window.Nexus.notify('Provider ID missing.', 'error'); return; }

        const name = $('#editProviderName').val().trim();
        const baseUrl = $('#editProviderBaseUrl').val().trim();
        if (!name || !baseUrl) {
            window.Nexus.notify('Provider Name and Base URL are required.', 'error');
            return;
        }

        const payload = {
            name: name,
            base_url: baseUrl,
            models_fetch_endpoint: $('#editProviderModelsEndpoint').val().trim() || '/models',
            auth_header_format: $('#editProviderAuthFormat').val(),
        };

        const newKey = $('#editProviderApiKey').val().trim();
        if (newKey) {
            payload.api_key = newKey;
        }

        window.Nexus.showTaskLoader('Saving...', 'Updating provider...');
        $.ajax({
            url: `/api/v1/ai/providers/${id}`,
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            data: JSON.stringify(payload),
            success: function(res) {
                window.Nexus.hideTaskLoader();
                if (res.success) {
                    window.Nexus.notify(res.message || 'Provider updated successfully.', 'success');
                    bootstrap.Offcanvas.getInstance(document.getElementById('editProviderOffcanvas')).hide();
                    setTimeout(() => location.reload(), 800);
                } else {
                    window.Nexus.notify(res.message || 'Update failed.', 'error');
                }
            },
            error: function(xhr) {
                window.Nexus.hideTaskLoader();
                const res = xhr.responseJSON;
                let msg = 'Update failed.';
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
