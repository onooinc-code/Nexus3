@props(['emotional', 'pacts' => [], 'pacts_only' => false])

@if($pacts_only)
    {{-- ── PACTS & COMMITMENTS VIEW ────────────────────────────────────── --}}
    <div class="studio-section-title">
        <i class="fa-solid fa-handshake"></i> Pacts & Commitments
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="studio-dual-panel" style="grid-template-columns: 1fr;">
                <div class="studio-dual-hedra">
                    <div class="studio-dual-label text-indigo"><i class="fa-solid fa-robot"></i> Explicit Promises (Hedra)</div>
                    <ul class="list-unstyled mb-0 d-flex flex-column gap-2" style="font-size: 0.8rem;">
                        @forelse($pacts['explicit_promises_hedra'] ?? [] as $p)
                            <li><i class="fa-solid fa-quote-left text-indigo me-2" style="font-size:0.6rem;"></i> {{ $p }}</li>
                        @empty
                            <li class="text-muted">None</li>
                        @endforelse
                    </ul>
                </div>
                <div class="studio-dual-contact mt-3">
                    <div class="studio-dual-label text-emerald"><i class="fa-solid fa-user"></i> Explicit Promises (Contact)</div>
                    <ul class="list-unstyled mb-0 d-flex flex-column gap-2" style="font-size: 0.8rem;">
                        @forelse($pacts['explicit_promises_contact'] ?? [] as $p)
                            <li><i class="fa-solid fa-quote-left text-emerald me-2" style="font-size:0.6rem;"></i> {{ $p }}</li>
                        @empty
                            <li class="text-muted">None</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="studio-card p-4 h-100">
                <h6 class="mb-4 text-amber"><i class="fa-solid fa-gavel me-2"></i> Boundaries & Violations</h6>
                
                <div class="mb-4">
                    <div class="studio-stat-label mb-2"><i class="fa-solid fa-shield-halved me-1 text-muted"></i> Silent Pacts & Boundaries</div>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach(array_merge($pacts['silent_pacts'] ?? [], $pacts['boundaries_set'] ?? []) as $b)
                            <span class="studio-chip studio-chip-indigo">{{ $b }}</span>
                        @endforeach
                    </div>
                </div>

                <div class="mb-4">
                    <div class="studio-stat-label mb-2"><i class="fa-solid fa-heart-crack me-1 text-red"></i> Broken Promises</div>
                    <ul class="list-unstyled mb-0 d-flex flex-column gap-2" style="font-size: 0.8rem;">
                        @forelse($pacts['broken_promises'] ?? [] as $b)
                            <li class="text-red"><i class="fa-solid fa-xmark me-2"></i> {{ $b }}</li>
                        @empty
                            <li class="text-muted">None</li>
                        @endforelse
                    </ul>
                </div>

                <div>
                    <div class="studio-stat-label mb-2"><i class="fa-solid fa-bomb me-1 text-amber"></i> Ultimatums Given</div>
                    <div class="d-flex flex-wrap gap-2">
                        @forelse($pacts['ultimatums_given'] ?? [] as $u)
                            <span class="studio-chip studio-chip-amber">{{ $u }}</span>
                        @empty
                            <span class="text-muted small">None</span>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

