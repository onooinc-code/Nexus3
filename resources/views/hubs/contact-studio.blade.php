@extends('layouts.app')

@section('page_title', ($profile['identity']['full_name'] ?? $contact->name) . ' — Contact Studio')

@push('styles')
<style>
    /* ═══════════════════════════════════════════════════════════════════
       CONTACT STUDIO — DEEP SPACE DESIGN SYSTEM
       Nexus v2 | Glassmorphism | Neon Indigo/Emerald/Red
    ═══════════════════════════════════════════════════════════════════ */

    .studio-root {
        --studio-bg:         #020617;
        --studio-surface:    rgba(15, 23, 42, 0.70);
        --studio-border:     rgba(99, 102, 241, 0.18);
        --studio-border-em:  rgba(99, 102, 241, 0.40);
        --studio-indigo:     #6366f1;
        --studio-indigo-dim: rgba(99, 102, 241, 0.12);
        --studio-emerald:    #10b981;
        --studio-emerald-dim:rgba(16, 185, 129, 0.12);
        --studio-red:        #ef4444;
        --studio-red-dim:    rgba(239, 68, 68, 0.12);
        --studio-amber:      #f59e0b;
        --studio-amber-dim:  rgba(245, 158, 11, 0.12);
        --studio-text:       #e2e8f0;
        --studio-muted:      #64748b;
        --studio-heading:    #f8fafc;
    }

    .studio-root {
        background: var(--studio-bg);
        min-height: 100vh;
        font-family: var(--font-sans, 'Inter', sans-serif);
        color: var(--studio-text);
    }

    /* Breadcrumb strip */
    .studio-breadcrumb {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
        font-size: 0.8rem;
        color: var(--studio-muted);
    }
    .studio-breadcrumb a {
        color: var(--studio-muted);
        text-decoration: none;
        transition: color 0.2s;
    }
    .studio-breadcrumb a:hover { color: var(--studio-indigo); }
    .studio-breadcrumb .sep { color: rgba(100,116,139,0.4); }
    .studio-breadcrumb .current { color: var(--studio-indigo); font-weight: 600; }

    /* Glass card */
    .studio-card {
        background: var(--studio-surface);
        border: 1px solid var(--studio-border);
        border-radius: 16px;
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        transition: border-color 0.3s, box-shadow 0.3s;
        overflow: hidden;
    }
    .studio-card:hover {
        border-color: var(--studio-border-em);
        box-shadow: 0 0 32px rgba(99,102,241,0.08);
    }

    /* ── STUDIO TABS ──────────────────────────────────────────────── */
    .studio-tabs-nav {
        display: flex;
        gap: 0;
        border-bottom: 1px solid var(--studio-border);
        overflow-x: auto;
        scrollbar-width: none;
        flex-shrink: 0;
    }
    .studio-tabs-nav::-webkit-scrollbar { display: none; }

    .studio-tab-btn {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.85rem 1.2rem;
        background: transparent;
        border: none;
        border-bottom: 2px solid transparent;
        color: var(--studio-muted);
        font-size: 0.78rem;
        font-weight: 500;
        cursor: pointer;
        white-space: nowrap;
        transition: all 0.25s;
        letter-spacing: 0.02em;
        text-transform: uppercase;
        flex-shrink: 0;
    }
    .studio-tab-btn i { font-size: 0.75rem; }
    .studio-tab-btn:hover {
        color: var(--studio-text);
        background: rgba(99,102,241,0.04);
    }
    .studio-tab-btn.active {
        color: var(--studio-indigo);
        border-bottom-color: var(--studio-indigo);
        background: var(--studio-indigo-dim);
    }

    .studio-tab-pane { display: none; }
    .studio-tab-pane.active { display: block; animation: studioFadeIn 0.3s ease; }

    @keyframes studioFadeIn {
        from { opacity: 0; transform: translateY(6px); }
        to   { opacity: 1; transform: translateY(0);   }
    }

    /* ── SECTION TITLE ────────────────────────────────────────────── */
    .studio-section-title {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: var(--studio-muted);
        margin-bottom: 1.25rem;
        padding-bottom: 0.65rem;
        border-bottom: 1px solid var(--studio-border);
    }
    .studio-section-title i { color: var(--studio-indigo); font-size: 0.8rem; }

    /* ── INFO CHIP ─────────────────────────────────────────────────── */
    .studio-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.3rem 0.75rem;
        border-radius: 20px;
        font-size: 0.72rem;
        font-weight: 500;
        border: 1px solid;
        line-height: 1;
    }
    .studio-chip-indigo {
        background: var(--studio-indigo-dim);
        border-color: rgba(99,102,241,0.3);
        color: #a5b4fc;
    }
    .studio-chip-emerald {
        background: var(--studio-emerald-dim);
        border-color: rgba(16,185,129,0.3);
        color: #6ee7b7;
    }
    .studio-chip-red {
        background: var(--studio-red-dim);
        border-color: rgba(239,68,68,0.3);
        color: #fca5a5;
    }
    .studio-chip-amber {
        background: var(--studio-amber-dim);
        border-color: rgba(245,158,11,0.3);
        color: #fcd34d;
    }

    /* ── STAT PAIR ─────────────────────────────────────────────────── */
    .studio-stat-pair {
        display: flex;
        flex-direction: column;
        gap: 0.2rem;
    }
    .studio-stat-label {
        font-size: 0.68rem;
        color: var(--studio-muted);
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }
    .studio-stat-value {
        font-size: 0.88rem;
        color: var(--studio-text);
        font-weight: 500;
        line-height: 1.3;
    }

    /* ── DUAL PANEL ────────────────────────────────────────────────── */
    .studio-dual-panel {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    @media (max-width: 768px) {
        .studio-dual-panel { grid-template-columns: 1fr; }
    }
    .studio-dual-hedra {
        border: 1px solid rgba(99,102,241,0.25);
        border-radius: 12px;
        padding: 1rem;
        background: rgba(99,102,241,0.05);
    }
    .studio-dual-contact {
        border: 1px solid rgba(16,185,129,0.25);
        border-radius: 12px;
        padding: 1rem;
        background: rgba(16,185,129,0.05);
    }
    .studio-dual-label {
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    /* ── PULSING LIVE DOT ──────────────────────────────────────────── */
    .studio-live-indicator {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.65rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: var(--studio-emerald);
    }
    .studio-live-dot {
        position: relative;
        width: 8px;
        height: 8px;
    }
    .studio-live-dot::before {
        content: '';
        position: absolute;
        inset: 0;
        background: var(--studio-emerald);
        border-radius: 50%;
        animation: studioPulse 2s ease-in-out infinite;
    }
    .studio-live-dot::after {
        content: '';
        position: absolute;
        inset: -4px;
        border: 1px solid var(--studio-emerald);
        border-radius: 50%;
        animation: studioPulseRing 2s ease-in-out infinite;
    }
    @keyframes studioPulse {
        0%, 100% { transform: scale(1); opacity: 1; }
        50% { transform: scale(0.7); opacity: 0.6; }
    }
    @keyframes studioPulseRing {
        0% { transform: scale(1); opacity: 0.6; }
        100% { transform: scale(2.2); opacity: 0; }
    }

    /* ── PROGRESS BAR ──────────────────────────────────────────────── */
    .studio-progress-track {
        height: 5px;
        background: rgba(255,255,255,0.05);
        border-radius: 4px;
        overflow: hidden;
        flex: 1;
    }
    .studio-progress-fill {
        height: 100%;
        border-radius: 4px;
        transition: width 1s ease;
    }

    /* ── TIMELINE ENTRY ────────────────────────────────────────────── */
    .studio-timeline-item {
        display: flex;
        gap: 1rem;
        padding-bottom: 1.25rem;
        position: relative;
    }
    .studio-timeline-item::before {
        content: '';
        position: absolute;
        left: 16px;
        top: 32px;
        bottom: 0;
        width: 1px;
        background: var(--studio-border);
    }
    .studio-timeline-item:last-child::before { display: none; }
    .studio-timeline-dot {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 0.7rem;
        border: 1px solid;
    }

    /* ── ENTITY NODE ────────────────────────────────────────────────── */
    .studio-entity-node {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.6rem 0.85rem;
        border-radius: 10px;
        border: 1px solid var(--studio-border);
        background: var(--studio-indigo-dim);
        transition: border-color 0.2s;
    }
    .studio-entity-node:hover { border-color: var(--studio-border-em); }
    .studio-entity-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--studio-indigo), #818cf8);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 700;
        color: #fff;
        flex-shrink: 0;
    }

    /* ── SUBCONSCIOUS LOCK CARD ─────────────────────────────────────── */
    .studio-locked-card {
        border: 1px solid rgba(239,68,68,0.2);
        border-radius: 12px;
        overflow: hidden;
        background: rgba(239,68,68,0.04);
    }
    .studio-locked-card .locked-overlay {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 1.25rem;
        gap: 0.4rem;
        cursor: pointer;
        transition: background 0.2s;
    }
    .studio-locked-card .locked-overlay:hover { background: rgba(239,68,68,0.06); }
    .studio-locked-card .card-content {
        padding: 1rem;
        display: none;
        animation: studioFadeIn 0.4s ease;
    }
    .studio-locked-card.unlocked .locked-overlay { display: none; }
    .studio-locked-card.unlocked .card-content { display: block; }

    /* ── SENTIMENT SPARKLINE ────────────────────────────────────────── */
    .studio-sparkline { height: 48px; width: 100%; }

    /* ── RADAR SVG ──────────────────────────────────────────────────── */
    .studio-radar-svg { max-width: 360px; margin: 0 auto; display: block; }
    .radar-grid-line { stroke: rgba(99,102,241,0.15); stroke-width: 0.5; fill: none; }
    .radar-axis-line { stroke: rgba(99,102,241,0.2); stroke-width: 0.5; }
    .radar-data-fill { fill: rgba(99,102,241,0.18); stroke: #6366f1; stroke-width: 1.5; }
    .radar-label { font-size: 8px; fill: #94a3b8; font-family: 'Inter', sans-serif; }
    .radar-value-label { font-size: 7px; fill: #6366f1; font-weight: 700; }

    /* ── AI TOGGLE SWITCH ───────────────────────────────────────────── */
    .studio-toggle {
        position: relative;
        display: inline-block;
        width: 42px;
        height: 22px;
        flex-shrink: 0;
    }
    .studio-toggle input { opacity: 0; width: 0; height: 0; }
    .studio-toggle-slider {
        position: absolute;
        inset: 0;
        background: rgba(100,116,139,0.3);
        border-radius: 22px;
        cursor: pointer;
        transition: background 0.3s;
    }
    .studio-toggle-slider::before {
        content: '';
        position: absolute;
        width: 16px;
        height: 16px;
        left: 3px;
        top: 3px;
        background: #94a3b8;
        border-radius: 50%;
        transition: transform 0.3s, background 0.3s;
    }
    .studio-toggle input:checked + .studio-toggle-slider {
        background: rgba(16,185,129,0.25);
        border: 1px solid var(--studio-emerald);
    }
    .studio-toggle input:checked + .studio-toggle-slider::before {
        transform: translateX(20px);
        background: var(--studio-emerald);
    }

    /* ── UNLOCK BUTTON ──────────────────────────────────────────────── */
    #studio-unlock-btn {
        background: linear-gradient(135deg, rgba(239,68,68,0.12), rgba(239,68,68,0.06));
        border: 1px solid rgba(239,68,68,0.35);
        color: #fca5a5;
        border-radius: 12px;
        padding: 1rem 2rem;
        font-size: 0.82rem;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }
    #studio-unlock-btn:hover {
        background: rgba(239,68,68,0.18);
        border-color: rgba(239,68,68,0.6);
        box-shadow: 0 0 24px rgba(239,68,68,0.2);
        transform: translateY(-1px);
    }
    #studio-unlock-btn i { font-size: 1rem; }

    /* ── FLOAT TOGGLE (Profile ↔ Studio) ────────────────────────────── */
    .studio-view-toggle {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(99,102,241,0.1);
        border: 1px solid rgba(99,102,241,0.25);
        border-radius: 12px;
        padding: 0.3rem;
    }
    .studio-view-btn {
        padding: 0.4rem 0.9rem;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
        color: var(--studio-muted);
        border: none;
        background: transparent;
        cursor: pointer;
        white-space: nowrap;
    }
    .studio-view-btn.active {
        background: var(--studio-indigo);
        color: #fff;
        box-shadow: 0 2px 12px rgba(99,102,241,0.4);
    }
</style>
@endpush

@section('content')
@php
    $identity = $profile['identity'];
    $personal = $profile['personal'];
    $comm     = $profile['communication'];
    $bf       = $profile['big_five'];
    $psych    = $profile['psychological'];
    $emo      = $profile['emotional'];
    $pacts    = $profile['pacts'];
    $graph    = $profile['graph'];
    $rules    = $profile['ai_rules'];
    $life     = $profile['lifestyle'];
    $social   = $profile['social'];
    $sub      = $profile['subconscious'];

    $displayName = $identity['full_name'] ?? $contact->name;
    $initials    = strtoupper(substr($displayName, 0, 1));
@endphp

<div class="studio-root" id="contact-studio">

    {{-- ── BREADCRUMB ──────────────────────────────────────────────── --}}
    <div class="studio-breadcrumb animate-fade-in stagger-1">
        <a href="{{ route('hub.contacts') }}"><i class="fa-solid fa-address-book"></i> Contacts</a>
        <span class="sep">/</span>
        <a href="{{ route('hub.contacts.profile', $contact->id) }}">{{ $displayName }}</a>
        <span class="sep">/</span>
        <span class="current"><i class="fa-solid fa-brain"></i> Contact Studio</span>
    </div>

    {{-- ── IDENTITY HEADER ─────────────────────────────────────────── --}}
    <x-studio.identity-header :contact="$contact" :identity="$identity" :psychological="$psych" />

    {{-- ── PROFILE ↔ STUDIO TOGGLE ────────────────────────────────── --}}
    <div class="d-flex justify-content-end mt-3 mb-3 animate-fade-in stagger-2">
        <div class="studio-view-toggle">
            <a href="{{ route('hub.contacts.profile', $contact->id) }}" class="studio-view-btn">
                <i class="fa-solid fa-id-card me-1"></i> Standard Profile
            </a>
            <button class="studio-view-btn active" disabled>
                <i class="fa-solid fa-brain me-1"></i> Contact Studio
            </button>
        </div>
    </div>

    {{-- ── MAIN STUDIO CARD ─────────────────────────────────────────── --}}
    <div class="studio-card animate-fade-in stagger-3">

        {{-- Tab Navigation --}}
        <div class="studio-tabs-nav" id="studioTabs" role="tablist">
            <button class="studio-tab-btn active" data-tab="demographics">
                <i class="fa-solid fa-user-circle"></i> Demographics
            </button>
            <button class="studio-tab-btn" data-tab="communication">
                <i class="fa-solid fa-comments"></i> Communication
            </button>
            <button class="studio-tab-btn" data-tab="psychology">
                <i class="fa-solid fa-brain"></i> Psychology
            </button>
            <button class="studio-tab-btn" data-tab="emotional">
                <i class="fa-solid fa-heart-pulse"></i> Emotional
            </button>
            <button class="studio-tab-btn" data-tab="pacts">
                <i class="fa-solid fa-handshake"></i> Pacts
            </button>
            <button class="studio-tab-btn" data-tab="graph">
                <i class="fa-solid fa-diagram-project"></i> Social Graph
            </button>
            <button class="studio-tab-btn" data-tab="ai-rules">
                <i class="fa-solid fa-robot"></i> AI Rules
            </button>
            <button class="studio-tab-btn" data-tab="subconscious">
                <i class="fa-solid fa-eye-slash"></i> Subconscious
            </button>
        </div>

        {{-- Tab Content --}}
        <div class="p-4">

            {{-- ① DEMOGRAPHICS ──────────────────────────────────────── --}}
            <div class="studio-tab-pane active" id="tab-demographics">
                <x-studio.demographics-grid :personal="$personal" :identity="$identity" />
            </div>

            {{-- ② COMMUNICATION ─────────────────────────────────────── --}}
            <div class="studio-tab-pane" id="tab-communication">
                <x-studio.communication-panel :communication="$comm" />
            </div>

            {{-- ③ PSYCHOLOGY ────────────────────────────────────────── --}}
            <div class="studio-tab-pane" id="tab-psychology">
                <x-studio.psychological-radar :big_five="$bf" :psychological="$psych" />
            </div>

            {{-- ④ EMOTIONAL DYNAMICS ────────────────────────────────── --}}
            <div class="studio-tab-pane" id="tab-emotional">
                <x-studio.emotional-dynamics :emotional="$emo" />
            </div>

            {{-- ⑤ PACTS & COMMITMENTS ───────────────────────────────── --}}
            <div class="studio-tab-pane" id="tab-pacts">
                <x-studio.emotional-dynamics :emotional="$emo" :pacts="$pacts" :pacts_only="true" />
            </div>

            {{-- ⑥ SOCIAL GRAPH ──────────────────────────────────────── --}}
            <div class="studio-tab-pane" id="tab-graph">
                <x-studio.social-graph :graph="$graph" :social="$social" :life="$life" />
            </div>

            {{-- ⑦ AI RULES ──────────────────────────────────────────── --}}
            <div class="studio-tab-pane" id="tab-ai-rules">
                <x-studio.ai-rules-panel :rules="$rules" />
            </div>

            {{-- ⑧ SUBCONSCIOUS LAYER ────────────────────────────────── --}}
            <div class="studio-tab-pane" id="tab-subconscious">
                <x-studio.subconscious-layer :subconscious="$sub" />
            </div>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    // ── TAB SWITCHING ─────────────────────────────────────────────────
    const tabs    = document.querySelectorAll('.studio-tab-btn');
    const panes   = document.querySelectorAll('.studio-tab-pane');

    tabs.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const target = this.dataset.tab;

            tabs.forEach(function (t) { t.classList.remove('active'); });
            panes.forEach(function (p) { p.classList.remove('active'); });

            this.classList.add('active');
            const pane = document.getElementById('tab-' + target);
            if (pane) {
                pane.classList.add('active');
                // Draw sparkline if graph tab
                if (target === 'graph') { initSparkline(); }
            }
        });
    });

    // ── SENTIMENT SPARKLINE (Canvas / pure JS) ────────────────────────
    function initSparkline() {
        const canvas = document.getElementById('studio-sparkline');
        if (!canvas || canvas.dataset.drawn) { return; }
        canvas.dataset.drawn = '1';

        const ctx    = canvas.getContext('2d');
        const data   = JSON.parse(canvas.dataset.values || '[]');
        const W      = canvas.offsetWidth;
        const H      = canvas.height;
        const max    = 100;
        const step   = W / Math.max(data.length - 1, 1);

        // Draw grid
        ctx.strokeStyle = 'rgba(99,102,241,0.08)';
        ctx.lineWidth   = 0.5;
        [25, 50, 75].forEach(function (y) {
            const yPx = H - (y / max) * H;
            ctx.beginPath(); ctx.moveTo(0, yPx); ctx.lineTo(W, yPx); ctx.stroke();
        });

        // Build gradient fill
        const grad = ctx.createLinearGradient(0, 0, 0, H);
        grad.addColorStop(0,   'rgba(99,102,241,0.35)');
        grad.addColorStop(1,   'rgba(99,102,241,0.02)');

        // Draw area
        ctx.beginPath();
        data.forEach(function (d, i) {
            const x = i * step;
            const y = H - (d.score / max) * H;
            if (i === 0) { ctx.moveTo(x, y); } else { ctx.lineTo(x, y); }
        });
        ctx.lineTo((data.length - 1) * step, H);
        ctx.lineTo(0, H);
        ctx.closePath();
        ctx.fillStyle = grad;
        ctx.fill();

        // Draw line
        ctx.beginPath();
        data.forEach(function (d, i) {
            const x = i * step;
            const y = H - (d.score / max) * H;
            if (i === 0) { ctx.moveTo(x, y); } else { ctx.lineTo(x, y); }
        });
        ctx.strokeStyle = '#6366f1';
        ctx.lineWidth   = 2;
        ctx.stroke();

        // Dots
        data.forEach(function (d, i) {
            const x = i * step;
            const y = H - (d.score / max) * H;
            ctx.beginPath();
            ctx.arc(x, y, 3, 0, Math.PI * 2);
            ctx.fillStyle   = d.score >= 50 ? '#10b981' : '#ef4444';
            ctx.strokeStyle = '#020617';
            ctx.lineWidth   = 1.5;
            ctx.fill();
            ctx.stroke();
        });
    }

    // ── SUBCONSCIOUS UNLOCK ───────────────────────────────────────────
    const unlockBtn   = document.getElementById('studio-unlock-btn');
    const lockedCards = document.querySelectorAll('.studio-locked-card');
    const lockScreen  = document.getElementById('studio-lock-screen');

    if (unlockBtn) {
        unlockBtn.addEventListener('click', function () {
            unlockBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Decrypting...';
            unlockBtn.style.pointerEvents = 'none';

            setTimeout(function () {
                if (lockScreen) {
                    lockScreen.style.transition = 'opacity 0.5s';
                    lockScreen.style.opacity    = '0';
                    setTimeout(function () { lockScreen.style.display = 'none'; }, 500);
                }
                lockedCards.forEach(function (card) { card.classList.add('unlocked'); });
            }, 1200);
        });
    }

    // ── PROGRESS BAR ANIMATION (on page load) ────────────────────────
    document.querySelectorAll('.studio-progress-fill').forEach(function (bar) {
        const targetWidth = bar.dataset.width || '0%';
        bar.style.width = '0%';
        setTimeout(function () { bar.style.width = targetWidth; }, 300);
    });

})();
</script>
@endpush
