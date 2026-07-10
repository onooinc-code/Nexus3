<!-- Job Simulator -->
<div class="tab-pane fade" id="play-jobs">
    <div class="row g-4 h-100" style="min-height: 500px;">
        <div class="col-md-5">
            <div class="card bg-dark border border-secondary h-100">
                <div class="card-header border-secondary">
                    <h6 class="mb-0 text-light fw-bold">Dispatch Configuration</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label small text-muted">Provider</label>
                        <select class="form-select form-select-sm bg-dark text-light border-secondary" id="jobProvider">
                            <option value="">Select Provider...</option>
                            @foreach($providers as $provider)
                                <option value="{{ $provider->id }}">{{ $provider->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Model</label>
                        <select class="form-select form-select-sm bg-dark text-light border-secondary" id="jobModel">
                            <option value="">Select Model...</option>
                            @foreach($models as $model)
                                <option value="{{ $model->id }}" data-provider="{{ $model->provider_id }}" style="display:none;">{{ $model->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Job Prompt/Message</label>
                        <textarea class="form-control bg-dark border-secondary text-light font-monospace" id="jobMessage" rows="5" style="font-size:0.75rem;">Simulate background processing for this model prompt.</textarea>
                    </div>
                    <button class="btn btn-primary w-100" id="dispatchJobBtn"><i class="fa-solid fa-rocket me-2"></i> Dispatch Job</button>
                </div>
            </div>
        </div>
        
        <div class="col-md-7">
            <div class="card bg-dark border border-secondary h-100">
                <div class="card-header border-secondary d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 text-light fw-bold">Live WebSocket Monitor</h6>
                    <span class="badge bg-success" id="monitorStatus"><i class="fa-solid fa-circle-dot me-1"></i>Connected</span>
                </div>
                <div class="card-body bg-black p-3 overflow-auto font-monospace" id="monitorConsole" style="max-height: 400px; font-size:0.75rem; min-height: 300px; color: #10b981;">
                    <div>[system] Listening on channel nexus-system...</div>
                    <div>[system] Waiting for jobs...</div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // Filter models based on selected provider for Job Simulator
        $('#jobProvider').on('change', function() {
            const providerId = $(this).val();
            $('#jobModel').val('');
            
            if (providerId) {
                $('#jobModel option').each(function() {
                    if ($(this).data('provider') == providerId || $(this).val() === "") {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            } else {
                $('#jobModel option').hide();
                $('#jobModel option[value=""]').show();
            }
        });

        // Dispatch Job AJAX
        $('#dispatchJobBtn').on('click', function() {
            const providerId = $('#jobProvider').val();
            const modelId = $('#jobModel').val();
            const message = $('#jobMessage').val().trim();
            const timestamp = new Date().toLocaleTimeString();
            
            if (!providerId || !modelId) {
                window.Nexus.notify('Please select a provider and model.', 'warning');
                return;
            }
            if (!message) {
                window.Nexus.notify('Please enter a message/prompt.', 'warning');
                return;
            }
            
            logConsole(`[${timestamp}] Dispatching SimulateAiJob...`);
            
            $.ajax({
                url: '{{ route("hub.models.playground.dispatch-job") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    provider_id: providerId,
                    model_id: modelId,
                    message: message
                },
                success: function(res) {
                    const doneTime = new Date().toLocaleTimeString();
                    if(res.success) {
                        logConsole(`[${doneTime}] <span class="text-success">SUCCESS:</span> ${res.message}`);
                        logConsole(`[${doneTime}] Job processed by Horizon simulator.`);
                        window.Nexus.notify('Job dispatched successfully!', 'success');
                    } else {
                        logConsole(`[${doneTime}] <span class="text-danger">FAILED:</span> Failed to dispatch job.`);
                    }
                },
                error: function(xhr) {
                    const doneTime = new Date().toLocaleTimeString();
                    logConsole(`[${doneTime}] <span class="text-danger">ERROR:</span> ${xhr.responseJSON?.message || 'Error occurred during dispatch'}`);
                    window.Nexus.notify('Failed to dispatch job', 'error');
                }
            });
        });

        function logConsole(text) {
            $('#monitorConsole').append(`<div>${text}</div>`);
            const consoleEl = document.getElementById('monitorConsole');
            consoleEl.scrollTop = consoleEl.scrollHeight;
        }
    });
</script>
@endpush
