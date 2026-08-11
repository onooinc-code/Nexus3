@extends('layouts.app')

@section('page_title', ($profileEg['identity']['full_name'] ?? $contact->name) . ' — الاستوديو التحليلي (مصر)')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
<style>
    /* ═══════════════════════════════════════════════════════════════════
       STUDIO EG — RTL DEEP SPACE DESIGN SYSTEM
    ═══════════════════════════════════════════════════════════════════ */
    :root {
        --studio-bg:         #020617;
        --studio-surface:    rgba(15, 23, 42, 0.75);
        --studio-border:     rgba(99, 102, 241, 0.2);
        --studio-border-em:  rgba(99, 102, 241, 0.45);
        --studio-indigo:     #6366f1;
        --studio-indigo-dim: rgba(99, 102, 241, 0.15);
        --studio-emerald:    #10b981;
        --studio-emerald-dim:rgba(16, 185, 129, 0.15);
        --studio-red:        #ef4444;
        --studio-red-dim:    rgba(239, 68, 68, 0.15);
        --studio-amber:      #f59e0b;
        --studio-amber-dim:  rgba(245, 158, 11, 0.15);
        --studio-text:       #e2e8f0;
        --studio-muted:      #94a3b8;
        --font-ar:           'Tajawal', sans-serif;
    }

    .studio-eg-root {
        direction: rtl;
        text-align: right;
        background: var(--studio-bg);
        min-height: 100vh;
        font-family: var(--font-ar);
        color: var(--studio-text);
        padding-bottom: 3rem;
    }

    /* Override bootstrap margins/paddings for RTL if needed */
    .studio-eg-root .me-1 { margin-left: 0.25rem !important; margin-right: 0 !important; }
    .studio-eg-root .me-2 { margin-left: 0.5rem !important; margin-right: 0 !important; }
    .studio-eg-root .me-3 { margin-left: 1rem !important; margin-right: 0 !important; }
    .studio-eg-root .ms-auto { margin-left: auto !important; margin-right: 0 !important; }
    .studio-eg-root .ps-4 { padding-right: 1.5rem !important; padding-left: 0 !important; }

    /* Glass card */
    .eg-card {
        background: var(--studio-surface);
        border: 1px solid var(--studio-border);
        border-radius: 16px;
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
        position: relative;
    }
    .eg-card:hover {
        border-color: var(--studio-border-em);
        box-shadow: 0 10px 30px rgba(99,102,241,0.1);
        transform: translateY(-2px);
    }

    /* Section Headers */
    .eg-section-title {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 1.1rem;
        font-weight: 800;
        color: #fff;
        margin-bottom: 1.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }
    .eg-section-title i { font-size: 1.2rem; }

    /* Data Points */
    .eg-label {
        font-size: 0.75rem;
        color: var(--studio-muted);
        font-weight: 700;
        margin-bottom: 0.3rem;
    }
    .eg-value {
        font-size: 0.95rem;
        font-weight: 500;
        color: #fff;
        line-height: 1.5;
    }

    /* Chips */
    .eg-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.4rem 0.8rem;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 700;
        border: 1px solid;
    }

    /* Animations */
    .animate-float { animation: float 6s ease-in-out infinite; }
    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-5px); }
        100% { transform: translateY(0px); }
    }
    
    .animate-pulse-slow { animation: pulseSlow 3s infinite; }
    @keyframes pulseSlow {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.7; transform: scale(0.98); }
    }

    /* Breadcrumb */
    .eg-breadcrumb {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
        font-size: 0.85rem;
        font-weight: 500;
    }
    .eg-breadcrumb a { color: var(--studio-muted); text-decoration: none; transition: 0.2s; }
    .eg-breadcrumb a:hover { color: var(--studio-indigo); }
    .eg-breadcrumb .sep { color: rgba(255,255,255,0.2); }
</style>
@endpush

@section('content')
@php
    $idCore = $profileEg['identity'] ?? [];
    $inter  = $profileEg['interrogation'] ?? [];
    $expl   = $profileEg['exploitation'] ?? [];
    $pred   = $profileEg['predictive_ai'] ?? [];
    $ling   = $profileEg['linguistics'] ?? [];
    $heat   = $profileEg['emotional_heatmap'] ?? [];
    $att    = $profileEg['attachment'] ?? [];
    $conf   = $profileEg['conflicts'] ?? [];
    $lev    = $profileEg['leverage'] ?? [];
    $sub    = $profileEg['subconscious'] ?? [];
@endphp

