@props(['linguistics'])

<div class="eg-card p-4 h-100">
    <div class="eg-section-title text-emerald" style="border-color: rgba(16,185,129,0.2);">
        <i class="fa-solid fa-language"></i> التحليل اللغوي والصمت العقابي
    </div>

    <div class="row g-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="eg-label"><i class="fa-solid fa-bolt text-red me-1"></i> نسبة العدوانية السلبية (Passive-Aggressive Ratio)</span>
                <span class="text-red fw-bold">{{ $linguistics['passive_aggressive_ratio'] ?? 0 }}%</span>
            </div>
            <div class="progress" style="height: 6px; background: rgba(255,255,255,0.05);">
                <div class="progress-bar bg-danger" style="width: {{ $linguistics['passive_aggressive_ratio'] ?? 0 }}%;"></div>
            </div>
        </div>

        <div class="col-md-6">
            <h6 class="text-white mb-3" style="font-size: 0.85rem;"><i class="fa-solid fa-book-dead text-emerald me-1"></i> قاموس المصطلحات السامة (Toxic Vocabulary)</h6>
            <div class="d-flex flex-column gap-3">
                @foreach($linguistics['toxic_vocabulary'] ?? [] as $category => $words)
                    <div>
                        <div class="eg-label text-emerald mb-1">{{ $category }}</div>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($words as $w)
                                <span class="badge" style="background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.2); color: #6ee7b7; font-weight: normal;">{{ $w }}</span>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="col-md-6">
            <h6 class="text-white mb-3" style="font-size: 0.85rem;"><i class="fa-solid fa-volume-xmark text-amber me-1"></i> إحصائيات الصمت العقابي</h6>
            <div class="p-3 rounded mb-3" style="background: rgba(245,158,11,0.05); border: 1px solid rgba(245,158,11,0.15);">
                <div class="eg-label text-amber mb-1">متوسط مدة الصمت لمعاقبة الطرف الآخر</div>
                <div class="text-white fw-bold" style="font-size: 1.1rem;">{{ $linguistics['silence_intervals']['average_silent_treatment'] ?? 'N/A' }}</div>
            </div>
            <div class="p-3 rounded" style="background: rgba(239,68,68,0.05); border: 1px solid rgba(239,68,68,0.15);">
                <div class="eg-label text-red mb-1">أطول فترة انقطاع (Longest Silence)</div>
                <div class="text-white fw-bold" style="font-size: 1.1rem;">{{ $linguistics['silence_intervals']['longest_silence'] ?? 'N/A' }}</div>
            </div>
        </div>
        
        <div class="col-12 mt-4 pt-3" style="border-top: 1px solid rgba(255,255,255,0.05);">
            <div class="eg-label mb-2"><i class="fa-solid fa-comment-medical text-indigo me-1"></i> أسلوب التواصل العام (Communication Style)</div>
            <div class="text-muted" style="font-size: 0.85rem; line-height: 1.6;">
                "{{ $linguistics['communication_style'] ?? 'N/A' }}"
            </div>
        </div>
    </div>
</div>
