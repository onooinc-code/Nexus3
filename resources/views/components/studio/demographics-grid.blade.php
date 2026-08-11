@props(['personal', 'identity'])

<div class="studio-section-title">
    <i class="fa-solid fa-id-card"></i> Demographics & Personal Info
</div>

<div class="row g-4">
    {{-- Personal Info --}}
    <div class="col-md-4">
        <div class="studio-card p-4 h-100">
            <h6 class="mb-4" style="color: var(--studio-indigo);"><i class="fa-solid fa-user me-2"></i> Basics</h6>
            <div class="d-flex flex-column gap-3">
                <div class="studio-stat-pair">
                    <span class="studio-stat-label">Location</span>
                    <span class="studio-stat-value"><i class="fa-solid fa-location-dot me-1 text-muted"></i> {{ $personal['city'] ?? 'Unknown' }}</span>
                </div>
                <div class="studio-stat-pair">
                    <span class="studio-stat-label">Workplace & Title</span>
                    <span class="studio-stat-value"><i class="fa-solid fa-briefcase me-1 text-muted"></i> {{ $personal['job_title'] ?? 'Unknown' }} @ {{ $personal['workplace'] ?? 'Unknown' }}</span>
                </div>
                <div class="studio-stat-pair">
                    <span class="studio-stat-label">Education</span>
                    <span class="studio-stat-value"><i class="fa-solid fa-graduation-cap me-1 text-muted"></i> {{ $personal['education_level'] ?? 'Unknown' }}</span>
                </div>
                <div class="studio-stat-pair">
                    <span class="studio-stat-label">Family / Marital</span>
                    <span class="studio-stat-value"><i class="fa-solid fa-ring me-1 text-muted"></i> {{ $personal['marital_status'] ?? 'Unknown' }} {{ ($personal['has_children'] ?? false) ? '(Has children)' : '' }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Traits --}}
    <div class="col-md-4">
        <div class="studio-card p-4 h-100">
            <h6 class="mb-4" style="color: var(--studio-emerald);"><i class="fa-solid fa-dna me-2"></i> Personality Traits</h6>
            
            <div class="mb-4">
                <div class="studio-stat-label mb-2 text-emerald"><i class="fa-solid fa-plus-circle me-1"></i> Positive</div>
                <div class="d-flex flex-wrap gap-2">
                    @forelse($personal['personality_traits_positive'] ?? [] as $trait)
                        <span class="studio-chip studio-chip-emerald">{{ $trait }}</span>
                    @empty
                        <span class="text-muted small">None recorded</span>
                    @endforelse
                </div>
            </div>

            <div>
                <div class="studio-stat-label mb-2 text-red"><i class="fa-solid fa-minus-circle me-1"></i> Negative</div>
                <div class="d-flex flex-wrap gap-2">
                    @forelse($personal['personality_traits_negative'] ?? [] as $trait)
                        <span class="studio-chip studio-chip-red">{{ $trait }}</span>
                    @empty
                        <span class="text-muted small">None recorded</span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Notes & Secrets --}}
    <div class="col-md-4">
        <div class="studio-card p-4 h-100">
            <h6 class="mb-4" style="color: var(--studio-amber);"><i class="fa-solid fa-user-secret me-2"></i> Observational Notes</h6>
            
            <div class="mb-4">
                <div class="studio-stat-label mb-2">Appearance Notes</div>
                <div class="p-3" style="background: rgba(255,255,255,0.03); border-radius: 8px; border-left: 3px solid var(--studio-amber); font-size: 0.85rem; line-height: 1.5;">
                    {{ $personal['appearance_notes'] ?? 'No notes available.' }}
                </div>
            </div>

            <div>
                <div class="studio-stat-label mb-2">Secrets Confided</div>
                <div class="p-3" style="background: rgba(255,255,255,0.03); border-radius: 8px; border-left: 3px solid var(--studio-indigo); font-size: 0.85rem; line-height: 1.5;">
                    <i class="fa-solid fa-lock text-indigo me-1"></i> {{ $personal['secrets_confided'] ?? 'No secrets recorded.' }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="studio-card p-4">
            <h6 class="mb-3"><i class="fa-solid fa-users me-2 text-indigo"></i> Family Members Mentioned</h6>
            <div class="d-flex flex-wrap gap-3">
                @forelse($personal['family_members_mentioned'] ?? [] as $member)
                    <div class="studio-entity-node">
                        <div class="studio-entity-avatar bg-secondary"><i class="fa-solid fa-user"></i></div>
                        <div style="font-size: 0.8rem;">{{ $member }}</div>
                    </div>
                @empty
                    <span class="text-muted small">No family members recorded.</span>
                @endforelse
            </div>
        </div>
    </div>
</div>
