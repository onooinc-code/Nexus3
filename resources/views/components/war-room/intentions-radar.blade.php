@props(['intentions', 'narcissism'])

<div class="eg-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="eg-section-title mb-0 border-0"><i class="fa-solid fa-satellite-dish me-2"></i> INTENTIONS_RADAR</div>
        <div class="text-end">
            <div class="eg-label text-warning mb-1">NARCISSISM_METER</div>
            <div class="text-white" style="font-family: var(--font-code); font-size: 1.2rem;">{{ $narcissism }}%</div>
        </div>
    </div>

    <div class="d-flex flex-column gap-3">
        @foreach($intentions ?? [] as $int)
            <div class="p-3" style="border-right: 2px solid var(--war-green); background: rgba(0,255,65,0.02);">
                <div class="mb-2">
                    <span class="eg-label text-green d-block">[ DECODED_SIGNAL ]</span>
                    <span class="text-muted" style="font-family: var(--font-ar); font-size: 0.85rem;">"{{ $int['said'] }}"</span>
                </div>
                <div>
                    <span class="eg-label text-warning d-block">[ TRUE_MEANING ]</span>
                    <span class="text-white" style="font-family: var(--font-ar); font-size: 0.9rem; font-weight: 500;">
                        <i class="fa-solid fa-arrow-left text-warning me-1"></i> {{ $int['means'] }}
                    </span>
                </div>
            </div>
        @endforeach
    </div>
</div>
