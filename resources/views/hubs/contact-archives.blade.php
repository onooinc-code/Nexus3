@extends('layouts.app')

@section('page_title', 'The Grand Archives 🗄️ — ' . ($contact->name ?? 'مجهول'))

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
<style>
    /* ═══════════════════════════════════════════════════════════════════
       STUDIO EG — RTL DEEP SPACE DESIGN SYSTEM (ARCHIVES)
    ═══════════════════════════════════════════════════════════════════ */
    :root {
        --studio-bg:         #020617;
        --studio-surface:    rgba(15, 23, 42, 0.75);
        --studio-border:     rgba(100, 116, 139, 0.4); /* Slate tint for archives */
        --studio-border-em:  rgba(100, 116, 139, 0.8);
        --studio-indigo:     #6366f1;
        --studio-emerald:    #10b981;
        --studio-red:        #ef4444;
        --studio-text:       #e2e8f0;
        --studio-muted:      #94a3b8;
        --font-ar:           'Tajawal', sans-serif;
    }

    body {
        background-color: var(--studio-bg) !important;
    }

    .archives-root {
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
        padding: 1.5rem;
    }
    .eg-card:hover {
        border-color: var(--studio-border-em);
        box-shadow: 0 10px 30px rgba(100,116,139,0.15);
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
    .eg-breadcrumb a:hover { color: #fff; }
    .eg-breadcrumb .sep { color: rgba(255,255,255,0.2); }

    /* Stamps */
    .dossier-stamp {
        position: absolute;
        top: 15px;
        left: 20px;
        font-size: 0.8rem;
        font-weight: 800;
        color: var(--studio-red);
        border: 2px solid var(--studio-red);
        border-radius: 6px;
        padding: 0.2rem 0.5rem;
        transform: rotate(-10deg);
        opacity: 0.8;
        pointer-events: none;
    }

    /* Dark tables */
    .ledger-table th { font-weight: 700; color: var(--studio-muted); border-bottom: 1px solid var(--studio-border); font-size: 0.85rem; }
    .ledger-table td { color: var(--studio-text); border-bottom: 1px solid rgba(255,255,255,0.05); padding: 0.75rem 0.5rem; font-size: 0.95rem; }
    .date-cell { color: var(--studio-muted); font-size: 0.8rem !important; }
</style>
@endpush

@section('content')
<div class="archives-root">
    
    {{-- Breadcrumb --}}
    <div class="d-flex justify-content-between align-items-center mb-4 pt-3 px-4">
        <div class="eg-breadcrumb">
            <a href="{{ route('hub.contacts') }}"><i class="fa-solid fa-address-book"></i> جهات الاتصال</a>
            <span class="sep">/</span>
            <a href="{{ route('hub.contacts.profile', $contact->id) }}">{{ strtoupper($contact->name) }}</a>
            <span class="sep">/</span>
            <span class="text-white"><i class="fa-solid fa-folder-open"></i> السجل الأعظم (The Grand Archives)</span>
        </div>
        <div>
            <a href="{{ route('hub.contacts.profile', $contact->id) }}" class="btn btn-sm" style="background: rgba(255,255,255,0.05); color: #fff; border: 1px solid rgba(255,255,255,0.1);">
                <i class="fa-solid fa-arrow-right"></i> عودة للملف
            </a>
        </div>
    </div>

    {{-- Archives Grid --}}
    <div class="container-fluid px-4">
        <div class="row g-4">
            
            {{-- Column 1 --}}
            <div class="col-lg-6 d-flex flex-column gap-4">
                <x-archives.ledger title="سجل التناقضات الزمنية (Chronological Hypocrisy)" stamp="CONFIRMED" :data="$archives['chronological_hypocrisy'] ?? []" type="hypocrisy" />
                <x-archives.ledger title="سجل الوعود المكسورة (Broken Promises)" stamp="VIOLATION" :data="$archives['broken_promises'] ?? []" type="broken" />
                <x-archives.ledger title="سجل الإهانات المغلفة (Passive-Aggressive)" :data="$archives['passive_aggressive'] ?? []" type="passive" />
                <x-archives.ledger title="سجل الاعتذارات المزيفة (Fake Apologies)" :data="$archives['fake_apologies'] ?? []" type="apology" />
                <x-archives.ledger title="سجل نقاط الضعف المسربة (Leaked Vulnerabilities)" stamp="EXPLOITABLE" :data="$archives['leaked_vulnerabilities'] ?? []" type="vulnerability" />
                <x-archives.ledger title="سجل التهرب والانسحاب (Avoidance & Retreat)" :data="$archives['avoidance_retreat'] ?? []" type="avoidance" />
            </div>

            {{-- Column 2 --}}
            <div class="col-lg-6 d-flex flex-column gap-4">
                <x-archives.ledger title="سجل الابتزاز العاطفي (Emotional Blackmail)" stamp="MANIPULATION" :data="$archives['emotional_blackmail'] ?? []" type="blackmail" />
                <x-archives.ledger title="سجل التلاعب المادي والمصلحي (Opportunistic)" :data="$archives['financial_opportunistic'] ?? []" type="opportunistic" />
                <x-archives.ledger title="سجل إثارة الغيرة (Triangulation)" :data="$archives['jealousy_triangulation'] ?? []" type="jealousy" />
                <x-archives.ledger title="سجل الأعذار الوهمية (Fabricated Excuses)" stamp="LIE DETECTED" :data="$archives['fabricated_excuses'] ?? []" type="excuse" />
                <x-archives.ledger title="سجل الاعترافات المتأخرة (Late Confessions)" :data="$archives['late_confessions'] ?? []" type="confession" />
                <x-archives.ledger title="سجل التنازلات (Concessions Ledger)" stamp="VICTORY" :data="$archives['concessions'] ?? []" type="concession" />
                <x-archives.ledger title="سجل الوعود المنفذة (Kept Promises)" :data="$archives['kept_promises'] ?? []" type="kept" />
            </div>

        </div>
    </div>
</div>
@endsection
