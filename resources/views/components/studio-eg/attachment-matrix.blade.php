@props(['attachment'])

<div class="eg-card p-4 h-100" style="border-right: 3px solid var(--studio-emerald);">
    <div class="eg-section-title text-emerald" style="border-color: rgba(16,185,129,0.2);">
        <i class="fa-solid fa-link"></i> مصفوفة الارتباط والاحتياج (Attachment Matrix)
    </div>

    <div class="row g-4">
        <div class="col-12">
            <div class="p-3 rounded mb-3 text-center" style="background: rgba(16,185,129,0.05); border: 1px dashed rgba(16,185,129,0.3);">
                <div class="eg-label text-emerald mb-1">نمط التعلق الأساسي (Attachment Style)</div>
                <div class="text-white" style="font-size: 1.1rem; font-weight: 700;">{{ $attachment['attachment_style'] ?? 'N/A' }}</div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="eg-label"><i class="fa-solid fa-utensils text-amber me-1"></i> مقياس الجوع العاطفي</span>
                    <span class="text-amber fw-bold">{{ $attachment['emotional_hunger_scale'] ?? 0 }}%</span>
                </div>
                <div class="progress" style="height: 8px; background: rgba(255,255,255,0.05); border-radius: 4px;">
                    <div class="progress-bar bg-warning" style="width: {{ $attachment['emotional_hunger_scale'] ?? 0 }}%;"></div>
                </div>
                <div class="mt-2 text-muted" style="font-size: 0.7rem;">مدى احتياجه للاهتمام رغم محاولته إخفاء ذلك.</div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="eg-label"><i class="fa-solid fa-person-walking-arrow-right text-indigo me-1"></i> مستوى الخوف من القرب الحقيقي</span>
                    <span class="text-indigo fw-bold">{{ $attachment['intimacy_fear_level'] ?? 0 }}%</span>
                </div>
                <div class="progress" style="height: 8px; background: rgba(255,255,255,0.05); border-radius: 4px;">
                    <div class="progress-bar" style="width: {{ $attachment['intimacy_fear_level'] ?? 0 }}%; background: var(--studio-indigo);"></div>
                </div>
                <div class="mt-2 text-muted" style="font-size: 0.7rem;">كلما اقتربت منه أكثر، زادت احتمالية هروبه.</div>
            </div>
        </div>

        <div class="col-12 mt-2 pt-3" style="border-top: 1px solid rgba(255,255,255,0.05);">
            <div class="eg-label mb-3"><i class="fa-solid fa-check-double text-emerald me-1"></i> احتياجات التوكيد (Validation Needs)</div>
            <ul class="list-unstyled d-flex flex-column gap-2 mb-0" style="font-size: 0.85rem;">
                @foreach($attachment['validation_needs'] ?? [] as $need)
                    <li><i class="fa-solid fa-caret-left me-2 text-emerald"></i> {{ $need }}</li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
