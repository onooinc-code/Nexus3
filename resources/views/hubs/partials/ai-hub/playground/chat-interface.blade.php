<!-- Chat Tester -->
<div class="tab-pane fade show active h-100" id="play-chat">
    <div class="row g-0 h-100 border border-secondary rounded overflow-hidden" style="min-height: 600px;">
        <!-- Left Sidebar -->
        <div class="col-md-3 bg-dark border-end border-secondary p-3 overflow-auto" style="max-height: 600px;">
            <div class="mb-3">
                <label class="form-label small text-muted">Routing Method</label>
                <div class="btn-group w-100" role="group">
                    <input type="radio" class="btn-check" name="routeMethod" id="routeDirect" autocomplete="off" checked>
                    <label class="btn btn-outline-secondary btn-sm" for="routeDirect">Direct Model</label>

                    <input type="radio" class="btn-check" name="routeMethod" id="routeIntent" autocomplete="off">
                    <label class="btn btn-outline-secondary btn-sm" for="routeIntent">Via Intent</label>
                </div>
            </div>
            
            <div class="mb-3" id="providerSelectGroup">
                <label class="form-label small text-muted">Provider</label>
                <select class="form-select form-select-sm bg-dark text-light border-secondary" id="playgroundProvider">
                    <option value="">Select Provider...</option>
                    @foreach($providers as $provider)
                        <option value="{{ $provider->id }}">{{ $provider->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="mb-4" id="modelSelectGroup">
                <label class="form-label small text-muted">Model</label>
                <select class="form-select form-select-sm bg-dark text-light border-secondary" id="playgroundModel">
                    <option value="">Select Model...</option>
                    @foreach($models as $model)
                        <option value="{{ $model->id }}" data-provider="{{ $model->provider_id }}" style="display:none;">{{ $model->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="mb-4">
                <div class="d-flex justify-content-between mb-1">
                    <label class="form-label small text-muted m-0">Temperature</label>
                    <span class="small text-light font-monospace" id="tempVal">0.7</span>
                </div>
                <input type="range" class="form-range custom-range" min="0" max="2" step="0.1" value="0.7" oninput="document.getElementById('tempVal').innerText=this.value">
            </div>
            
            <div class="mb-4">
                <div class="d-flex justify-content-between mb-1">
                    <label class="form-label small text-muted m-0">Max Tokens</label>
                    <span class="small text-light font-monospace" id="tokenVal">4096</span>
                </div>
                <input type="range" class="form-range custom-range" min="256" max="16384" step="256" value="4096" oninput="document.getElementById('tokenVal').innerText=this.value">
            </div>
            
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label small text-muted m-0">System Prompt</label>
                    <button class="btn btn-sm btn-link text-info p-0" style="font-size:0.75rem;">Load Preset</button>
                </div>
                <textarea class="form-control bg-dark border-secondary text-light font-monospace" id="playgroundSystemPrompt" rows="4" style="font-size:0.8rem;">You are a helpful AI assistant.</textarea>
            </div>
            
            <div class="d-grid gap-2 mt-4">
                <button class="btn btn-sm btn-outline-secondary" id="clearChatBtn">Clear Chat</button>
                <button class="btn btn-sm btn-outline-info">Save as Preset</button>
            </div>
        </div>
        
        <!-- Right Chat Area -->
        <div class="col-md-9 d-flex flex-column" style="background: rgba(22, 27, 34, 0.3);">
            <!-- Chat History -->
            <div class="flex-grow-1 p-4 overflow-auto" id="chatContainer" style="max-height: 480px;">
                <div class="text-center text-muted py-5" id="chatPlaceholder">
                    <i class="fa-solid fa-comments mb-3" style="font-size: 3rem;"></i>
                    <h5>AI Hub Playground</h5>
                    <p class="small">Select a provider and model, type your message, and test responses.</p>
                </div>
            </div>
            
            <!-- Input Area -->
            <div class="p-3 border-top border-secondary bg-dark">
                <div class="position-relative">
                    <textarea class="form-control bg-dark border-secondary text-light pe-5 py-3 shadow-none" id="playgroundMessage" rows="2" placeholder="Type your prompt here..."></textarea>
                    <button class="btn btn-primary position-absolute bottom-0 end-0 m-2 rounded-circle shadow" id="playgroundSendBtn" style="width: 35px; height: 35px; padding: 0;">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // Filter models based on selected provider
        $('#playgroundProvider').on('change', function() {
            const providerId = $(this).val();
            $('#playgroundModel').val('');
            
            if (providerId) {
                $('#playgroundModel option').each(function() {
                    if ($(this).data('provider') == providerId || $(this).val() === "") {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            } else {
                $('#playgroundModel option').hide();
                $('#playgroundModel option[value=""]').show();
            }
        });

        // Clear Chat
        $('#clearChatBtn').on('click', function() {
            $('#chatContainer').html(`
                <div class="text-center text-muted py-5" id="chatPlaceholder">
                    <i class="fa-solid fa-comments mb-3" style="font-size: 3rem;"></i>
                    <h5>AI Hub Playground</h5>
                    <p class="small">Select a provider and model, type your message, and test responses.</p>
                </div>
            `);
        });

        // Send Message
        $('#playgroundSendBtn').on('click', function() {
            const providerId = $('#playgroundProvider').val();
            const modelId = $('#playgroundModel').val();
            const message = $('#playgroundMessage').val().trim();
            
            if (!providerId || !modelId) {
                window.Nexus.notify('Please select a provider and model first.', 'warning');
                return;
            }
            if (!message) {
                return;
            }
            
            // Hide placeholder
            $('#chatPlaceholder').remove();
            
            // Append User Message
            $('#chatContainer').append(`
                <div class="d-flex flex-row-reverse mb-4 animate-fade-in">
                    <div class="bg-primary bg-opacity-25 border border-primary border-opacity-50 text-light p-3 rounded-3 shadow-sm" style="max-width: 75%;">
                        ${escapeHtml(message)}
                    </div>
                </div>
            `);
            
            // Clear input
            $('#playgroundMessage').val('');
            
            // Scroll to bottom
            scrollToBottom();
            
            // Append temporary loading state for AI
            const loadingId = 'ai-loading-' + Date.now();
            $('#chatContainer').append(`
                <div class="d-flex mb-4 animate-fade-in" id="${loadingId}">
                    <div class="bg-dark border border-secondary text-light p-3 rounded-3 shadow-sm w-100">
                        <div class="d-flex align-items-center gap-2 text-muted">
                            <div class="spinner-border spinner-border-sm text-info" role="status"></div>
                            <span>Generating response...</span>
                        </div>
                    </div>
                </div>
            `);
            
            scrollToBottom();
            
            // Send AJAX
            $.ajax({
                url: '{{ route("hub.models.playground.chat") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    provider_id: providerId,
                    model_id: modelId,
                    message: message
                },
                success: function(res) {
                    $(`#${loadingId}`).remove();
                    if (res.success) {
                        $('#chatContainer').append(`
                            <div class="d-flex mb-4 animate-fade-in">
                                <div class="bg-dark border border-secondary text-light p-3 rounded-3 shadow-sm w-100">
                                    <p class="mb-2">${escapeHtml(res.data.response)}</p>
                                    <hr class="border-secondary my-2 opacity-25">
                                    <div class="d-flex justify-content-between align-items-center font-monospace text-muted" style="font-size: 0.7rem;">
                                        <div>
                                            <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 me-2">${$('#playgroundModel option:selected').text()}</span>
                                            <i class="fa-solid fa-stopwatch me-1"></i> Simulated
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `);
                    } else {
                        window.Nexus.notify('Failed to simulate chat response', 'error');
                    }
                    scrollToBottom();
                },
                error: function(xhr) {
                    $(`#${loadingId}`).remove();
                    window.Nexus.notify(xhr.responseJSON?.message || 'Error occurred during simulation', 'error');
                    scrollToBottom();
                }
            });
        });

        function scrollToBottom() {
            const container = document.getElementById('chatContainer');
            container.scrollTop = container.scrollHeight;
        }

        function escapeHtml(text) {
            return text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
    });
</script>
@endpush
