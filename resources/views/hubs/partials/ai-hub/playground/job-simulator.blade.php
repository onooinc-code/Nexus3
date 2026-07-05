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
                        <label class="form-label small text-muted">Job Class</label>
                        <select class="form-select form-select-sm bg-dark text-light border-secondary">
                            <option>ProcessAiInferenceJob</option>
                            <option>ExecuteAiModelJob</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Queue</label>
                        <select class="form-select form-select-sm bg-dark text-light border-secondary">
                            <option>llm-inference</option>
                            <option>default</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <label class="form-label small text-muted">Payload (JSON)</label>
                            <button class="btn btn-sm btn-link text-info p-0" style="font-size:0.75rem;">Format</button>
                        </div>
                        <textarea class="form-control bg-dark border-secondary text-light font-monospace" rows="8" style="font-size:0.75rem;">{
  "intent": "general_chat",
  "prompt": "Hello World",
  "user_id": 1
}</textarea>
                    </div>
                    <button class="btn btn-primary w-100"><i class="fa-solid fa-rocket me-2"></i> Dispatch Job</button>
                </div>
            </div>
        </div>
        
        <div class="col-md-7">
            <div class="card bg-dark border border-secondary h-100">
                <div class="card-header border-secondary d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 text-light fw-bold">Live WebSocket Monitor</h6>
                    <span class="badge bg-danger"><i class="fa-solid fa-circle-dot me-1"></i>Disconnected</span>
                </div>
                <div class="card-body bg-black p-0 overflow-auto font-monospace" style="max-height: 400px; font-size:0.75rem;">
                    <div class="p-3 text-muted">
                        [14:32:01] Listening on channel nexus-system...<br>
                        [14:32:01] Waiting for jobs...
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
