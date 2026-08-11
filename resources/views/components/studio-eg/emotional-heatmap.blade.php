@props(['heatmap'])

<div class="eg-card p-4 h-100">
    <div class="eg-section-title text-amber" style="border-color: rgba(245,158,11,0.2);">
        <i class="fa-solid fa-fire"></i> الخريطة الحرارية للمشاعر (Emotional Heatmap)
    </div>

    <div class="mb-4">
        <h6 class="text-white mb-3" style="font-size: 0.85rem;"><i class="fa-solid fa-calendar-week text-amber me-1"></i> تقلبات المزاج الأسبوعية (Weekly Mood Swings)</h6>
        <div class="d-flex align-items-end justify-content-between h-100 pt-2" style="height: 120px; border-bottom: 1px solid rgba(255,255,255,0.1);">
            @php
                $days = [
                    'Monday' => 'الاثنين', 'Tuesday' => 'الثلاثاء', 'Wednesday' => 'الأربعاء',
                    'Thursday' => 'الخميس', 'Friday' => 'الجمعة', 'Saturday' => 'السبت', 'Sunday' => 'الأحد'
                ];
                $swings = $heatmap['weekly_mood_swings'] ?? [];
            @endphp
            @foreach($days as $en => $ar)
                @php 
                    $val = $swings[$en] ?? 50; 
                    $color = $val > 60 ? 'var(--studio-emerald)' : ($val < 40 ? 'var(--studio-red)' : 'var(--studio-amber)');
                @endphp
                <div class="d-flex flex-column align-items-center" style="width: 14%; gap: 0.5rem;">
                    <div style="font-size: 0.65rem; color: {{ $color }};">{{ $val }}%</div>
                    <div style="width: 20px; height: {{ max($val, 5) }}px; background: {{ $color }}; border-radius: 4px 4px 0 0; transition: 0.5s; opacity: 0.8;"></div>
                    <div style="font-size: 0.6rem; color: var(--studio-muted);">{{ mb_substr($ar, 0, 3) }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="row g-3 mt-2">
        <div class="col-md-6">
            <div class="p-3 rounded h-100" style="background: rgba(99,102,241,0.05); border: 1px solid rgba(99,102,241,0.15);">
                <div class="eg-label text-indigo mb-2"><i class="fa-solid fa-clock me-1"></i> ساعات ذروة الضعف والاحتياج</div>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($heatmap['peak_vulnerability_hours'] ?? [] as $hour)
                        <span class="eg-chip" style="background: var(--studio-indigo); border: none; color: #fff;"><i class="fa-regular fa-moon me-1"></i> {{ $hour }}</span>
                    @endforeach
                </div>
                <div class="mt-2 text-muted" style="font-size: 0.7rem;">في هذا الوقت تضعف الدفاعات النفسية بشكل كبير.</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="p-3 rounded h-100" style="background: rgba(239,68,68,0.05); border: 1px solid rgba(239,68,68,0.15);">
                <div class="eg-label text-red mb-2"><i class="fa-solid fa-calendar-xmark me-1"></i> أيام ذروة الغضب والضغط</div>
                <div class="d-flex flex-column gap-1">
                    @foreach($heatmap['peak_anger_days'] ?? [] as $day)
                        <div style="font-size: 0.8rem; color: #fca5a5;"><i class="fa-solid fa-circle-exclamation me-1" style="font-size:0.6rem;"></i> {{ $day }}</div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
