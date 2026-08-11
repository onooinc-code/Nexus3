@props(['interrogation'])

<div class="eg-card p-4 h-100" style="border-right: 3px solid var(--studio-red);">
    <div class="eg-section-title text-red" style="border-color: rgba(239,68,68,0.2);">
        <i class="fa-solid fa-scale-unbalanced"></i> غرفة التحقيق وسجل التناقضات
    </div>

    <div class="row g-4">
        {{-- Hypocrisy Ledger --}}
        <div class="col-md-7">
            <h6 class="text-white mb-3"><i class="fa-solid fa-book-skull text-red me-2"></i> سجل التناقضات (Hypocrisy Ledger)</h6>
            <div class="d-flex flex-column gap-3" style="max-height: 300px; overflow-y: auto; padding-left: 0.5rem;">
                @foreach($interrogation['hypocrisy_ledger'] ?? [] as $entry)
                    <div class="p-3 rounded" style="background: rgba(239,68,68,0.05); border: 1px solid rgba(239,68,68,0.15); transition: 0.2s;" onmouseover="this.style.background='rgba(239,68,68,0.1)'" onmouseout="this.style.background='rgba(239,68,68,0.05)'">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge" style="background: rgba(239,68,68,0.2); color: #fca5a5;"><i class="fa-solid fa-triangle-exclamation me-1"></i> {{ $entry['severity'] }}</span>
                            <span class="text-muted" style="font-size: 0.7rem; font-family: monospace;">{{ $entry['timestamp'] }}</span>
                        </div>
                        <div class="mb-2">
                            <div class="eg-label text-indigo mb-1"><i class="fa-solid fa-comment-dots me-1"></i> الإدعاء (ما قالته):</div>
                            <div class="eg-value text-white" style="font-size:0.85rem;">"{{ $entry['statement'] }}"</div>
                        </div>
                        <div>
                            <div class="eg-label text-red mb-1"><i class="fa-solid fa-bolt me-1"></i> الفعل الواقعي:</div>
                            <div class="text-red" style="font-size:0.85rem; font-weight: 600;">{{ $entry['action'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Deception Matrix --}}
        <div class="col-md-5">
            <h6 class="text-white mb-3"><i class="fa-solid fa-mask text-indigo me-2"></i> مصفوفة الكذب (Deception Matrix)</h6>
            
            <div class="d-flex flex-column gap-3 mb-4">
                @php
                    $matrix = $interrogation['deception_matrix'] ?? [];
                    $metrics = [
                        ['label' => 'التلاعب بالحقائق (Gaslighting)', 'val' => $matrix['gaslighting_frequency'] ?? 0, 'color' => 'red'],
                        ['label' => 'تحويل اللوم (Blame Shifting)', 'val' => $matrix['blame_shifting'] ?? 0, 'color' => 'amber'],
                        ['label' => 'لعب دور الضحية (Playing Victim)', 'val' => $matrix['playing_victim'] ?? 0, 'color' => 'indigo'],
                    ];
                @endphp

                @foreach($metrics as $m)
                    <div>
                        <div class="d-flex justify-content-between eg-label">
                            <span>{{ $m['label'] }}</span>
                            <span>{{ $m['val'] }}%</span>
                        </div>
                        <div class="progress" style="height: 6px; background: rgba(255,255,255,0.05);">
                            <div class="progress-bar progress-bar-animated" style="width: {{ $m['val'] }}%; background: var(--studio-{{ $m['color'] }});"></div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="p-3 rounded" style="background: rgba(255,255,255,0.03); border: 1px dashed rgba(255,255,255,0.1);">
                <div class="eg-label text-white mb-2"><i class="fa-solid fa-quote-right text-muted me-1"></i> الحجج والأعذار المكررة:</div>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($matrix['common_excuses'] ?? [] as $excuse)
                        <span class="eg-chip" style="background: rgba(255,255,255,0.05); color: #cbd5e1; border-color: transparent;">{{ $excuse }}</span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
