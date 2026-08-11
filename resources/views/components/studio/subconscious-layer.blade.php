@props(['subconscious'])

<div class="studio-section-title text-red">
    <i class="fa-solid fa-eye-slash"></i> The Subconscious Layer (Classified)
</div>

{{-- Master Lock Screen Overlay --}}
<div id="studio-lock-screen" class="d-flex flex-column align-items-center justify-content-center py-5">
    <div class="mb-4 text-center">
        <i class="fa-solid fa-lock text-red" style="font-size: 3rem; opacity: 0.8; filter: drop-shadow(0 0 12px rgba(239,68,68,0.5));"></i>
        <h3 class="mt-3 text-red" style="font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase;">Cognitive Firewall Active</h3>
        <p class="text-muted" style="max-width: 500px; margin: 0 auto; font-size: 0.9rem;">
            This sector contains highly sensitive, subconscious behavioral data extracted by the deep analysis engine. Unauthorized access is restricted.
        </p>
    </div>
    
    <button id="studio-unlock-btn" type="button">
        <i class="fa-solid fa-fingerprint"></i> Unlock Deep Insights
    </button>
</div>

{{-- Protected Content (Hidden by default via CSS in master view) --}}
<div class="row g-4 mt-2">
    
    <div class="col-md-6">
        <div class="studio-card studio-locked-card h-100">
            <div class="locked-overlay">
                <i class="fa-solid fa-lock"></i>
                <span class="small text-uppercase tracking-wider">Encrypted Node</span>
            </div>
            <div class="card-content">
                <h6 class="mb-4 text-red"><i class="fa-solid fa-shield-virus me-2"></i> Defense Mechanisms & Dissonance</h6>
                
                <div class="d-flex flex-column gap-4">
                    <div>
                        <div class="studio-stat-label mb-2">Primary Defense Mechanisms</div>
                        <div class="p-3 rounded" style="background: rgba(239,68,68,0.05); border-left: 2px solid var(--studio-red); font-size: 0.85rem; color: #fca5a5;">
                            {{ $subconscious['defense_mechanisms'] ?? 'N/A' }}
                        </div>
                    </div>
                    <div>
                        <div class="studio-stat-label mb-2">Cognitive Dissonance</div>
                        <div class="p-3 rounded" style="background: rgba(245,158,11,0.05); border-left: 2px solid var(--studio-amber); font-size: 0.85rem; color: #fcd34d;">
                            {{ $subconscious['cognitive_dissonance'] ?? 'N/A' }}
                        </div>
                    </div>
                    <div>
                        <div class="studio-stat-label mb-2">Attachment Trigger Points</div>
                        <div class="small text-muted">{{ $subconscious['attachment_trigger_points'] ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="studio-card studio-locked-card h-100">
            <div class="locked-overlay">
                <i class="fa-solid fa-lock"></i>
                <span class="small text-uppercase tracking-wider">Encrypted Node</span>
            </div>
            <div class="card-content">
                <h6 class="mb-4 text-red"><i class="fa-solid fa-brain me-2"></i> Subconscious Loops & Truth Ratio</h6>
                
                <div class="mb-4">
                    <div class="studio-stat-label mb-2">Manipulation Loop</div>
                    <div class="p-3 rounded text-center" style="background: rgba(255,255,255,0.03); border: 1px dashed rgba(239,68,68,0.3); font-size: 0.8rem; letter-spacing: 0.05em;">
                        {{ $subconscious['manipulation_loop'] ?? 'N/A' }}
                    </div>
                </div>

                <div class="mb-4">
                    <div class="studio-stat-label mb-2">Power Struggle Tactics</div>
                    <div class="small text-muted mb-3">{{ $subconscious['power_struggle_tactics'] ?? 'N/A' }}</div>
                </div>

                <div class="pt-3 border-top border-secondary">
                    <div class="studio-stat-label mb-3">Truth vs Mask Ratio</div>
                    
                    <div class="d-flex gap-1 mb-2" style="height: 12px;">
                        <div style="width: {{ $subconscious['truth_vs_mask_ratio']['mask'] ?? 50 }}%; background: var(--studio-indigo); border-radius: 6px 0 0 6px;" title="Mask: {{ $subconscious['truth_vs_mask_ratio']['mask'] ?? 50 }}%"></div>
                        <div style="width: {{ $subconscious['truth_vs_mask_ratio']['truth'] ?? 50 }}%; background: var(--studio-emerald); border-radius: 0 6px 6px 0;" title="Truth: {{ $subconscious['truth_vs_mask_ratio']['truth'] ?? 50 }}%"></div>
                    </div>
                    <div class="d-flex justify-content-between studio-stat-label" style="font-size: 0.6rem;">
                        <span class="text-indigo">Public Mask ({{ $subconscious['truth_vs_mask_ratio']['mask'] ?? 50 }}%)</span>
                        <span class="text-emerald">Authentic Truth ({{ $subconscious['truth_vs_mask_ratio']['truth'] ?? 50 }}%)</span>
                    </div>
                    <div class="mt-3 p-2 rounded" style="background: rgba(0,0,0,0.2); font-size: 0.75rem; color: #94a3b8; font-style: italic;">
                        "{{ $subconscious['truth_vs_mask_ratio']['analysis'] ?? 'N/A' }}"
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>
