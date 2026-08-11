@props(['conflicts'])

<div class="eg-card p-4 h-100">
    <div class="eg-section-title text-red" style="border-color: rgba(239,68,68,0.2);">
        <i class="fa-solid fa-swords"></i> محاكي الصراعات وتاريخ المعارك
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="d-flex align-items-center gap-3 p-3 rounded" style="background: rgba(239,68,68,0.05); border: 1px solid rgba(239,68,68,0.15);">
                <i class="fa-solid fa-stopwatch text-red" style="font-size: 1.5rem;"></i>
                <div>
                    <div class="eg-label text-red mb-1">المدة المعتادة للخصام</div>
                    <div class="text-white fw-bold" style="font-size: 0.9rem;">{{ $conflicts['typical_fight_duration'] ?? 'N/A' }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="d-flex align-items-center gap-3 p-3 rounded" style="background: rgba(16,185,129,0.05); border: 1px solid rgba(16,185,129,0.15);">
                <i class="fa-solid fa-handshake-angle text-emerald" style="font-size: 1.5rem;"></i>
                <div>
                    <div class="eg-label text-emerald mb-1">المبادر بالصلح غالباً</div>
                    <div class="text-white fw-bold" style="font-size: 0.9rem;">{{ $conflicts['reconciliation_initiator'] ?? 'N/A' }}</div>
                </div>
            </div>
        </div>
    </div>

    <h6 class="text-white mb-3" style="font-size: 0.85rem;"><i class="fa-solid fa-clock-rotate-left text-amber me-1"></i> أرشيف المعارك التاريخية (Historical Battles)</h6>
    
    <div class="d-flex flex-column gap-3" style="max-height: 250px; overflow-y: auto; padding-left: 0.5rem;">
        @foreach($conflicts['historical_battles'] ?? [] as $battle)
            <div class="p-3 rounded position-relative" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-amber fw-bold" style="font-size: 0.9rem;">{{ $battle['name'] }}</span>
                    <span class="text-muted" style="font-size: 0.7rem;">{{ $battle['date'] }}</span>
                </div>
                
                <div class="row g-2 mt-2">
                    <div class="col-12">
                        <span class="eg-label text-indigo me-2">المنتصر (تكتيكياً):</span>
                        <span style="font-size: 0.85rem; color: #a5b4fc;">{{ $battle['victor'] }}</span>
                    </div>
                    <div class="col-12">
                        <span class="eg-label text-red me-2">الخسائر العاطفية:</span>
                        <span style="font-size: 0.85rem; color: #fca5a5;">{{ $battle['emotional_casualties'] }}</span>
                    </div>
                    <div class="col-12 mt-2 pt-2" style="border-top: 1px dashed rgba(255,255,255,0.1);">
                        <span class="eg-label text-emerald me-2">طريقة الحل:</span>
                        <span style="font-size: 0.8rem; color: #6ee7b7; font-style: italic;">{{ $battle['resolution'] }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
