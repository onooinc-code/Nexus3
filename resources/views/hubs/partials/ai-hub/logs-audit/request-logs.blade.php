<!-- Request Logs -->
<div class="tab-pane fade show active" id="log-requests">
    <!-- Log List -->
    <div class="d-flex flex-column gap-2 mb-4">
        @forelse($logs as $log)
            <div class="card card-dashboard border-secondary p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="d-flex align-items-center gap-3">
                        <span class="text-muted font-monospace small">{{ \Carbon\Carbon::parse($log->timestamp ?? $log->created_at)->format('Y-m-d H:i:s') }}</span>
                        <span class="badge bg-dark border border-secondary text-info">{{ $log->intent_name ?? 'general_chat' }}</span>
                        <span class="text-light fw-bold">{{ $log->model_name ?? $log->model_code ?? 'Model' }} <span class="text-muted fw-normal">({{ $log->provider_name ?? 'Provider' }})</span></span>
                    </div>
                </div>
                
                <div class="d-flex flex-wrap gap-4 align-items-center mb-2 font-monospace" style="font-size: 0.8rem;">
                    <div class="text-success"><i class="fa-solid fa-circle-check me-1"></i>200 OK</div>
                    <div class="text-light"><i class="fa-solid fa-file-lines text-muted me-1"></i>{{ number_format($log->input_tokens) }}/{{ number_format($log->output_tokens) }} tokens</div>
                    <div class="text-warning"><i class="fa-solid fa-coins text-muted me-1"></i>${{ number_format($log->total_cost, 5) }}</div>
                </div>
                
                <div class="bg-dark rounded p-2 border border-secondary text-muted" style="font-size: 0.75rem;">
                    <span class="text-info fw-bold">Execution Detail:</span> Intent '{{ $log->intent_name ?? 'general_chat' }}' routed to {{ $log->provider_name ?? 'Provider' }} ({{ $log->model_name ?? $log->model_code ?? 'Model' }}).
                </div>
            </div>
        @empty
            <div class="card card-dashboard border-secondary p-5 text-center text-muted">
                <i class="fa-solid fa-list-check mb-3 text-info" style="font-size: 3rem;"></i>
                <h5 class="text-light">No Audit Logs Recorded</h5>
                <p>Execute prompts in Playground or API Gateway to generate telemetry logs.</p>
            </div>
        @endforelse
    </div>

    <!-- Dynamic Pagination Links -->
    @if(isset($logs) && method_exists($logs, 'links'))
        <div class="d-flex justify-content-center">
            {{ $logs->links() }}
        </div>
    @endif
</div>
