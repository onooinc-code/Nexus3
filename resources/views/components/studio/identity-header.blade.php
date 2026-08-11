@props(['contact', 'identity', 'psychological'])

<div class="row mb-4">
    <div class="col-12">
        <div class="studio-card p-4 d-flex align-items-center gap-4">
            
            {{-- Avatar & Basic Status --}}
            <div class="d-flex flex-column align-items-center gap-2">
                <div class="studio-entity-avatar" style="width: 72px; height: 72px; font-size: 1.5rem;">
                    {{ strtoupper(substr($identity['full_name'] ?? 'U', 0, 1)) }}
                </div>
                <div class="studio-live-indicator mt-1">
                    <div class="studio-live-dot"></div>
                    <span style="font-size: 0.6rem;">Live Sync</span>
                </div>
            </div>

            {{-- Core Identity Info --}}
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h2 class="m-0 mb-1" style="font-weight: 700; letter-spacing: -0.02em; font-size: 1.5rem; color: #fff;">
                            {{ $identity['full_name'] ?? 'Unknown Profile' }}
                        </h2>
                        <div class="d-flex gap-3 text-muted" style="font-size: 0.8rem;">
                            <span><i class="fa-solid fa-phone me-1 text-indigo"></i> {{ implode(', ', $identity['phone_numbers'] ?? []) ?: 'No phone' }}</span>
                            <span><i class="fa-solid fa-venus-mars me-1 text-indigo"></i> {{ $identity['gender'] ?? 'Unknown' }} • {{ $identity['estimated_age'] ?? '?' }}y</span>
                        </div>
                    </div>
                    
                    {{-- Relationship Status Badge --}}
                    <div class="text-end">
                        <div class="studio-chip studio-chip-{{ str_contains($identity['relationship_status'], 'خلاف') ? 'amber' : 'emerald' }} mb-2">
                            <i class="fa-solid fa-heart-crack me-1"></i> {{ $identity['relationship_type'] ?? 'Unknown' }}
                        </div>
                        <div style="font-size: 0.75rem; color: var(--studio-muted);">
                            {{ $identity['relationship_status'] ?? 'Unknown Status' }}
                        </div>
                    </div>
                </div>

                {{-- Aliases / Nicknames (Dual View) --}}
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="studio-stat-label mb-2"><i class="fa-solid fa-tag me-1"></i> Nicknames by Hedra</div>
                        <div class="d-flex flex-wrap gap-2">
                            @forelse($identity['nicknames_used_by_hedra'] ?? [] as $nick)
                                <span class="studio-chip studio-chip-indigo">{{ $nick }}</span>
                            @empty
                                <span class="text-muted small">None recorded</span>
                            @endforelse
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="studio-stat-label mb-2"><i class="fa-solid fa-tag me-1 text-emerald"></i> Nicknames by Contact</div>
                        <div class="d-flex flex-wrap gap-2">
                            @forelse($identity['nicknames_used_by_contact'] ?? [] as $nick)
                                <span class="studio-chip studio-chip-emerald">{{ $nick }}</span>
                            @empty
                                <span class="text-muted small">None recorded</span>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>

            {{-- Psychological Snapshot (Right Sidebar of Header) --}}
            <div class="ps-4 ms-2" style="border-left: 1px solid var(--studio-border); min-width: 250px;">
                <div class="studio-stat-label mb-3">Core Psychological State</div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between studio-stat-label mb-1">
                        <span>Attachment Style</span>
                        <span>{{ $psychological['attachment_style'] ?? 0 }}%</span>
                    </div>
                    <div class="studio-progress-track">
                        <div class="studio-progress-fill" style="background: var(--studio-red);" data-width="{{ $psychological['attachment_style'] ?? 0 }}%"></div>
                    </div>
                    <div class="mt-1" style="font-size: 0.65rem; color: #fca5a5;">{{ $psychological['attachment_label'] ?? 'Unknown' }}</div>
                </div>

                <div>
                    <div class="d-flex justify-content-between studio-stat-label mb-1">
                        <span>Emotional Stability</span>
                        <span>{{ $psychological['emotional_stability'] ?? 0 }}%</span>
                    </div>
                    <div class="studio-progress-track">
                        <div class="studio-progress-fill" style="background: var(--studio-amber);" data-width="{{ $psychological['emotional_stability'] ?? 0 }}%"></div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
