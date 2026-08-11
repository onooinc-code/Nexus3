@props(['communication'])

<div class="studio-section-title">
    <i class="fa-solid fa-comments"></i> Communication Patterns
</div>

<div class="row g-4 mb-4">
    {{-- Dual Catchphrases --}}
    <div class="col-md-6">
        <div class="studio-dual-panel" style="grid-template-columns: 1fr;">
            <div class="studio-dual-hedra">
                <div class="studio-dual-label text-indigo"><i class="fa-solid fa-robot"></i> Hedra Catchphrases</div>
                <div class="d-flex flex-wrap gap-2">
                    @forelse($communication['catchphrases_hedra'] ?? [] as $phrase)
                        <span class="studio-chip studio-chip-indigo">"{{ $phrase }}"</span>
                    @empty
                        <span class="text-muted small">None</span>
                    @endforelse
                </div>
            </div>
            <div class="studio-dual-contact mt-3">
                <div class="studio-dual-label text-emerald"><i class="fa-solid fa-user"></i> Contact Catchphrases</div>
                <div class="d-flex flex-wrap gap-2">
                    @forelse($communication['catchphrases_contact'] ?? [] as $phrase)
                        <span class="studio-chip studio-chip-emerald">"{{ $phrase }}"</span>
                    @empty
                        <span class="text-muted small">None</span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Meta Stats --}}
    <div class="col-md-6">
        <div class="studio-card p-4 h-100">
            <h6 class="mb-4 text-indigo"><i class="fa-solid fa-chart-line me-2"></i> Meta Patterns</h6>
            
            <div class="row g-3">
                <div class="col-sm-6">
                    <div class="studio-stat-pair">
                        <span class="studio-stat-label">Linguistic Register</span>
                        <span class="studio-stat-value" style="font-size: 0.8rem;">{{ $communication['linguistic_register'] ?? 'N/A' }}</span>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="studio-stat-pair">
                        <span class="studio-stat-label">Initiator</span>
                        <span class="studio-stat-value">{{ $communication['conversation_initiator'] ?? 'N/A' }}</span>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="studio-stat-pair">
                        <span class="studio-stat-label">Active Hours</span>
                        <span class="studio-stat-value"><i class="fa-regular fa-clock me-1 text-muted"></i> {{ $communication['active_hours'] ?? 'N/A' }}</span>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="studio-stat-pair">
                        <span class="studio-stat-label">Block History</span>
                        <span class="studio-stat-value text-red"><i class="fa-solid fa-ban me-1"></i> {{ $communication['block_history'] ?? 'N/A' }}</span>
                    </div>
                </div>
                <div class="col-sm-12">
                    <div class="studio-stat-pair">
                        <span class="studio-stat-label">Response Time Pattern</span>
                        <span class="studio-stat-value" style="font-size: 0.8rem;">{{ $communication['response_time_pattern'] ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="studio-card p-4 h-100">
            <div class="studio-stat-label mb-3"><i class="fa-solid fa-microphone me-1 text-indigo"></i> Voice & Media</div>
            <div class="d-flex flex-column gap-3">
                <div class="studio-stat-pair">
                    <span class="studio-stat-label">Voice Note Frequency</span>
                    <span class="studio-stat-value" style="font-size: 0.8rem;">{{ $communication['voice_note_frequency'] ?? 'N/A' }}</span>
                </div>
                <div class="studio-stat-pair">
                    <span class="studio-stat-label">Media Sharing Style</span>
                    <span class="studio-stat-value" style="font-size: 0.8rem;">{{ $communication['media_sharing_style'] ?? 'N/A' }}</span>
                </div>
                <div class="studio-stat-pair">
                    <span class="studio-stat-label">Call Frequency</span>
                    <span class="studio-stat-value" style="font-size: 0.8rem;">{{ $communication['call_frequency'] ?? 'N/A' }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="studio-card p-4 h-100">
            <div class="studio-stat-label mb-3"><i class="fa-solid fa-face-smile me-1 text-emerald"></i> Emoji Usage</div>
            
            <div class="mb-3">
                <div class="studio-stat-label text-indigo mb-1" style="font-size: 0.6rem;">Hedra</div>
                <div class="d-flex gap-2" style="font-size: 1.25rem;">
                    @forelse($communication['emoji_usage_hedra'] ?? [] as $emj)
                        <span>{{ $emj }}</span>
                    @empty
                        <span class="text-muted" style="font-size: 0.8rem;">None</span>
                    @endforelse
                </div>
            </div>
            <div>
                <div class="studio-stat-label text-emerald mb-1" style="font-size: 0.6rem;">Contact</div>
                <div class="d-flex gap-2" style="font-size: 1.25rem;">
                    @forelse($communication['emoji_usage_contact'] ?? [] as $emj)
                        <span>{{ $emj }}</span>
                    @empty
                        <span class="text-muted" style="font-size: 0.8rem;">None</span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="studio-card p-4 h-100">
            <div class="studio-stat-label mb-3"><i class="fa-solid fa-message me-1 text-amber"></i> Texting Style</div>
            <div class="d-flex flex-column gap-3">
                <div class="studio-stat-pair">
                    <span class="studio-stat-label">Message Length</span>
                    <span class="studio-stat-value" style="font-size: 0.8rem;">{{ $communication['avg_message_length'] ?? 'N/A' }}</span>
                </div>
                <div class="studio-stat-pair">
                    <span class="studio-stat-label">Topic Initiation</span>
                    <span class="studio-stat-value" style="font-size: 0.8rem;">{{ $communication['topic_initiation_style'] ?? 'N/A' }}</span>
                </div>
                <div class="studio-stat-pair">
                    <span class="studio-stat-label">Deletion Pattern</span>
                    <span class="studio-stat-value text-amber" style="font-size: 0.8rem;">{{ $communication['message_deletion_pattern'] ?? 'N/A' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
