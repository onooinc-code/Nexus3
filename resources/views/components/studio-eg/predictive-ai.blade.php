@props(['predictive'])

<div class="eg-card p-4 h-100" style="border-top: 3px solid var(--studio-indigo);">
    <div class="eg-section-title text-indigo" style="border-color: rgba(99,102,241,0.2);">
        <i class="fa-solid fa-microchip"></i> توقعات الذكاء الاصطناعي (Predictive AI)
    </div>

    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-end mb-2">
            <div>
                <div class="eg-label"><i class="fa-solid fa-chart-line text-indigo me-1"></i> احتمالية الانهيار / الانفصال (Churn Probability)</div>
                <div class="text-white" style="font-size: 0.85rem;">مؤشر الخطر المستقبلي للعلاقة.</div>
            </div>
            <div style="font-size: 1.5rem; font-weight: 800; color: {{ ($predictive['churn_probability'] ?? 0) > 60 ? 'var(--studio-red)' : 'var(--studio-emerald)' }};">
                {{ $predictive['churn_probability'] ?? 0 }}%
            </div>
        </div>
        <div class="progress" style="height: 10px; background: rgba(255,255,255,0.05); border-radius: 10px;">
            <div class="progress-bar progress-bar-striped progress-bar-animated {{ ($predictive['churn_probability'] ?? 0) > 60 ? 'bg-danger' : 'bg-success' }}" style="width: {{ $predictive['churn_probability'] ?? 0 }}%;"></div>
        </div>
    </div>

    <div class="mb-4 p-3 rounded" style="background: rgba(99,102,241,0.05); border-right: 3px solid var(--studio-indigo);">
        <div class="eg-label text-indigo mb-2"><i class="fa-solid fa-satellite-dish me-1"></i> التوقع للخطوة القادمة (Next Move Forecast)</div>
        <div class="text-white" style="font-size: 0.9rem; line-height: 1.6;">
            {{ $predictive['next_move_forecast'] ?? 'لا توجد بيانات كافية للتوقع.' }}
        </div>
    </div>

    <div>
        <h6 class="text-white mb-3"><i class="fa-solid fa-code-branch text-emerald me-2"></i> شجرة السيناريوهات (Scenario Simulator)</h6>
        <div class="d-flex flex-column gap-3">
            @foreach($predictive['scenario_simulator'] ?? [] as $sim)
                <div class="p-3 rounded position-relative" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.1);">
                    <span class="badge position-absolute top-0 end-0 m-2" style="background: {{ $sim['emotional_cost'] == 'High' ? 'var(--studio-red)' : 'var(--studio-emerald)' }};">{{ $sim['emotional_cost'] }} Cost</span>
                    
                    <div class="mb-2 pe-5">
                        <span class="eg-label text-indigo d-block">إذا قام هدرا بـ:</span>
                        <span style="font-size:0.85rem; color:#a5b4fc;">{{ $sim['if_hedra_does'] }}</span>
                    </div>
                    
                    <div class="ms-3 ps-3 border-start border-secondary">
                        <span class="eg-label text-emerald d-block">النتيجة المتوقعة:</span>
                        <span style="font-size:0.85rem; color:#6ee7b7;">{{ $sim['contact_reaction'] }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
