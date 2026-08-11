@props(['matrix'])

<div class="eg-card card-red p-4 h-100">
    <div class="eg-section-title"><i class="fa-solid fa-gavel me-2"></i> OPERANT_CONDITIONING.exe</div>
    
    <div class="row g-3">
        <div class="col-12 border-bottom border-danger pb-3">
            <div class="d-flex gap-2 mb-2">
                <span class="badge bg-danger text-dark" style="font-family: var(--font-code);">[ PUNISHMENT ]</span>
            </div>
            <div class="row">
                <div class="col-6">
                    <div class="eg-label text-danger mb-1">TRIGGER (WHEN)</div>
                    <ul class="list-unstyled mb-0" style="font-family: var(--font-ar); font-size: 0.8rem; color: #fca5a5;">
                        @foreach($matrix['when_to_punish'] ?? [] as $w)
                            <li>- {{ $w }}</li>
                        @endforeach
                    </ul>
                </div>
                <div class="col-6" style="border-right: 1px dashed rgba(255,0,60,0.3);">
                    <div class="eg-label text-danger mb-1">ACTION (HOW)</div>
                    <ul class="list-unstyled mb-0" style="font-family: var(--font-ar); font-size: 0.8rem; color: #fff;">
                        @foreach($matrix['how_to_punish'] ?? [] as $h)
                            <li><i class="fa-solid fa-angle-left text-danger me-1"></i> {{ $h }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-12 pt-2">
            <div class="d-flex gap-2 mb-2">
                <span class="badge bg-success text-dark" style="font-family: var(--font-code);">[ REWARD ]</span>
            </div>
            <div class="row">
                <div class="col-6">
                    <div class="eg-label text-success mb-1">TRIGGER (WHEN)</div>
                    <ul class="list-unstyled mb-0" style="font-family: var(--font-ar); font-size: 0.8rem; color: #6ee7b7;">
                        @foreach($matrix['when_to_reward'] ?? [] as $w)
                            <li>- {{ $w }}</li>
                        @endforeach
                    </ul>
                </div>
                <div class="col-6" style="border-right: 1px dashed rgba(0,255,65,0.3);">
                    <div class="eg-label text-success mb-1">ACTION (HOW)</div>
                    <ul class="list-unstyled mb-0" style="font-family: var(--font-ar); font-size: 0.8rem; color: #fff;">
                        @foreach($matrix['how_to_reward'] ?? [] as $h)
                            <li><i class="fa-solid fa-angle-left text-success me-1"></i> {{ $h }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
