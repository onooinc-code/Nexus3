<!-- Interactive Model Test Prompt Modal -->
<div class="modal fade" id="promptTestModal" tabindex="-1" aria-labelledby="promptTestModalLabel" aria-hidden="true" style="z-index: 1060 !important;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark text-light border border-secondary shadow-lg">
            <div class="modal-header border-bottom border-secondary py-3">
                <h5 class="modal-header-title fw-bold text-light mb-0 d-flex align-items-center gap-2" id="promptTestModalLabel">
                    <i class="fa-solid fa-vial text-info"></i> Test AI Model Inference
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="modal-prompt-test-form">
                    <input type="hidden" id="test-model-provider-id" name="provider_id">
                    <input type="hidden" id="test-model-id" name="model_id">

                    <div class="d-flex align-items-center justify-content-between mb-3 bg-black bg-opacity-25 p-3 rounded border border-secondary">
                        <div>
                            <span class="text-muted small d-block">Target Model</span>
                            <span class="fw-bold text-info fs-5" id="test-model-name-display">Model Name</span>
                        </div>
                        <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-25 px-3 py-2" id="test-provider-name-display">Provider</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">Test Prompt / Instructions</label>
                        <textarea id="test-prompt-input" name="message" class="form-control bg-dark text-light border-secondary" rows="4" placeholder="Enter prompt to execute (e.g. Explain quantum computing in 2 simple sentences)..." required></textarea>
                    </div>

                    <div class="text-end mb-4">
                        <button type="submit" class="btn btn-info px-4 fw-bold" id="btn-execute-prompt">
                            <i class="fa-solid fa-paper-plane me-1"></i> Execute Prompt
                        </button>
                    </div>
                </form>

                <!-- Response Display Box -->
                <div class="d-none" id="test-prompt-response-container">
                    <div class="border-top border-secondary pt-3">
                        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                            <h6 class="text-success fw-bold mb-0 d-flex align-items-center gap-2">
                                <i class="fa-solid fa-robot"></i> Execution Response & Result
                            </h6>
                            <div class="d-flex align-items-center gap-2" id="test-response-metrics">
                                <span class="badge bg-secondary font-mono" id="metric-latency">0ms</span>
                            </div>
                        </div>
                        <div class="position-relative">
                            <button type="button" class="btn btn-sm btn-outline-secondary position-absolute top-0 end-0 m-2 py-1 px-2 text-muted" id="btn-copy-test-response" onclick="navigator.clipboard.writeText($('#test-prompt-response-text').text()); if(window.Nexus&&window.Nexus.notify) window.Nexus.notify('Copied to clipboard!', 'info');" title="Copy Response">
                                <i class="fa-solid fa-copy me-1"></i> Copy
                            </button>
                            <div class="card bg-black border border-secondary p-3 text-light font-mono small" id="test-prompt-response-text" style="white-space: pre-wrap; min-height: 140px; max-height: 320px; overflow-y: auto;">
                                <i class="fa-solid fa-spinner fa-spin me-2 text-info"></i> Processing inference...
                            </div>
                        </div>

                        <!-- Execution Result Details Box -->
                        <div class="mt-3 p-3 bg-black bg-opacity-40 rounded border border-secondary" id="test-execution-proof-box">
                            <div class="row g-2 text-center">
                                <div class="col-3 border-end border-secondary">
                                    <span class="text-muted d-block" style="font-size: 0.75rem;">Status</span>
                                    <span class="fw-bold" id="proof-status-badge"><span class="badge bg-success">Success</span></span>
                                </div>
                                <div class="col-3 border-end border-secondary">
                                    <span class="text-muted d-block" style="font-size: 0.75rem;">Input / Output</span>
                                    <span class="fw-bold text-info font-mono" id="proof-tokens" style="font-size: 0.85rem;">0 / 0 tokens</span>
                                </div>
                                <div class="col-3 border-end border-secondary">
                                    <span class="text-muted d-block" style="font-size: 0.75rem;">Est. Cost</span>
                                    <span class="fw-bold text-success font-mono" id="proof-cost" style="font-size: 0.85rem;">$0.000000</span>
                                </div>
                                <div class="col-3">
                                    <span class="text-muted d-block" style="font-size: 0.75rem;">Latency</span>
                                    <span class="fw-bold text-warning font-mono" id="proof-latency" style="font-size: 0.85rem;">0ms</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top border-secondary py-2">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Close Modal</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    window.openTestPromptModal = function(providerId, modelId, modelName, providerName = '') {
        $('#test-model-provider-id').val(providerId);
        $('#test-model-id').val(modelId);
        $('#test-model-name-display').text(modelName);
        $('#test-provider-name-display').text(providerName || 'Provider');
        $('#test-prompt-input').val('');
        $('#test-prompt-response-container').addClass('d-none');
        $('#test-prompt-response-text').text('');

        const modalEl = document.getElementById('promptTestModal');
        if (modalEl && modalEl.parentNode !== document.body) {
            document.body.appendChild(modalEl);
        }
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    };

    $(document).ready(function() {
        $('#modal-prompt-test-form').on('submit', function(e) {
            e.preventDefault();
            const btn = $('#btn-execute-prompt');
            btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Executing...');
            
            $('#test-prompt-response-container').removeClass('d-none');
            $('#test-prompt-response-text').html('<i class="fa-solid fa-spinner fa-spin me-2 text-info"></i> Running model inference...');

            const start = Date.now();
            $.ajax({
                url: '/hub/models/playground/chat',
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                data: $(this).serialize(),
                success: function(res) {
                    btn.prop('disabled', false).html('<i class="fa-solid fa-paper-plane me-1"></i> Execute Prompt');
                    const elapsed = Date.now() - start;

                    if (res.success && res.data) {
                        const d = res.data;
                        const output = d.response || 'No text output returned.';
                        const latency = d.latency_ms || elapsed;
                        const inTok = d.input_tokens || 0;
                        const outTok = d.output_tokens || 0;
                        const cost = d.total_cost ? `$${d.total_cost}` : '$0.000000';
                        const isLive = d.is_live;

                        $('#test-prompt-response-text').text(output);
                        
                        $('#test-response-metrics').html(`
                            <span class="badge ${isLive ? 'bg-success' : 'bg-info'} text-dark"><i class="fa-solid ${isLive ? 'fa-bolt' : 'fa-flask'} me-1"></i>${isLive ? 'Live API' : 'Engine'}</span>
                            <span class="badge bg-secondary font-mono">${latency}ms</span>
                        `);

                        $('#proof-status-badge').html(`<span class="badge ${isLive ? 'bg-success' : 'bg-info'}">${isLive ? 'Live API' : 'Completed'}</span>`);
                        $('#proof-tokens').text(`${inTok} in / ${outTok} out`);
                        $('#proof-cost').text(cost);
                        $('#proof-latency').text(`${latency}ms`);

                        if (window.Nexus && window.Nexus.notify) {
                            window.Nexus.notify('Prompt executed successfully!', 'success');
                        }
                    } else {
                        const errorMsg = res.message || 'Model execution failed.';
                        $('#test-prompt-response-text').text(errorMsg);
                        $('#proof-status-badge').html('<span class="badge bg-danger">Failed</span>');
                        if (window.Nexus && window.Nexus.notify) {
                            window.Nexus.notify(errorMsg, 'error');
                        }
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="fa-solid fa-paper-plane me-1"></i> Execute Prompt');
                    const res = xhr.responseJSON;
                    const msg = (res && res.message) ? res.message : 'Execution error.';
                    $('#test-prompt-response-text').text(msg);
                    $('#proof-status-badge').html('<span class="badge bg-danger">Error</span>');
                    if (window.Nexus && window.Nexus.notify) {
                        window.Nexus.notify(msg, 'error');
                    }
                }
            });
        });
    });
</script>
@endpush
