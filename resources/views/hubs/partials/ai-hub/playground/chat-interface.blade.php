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
            
            <div class="mb-3">
                <label class="form-label small text-muted">Provider</label>
                <select class="form-select form-select-sm bg-dark text-light border-secondary">
                    <option>OpenAI</option>
                    <option>Anthropic</option>
                    <option>Google</option>
                </select>
            </div>
            
            <div class="mb-4">
                <label class="form-label small text-muted">Model</label>
                <select class="form-select form-select-sm bg-dark text-light border-secondary">
                    <option>gpt-4o</option>
                    <option>gpt-4o-mini</option>
                    <option>gpt-3.5-turbo</option>
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
                <textarea class="form-control bg-dark border-secondary text-light font-monospace" rows="4" style="font-size:0.8rem;">You are a helpful AI assistant.</textarea>
            </div>
            
            <div class="d-grid gap-2 mt-4">
                <button class="btn btn-sm btn-outline-secondary">Clear Chat</button>
                <button class="btn btn-sm btn-outline-info">Save as Preset</button>
            </div>
        </div>
        
        <!-- Right Chat Area -->
        <div class="col-md-9 d-flex flex-column" style="background: rgba(22, 27, 34, 0.3);">
            <!-- Chat History -->
            <div class="flex-grow-1 p-4 overflow-auto" id="chatContainer">
                
                <!-- User Message -->
                <div class="d-flex flex-row-reverse mb-4">
                    <div class="bg-primary bg-opacity-25 border border-primary border-opacity-50 text-light p-3 rounded-3 shadow-sm" style="max-width: 75%;">
                        Explain how Laravel Echo works with Reverb.
                    </div>
                </div>
                
                <!-- AI Message -->
                <div class="d-flex mb-4">
                    <div class="bg-dark border border-secondary text-light p-3 rounded-3 shadow-sm w-100">
                        <p>Laravel Echo is a JavaScript library that makes it painless to subscribe to channels and listen for events broadcast by your Laravel application.</p>
                        <p>When used with Laravel Reverb (a first-party WebSocket server for Laravel), the flow is:</p>
                        <ol>
                            <li>Reverb runs as a WebSocket server on a specific port.</li>
                            <li>Echo connects to Reverb from the client-side browser using the WebSocket protocol.</li>
                            <li>When your backend fires a `ShouldBroadcast` event, Laravel sends it to Reverb.</li>
                            <li>Reverb immediately pushes the event down the active WebSocket connection to Echo, which triggers your JS listeners.</li>
                        </ol>
                        
                        <hr class="border-secondary my-3 opacity-25">
                        <div class="d-flex justify-content-between align-items-center font-monospace text-muted" style="font-size: 0.7rem;">
                            <div>
                                <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 me-2">gpt-4o</span>
                                <i class="fa-solid fa-stopwatch me-1"></i> TTFT: 240ms | Total: 1,420ms &nbsp;|&nbsp;
                                <i class="fa-solid fa-keyboard me-1"></i> 14/112 tokens &nbsp;|&nbsp;
                                <i class="fa-solid fa-coins me-1"></i> $0.0012 &nbsp;|&nbsp;
                                <span class="text-success"><i class="fa-solid fa-check me-1"></i>Primary</span>
                            </div>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-link text-muted p-0 mx-1" title="Copy"><i class="fa-regular fa-copy"></i></button>
                                <button class="btn btn-sm btn-link text-muted p-0 mx-1" title="Regenerate"><i class="fa-solid fa-rotate-right"></i></button>
                                <button class="btn btn-sm btn-link text-muted p-0 mx-1" title="Good"><i class="fa-regular fa-thumbs-up"></i></button>
                                <button class="btn btn-sm btn-link text-muted p-0 mx-1" title="Replay in Battle"><i class="fa-solid fa-bolt"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Input Area -->
            <div class="p-3 border-top border-secondary bg-dark">
                <div class="position-relative">
                    <textarea class="form-control bg-dark border-secondary text-light pe-5 py-3 shadow-none" rows="2" placeholder="Type your prompt here..."></textarea>
                    <button class="btn btn-primary position-absolute bottom-0 end-0 m-2 rounded-circle shadow" style="width: 35px; height: 35px; padding: 0;">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </div>
                <div class="text-end mt-1 text-muted font-monospace" style="font-size: 0.65rem;">
                    ~14 tokens estimated
                </div>
            </div>
        </div>
    </div>
</div>