@else
    {{-- ── EMOTIONAL DYNAMICS VIEW ─────────────────────────────────────── --}}
    <div class="studio-section-title">
        <i class="fa-solid fa-heart-pulse"></i> Emotional Dynamics
    </div>

    <div class="row g-4 mb-4">
        {{-- Hedra Side --}}
        <div class="col-md-6">
            <div class="studio-card p-4 h-100" style="border-top: 3px solid var(--studio-indigo);">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="studio-entity-avatar"><i class="fa-solid fa-robot"></i></div>
                    <h6 class="m-0 text-indigo">Hedra's Emotion Engine</h6>
                </div>
                
                <div class="d-flex flex-column gap-4">
                    <div>
                        <div class="studio-stat-label mb-2"><i class="fa-solid fa-heart me-1 text-emerald"></i> Expressions of Love</div>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($emotional['hedra_side']['love_expressions'] ?? [] as $exp)
                                <span class="studio-chip studio-chip-emerald" style="font-style: italic;">{{ $exp }}</span>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <div class="studio-stat-label mb-2"><i class="fa-solid fa-fire me-1 text-amber"></i> Jealousy Triggers</div>
                        <ul class="list-unstyled mb-0 d-flex flex-column gap-1" style="font-size: 0.8rem; color: #cbd5e1;">
                            @foreach($emotional['hedra_side']['jealousy_triggers'] ?? [] as $jt)
                                <li>• {{ $jt }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="d-flex gap-3">
                        <div class="flex-grow-1 p-3 rounded" style="background: rgba(239,68,68,0.05); border: 1px solid rgba(239,68,68,0.15);">
                            <div class="studio-stat-label text-red mb-2"><i class="fa-solid fa-flag me-1"></i> Red Flags</div>
                            <div class="small">{{ implode(' • ', $emotional['hedra_side']['red_flags'] ?? []) }}</div>
                        </div>
                        <div class="flex-grow-1 p-3 rounded" style="background: rgba(16,185,129,0.05); border: 1px solid rgba(16,185,129,0.15);">
                            <div class="studio-stat-label text-emerald mb-2"><i class="fa-solid fa-flag me-1"></i> Green Flags</div>
                            <div class="small">{{ implode(' • ', $emotional['hedra_side']['green_flags'] ?? []) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Contact Side --}}
        <div class="col-md-6">
            <div class="studio-card p-4 h-100" style="border-top: 3px solid var(--studio-emerald);">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="studio-entity-avatar bg-emerald" style="background: linear-gradient(135deg, var(--studio-emerald), #34d399);"><i class="fa-solid fa-user"></i></div>
                    <h6 class="m-0 text-emerald">Contact's Emotion Engine</h6>
                </div>
                
                <div class="d-flex flex-column gap-4">
                    <div>
                        <div class="studio-stat-label mb-2"><i class="fa-solid fa-heart me-1 text-emerald"></i> Expressions of Love</div>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($emotional['contact_side']['love_expressions'] ?? [] as $exp)
                                <span class="studio-chip studio-chip-emerald" style="font-style: italic;">{{ $exp }}</span>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <div class="studio-stat-label mb-2"><i class="fa-solid fa-fire me-1 text-amber"></i> Jealousy Triggers</div>
                        <ul class="list-unstyled mb-0 d-flex flex-column gap-1" style="font-size: 0.8rem; color: #cbd5e1;">
                            @foreach($emotional['contact_side']['jealousy_triggers'] ?? [] as $jt)
                                <li>• {{ $jt }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="p-3 rounded" style="background: rgba(245,158,11,0.05); border: 1px solid rgba(245,158,11,0.15);">
                        <div class="studio-stat-label text-amber mb-2"><i class="fa-solid fa-masks-theater me-1"></i> Manipulation Instances</div>
                        <div class="small">{{ implode(' • ', $emotional['contact_side']['manipulation_instances'] ?? []) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Shared Dynamics --}}
    <div class="row">
        <div class="col-12">
            <div class="studio-card p-4">
                <h6 class="mb-4 text-center w-100"><i class="fa-solid fa-arrows-turn-to-dots me-2 text-indigo"></i> Shared Ecosystem & Conflicts</h6>
                
                <div class="row g-4">
                    <div class="col-md-3">
                        <div class="studio-stat-pair mb-3">
                            <span class="studio-stat-label text-red">Major Conflicts</span>
                            <span class="studio-stat-value" style="font-size:0.8rem;">
                                @foreach($emotional['shared']['major_conflicts'] ?? [] as $c)
                                    <div><i class="fa-solid fa-caret-right me-1 text-muted"></i> {{ $c }}</div>
                                @endforeach
                            </span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="studio-stat-pair mb-3">
                            <span class="studio-stat-label text-emerald">Future Plans</span>
                            <span class="studio-stat-value" style="font-size:0.8rem;">
                                @foreach($emotional['shared']['future_plans'] ?? [] as $p)
                                    <div><i class="fa-solid fa-caret-right me-1 text-muted"></i> {{ $p }}</div>
                                @endforeach
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex flex-column gap-2">
                            <div class="studio-stat-pair">
                                <span class="studio-stat-label">Conflict Resolution Style</span>
                                <span class="studio-stat-value" style="font-size: 0.85rem;">{{ $emotional['shared']['conflict_resolution'] ?? 'N/A' }}</span>
                            </div>
                            <div class="studio-stat-pair">
                                <span class="studio-stat-label">Marriage Discussions</span>
                                <span class="studio-stat-value text-indigo" style="font-size: 0.85rem;">{{ $emotional['shared']['marriage_discussions'] ?? 'N/A' }}</span>
                            </div>
                            <div class="studio-stat-pair">
                                <span class="studio-stat-label">Trust Issues</span>
                                <span class="studio-stat-value text-amber" style="font-size: 0.85rem;">{{ $emotional['shared']['trust_issues'] ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
