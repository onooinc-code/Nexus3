@props(['big_five', 'psychological'])

@php
    // Extract values (0-100)
    $axes = [
        ['label' => 'Openness',          'val' => $big_five['openness'] ?? 0],
        ['label' => 'Conscient.',        'val' => $big_five['conscientiousness'] ?? 0],
        ['label' => 'Extraversion',      'val' => $big_five['extraversion'] ?? 0],
        ['label' => 'Agreeableness',     'val' => $big_five['agreeableness'] ?? 0],
        ['label' => 'Neuroticism',       'val' => $big_five['neuroticism'] ?? 0],
        ['label' => 'Attachment',        'val' => $psychological['attachment_style'] ?? 0],
        ['label' => 'Stability',         'val' => $psychological['emotional_stability'] ?? 0],
        ['label' => 'Ego Level',         'val' => $psychological['ego_level'] ?? 0],
    ];

    $cx = 100;
    $cy = 100;
    $rMax = 70; // Max radius for data

    // Generate points for the data polygon
    $dataPoints = [];
    foreach($axes as $i => $axis) {
        $angle = ($i * 45) - 90; // Start at top (-90 deg), clockwise
        $rad = deg2rad($angle);
        
        $r = ($axis['val'] / 100) * $rMax;
        
        $x = $cx + ($r * cos($rad));
        $y = $cy + ($r * sin($rad));
        
        $dataPoints[] = "{$x},{$y}";
    }
    $polygonPath = implode(' ', $dataPoints);
@endphp

<div class="studio-section-title">
    <i class="fa-solid fa-brain"></i> Psychological & Cognitive Profile
</div>

<div class="row g-4">
    {{-- Radar Chart Column --}}
    <div class="col-md-5">
        <div class="studio-card p-4 h-100 d-flex flex-column align-items-center justify-content-center">
            <h6 class="mb-4 text-center w-100" style="color: var(--studio-indigo);"><i class="fa-solid fa-radar me-2"></i> 8-Axis Cognitive Radar</h6>
            
            <svg viewBox="0 0 200 200" class="studio-radar-svg w-100 h-auto" style="max-width: 320px;">
                {{-- Background Grids --}}
                @foreach([0.25, 0.5, 0.75, 1.0] as $scale)
                    @php
                        $gridPoints = [];
                        foreach(range(0, 7) as $i) {
                            $angle = ($i * 45) - 90;
                            $rad = deg2rad($angle);
                            $r = $rMax * $scale;
                            $x = $cx + ($r * cos($rad));
                            $y = $cy + ($r * sin($rad));
                            $gridPoints[] = "{$x},{$y}";
                        }
                    @endphp
                    <polygon points="{{ implode(' ', $gridPoints) }}" class="radar-grid-line"></polygon>
                @endforeach

                {{-- Axes Lines --}}
                @foreach(range(0, 7) as $i)
                    @php
                        $angle = ($i * 45) - 90;
                        $rad = deg2rad($angle);
                        $x = $cx + ($rMax * cos($rad));
                        $y = $cy + ($rMax * sin($rad));
                    @endphp
                    <line x1="{{ $cx }}" y1="{{ $cy }}" x2="{{ $x }}" y2="{{ $y }}" class="radar-axis-line"></line>
                @endforeach

                {{-- Data Polygon --}}
                <polygon points="{{ $polygonPath }}" class="radar-data-fill"></polygon>

                {{-- Labels --}}
                @foreach($axes as $i => $axis)
                    @php
                        $angle = ($i * 45) - 90;
                        $rad = deg2rad($angle);
                        $labelR = $rMax + 18;
                        $lx = $cx + ($labelR * cos($rad));
                        $ly = $cy + ($labelR * sin($rad));
                        
                        // Text anchoring based on position
                        $anchor = 'middle';
                        if ($lx > 110) $anchor = 'start';
                        elseif ($lx < 90) $anchor = 'end';
                    @endphp
                    <text x="{{ $lx }}" y="{{ $ly }}" text-anchor="{{ $anchor }}" class="radar-label" dominant-baseline="middle">{{ $axis['label'] }}</text>
                    <text x="{{ $lx }}" y="{{ $ly + 10 }}" text-anchor="{{ $anchor }}" class="radar-value-label" dominant-baseline="middle">{{ $axis['val'] }}</text>
                @endforeach
            </svg>
        </div>
    </div>

    {{-- Traits Detail Column --}}
    <div class="col-md-7">
        <div class="studio-card p-4 h-100">
            <h6 class="mb-4 text-emerald"><i class="fa-solid fa-puzzle-piece me-2"></i> Core Drivers & Triggers</h6>
            
            <div class="row g-4">
                <div class="col-sm-6">
                    <div class="studio-stat-label mb-2"><i class="fa-solid fa-star me-1 text-indigo"></i> Core Values</div>
                    <ul class="list-unstyled mb-0 d-flex flex-column gap-2" style="font-size: 0.8rem;">
                        @forelse($psychological['core_values'] ?? [] as $val)
                            <li><i class="fa-solid fa-check text-emerald me-2"></i> {{ $val }}</li>
                        @empty
                            <li class="text-muted">None recorded</li>
                        @endforelse
                    </ul>
                </div>

                <div class="col-sm-6">
                    <div class="studio-stat-label mb-2"><i class="fa-solid fa-skull me-1 text-red"></i> Primary Fear</div>
                    <div class="p-3" style="background: rgba(239,68,68,0.05); border-radius: 8px; border-left: 2px solid var(--studio-red); font-size: 0.8rem; line-height: 1.5; color: #fca5a5;">
                        {{ $psychological['primary_fear'] ?? 'Unknown' }}
                    </div>
                </div>

                <div class="col-sm-6">
                    <div class="studio-stat-label mb-2"><i class="fa-solid fa-masks-theater me-1 text-amber"></i> Manipulation Tactics</div>
                    <div class="d-flex flex-wrap gap-2">
                        @forelse($psychological['manipulation_tactics'] ?? [] as $tac)
                            <span class="studio-chip studio-chip-amber">{{ $tac }}</span>
                        @empty
                            <span class="text-muted small">None recorded</span>
                        @endforelse
                    </div>
                </div>

                <div class="col-sm-6">
                    <div class="studio-stat-label mb-2"><i class="fa-solid fa-brain me-1 text-indigo"></i> Cognitive Biases</div>
                    <div class="d-flex flex-wrap gap-2">
                        @forelse($psychological['cognitive_biases'] ?? [] as $bias)
                            <span class="studio-chip studio-chip-indigo">{{ $bias }}</span>
                        @empty
                            <span class="text-muted small">None recorded</span>
                        @endforelse
                    </div>
                </div>

                <div class="col-12 mt-2">
                    <div class="row g-3">
                        <div class="col-sm-4">
                            <div class="studio-stat-pair">
                                <span class="studio-stat-label">Love Language</span>
                                <span class="studio-stat-value text-emerald" style="font-size: 0.75rem;">{{ $psychological['love_language'] ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="studio-stat-pair">
                                <span class="studio-stat-label">Guilt Trigger</span>
                                <span class="studio-stat-value text-red" style="font-size: 0.75rem;">{{ $psychological['guilt_trigger'] ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="studio-stat-pair">
                                <span class="studio-stat-label">Stress Response</span>
                                <span class="studio-stat-value text-amber" style="font-size: 0.75rem;">{{ $psychological['stress_response'] ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
