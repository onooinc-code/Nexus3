@props(['leverage'])

<div class="eg-card p-4 h-100">
    <div class="eg-section-title text-indigo" style="border-color: rgba(99,102,241,0.2);">
        <i class="fa-solid fa-coins"></i> أوراق الضغط المادية والاجتماعية
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="eg-label text-amber mb-2"><i class="fa-solid fa-wallet me-1"></i> الوضع المادي الحالي</div>
            <div class="p-3 rounded" style="background: rgba(245,158,11,0.05); border: 1px solid rgba(245,158,11,0.15); font-size: 0.85rem; color: #fcd34d;">
                {{ $leverage['financial_status'] ?? 'N/A' }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="eg-label text-indigo mb-2"><i class="fa-solid fa-users-rays me-1"></i> رأس المال الاجتماعي</div>
            <div class="p-3 rounded" style="background: rgba(99,102,241,0.05); border: 1px solid rgba(99,102,241,0.15); font-size: 0.85rem; color: #a5b4fc;">
                {{ $leverage['social_capital'] ?? 'N/A' }}
            </div>
        </div>
    </div>

    <div class="row g-4 pt-3" style="border-top: 1px solid rgba(255,255,255,0.05);">
        <div class="col-md-6">
            <h6 class="text-emerald mb-3" style="font-size: 0.85rem;"><i class="fa-solid fa-hand-holding-dollar me-1"></i> أوراق ضغط يمتلكها هدرا</h6>
            <ul class="list-unstyled d-flex flex-column gap-2 mb-0" style="font-size: 0.8rem;">
                @foreach($leverage['leverage_points_for_hedra'] ?? [] as $point)
                    <li class="p-2 rounded" style="background: rgba(16,185,129,0.05); border-right: 2px solid var(--studio-emerald);">
                        <i class="fa-solid fa-check text-emerald ms-2"></i> {{ $point }}
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="col-md-6">
            <h6 class="text-red mb-3" style="font-size: 0.85rem;"><i class="fa-solid fa-triangle-exclamation me-1"></i> أوراق ضغط ضده (نقاط ضعف هدرا)</h6>
            <ul class="list-unstyled d-flex flex-column gap-2 mb-0" style="font-size: 0.8rem;">
                @foreach($leverage['leverage_points_against_hedra'] ?? [] as $point)
                    <li class="p-2 rounded" style="background: rgba(239,68,68,0.05); border-right: 2px solid var(--studio-red);">
                        <i class="fa-solid fa-xmark text-red ms-2"></i> {{ $point }}
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
