@props(['architect'])

<div class="eg-card p-4">
    <div class="eg-section-title"><i class="fa-solid fa-code-branch me-2"></i> RESPONSE_ARCHITECT.exe</div>
    
    <div class="mb-4">
        <div class="eg-label mb-2">[ INCOMING_MESSAGE ]</div>
        <div class="p-3" style="background: rgba(0,255,65,0.02); border-right: 3px solid var(--war-green); font-family: var(--font-ar); font-size: 1.1rem; color: #fff;">
            "{{ $architect['latest_incoming'] ?? 'N/A' }}"
        </div>
    </div>

    <div class="eg-label mb-3">[ GENERATED_RESPONSES ]</div>
    
    <div class="d-flex flex-column gap-3">
        @foreach($architect['suggested_responses'] ?? [] as $i => $resp)
            <div class="p-3 position-relative" style="border: 1px solid rgba(0,255,65,0.15); background: rgba(0,0,0,0.5);">
                <div class="d-flex justify-content-between mb-2">
                    <span class="eg-chip">{{ $resp['type'] }}</span>
                    <span class="text-muted" style="font-family: var(--font-code); font-size: 0.7rem;">OPT_{{ $i+1 }}</span>
                </div>
                
                <div class="text-white mb-2" style="font-family: var(--font-ar); font-size: 1rem;">
                    > {{ $resp['text'] }}
                </div>
                
                <div style="font-size: 0.8rem; color: var(--war-muted); font-family: var(--font-ar); border-top: 1px dashed rgba(0,255,65,0.2); padding-top: 0.5rem;">
                    <span class="text-warning"><i class="fa-solid fa-bolt"></i> IMPACT:</span> {{ $resp['psych_impact'] }}
                </div>
            </div>
        @endforeach
    </div>
</div>
