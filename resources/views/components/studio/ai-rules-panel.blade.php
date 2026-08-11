@props(['rules'])

<div class="studio-section-title">
    <i class="fa-solid fa-robot"></i> Cognitive AI Directives
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="studio-card p-0" style="border-top: 4px solid var(--studio-indigo);">
            
            <div class="p-4 border-bottom border-secondary" style="background: rgba(99,102,241,0.05);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="m-0 text-indigo" style="font-weight: 700;">Agent Behavior Profile</h5>
                        <div class="text-muted small mt-1">Rules of engagement for this specific persona.</div>
                    </div>
                    <div class="studio-chip studio-chip-indigo">
                        <i class="fa-solid fa-microchip me-1"></i> Active
                    </div>
                </div>
            </div>

            <div class="p-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="studio-stat-pair">
                            <span class="studio-stat-label text-indigo">Initiation Protocol</span>
                            <span class="studio-stat-value" style="font-family: monospace; font-size: 0.9rem;">
                                [ {{ $rules['should_agent_initiate'] ?? 'UNKNOWN' }} ]
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="studio-stat-pair">
                            <span class="studio-stat-label text-emerald">Relationship Phase Context</span>
                            <span class="studio-stat-value" style="font-size: 0.85rem;">
                                {{ $rules['relationship_phase'] ?? 'N/A' }}
                            </span>
                        </div>
                    </div>
                </div>

                <hr style="border-color: var(--studio-border); margin: 1.5rem 0;">

                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="studio-stat-label mb-2"><i class="fa-solid fa-ban me-1 text-red"></i> Forbidden Topics</div>
                        <ul class="list-unstyled d-flex flex-column gap-2 mb-0" style="font-size: 0.85rem;">
                            @forelse($rules['forbidden_topics'] ?? [] as $topic)
                                <li class="text-red"><i class="fa-solid fa-xmark me-2"></i> {{ $topic }}</li>
                            @empty
                                <li class="text-muted">None</li>
                            @endforelse
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <div class="studio-stat-label mb-2"><i class="fa-solid fa-bolt me-1 text-amber"></i> Initiation Triggers</div>
                        <ul class="list-unstyled d-flex flex-column gap-2 mb-0" style="font-size: 0.85rem;">
                            @forelse($rules['initiation_triggers'] ?? [] as $trigger)
                                <li><i class="fa-solid fa-caret-right me-2 text-amber"></i> {{ $trigger }}</li>
                            @empty
                                <li class="text-muted">None</li>
                            @endforelse
                        </ul>
                    </div>
                </div>

                <div class="mt-4 p-3 rounded" style="background: rgba(16,185,129,0.05); border: 1px solid rgba(16,185,129,0.15);">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="studio-stat-label mb-1 text-emerald">Recommended Tone</div>
                            <div class="small">{{ $rules['recommended_tone'] ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="studio-stat-label mb-1 text-emerald">Conversation Deepness</div>
                            <div class="small">{{ $rules['conversation_deepness'] ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