<div class="studio-eg-root" id="studio-eg-dashboard">

    {{-- Breadcrumb & Toggle --}}
    <div class="d-flex justify-content-between align-items-center mb-4 animate-fade-in">
        <div class="eg-breadcrumb">
            <a href="{{ route('hub.contacts') }}"><i class="fa-solid fa-address-book"></i> جهات الاتصال</a>
            <span class="sep">/</span>
            <a href="{{ route('hub.contacts.profile', $contact->id) }}">{{ $idCore['full_name'] ?? 'مجهول' }}</a>
            <span class="sep">/</span>
            <span class="text-indigo"><i class="fa-solid fa-eye"></i> الاستوديو التحليلي (مصر)</span>
        </div>
        
        <div>
            <a href="{{ route('hub.contacts.studio', $contact->id) }}" class="btn btn-sm" style="background: rgba(255,255,255,0.05); color: #fff; border: 1px solid rgba(255,255,255,0.1);">
                <i class="fa-solid fa-globe"></i> النسخة الإنجليزية
            </a>
        </div>
    </div>

    {{-- Massive Grid Layout (Instead of Tabs) --}}
    <div class="row g-4">
        
        {{-- Row 1: Identity & Interrogation --}}
        <div class="col-lg-4 animate-fade-in stagger-1">
            <x-studio-eg.identity-core :identity="$idCore" />
        </div>
        <div class="col-lg-8 animate-fade-in stagger-2">
            <x-studio-eg.interrogation-room :interrogation="$inter" />
        </div>

        {{-- Row 2: Exploitation & AI Prediction --}}
        <div class="col-lg-7 animate-fade-in stagger-3">
            <x-studio-eg.exploitation-map :exploitation="$expl" />
        </div>
        <div class="col-lg-5 animate-fade-in stagger-4">
            <x-studio-eg.predictive-ai :predictive="$pred" />
        </div>

        {{-- Row 3: Linguistics & Attachment --}}
        <div class="col-lg-6 animate-fade-in stagger-5">
            <x-studio-eg.linguistic-analysis :linguistics="$ling" />
        </div>
        <div class="col-lg-6 animate-fade-in stagger-6">
            <x-studio-eg.attachment-matrix :attachment="$att" />
        </div>

        {{-- Row 4: Conflict Simulator & Leverage --}}
        <div class="col-lg-6 animate-fade-in stagger-7">
            <x-studio-eg.conflict-simulator :conflicts="$conf" />
        </div>
        <div class="col-lg-6 animate-fade-in stagger-8">
            <x-studio-eg.financial-social-leverage :leverage="$lev" />
        </div>

        {{-- Row 5: Deep Subconscious --}}
        <div class="col-12 animate-fade-in stagger-9">
            <x-studio-eg.deep-subconscious :subconscious="$sub" />
        </div>
        
    </div>

</div>
@endsection

@push('styles')
<style>
    /* Staggered Fade In */
    .animate-fade-in { opacity: 0; transform: translateY(20px); animation: fadeInUp 0.8s ease forwards; }
    @keyframes fadeInUp { to { opacity: 1; transform: translateY(0); } }
    .stagger-1 { animation-delay: 0.1s; }
    .stagger-2 { animation-delay: 0.2s; }
    .stagger-3 { animation-delay: 0.3s; }
    .stagger-4 { animation-delay: 0.4s; }
    .stagger-5 { animation-delay: 0.5s; }
    .stagger-6 { animation-delay: 0.6s; }
    .stagger-7 { animation-delay: 0.7s; }
    .stagger-8 { animation-delay: 0.8s; }
    .stagger-9 { animation-delay: 0.9s; }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const unlockBtn = document.getElementById('eg-unlock-btn');
        const lockScreen = document.getElementById('eg-lock-screen');
        const progress = document.getElementById('eg-unlock-progress');
        const content = document.getElementById('eg-unlocked-content');
        const container = document.getElementById('eg-subconscious-container');

        if(unlockBtn) {
            unlockBtn.addEventListener('click', function() {
                unlockBtn.style.display = 'none';
                progress.classList.remove('d-none');
                
                let pct = 0;
                let interval = setInterval(() => {
                    pct += Math.floor(Math.random() * 15) + 5;
                    if(pct >= 100) {
                        pct = 100;
                        clearInterval(interval);
                        setTimeout(() => {
                            lockScreen.style.opacity = '0';
                            lockScreen.style.pointerEvents = 'none';
                            setTimeout(() => {
                                lockScreen.style.display = 'none';
                                content.style.opacity = '1';
                                content.style.filter = 'blur(0px)';
                                content.style.pointerEvents = 'auto';
                                container.style.border = '1px solid rgba(239,68,68,0.3)';
                            }, 500);
                        }, 200);
                    }
                    progress.innerText = `Decrypting Protocol... ${pct}%`;
                }, 100);
            });
        }
    });
</script>
@endpush
