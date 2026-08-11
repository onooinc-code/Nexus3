@props(['identity'])

<div class="eg-card p-4 h-100 position-relative overflow-hidden">
    {{-- Animated Background Glow --}}
    <div style="position:absolute; top:-50px; right:-50px; width:150px; height:150px; background:var(--studio-indigo); filter:blur(100px); opacity:0.15; border-radius:50%; z-index:0;" class="animate-pulse-slow"></div>

    <div class="position-relative" style="z-index: 1;">
        <div class="d-flex align-items-center gap-4 mb-4">
            <div class="animate-float">
                <div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, var(--studio-indigo), #312e81); border: 2px solid var(--studio-border-em); display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 800; color: #fff; box-shadow: 0 0 20px rgba(99,102,241,0.2);">
                    {{ mb_substr($identity['full_name'] ?? 'مجهول', 0, 1) }}
                </div>
            </div>
            
            <div class="flex-grow-1">
                <h3 class="m-0 text-white fw-bold mb-2">{{ $identity['full_name'] ?? 'مجهول' }}</h3>
                <div class="d-flex gap-2">
                    <span class="eg-chip" style="background: rgba(239,68,68,0.1); border-color: rgba(239,68,68,0.3); color: #fca5a5; font-size: 0.7rem;">
                        <i class="fa-solid fa-triangle-exclamation animate-pulse"></i> مستوى الخطر: {{ $identity['current_danger_level'] ?? 'غير محدد' }}
                    </span>
                    <span class="eg-chip" style="background: rgba(245,158,11,0.1); border-color: rgba(245,158,11,0.3); color: #fcd34d; font-size: 0.7rem;">
                        {{ $identity['relationship_status'] ?? 'غير معروف' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6">
                <div class="eg-label"><i class="fa-solid fa-user-tag text-indigo me-1"></i> ألقاب يستخدمها هدرا</div>
                <div class="d-flex flex-wrap gap-1 mt-1">
                    @foreach($identity['nicknames']['hedra'] ?? [] as $n)
                        <span class="badge bg-indigo-900 text-indigo-200" style="background:var(--studio-indigo-dim); border:1px solid rgba(99,102,241,0.2);">{{ $n }}</span>
                    @endforeach
                </div>
            </div>
            <div class="col-6">
                <div class="eg-label"><i class="fa-solid fa-user-tag text-emerald me-1"></i> ألقاب تستخدمها هي</div>
                <div class="d-flex flex-wrap gap-1 mt-1">
                    @foreach($identity['nicknames']['contact'] ?? [] as $n)
                        <span class="badge" style="background:var(--studio-emerald-dim); border:1px solid rgba(16,185,129,0.2); color:#6ee7b7;">{{ $n }}</span>
                    @endforeach
                </div>
            </div>
        </div>

        <hr style="border-color: rgba(255,255,255,0.05); margin: 1.5rem 0;">

        <div class="mb-3">
            <div class="d-flex justify-content-between eg-label">
                <span><i class="fa-solid fa-mask text-amber me-1"></i> مؤشر هشاشة الغرور (Ego Fragility)</span>
                <span class="text-amber">{{ $identity['ego_fragility_index'] ?? 0 }}%</span>
            </div>
            <div class="progress" style="height: 6px; background: rgba(255,255,255,0.05);">
                <div class="progress-bar bg-warning progress-bar-striped progress-bar-animated" style="width: {{ $identity['ego_fragility_index'] ?? 0 }}%;"></div>
            </div>
            <div class="mt-2 text-muted" style="font-size:0.7rem; font-style:italic;">كلما ارتفع المؤشر، كلما زادت الحساسية تجاه الانتقاد والمقارنة.</div>
        </div>

        <div>
            <div class="eg-label mb-2"><i class="fa-solid fa-tags text-indigo me-1"></i> التصنيف النفسي الأساسي</div>
            <div class="d-flex gap-2">
                @foreach($identity['core_labels'] ?? [] as $lbl)
                    <div class="eg-chip" style="background: rgba(99,102,241,0.1); border-color: rgba(99,102,241,0.2); color: #a5b4fc;">
                        {{ $lbl }}
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
