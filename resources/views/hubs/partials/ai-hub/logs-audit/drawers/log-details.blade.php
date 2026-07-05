<!-- Log Detail Drawer -->
<div class="offcanvas offcanvas-end bg-dark border-start border-secondary text-light" tabindex="-1" id="logDetailDrawer" style="width: 800px;">
    <div class="offcanvas-header border-bottom border-secondary pb-3">
        <div>
            <h5 class="offcanvas-title fw-bold mb-1"><i class="fa-solid fa-file-invoice me-2"></i>Request Details</h5>
            <div class="text-muted font-monospace small">Req ID: req_9a8b7c6d5e4f3g2h1</div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        
        <ul class="nav nav-tabs ai-hub-tabs pt-3 px-4" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#detail-overview">Overview</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#detail-payload">Payload JSON</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#detail-timeline">Timeline</button></li>
        </ul>
        
        <div class="tab-content p-4">
            
            <div class="tab-pane fade show active" id="detail-overview">
                <!-- Metadata Grid -->
                <div class="row g-3 mb-4">
                    <div class="col-sm-4">
                        <div class="text-muted small mb-1">Timestamp</div>
                        <div class="text-light font-monospace">2026-07-04 14:32:15.123</div>
                    </div>
                    <div class="col-sm-4">
                        <div class="text-muted small mb-1">Intent</div>
                        <div class="text-light">general_chat</div>
                    </div>
                    <div class="col-sm-4">
                        <div class="text-muted small mb-1">Status</div>
                        <div class="text-success fw-bold">200 OK</div>
                    </div>
                    
                    <div class="col-sm-4">
                        <div class="text-muted small mb-1">Final Model</div>
                        <div class="text-light">gpt-4o-mini <span class="text-muted">(OpenAI)</span></div>
                    </div>
                    <div class="col-sm-4">
                        <div class="text-muted small mb-1">Total Latency</div>
                        <div class="text-light font-monospace">820ms</div>
                    </div>
                    <div class="col-sm-4">
                        <div class="text-muted small mb-1">Tokens (In/Out)</div>
                        <div class="text-light font-monospace">123 / 456</div>
                    </div>
                </div>
                
                <h6 class="text-light border-bottom border-secondary pb-2 mb-3 mt-4">Decision Explainability Tree</h6>
                <div class="bg-dark rounded border border-secondary p-3 font-monospace" style="font-size: 0.8rem; white-space: pre;">
<span class="text-info">🔀 Routing Decision Tree:</span>
├── Intent: general_chat → Matched Route <span class="text-light">#R001</span>
├── Profile: cost_optimized → Prefer budget models
├── Provider: OpenAI (Primary) → Circuit: <span class="text-success">CLOSED ✅</span>
└── Model: gpt-4o-mini → Context OK (579 / 128,000)

Result: <span class="text-success">Primary route used. No fallback triggered.</span></div>
            </div>
            
            <div class="tab-pane fade" id="detail-payload">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="text-light m-0">Request Body</h6>
                    <button class="btn btn-sm btn-link text-muted p-0"><i class="fa-regular fa-copy"></i> Copy</button>
                </div>
                <div class="bg-black rounded border border-secondary p-3 text-light font-monospace mb-4 overflow-auto" style="font-size: 0.75rem; max-height: 200px;">
{
  "model": "gpt-4o-mini",
  "messages": [
    {
      "role": "system",
      "content": "You are a helpful assistant."
    },
    {
      "role": "user",
      "content": "Hello world!"
    }
  ],
  "temperature": 0.7
}</div>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="text-light m-0">Response Body</h6>
                    <button class="btn btn-sm btn-link text-muted p-0"><i class="fa-regular fa-copy"></i> Copy</button>
                </div>
                <div class="bg-black rounded border border-secondary p-3 text-light font-monospace overflow-auto" style="font-size: 0.75rem; max-height: 200px;">
{
  "id": "chatcmpl-9a8b...",
  "object": "chat.completion",
  "created": 1720103535,
  "model": "gpt-4o-mini-2024-07-18",
  "choices": [
    {
      "index": 0,
      "message": {
        "role": "assistant",
        "content": "Hello! How can I help you today?"
      },
      "finish_reason": "stop"
    }
  ],
  "usage": {
    "prompt_tokens": 123,
    "completion_tokens": 456,
    "total_tokens": 579
  }
}</div>
            </div>
            
            <div class="tab-pane fade" id="detail-timeline">
                <h6 class="text-light border-bottom border-secondary pb-2 mb-4">Performance Waterfall</h6>
                
                <div class="font-monospace mb-3" style="font-size: 0.8rem;">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted" style="width: 120px;">Key Decrypt</span>
                        <div class="flex-grow-1 mx-3 position-relative">
                            <div class="position-absolute bg-primary rounded" style="left: 0%; width: 2%; height: 12px; top: 2px;"></div>
                        </div>
                        <span class="text-light text-end" style="width: 50px;">12ms</span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted" style="width: 120px;">Payload Adapt</span>
                        <div class="flex-grow-1 mx-3 position-relative">
                            <div class="position-absolute bg-info rounded" style="left: 2%; width: 1%; height: 12px; top: 2px;"></div>
                        </div>
                        <span class="text-light text-end" style="width: 50px;">8ms</span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted" style="width: 120px;">API Call TTFT</span>
                        <div class="flex-grow-1 mx-3 position-relative">
                            <div class="position-absolute bg-warning rounded" style="left: 3%; width: 30%; height: 12px; top: 2px;"></div>
                        </div>
                        <span class="text-light text-end" style="width: 50px;">240ms</span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted" style="width: 120px;">API Streaming</span>
                        <div class="flex-grow-1 mx-3 position-relative">
                            <div class="position-absolute bg-success rounded" style="left: 33%; width: 65%; height: 12px; top: 2px;"></div>
                        </div>
                        <span class="text-light text-end" style="width: 50px;">542ms</span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted" style="width: 120px;">Response Parse</span>
                        <div class="flex-grow-1 mx-3 position-relative">
                            <div class="position-absolute bg-secondary rounded" style="left: 98%; width: 2%; height: 12px; top: 2px;"></div>
                        </div>
                        <span class="text-light text-end" style="width: 50px;">18ms</span>
                    </div>
                    
                    <hr class="border-secondary my-2">
                    
                    <div class="d-flex justify-content-between">
                        <span class="text-light fw-bold" style="width: 120px;">Total</span>
                        <div class="flex-grow-1 mx-3"></div>
                        <span class="text-light fw-bold text-end" style="width: 50px;">820ms</span>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>
