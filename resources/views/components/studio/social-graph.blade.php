@props(['graph', 'social', 'life'])

<div class="studio-section-title">
    <i class="fa-solid fa-diagram-project"></i> Ecosystem, Graph & Lifestyle
</div>

{{-- Sentinel Sparkline & Timeline --}}
<div class="row g-4 mb-4">
    <div class="col-md-7">
        <div class="studio-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h6 class="m-0 text-indigo"><i class="fa-solid fa-wave-square me-2"></i> Sentiment Velocity (8-Week Trend)</h6>
                <div class="studio-chip studio-chip-indigo">
                    <i class="fa-solid fa-arrow-trend-up me-1"></i> Live Tracking
                </div>
            </div>
            
            {{-- Canvas for pure JS sparkline --}}
            <canvas id="studio-sparkline" class="studio-sparkline mb-3" 
                    data-values="{{ json_encode($graph['sentiment_timeline'] ?? []) }}"></canvas>
            
            <div class="row mt-4">
                <div class="col-sm-6">
                    <div class="studio-stat-pair">
                        <span class="studio-stat-label">Power Dynamic</span>
                        <span class="studio-stat-value text-amber" style="font-size: 0.8rem;">{{ $graph['power_dynamic'] ?? 'N/A' }}</span>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="studio-stat-pair">
                        <span class="studio-stat-label">Relationship Cycle</span>
                        <span class="studio-stat-value" style="font-size: 0.8rem;">
                            {{ implode(' → ', $graph['relationship_cycle'] ?? []) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="studio-card p-4 h-100">
            <h6 class="mb-4 text-emerald"><i class="fa-regular fa-calendar-days me-2"></i> Timeline Milestones</h6>
            <div class="d-flex flex-column" style="max-height: 250px; overflow-y: auto;">
                @forelse($graph['timeline_milestones'] ?? [] as $i => $ms)
                    <div class="studio-timeline-item">
                        <div class="studio-timeline-dot" style="border-color: var(--studio-{{ $i === 0 ? 'emerald' : ($i % 2 === 0 ? 'indigo' : 'muted') }}); color: var(--studio-{{ $i === 0 ? 'emerald' : ($i % 2 === 0 ? 'indigo' : 'muted') }});">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <div class="pt-1">
                            <div style="font-size: 0.75rem; font-weight: 700; color: var(--studio-text);">{{ $ms['event'] }}</div>
                            <div style="font-size: 0.65rem; color: var(--studio-muted);">{{ $ms['date'] }}</div>
                        </div>
                    </div>
                @empty
                    <div class="text-muted small">No milestones recorded.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Graph Nodes & Lifestyle --}}
<div class="row g-4">
    <div class="col-md-4">
        <div class="studio-card p-4 h-100">
            <h6 class="mb-4 text-indigo"><i class="fa-solid fa-users me-2"></i> Persons Mentioned</h6>
            <div class="d-flex flex-column gap-3">
                @forelse($graph['persons_mentioned'] ?? [] as $person)
                    <div class="p-3 rounded" style="background: rgba(99,102,241,0.05); border: 1px solid rgba(99,102,241,0.15);">
                        <div style="font-weight: 600; font-size: 0.85rem; color: #a5b4fc;">{{ $person['name'] }}</div>
                        <div class="mt-1 text-muted" style="font-size: 0.75rem;">{{ $person['relation'] }}</div>
                    </div>
                @empty
                    <div class="text-muted small">None</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="studio-card p-4 h-100">
            <h6 class="mb-4 text-emerald"><i class="fa-solid fa-map-location-dot me-2"></i> Spatial Anchors (Places)</h6>
            <div class="d-flex flex-column gap-3">
                @forelse($graph['places_mentioned'] ?? [] as $place)
                    <div class="p-3 rounded" style="background: rgba(16,185,129,0.05); border: 1px solid rgba(16,185,129,0.15);">
                        <div style="font-weight: 600; font-size: 0.85rem; color: #6ee7b7;"><i class="fa-solid fa-location-dot me-1"></i> {{ $place['place'] }}</div>
                        <div class="mt-1 text-muted" style="font-size: 0.75rem;">{{ $place['context'] }}</div>
                    </div>
                @empty
                    <div class="text-muted small">None</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="studio-card p-4 h-100">
            <h6 class="mb-4 text-amber"><i class="fa-solid fa-masks-theater me-2"></i> Social & Lifestyle Dynamics</h6>
            
            <div class="d-flex flex-column gap-3">
                <div>
                    <div class="studio-stat-label mb-1">Key Influencers</div>
                    <div class="small">{{ implode(' • ', $social['key_influencers'] ?? []) }}</div>
                </div>
                <div>
                    <div class="studio-stat-label mb-1">Public vs Private Persona</div>
                    <div class="small text-amber">{{ $social['public_vs_private_persona'] ?? 'N/A' }}</div>
                </div>
                <div class="pt-2 border-top border-secondary">
                    <div class="studio-stat-label mb-2">Music & Digital</div>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($life['favorite_music'] ?? [] as $m)
                            <span class="studio-chip studio-chip-indigo"><i class="fa-solid fa-music me-1"></i> {{ $m }}</span>
                        @endforeach
                        @foreach($life['digital_habits'] ?? [] as $d)
                            <span class="studio-chip studio-chip-emerald"><i class="fa-solid fa-mobile-screen me-1"></i> {{ $d }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
