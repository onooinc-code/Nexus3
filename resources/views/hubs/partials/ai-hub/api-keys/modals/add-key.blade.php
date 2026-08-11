<!-- Add Key Modal -->
<div class="modal fade" id="addKeyModal" tabindex="-1" aria-hidden="true" style="z-index: 1060 !important;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-dark border-secondary text-light shadow-lg">
            <div class="modal-header border-bottom border-secondary px-4 py-3">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2">
                    <i class="fa-solid fa-key text-primary"></i> Add & Encrypt New API Key
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="add-api-key-form">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-bold">Select AI Provider <span class="text-danger">*</span></label>
                            <select name="provider_id" id="key-provider-select" class="form-select bg-dark text-light border-secondary" required>
                                <option value="" disabled selected>-- Select Provider --</option>
                                @foreach($providers as $provider)
                                    <option value="{{ $provider->id }}">{{ $provider->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-bold">Key Label / Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control bg-dark text-light border-secondary" placeholder="e.g. Production Key #1" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-secondary small fw-bold">API Key Value (AES-256 Encrypted) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" name="api_key" id="key-value-input" class="form-control bg-dark text-light border-secondary font-monospace" placeholder="sk-proj-..." required autocomplete="off">
                                <button class="btn btn-outline-secondary text-muted" type="button" onclick="toggleKeyVisibility()">
                                    <i class="fa-solid fa-eye" id="toggle-key-eye"></i>
                                </button>
                            </div>
                            <div class="form-text text-muted small mt-2">
                                <i class="fa-solid fa-shield-halved text-success me-1"></i> Your key will be encrypted at rest using AES-256-CBC and will never be returned in plaintext.
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-bold">Expiration Date (Optional)</label>
                            <input type="date" name="expires_at" class="form-control bg-dark text-light border-secondary">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-bold">Rotation Priority</label>
                            <input type="number" name="priority" class="form-control bg-dark text-light border-secondary" value="1" min="1" max="100">
                        </div>
                        <div class="col-12 mt-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_default" id="is_default_key" value="1">
                                <label class="form-check-label text-light small fw-bold" for="is_default_key">Set as Primary Default Key for this Provider</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary px-4 py-3">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 d-flex align-items-center gap-2" id="submit-key-btn">
                        <i class="fa-solid fa-lock"></i> Save & Encrypt Key
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function toggleKeyVisibility() {
        const input = document.getElementById('key-value-input');
        const icon = document.getElementById('toggle-key-eye');
        if (!input || !icon) return;
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

    $(document).ready(function() {
        // Ensure modal is moved to document.body so it isn't trapped inside tab container or backdrop
        const modalEl = document.getElementById('addKeyModal');
        if (modalEl && modalEl.parentNode !== document.body) {
            document.body.appendChild(modalEl);
        }

        $('#add-api-key-form').on('submit', function(e) {
            e.preventDefault();
            const btn = $('#submit-key-btn');
            btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-2"></i> Encrypting...');

            $.ajax({
                url: '/hub/models/api-keys',
                method: 'POST',
                data: $(this).serialize(),
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                success: function(res) {
                    btn.prop('disabled', false).html('<i class="fa-solid fa-lock"></i> Save & Encrypt Key');
                    if (res.success) {
                        if (window.Nexus && window.Nexus.notify) {
                            window.Nexus.notify('API Key saved and encrypted successfully.', 'success');
                        }
                        const modalEl = document.getElementById('addKeyModal');
                        if (modalEl) {
                            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                            modal.hide();
                        }
                        $('#add-api-key-form')[0].reset();
                        const keyInput = document.getElementById('key-value-input');
                        if (keyInput) keyInput.type = 'password';
                        const icon = document.getElementById('toggle-key-eye');
                        if (icon) icon.className = 'fa-solid fa-eye';

                        setTimeout(() => location.reload(), 800);
                    } else {
                        if (window.Nexus && window.Nexus.notify) {
                            window.Nexus.notify(res.message || 'Failed to store API Key.', 'error');
                        }
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="fa-solid fa-lock"></i> Save & Encrypt Key');
                    const res = xhr.responseJSON;
                    let msg = 'Failed to store API Key.';
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
    });
</script>
@endpush
