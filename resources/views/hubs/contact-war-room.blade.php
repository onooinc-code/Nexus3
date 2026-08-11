@extends('layouts.app')

@section('page_title', 'The War Room ☢️ — ' . ($contact->name ?? 'مجهول'))

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
<style>
    /* ═══════════════════════════════════════════════════════════════════
       STUDIO EG — RTL DEEP SPACE DESIGN SYSTEM (WAR ROOM)
    ═══════════════════════════════════════════════════════════════════ */
    :root {
        --studio-bg:         #020617;
        --studio-surface:    rgba(15, 23, 42, 0.75);
        --studio-border:     rgba(239, 68, 68, 0.3); /* Reddish tint for war room */
        --studio-border-em:  rgba(239, 68, 68, 0.6);
        --studio-indigo:     #6366f1;
        --studio-emerald:    #10b981;
        --studio-red:        #ef4444;
        --studio-red-dim:    rgba(239, 68, 68, 0.15);
        --studio-amber:      #f59e0b;
        --studio-text:       #e2e8f0;
        --studio-muted:      #94a3b8;
        --font-ar:           'Tajawal', sans-serif;
    }

    body {
        background-color: var(--studio-bg) !important;
    }

    .war-room-root {
        direction: rtl;
        text-align: right;
        background: var(--studio-bg);
        min-height: 100vh;
        font-family: var(--font-ar);
        color: var(--studio-text);
        padding-bottom: 3rem;
    }

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
        box-shadow: 0 10px 30px rgba(239,68,68,0.15);
        transform: translateY(-2px);
    }

    /* Section Headers */
    .eg-section-title {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 1.1rem;
        font-weight: 800;
        color: var(--studio-red);
        margin-bottom: 1.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid rgba(239,68,68,0.15);
    }
    .eg-section-title i { font-size: 1.2rem; }

    /* Data Points */
    .eg-label {
        font-size: 0.75rem;
        color: var(--studio-muted);
        font-weight: 700;
        margin-bottom: 0.3rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
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
    .eg-breadcrumb a:hover { color: var(--studio-red); }
    .eg-breadcrumb .sep { color: rgba(255,255,255,0.2); }

    /* Glitch/Pulse animations from war room adapted */
    .animate-glitch { animation: pulse 2s infinite; }
</style>
@endpush

@section('content')
<div class="war-room-root" id="studio-war-dashboard">
    
    {{-- Breadcrumb --}}
    <div class="d-flex justify-content-between align-items-center mb-4 pt-3 px-3">
        <div class="eg-breadcrumb">
            <a href="{{ route('hub.contacts') }}"><i class="fa-solid fa-address-book"></i> جهات الاتصال</a>
            <span class="sep">/</span>
            <a href="{{ route('hub.contacts.profile', $contact->id) }}">{{ strtoupper($contact->name) }}</a>
            <span class="sep">/</span>
            <span class="text-danger"><i class="fa-solid fa-radiation"></i> غرفة الحرب (God Mode)</span>
        </div>
        <div>
            <a href="{{ route('hub.contacts.profile', $contact->id) }}" class="btn btn-sm" style="background: rgba(255,255,255,0.05); color: #fff; border: 1px solid rgba(255,255,255,0.1);">
                <i class="fa-solid fa-arrow-right"></i> عودة للملف
            </a>
        </div>
    </div>

    {{-- War Room Grid --}}
    <div class="row g-4 px-3">
        
        {{-- Left Column --}}
        <div class="col-lg-7 d-flex flex-column gap-4">
            <x-war-room.response-architect :architect="$warRoom['response_architect'] ?? []" />
            
            <div class="row g-4">
                <div class="col-md-6">
                    <x-war-room.reality-distortion :distortion="$warRoom['reality_distortion'] ?? []" />
                </div>
                <div class="col-md-6">
                    <x-war-room.punishment-matrix :matrix="$warRoom['punishment_reward'] ?? []" />
                </div>
            </div>
        </div>

        {{-- Right Column --}}
        <div class="col-lg-5 d-flex flex-column gap-4">
            <x-war-room.burnout-countdown :burnout="$warRoom['burnout'] ?? []" />
            
            <x-war-room.intentions-radar 
                :narcissism="$warRoom['narcissism_meter'] ?? 0"
                :intentions="$warRoom['hidden_intentions'] ?? []" 
            />
            
            <x-war-room.anatomy-target />
        </div>

    </div>
</div>
@endsection
