@props(['burnout'])

<div class="eg-card card-red p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="eg-section-title mb-0 border-0"><i class="fa-solid fa-hourglass-half me-2"></i> BURNOUT_COUNTDOWN</div>
        <span class="eg-chip" style="background: var(--war-red-dim); border-color: var(--war-red); color: var(--war-red);">
            {{ $burnout['current_tension_level'] ?? 'UNKNOWN' }}
        </span>
    </div>

    <div class="d-flex align-items-center justify-content-center gap-4 py-3" style="background: rgba(255,0,60,0.05); border: 1px dashed var(--war-border-red);">
        <div class="text-center">
            <div class="eg-label text-red">EST_DAYS_TO_BREAK</div>
            <div class="text-white animate-glitch" style="font-family: var(--font-code); font-size: 3.5rem; font-weight: bold; text-shadow: 0 0 10px rgba(255,0,60,0.5);">
                0{{ $burnout['estimated_days_to_break'] ?? 0 }}
            </div>
        </div>
    </div>

    <div class="mt-4 pt-3 border-top border-secondary">
        <div class="d-flex justify-content-between mb-2">
            <span class="eg-label">HEDRA_ELO: <span class="text-white">{{ $burnout['hedra_elo'] ?? 0 }}</span></span>
            <span class="eg-label">CONTACT_ELO: <span class="text-muted">{{ $burnout['contact_elo'] ?? 0 }}</span></span>
        </div>
        <div class="progress" style="height: 4px; background: rgba(255,255,255,0.1);">
            @php 
                $total = ($burnout['hedra_elo'] ?? 1) + ($burnout['contact_elo'] ?? 1);
                $pct = (($burnout['hedra_elo'] ?? 0) / $total) * 100;
            @endphp
            <div class="progress-bar bg-success" style="width: {{ $pct }}%;"></div>
        </div>
        <div class="mt-2 text-center text-warning" style="font-family: var(--font-code); font-size: 0.8rem;">
            <i class="fa-solid fa-fire"></i> WIN_STREAK: {{ $burnout['win_streak'] ?? 0 }}
        </div>
    </div>
</div>
