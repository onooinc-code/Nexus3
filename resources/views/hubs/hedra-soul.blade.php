@extends('layouts.app')

@push('styles')
<link href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/tokyo-night-dark.min.css" rel="stylesheet">
<style>
    /* Modern Glassmorphism & Cyberpunk Theme for HedraSoul */
    :root {
        --hs-bg: #0b0e14;
        --hs-card-bg: rgba(18, 24, 38, 0.75);
        --hs-border: rgba(255, 255, 255, 0.08);
        --hs-border-glow: rgba(0, 176, 255, 0.3);
        --hs-cyan: #00b0ff;
        --hs-emerald: #00e676;
        --hs-indigo: #6366f1;
        --hs-purple: #a855f7;
        --hs-amber: #ffb300;
        --hs-text-muted: #94a3b8;
    }

    #HAgentsSetupModal, #HSessionsModal, #HHealthModal {
        z-index: 1060 !important;
    }

    /* Top Glowing Progress Bar */
    .soul-progress-bar {
        position: absolute;
        top: 0;
        left: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--hs-indigo), var(--hs-cyan), var(--hs-emerald));
        box-shadow: 0 0 12px var(--hs-cyan), 0 0 6px var(--hs-emerald);
        z-index: 1050;
        transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.3s ease;
        width: 0%;
        opacity: 0;
    }

    /* Override global layout specifically for HedraSoul full-height */
    .main-content {
        display: flex !important;
        flex-direction: column !important;
        height: calc(100vh - var(--topbar-height, 64px) - var(--statusbar-height, 40px)) !important;
        max-height: calc(100vh - var(--topbar-height, 64px) - var(--statusbar-height, 40px)) !important;
        overflow: hidden !important;
        padding: 4px 16px !important;
    }

    .hs-header {
        flex-shrink: 0;
        margin-bottom: 12px;
    }

    .soul-container {
        flex: 1;
        min-height: 0; /* Critical: allows flex child to shrink below content size */
        position: relative;
        display: flex;
        background: var(--hs-bg);
        border: 1px solid var(--hs-border);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6), inset 0 1px 0 rgba(255, 255, 255, 0.05);
    }
    
    /* Left Column: Sessions Sidebar */
    .soul-sessions {
        width: 280px;
        border-right: 1px solid var(--hs-border);
        background: rgba(15, 20, 30, 0.7);
        backdrop-filter: blur(16px);
        display: flex;
        flex-direction: column;
        transition: all 0.3s ease;
    }

    .soul-sessions.collapsed {
        width: 72px;
        min-width: 72px;
        overflow: hidden;
    }
    .soul-sessions.collapsed .session-search-box,
    .soul-sessions.collapsed .session-item-content,
    .soul-sessions.collapsed .sidebar-header-text,
    .soul-sessions.collapsed .sidebar-header-actions {
        display: none !important;
    }
    .soul-sessions.collapsed .session-item-icon-only,
    .soul-sessions.collapsed .sidebar-header-icon {
        display: flex !important;
    }

    .session-item-icon-only {
        display: none;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        color: var(--hs-text-muted);
        padding: 4px 0;
    }
    .session-item.active .session-item-icon-only {
        color: var(--hs-cyan);
    }
    .sidebar-header-icon {
        display: none;
        justify-content: center;
        align-items: center;
        width: 100%;
        cursor: pointer;
        padding: 4px 0;
    }

    .session-search-box {
        padding: 10px 12px;
        background: rgba(0, 0, 0, 0.3);
        border-bottom: 1px solid var(--hs-border);
    }

    .session-search-input {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid var(--hs-border);
        color: #fff;
        font-size: 0.82rem;
        border-radius: 20px;
        padding: 5px 12px 5px 30px;
        width: 100%;
        transition: all 0.2s ease;
    }
    .session-search-input:focus {
        background: rgba(0, 0, 0, 0.5);
        border-color: var(--hs-cyan);
        box-shadow: 0 0 10px rgba(0, 176, 255, 0.2);
        outline: none;
    }

    .session-list {
        flex: 1;
        overflow-y: auto;
        padding: 8px;
    }

    .session-item {
        padding: 10px 12px;
        margin-bottom: 6px;
        border: 1px solid transparent;
        border-radius: 10px;
        cursor: pointer;
        background: rgba(255, 255, 255, 0.02);
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .session-item:hover {
        background: rgba(255, 255, 255, 0.05);
        border-color: rgba(255, 255, 255, 0.1);
        transform: translateX(2px);
    }
    .session-item.active {
        background: rgba(0, 176, 255, 0.12);
        border-color: var(--hs-cyan);
        box-shadow: 0 0 15px rgba(0, 176, 255, 0.15), inset 3px 0 0 var(--hs-cyan);
    }
    .session-item.focused-item {
        background: rgba(255, 255, 255, 0.1);
        border-color: rgba(255, 255, 255, 0.2);
    }
    
    /* Pagination Styles for Cyberpunk Glass Theme */
    .pagination {
        margin-bottom: 0;
        justify-content: center;
        gap: 6px;
    }
    
    /* Hide the verbose 'Showing X to Y' text from Laravel default view */
    .soul-sessions .d-flex.justify-content-sm-between > div:first-child {
        display: none !important;
    }
    .soul-sessions .d-flex.justify-content-sm-between > div:last-child {
        width: 100%;
        display: flex;
        justify-content: center;
    }

    .page-item .page-link {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: var(--hs-text-muted);
        border-radius: 8px !important;
        backdrop-filter: blur(8px);
        transition: all 0.3s ease;
        padding: 4px 10px;
        font-weight: 500;
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 32px;
        font-size: 0.8rem;
    }
    .page-item .page-link:hover {
        background: rgba(0, 176, 255, 0.15);
        color: var(--hs-cyan);
        border-color: var(--hs-cyan);
        box-shadow: 0 0 10px rgba(0, 176, 255, 0.2);
        transform: translateY(-1px);
        z-index: 2;
    }
    .page-item.active .page-link {
        background: linear-gradient(135deg, rgba(0, 176, 255, 0.8), rgba(99, 102, 241, 0.8));
        border-color: rgba(0, 176, 255, 0.5);
        color: #fff;
        box-shadow: 0 4px 12px rgba(0, 176, 255, 0.3);
        z-index: 3;
    }
    .page-item.disabled .page-link {
        background: rgba(0, 0, 0, 0.2);
        border-color: rgba(255, 255, 255, 0.05);
        color: rgba(255, 255, 255, 0.2);
    }

    /* Modal Pagination Buttons */
    .hs-page-btn {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: var(--hs-text-muted);
        border-radius: 8px;
        backdrop-filter: blur(8px);
        transition: all 0.3s ease;
        padding: 4px 12px;
        font-weight: 500;
        font-size: 0.8rem;
    }
    .hs-page-btn:hover:not(:disabled) {
        background: rgba(0, 176, 255, 0.15);
        color: var(--hs-cyan);
        border-color: var(--hs-cyan);
        box-shadow: 0 0 10px rgba(0, 176, 255, 0.2);
    }
    .hs-page-btn:disabled {
        background: rgba(0, 0, 0, 0.2);
        border-color: rgba(255, 255, 255, 0.05);
        color: rgba(255, 255, 255, 0.2);
    }
    
    /* Uniform Header Action Buttons (4 equal-sized buttons with flag) */
    .hs-action-btn {
        width: 160px;
        height: 42px;
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        color: #e2e8f0;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-flex;
        align-items: center;
        justify-content: flex-start;
        border-radius: 8px;
        padding: 0;
        font-size: 0.82rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        overflow: hidden;
    }
    .hs-action-btn:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: rgba(255, 255, 255, 0.25);
        box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        transform: translateY(-1px);
        color: #fff;
    }
    
    .hs-btn-flag {
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-right: 1px solid rgba(255,255,255,0.1);
        background: rgba(0,0,0,0.2);
        font-size: 1rem;
    }

    .hs-btn-text {
        flex: 1;
        text-align: center;
        font-weight: 600;
        padding: 0 8px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Primary (Session) */
    .hs-action-btn.btn-primary .hs-btn-flag { color: #00b0ff; border-left: 3px solid #00b0ff; }
    .hs-action-btn.btn-primary:hover { box-shadow: 0 4px 15px rgba(0, 176, 255, 0.25); border-color: rgba(0, 176, 255, 0.4); }

    /* Success (H-Health) */
    .hs-action-btn.btn-success .hs-btn-flag { color: #00e676; border-left: 3px solid #00e676; }
    .hs-action-btn.btn-success:hover { box-shadow: 0 4px 15px rgba(0, 230, 118, 0.25); border-color: rgba(0, 230, 118, 0.4); }

    /* Info (Setup) */
    .hs-action-btn.btn-info .hs-btn-flag { color: #00e5ff; border-left: 3px solid #00e5ff; }
    .hs-action-btn.btn-info:hover { box-shadow: 0 4px 15px rgba(0, 229, 255, 0.25); border-color: rgba(0, 229, 255, 0.4); }

    /* Danger (Sync DB) */
    .hs-action-btn.btn-danger .hs-btn-flag { color: #ff1744; border-left: 3px solid #ff1744; }
    .hs-action-btn.btn-danger:hover { box-shadow: 0 4px 15px rgba(255, 23, 68, 0.25); border-color: rgba(255, 23, 68, 0.4); }
    
    /* Center Column: Main Chat Hub */
    .soul-chat {
        flex: 1;
        position: relative;
        display: flex;
        flex-direction: column;
        background: rgba(11, 14, 20, 0.4);
        min-width: 0;
        min-height: 0; /* Critical: allows chat-history to grow correctly */
    }

    .chat-header {
        padding: 12px 20px;
        border-bottom: 1px solid var(--hs-border);
        background: rgba(15, 20, 30, 0.85);
        backdrop-filter: blur(16px);
    }

    .chat-history {
        flex: 1;
        min-height: 0; /* Critical: without this, flex: 1 won't shrink properly */
        padding: 24px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .chat-history::-webkit-scrollbar {
        width: 6px;
    }
    .chat-history::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.15);
        border-radius: 10px;
    }
    .chat-history::-webkit-scrollbar-thumb:hover {
        background: var(--hs-cyan);
    }

    .chat-bubble {
        max-width: 90%;
        width: 85%;
        padding: 14px 20px;
        border-radius: 16px;
        font-size: 0.93rem;
        line-height: 1.4; /* Reduced line-height */
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        direction: rtl;
        text-align: right;
        word-break: break-word;
        overflow-wrap: break-word;
        white-space: normal; /* Allow normal whitespace processing for markdown */
        align-self: center; /* Center all bubbles */
    }

    .msg-content p, .markdown-body p {
        margin-bottom: 0.5rem; /* Reduce paragraph spacing */
    }
    
    .msg-content p:last-child, .markdown-body p:last-child {
        margin-bottom: 0;
    }

    .chat-bubble.user {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.25), rgba(79, 70, 229, 0.15));
        border: 1px solid rgba(99, 102, 241, 0.4);
        color: #f1f5f9;
    }

    .chat-bubble.agent {
        background: linear-gradient(135deg, rgba(0, 176, 255, 0.12), rgba(15, 23, 42, 0.8));
        border: 1px solid rgba(0, 176, 255, 0.3);
        color: #e2e8f0;
    }

    .chat-bubble.system {
        background: rgba(255, 179, 0, 0.08);
        border: 1px dashed rgba(255, 179, 0, 0.3);
        font-size: 0.82rem;
        color: #fbbf24;
        border-radius: 10px;
    }

    .msg-content pre {
        white-space: pre-wrap !important;
        word-break: break-all !important;
        max-width: 100%;
        overflow-x: auto;
        text-align: left; /* Keep code blocks LTR for readability */
        direction: ltr;
    }
    .msg-content code {
        word-break: break-word;
        white-space: pre-wrap;
    }

    .chat-composer {
        padding: 16px 20px;
        border-top: 1px solid var(--hs-border);
        background: rgba(15, 20, 30, 0.85);
        backdrop-filter: blur(16px);
    }

    .composer-box {
        background: rgba(10, 15, 25, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 24px;
        transition: all 0.3s ease;
        backdrop-filter: blur(12px);
        box-shadow: inset 0 2px 10px rgba(0,0,0,0.2);
    }
    .composer-box:focus-within {
        border-color: var(--hs-cyan);
        box-shadow: 0 0 20px rgba(0, 176, 255, 0.2), inset 0 2px 10px rgba(0,0,0,0.3);
        background: rgba(10, 15, 25, 0.8);
    }
    
    .composer-textarea {
        background: transparent;
        border: none;
        color: #fff;
        resize: none;
        width: 100%;
        padding: 14px 20px;
        outline: none;
        font-size: 0.95rem;
        max-height: 150px;
        overflow-y: auto;
    }
    .composer-textarea::placeholder {
        color: rgba(255, 255, 255, 0.3) !important;
        font-style: italic;
        transition: opacity 0.3s ease;
    }
    .composer-textarea:focus::placeholder {
        opacity: 0.5;
    }

    /* Send Button */
    #soulSendBtn {
        background: linear-gradient(135deg, var(--hs-indigo), var(--hs-cyan));
        border: none;
        border-radius: 50%;
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 15px rgba(0, 176, 255, 0.3);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        color: white;
    }
    #soulSendBtn:hover {
        transform: scale(1.1) rotate(-5deg);
        box-shadow: 0 6px 20px rgba(0, 176, 255, 0.5);
    }
    #soulSendBtn:active {
        transform: scale(0.95);
    }
    #soulSendBtn i {
        margin-left: -2px;
        font-size: 1.1rem;
    }

    /* Right Column: Controls & Telemetry */
    .soul-controls {
        width: 310px;
        border-left: 1px solid var(--hs-border);
        background: rgba(15, 20, 30, 0.7);
        backdrop-filter: blur(16px);
        padding: 16px;
        overflow-y: auto;
        transition: all 0.3s ease;
    }

    .soul-controls.collapsed {
        width: 0px;
        min-width: 0px;
        overflow: hidden;
        border-left: none;
        padding: 0;
    }

    .control-card {
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid var(--hs-border);
        border-radius: 12px;
        padding: 14px;
        margin-bottom: 16px;
    }

    .animate-pulse-glow {
        animation: pulseGlow 2s infinite ease-in-out;
    }

    @keyframes pulseGlow {
        0% { box-shadow: 0 0 0 0 rgba(0, 230, 118, 0.4); }
        70% { box-shadow: 0 0 0 10px rgba(0, 230, 118, 0); }
        100% { box-shadow: 0 0 0 0 rgba(0, 230, 118, 0); }
    }

    /* Responsive Layout Overrides */
    @media (max-width: 1200px) {
        .soul-controls:not(.collapsed) {
            position: absolute;
            right: 0;
            height: 100%;
            z-index: 10;
            box-shadow: -10px 0 30px rgba(0,0,0,0.5);
        }
    }

    @media (max-width: 768px) {
        .soul-sessions:not(.collapsed) {
            position: absolute;
            left: 0;
            height: 100%;
            z-index: 10;
            box-shadow: 10px 0 30px rgba(0,0,0,0.5);
            width: 100%;
        }
        .hs-header.d-flex {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 12px;
        }
        .hs-header > div {
            width: 100%;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .chat-header {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 12px;
        }
        .chat-header > div {
            width: 100%;
            justify-content: space-between;
        }
        .chat-bubble { max-width: 95%; }
        .chat-history { padding: 12px; }
    }
</style>
@endpush

@section('content')
<!-- Page Header (Compact) -->
<div class="hs-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2 class="h4 text-light mb-1 d-flex align-items-center gap-2">
            <i class="fa-solid fa-ghost text-primary"></i> Hedra Soul
            <span class="badge bg-primary bg-opacity-20 text-primary border border-primary border-opacity-30 rounded-pill fs-7">Cyberpunk Glass v3.0</span>
        </h2>
        <p class="text-muted small mb-0">Direct interaction, local DB synchronization, and live control panel for Souly AI.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <!-- 1. H-Session Selector -->
        <button class="hs-action-btn btn-primary" id="hSessionBtn" data-bs-toggle="modal" data-bs-target="#HSessionsModal" title="Click to view and switch Hermes Sessions">
            <div class="hs-btn-flag"><i class="fa-solid fa-layer-group"></i></div>
            <div class="hs-btn-text" id="hSessionBtnTitle" title="{{ $hermesSettings['current_session_title'] ?? ($selectedSession->title ?? 'Session') }}">{{ $hermesSettings['current_session_title'] ?? ($selectedSession->title ?? 'Session') }}</div>
        </button>

        <!-- 2. H-Health Telemetry -->
        <button class="hs-action-btn btn-success" id="hHealthBtn" data-bs-toggle="modal" data-bs-target="#HHealthModal" title="Click to view Hermes Health Telemetry Report">
            <div class="hs-btn-flag"><i class="fa-solid fa-heart-pulse"></i></div>
            <div class="hs-btn-text">H-Health <span class="badge bg-success ms-1" style="font-size: 0.6rem;" id="hHealthStatusBadge">Checking</span></div>
        </button>

        <!-- 3. Setup -->
        <button class="hs-action-btn btn-info" data-bs-toggle="modal" data-bs-target="#HAgentsSetupModal">
            <div class="hs-btn-flag"><i class="fa-solid fa-sliders"></i></div>
            <div class="hs-btn-text">Setup</div>
        </button>

        <!-- 4. Sync DB -->
        <button class="hs-action-btn btn-danger" onclick="triggerForceSync()">
            <div class="hs-btn-flag"><i class="fa-solid fa-rotate"></i></div>
            <div class="hs-btn-text">Sync DB</div>
        </button>
    </div>
</div>

<!-- Main Glass Container -->
<div class="soul-container animate-fade-in">
    <!-- Top Animated Progress Bar -->
    <div id="soulTopProgressBar" class="soul-progress-bar"></div>

    <!-- Left: Sessions Sidebar -->
    <div class="soul-sessions" id="soulSessionsSidebar">
        <div class="p-3 border-bottom border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
            <span class="fw-bold small text-light d-flex align-items-center gap-2 sidebar-header-text">
                <i class="fa-solid fa-layer-group text-primary"></i> Hermes Sessions
            </span>
            <div class="d-flex align-items-center gap-1 sidebar-header-actions">
                <button class="btn btn-outline-secondary btn-sm py-0 px-2 rounded-circle" onclick="loadHermesSessionsModal(1)" data-bs-toggle="modal" data-bs-target="#HSessionsModal" title="Explorer Modal">
                    <i class="fa-solid fa-expand"></i>
                </button>
                <button class="btn btn-outline-secondary btn-sm py-0 px-2 rounded-circle" onclick="toggleSidebar('soulSessionsSidebar')" title="Collapse Sidebar">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
            </div>
            <!-- Icon when collapsed -->
            <div class="sidebar-header-icon" onclick="toggleSidebar('soulSessionsSidebar')" title="Expand Sidebar">
                <i class="fa-solid fa-layer-group text-primary"></i>
            </div>
        </div>

        <!-- Live Search Filter -->
        <div class="session-search-box position-relative">
            <i class="fa-solid fa-magnifying-glass position-absolute text-muted small" style="left: 22px; top: 18px;"></i>
            <input type="text" id="sessionSearchInput" class="session-search-input" placeholder="Search sessions..." onkeyup="filterLocalSessions()">
        </div>

        <!-- Sessions List -->
        <div class="session-list" id="soulSessionsList">
            @forelse($sessions as $session)
            <div class="session-item {{ isset($selectedSession) && $selectedSession->id == $session->id ? 'active' : '' }}" 
                 id="session-item-{{ $session->id }}" 
                 data-session-id="{{ $session->id }}"
                 data-session-title="{{ e($session->title ?? $session->id) }}"
                 onclick="handleSessionItemClick(this)">
                <!-- Expanded View -->
                <div class="session-item-content">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong class="text-light text-truncate session-title" style="max-width: 70%;">{{ $session->title ?? $session->id }}</strong>
                        <span class="text-muted" style="font-size: 0.72rem;">{{ $session->last_active ? $session->last_active->diffForHumans(null, true, true) : ($session->updated_at ? $session->updated_at->diffForHumans(null, true, true) : 'Now') }}</span>
                    </div>
                    <div class="text-muted small text-truncate d-flex justify-content-between">
                        <span><i class="fa-solid fa-comments me-1 text-info"></i> {{ $session->message_count ?? 0 }} Msgs</span>
                        <span class="badge bg-secondary bg-opacity-50" style="font-size: 0.65rem;">{{ $session->source ?? 'api' }}</span>
                    </div>
                </div>
                <!-- Collapsed View (Icon Only) -->
                <div class="session-item-icon-only" title="{{ $session->title ?? $session->id }} ({{ $session->message_count ?? 0 }} Msgs)">
                    <i class="fa-solid fa-circle-user"></i>
                </div>
            </div>
            @empty
            <div class="p-3 text-center text-muted small border border-secondary border-dashed rounded m-2">No active sessions synced.</div>
            @endforelse
        </div>
        
        <!-- Pagination Links -->
        <div class="px-2 pt-2 pb-1 border-top border-secondary" style="font-size: 0.8rem;">
            {{ $sessions->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
    </div>

    <!-- Center: Main Chat Hub -->
    <div class="soul-chat">
        <!-- Chat Header -->
        <div class="chat-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-sm btn-dark text-muted border border-secondary px-2 me-1" onclick="toggleSidebar('soulSessionsSidebar')" title="Toggle Left Sidebar">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <i class="fa-solid fa-circle-dot text-success animate-pulse-glow" id="connectionStatusDot" style="font-size: 0.75rem;" title="Connected"></i>
                <span class="fw-bold text-light" id="currentChatSessionTitle">Session: {{ $selectedSession->title ?? ($selectedSession->id ?? 'No Active Session') }}</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-dark border border-secondary text-info small py-1 px-3" id="currentChatSessionModel">
                    <i class="fa-solid fa-microchip me-1"></i> {{ $selectedSession->model ?? 'hermes-agent' }}
                </span>
                <button class="btn btn-sm btn-outline-secondary rounded-circle px-2" onclick="loadActiveSessionMessagesNow()" title="Reload Messages">
                    <i class="fa-solid fa-rotate-right"></i>
                </button>
                <button class="btn btn-sm btn-dark text-muted border border-secondary px-2 ms-1" onclick="toggleSidebar('soulControlsSidebar')" title="Toggle Right Panel">
                    <i class="fa-solid fa-sliders"></i>
                </button>
            </div>
        </div>

        <!-- History Area -->
        <div class="chat-history position-relative" id="soulChatHistory" onscroll="handleChatScroll()">
            <div class="text-center text-muted py-5 mt-5" id="noMessagesPlaceholder">
                <div class="mb-4 animate-pulse-glow" style="opacity: 0.5;">
                    <i class="fa-solid fa-ghost fa-4x text-info"></i>
                </div>
                <h5 class="fw-bold text-light">Silence in the Hub</h5>
                <p class="small text-secondary">Select a session from the sidebar or type a message below to awaken Hermes.</p>
            </div>
        </div>

        <!-- Floating Scroll Buttons -->
        <div class="position-absolute d-flex flex-column gap-2" style="bottom: 90px; right: 30px; z-index: 100;">
            <button id="jumpToTopBtn" class="btn btn-secondary bg-opacity-75 rounded-circle shadow d-none" style="width: 45px; height: 45px; backdrop-filter: blur(5px);" onclick="scrollToTop()">
                <i class="fa-solid fa-chevron-up"></i>
            </button>
            <button id="jumpToBottomBtn" class="btn btn-primary rounded-circle shadow position-relative d-none" style="width: 45px; height: 45px;" onclick="scrollToBottom(true)">
                <i class="fa-solid fa-chevron-down"></i>
                <span class="position-absolute top-0 start-100 translate-middle p-2 bg-danger border border-dark rounded-circle d-none" id="unreadIndicator">
                    <span class="visually-hidden">New messages</span>
                </span>
            </button>
        </div>

        <!-- Chat Composer Box -->
        <div class="chat-composer">
            <div class="composer-box d-flex align-items-center">
                <textarea class="composer-textarea" id="soulChatComposerText" rows="1" placeholder="Type a message or command for Souly AI..." onkeydown="handleComposerKeyDown(event)" oninput="autoResizeTextarea(this)"></textarea>
                <div class="p-2 d-flex gap-2">
                    <button class="shadow-sm" onclick="submitChatMessage()" id="soulSendBtn" title="Send Message">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Controls & Telemetry Panel -->
    <div class="soul-controls" id="soulControlsSidebar">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="fw-bold small text-light"><i class="fa-solid fa-gauge-high text-info me-1"></i> System Telemetry</span>
            <button class="btn btn-outline-secondary btn-sm py-0 px-2 rounded-circle" onclick="toggleSidebar('soulControlsSidebar')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- H-Health Status Card -->
        <div class="control-card border-success border-opacity-30">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="fw-semibold text-light small"><i class="fa-solid fa-server text-success me-1"></i> Hermes Gateway</span>
                <span class="badge bg-success" id="hHealthCardBadge">ONLINE</span>
            </div>
            <div class="small text-muted" id="hHealthCardDetails">
                <div><i class="fa-solid fa-microchip me-1"></i> Host: 162.243.58.33</div>
                <div><i class="fa-solid fa-plug me-1"></i> Port: 8642</div>
            </div>
        </div>

        <!-- Active Session Metrics Card -->
        <div class="control-card">
            <div class="fw-semibold text-light small mb-2"><i class="fa-solid fa-chart-pie text-warning me-1"></i> Active Session Metrics</div>
            <div class="d-flex justify-content-between text-muted small py-1 border-bottom border-secondary border-opacity-25">
                <span>Total Messages</span>
                <strong class="text-light" id="sideStatMessageCount">{{ $selectedSession->message_count ?? 0 }}</strong>
            </div>
            <div class="d-flex justify-content-between text-muted small py-1 border-bottom border-secondary border-opacity-25">
                <span>Tool Calls</span>
                <strong class="text-light" id="sideStatToolCalls">{{ $selectedSession->tool_call_count ?? 0 }}</strong>
            </div>
            <div class="d-flex justify-content-between text-muted small py-1">
                <span>Source</span>
                <strong class="text-info text-capitalize">{{ $selectedSession->source ?? 'api' }}</strong>
            </div>
        </div>

        <!-- Global DB Controls Card -->
        <div class="control-card">
            <div class="fw-semibold text-light small mb-2"><i class="fa-solid fa-database text-primary me-1"></i> Local DB Sync Engine</div>
            <p class="small text-muted mb-2" style="font-size: 0.75rem;">15-second background polling active. Data parity synced with Hermes API.</p>
            <button class="btn btn-sm btn-outline-primary w-100 rounded-pill" onclick="triggerForceSync()">
                <i class="fa-solid fa-sync me-1"></i> Force Full Sync Now
            </button>
        </div>
    </div>
</div>

<!-- MODAL 1: HAgents Setup Modal -->
<div class="modal fade" id="HAgentsSetupModal" tabindex="-1" aria-labelledby="HAgentsSetupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-light border-secondary shadow-lg">
            <div class="modal-header border-secondary">
                <h5 class="modal-title h6 text-info fw-bold" id="HAgentsSetupModalLabel">
                    <i class="fa-solid fa-sliders me-2"></i> HAgents Setup & Gateway Configuration
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="hAgentsSetupForm">
                    <div class="mb-3">
                        <label class="form-label small text-muted">Hermes API Base URL</label>
                        <input type="text" class="form-control bg-dark text-light border-secondary" id="hermes_api_url" value="{{ $hermesSettings['api_url'] ?? 'http://162.243.58.33:8642/v1' }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted">Hermes API Gateway Key</label>
                        <div class="input-group">
                            <input type="password" class="form-control bg-dark text-light border-secondary" id="hermes_api_key" value="{{ $hermesSettings['api_key'] ?? 'a84cb5ec9ab34280d3d842ff3db11243be8214d04bd37f9cb3720b6f3e655d27' }}">
                            <button class="btn btn-outline-secondary" type="button" onclick="toggleKeyVisibility()">
                                <i class="fa-solid fa-eye" id="toggleKeyIcon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small text-muted">Default Agent</label>
                        <select class="form-select bg-dark text-light border-secondary" id="hermes_default_agent">
                            @if(isset($availableProfiles) && is_array($availableProfiles) && count($availableProfiles) > 0)
                                @foreach($availableProfiles as $profile)
                                    <option value="{{ $profile }}" {{ ($hermesSettings['default_agent'] ?? 'hermes-souly') === $profile ? 'selected' : '' }}>{{ $profile }}</option>
                                @endforeach
                            @else
                                <option value="hermes-souly" {{ ($hermesSettings['default_agent'] ?? 'hermes-souly') === 'hermes-souly' ? 'selected' : '' }}>hermes-souly</option>
                            @endif
                        </select>
                    </div>

                    <div id="hermesTestResult" class="alert alert-dark border-secondary d-none py-2 px-3 small"></div>
                </form>
            </div>
            <div class="modal-footer border-secondary justify-content-between">
                <div>
                    <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3" onclick="testHermesApi()" id="btnTestHermes">
                        <i class="fa-solid fa-vial me-1"></i> Test Connection
                    </button>
                </div>
                <div>
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3 me-2" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-4" onclick="saveHermesConfig()" id="btnSaveHermes">
                        <i class="fa-solid fa-save me-1"></i> Save
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL 2: H-Health Telemetry Modal -->
<div class="modal fade" id="HHealthModal" tabindex="-1" aria-labelledby="HHealthModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-dark text-light border-secondary shadow-lg">
            <div class="modal-header border-secondary">
                <h5 class="modal-title h6 text-success fw-bold" id="HHealthModalLabel">
                    <i class="fa-solid fa-heart-pulse me-2 text-success"></i> H-Health Diagnostic Telemetry Report
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="hHealthAlert" class="alert alert-success border-success py-2 px-3 mb-3 d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fa-solid fa-circle-check me-2"></i>
                        <span id="hHealthStatusText" class="fw-bold">ONLINE & OPERATIONAL</span>
                    </div>
                    <span class="badge bg-secondary" id="hHealthVersionBadge">hermes v0.9.1</span>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="border border-secondary rounded p-3 bg-dark">
                            <h6 class="text-info small fw-bold mb-2"><i class="fa-solid fa-network-wired me-1"></i> Connected Gateway Platforms</h6>
                            <div class="d-flex flex-column gap-1" id="hHealthPlatformsList">
                                <div class="text-muted small">Loading platform statuses...</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border border-secondary rounded p-3 bg-dark">
                            <h6 class="text-warning small fw-bold mb-2"><i class="fa-solid fa-list-check me-1"></i> Health Readiness Checks</h6>
                            <div class="d-flex flex-column gap-1" id="hHealthChecksList">
                                <div class="text-muted small">Loading readiness checks...</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Raw JSON Accordion -->
                <div class="accordion" id="hHealthAccordion">
                    <div class="accordion-item bg-dark border-secondary">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed bg-dark text-light small py-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHealthJson">
                                <i class="fa-solid fa-code me-2 text-warning"></i> View Raw Telemetry JSON
                            </button>
                        </h2>
                        <div id="collapseHealthJson" class="accordion-collapse collapse" data-bs-parent="#hHealthAccordion">
                            <div class="accordion-body bg-dark text-info p-2 font-monospace small">
                                <pre id="hHealthRawJson" class="m-0 text-light" style="max-height: 200px; overflow-y: auto;">{}</pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-secondary justify-content-between">
                <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3" onclick="checkHermesHealth(true)">
                    <i class="fa-solid fa-arrows-rotate me-1"></i> Refresh Telemetry
                </button>
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL 3: Hermes Sessions Explorer Modal -->
<div class="modal fade" id="HSessionsModal" tabindex="-1" aria-labelledby="HSessionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-dark text-light border-secondary shadow-lg">
            <div class="modal-header border-secondary bg-dark">
                <h5 class="modal-title h6 text-primary fw-bold" id="HSessionsModalLabel">
                    <i class="fa-solid fa-layer-group text-primary me-2"></i> Hermes Active Sessions Explorer
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-sm btn-outline-secondary py-1 px-2" onclick="loadHermesSessionsModal(currentSessionsPage)" title="Refresh Sessions List">
                        <i class="fa-solid fa-arrows-rotate" id="hSessionsRefreshIcon"></i>
                    </button>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted small"><i class="fa-solid fa-circle-info me-1"></i> Select a Hermes session to activate in primary chat workspace.</span>
                    <span class="badge bg-secondary" id="hSessionsCountBadge">0 Sessions</span>
                </div>

                <div class="list-group gap-2 mb-3" id="hSessionsList" style="max-height: 420px; overflow-y: auto;">
                    <div class="text-center py-4 text-muted">
                        <i class="fa-solid fa-spinner fa-spin me-2"></i> Loading Hermes Sessions...
                    </div>
                </div>

                <!-- Pagination Controls -->
                <div class="d-flex justify-content-between align-items-center border-top border-secondary pt-3" id="hSessionsPaginationBar">
                    <span class="small text-muted" id="hSessionsPageIndicator">Page 1 of 1</span>
                    <div class="d-flex gap-2">
                        <button class="btn hs-page-btn" id="hSessionsPrevBtn" onclick="changeSessionsPage(-1)" disabled>
                            <i class="fa-solid fa-chevron-left me-1"></i> Previous
                        </button>
                        <button class="btn hs-page-btn" id="hSessionsNextBtn" onclick="changeSessionsPage(1)" disabled>
                            Next <i class="fa-solid fa-chevron-right ms-1"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-secondary justify-content-between">
                <span class="small text-muted"><i class="fa-solid fa-database me-1"></i> Synced with local DB & Hermes API</span>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 99999 !important;">
    <div id="hsToast" class="toast align-items-center text-white bg-dark border-secondary" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="hsToastBody"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/mermaid@10.8.0/dist/mermaid.min.js"></script>
<script>
    if (typeof mermaid !== 'undefined') {
        mermaid.initialize({ startOnLoad: false, theme: 'dark' });
    }

    let currentSessionsPage = 1;
    let totalSessionsPages = 1;
    let activeHermesSessionId = '{{ $hermesSettings["current_session_id"] ?? ($selectedSession->id ?? "") }}';

    // --- Progress Bar Controller ---
    function startProgressBar() {
        const bar = document.getElementById('soulTopProgressBar');
        if (!bar) return;
        bar.style.opacity = '1';
        bar.style.width = '15%';
        setTimeout(() => { if (bar.style.opacity === '1') bar.style.width = '70%'; }, 150);
    }

    function completeProgressBar() {
        const bar = document.getElementById('soulTopProgressBar');
        if (!bar) return;
        bar.style.width = '100%';
        setTimeout(() => {
            bar.style.opacity = '0';
            setTimeout(() => { bar.style.width = '0%'; }, 300);
        }, 200);
    }

    // --- UI Helpers & Escaping ---
    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function showToast(message, type = 'success') {
        const toastEl = document.getElementById('hsToast');
        const toastBody = document.getElementById('hsToastBody');
        if (!toastEl) return;
        toastEl.className = `toast align-items-center text-white border-secondary bg-${type === 'success' ? 'success' : 'danger'}`;
        toastBody.innerHTML = message;
        const toast = new bootstrap.Toast(toastEl);
        toast.show();
    }

    function updateConnectionStatusDot(connected) {
        const dot = document.getElementById('connectionStatusDot');
        if (!dot) return;
        if (connected) {
            dot.className = 'fa-solid fa-circle-dot text-success animate-pulse-glow';
            dot.title = 'Connected';
        } else {
            dot.className = 'fa-solid fa-circle-dot text-danger';
            dot.title = 'Disconnected';
            dot.classList.remove('animate-pulse-glow');
        }
    }

    function toggleSidebar(id) {
        const el = document.getElementById(id);
        if (el) {
            el.classList.toggle('collapsed');
            localStorage.setItem('hs_sidebar_' + id, el.classList.contains('collapsed') ? '1' : '0');
        }
    }

    // Initialize sidebar states and Modals on load
    document.addEventListener('DOMContentLoaded', () => {
        ['soulSessionsSidebar', 'soulControlsSidebar'].forEach(id => {
            const el = document.getElementById(id);
            if (el && localStorage.getItem('hs_sidebar_' + id) === '1') {
                el.classList.add('collapsed');
            }
        });

        // Fix Bootstrap Modal Z-Index issues when inside overflow:hidden containers
        document.querySelectorAll('.modal').forEach(modal => {
            document.body.appendChild(modal);
        });
    });

    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    const filterLocalSessions = debounce(function() {
        const query = document.getElementById('sessionSearchInput').value.toLowerCase();
        document.querySelectorAll('#soulSessionsList .session-item').forEach(item => {
            const titleEl = item.querySelector('.session-title');
            const originalTitle = item.getAttribute('data-session-title');
            const id = item.getAttribute('data-session-id').toLowerCase();
            if (originalTitle.toLowerCase().includes(query) || id.includes(query)) {
                item.style.display = 'block';
                if (query) {
                    const regex = new RegExp(`(${query})`, 'gi');
                    titleEl.innerHTML = escapeHtml(originalTitle).replace(regex, '<mark class="bg-warning text-dark px-1 rounded">$1</mark>');
                } else {
                    titleEl.textContent = originalTitle;
                }
            } else {
                item.style.display = 'none';
                titleEl.textContent = originalTitle;
            }
        });
    }, 300);

    function handleSessionItemClick(element) {
        const sessionId = element.getAttribute('data-session-id');
        const sessionTitle = element.getAttribute('data-session-title');
        selectAndLoadSession(sessionId, sessionTitle);
    }

    function handleModalSessionClick(element) {
        const sessionId = element.getAttribute('data-session-id');
        const sessionTitle = decodeURIComponent(element.getAttribute('data-session-title') || '');
        selectHermesSession(sessionId, sessionTitle);
    }

    // --- Hermes Health Monitoring ---
    function checkHermesHealth(manual = false) {
        startProgressBar();
        const badge = document.getElementById('hHealthStatusBadge');
        const spinner = document.getElementById('hHealthSpinner');
        const refreshIcon = document.getElementById('hHealthRefreshIcon');

        if (manual && refreshIcon) refreshIcon.className = 'fa-solid fa-arrows-rotate fa-spin';

        fetch('{{ route("hub.hedra-soul.hermes.test-connection") }}')
            .then(res => res.json())
            .then(data => {
                completeProgressBar();
                if (manual && refreshIcon) refreshIcon.className = 'fa-solid fa-arrows-rotate';

                const alertEl = document.getElementById('hHealthAlert');
                const statusText = document.getElementById('hHealthStatusText');
                const versionBadge = document.getElementById('hHealthVersionBadge');
                const rawJson = document.getElementById('hHealthRawJson');
                const platformsList = document.getElementById('hHealthPlatformsList');
                const checksList = document.getElementById('hHealthChecksList');

                if (data.success || data.connected) {
                    updateConnectionStatusDot(true);
                    if (manual) showToast('Hermes connection verified.', 'success');
                    if (badge) {
                        badge.textContent = 'Online';
                        badge.className = 'badge bg-success ms-1';
                    }
                    if (spinner) spinner.className = 'spinner-grow spinner-grow-sm text-success';
                    
                    // Populate Modal UI
                    if (alertEl) {
                        alertEl.className = 'alert alert-success border-success py-2 px-3 mb-3 d-flex justify-content-between align-items-center';
                        statusText.textContent = 'ONLINE & OPERATIONAL';
                        versionBadge.textContent = data.health?.version ? `hermes v${data.health.version}` : 'hermes API';
                    }
                    if (platformsList) {
                        if (data.health?.components) {
                            platformsList.innerHTML = Object.entries(data.health.components).map(([name, info]) => {
                                const isOk = info.status === 'ok' || info.status === 'connected' || info.status === 'operational';
                                const badgeColor = isOk ? 'success' : 'danger';
                                const icon = isOk ? 'fa-check-circle' : 'fa-times-circle';
                                return `
                                    <div class="d-flex justify-content-between align-items-center border-bottom border-secondary pb-2 mb-2">
                                        <span class="text-light small text-capitalize"><i class="fa-solid fa-server text-secondary me-2"></i>${escapeHtml(name)}</span>
                                        <div>
                                            ${info.latency ? `<span class="badge bg-dark text-muted me-2 border border-secondary">${escapeHtml(info.latency)}</span>` : ''}
                                            <span class="badge bg-${badgeColor} bg-opacity-25 text-${badgeColor} border border-${badgeColor}">
                                                <i class="fa-solid ${icon} me-1"></i>${escapeHtml(info.status || 'unknown')}
                                            </span>
                                        </div>
                                    </div>
                                `;
                            }).join('');
                        } else {
                            platformsList.innerHTML = `<div class="d-flex justify-content-between text-light small border-bottom border-secondary pb-1"><span>Platform</span><span class="text-info">${escapeHtml(data.health?.platform || 'Unknown')}</span></div>`;
                        }
                    }
                    if (checksList && data.profiles) {
                        let html = `<div class="d-flex justify-content-between align-items-center border-bottom border-secondary pb-2 mb-2">
                            <span class="text-light small"><i class="fa-solid fa-robot text-secondary me-2"></i>Loaded Profiles</span>
                            <span class="badge bg-warning bg-opacity-25 text-warning border border-warning">${data.profiles.length} Active</span>
                        </div>`;
                        if (data.profiles.length > 0) {
                            html += `<div class="mt-2 text-muted" style="font-size: 0.75rem;">
                                ${data.profiles.map(p => `<span class="badge bg-dark border border-secondary me-1 mb-1">${escapeHtml(p)}</span>`).join('')}
                            </div>`;
                        }
                        checksList.innerHTML = html;
                    }
                    if (rawJson) {
                        rawJson.textContent = JSON.stringify(data, null, 2);
                    }
                } else {
                    updateConnectionStatusDot(false);
                    if (manual) showToast('Hermes connection failed.', 'danger');
                    if (badge) {
                        badge.textContent = 'Disconnected';
                        badge.className = 'badge bg-danger ms-1';
                    }
                    if (spinner) spinner.className = 'spinner-grow spinner-grow-sm text-danger';

                    // Populate Modal UI (Error State)
                    if (alertEl) {
                        alertEl.className = 'alert alert-danger border-danger py-2 px-3 mb-3 d-flex justify-content-between align-items-center';
                        statusText.textContent = 'OFFLINE / DISCONNECTED';
                        versionBadge.textContent = 'unavailable';
                    }
                    if (platformsList) platformsList.innerHTML = `<div class="text-danger small">Cannot reach platform.</div>`;
                    if (checksList) checksList.innerHTML = `<div class="text-danger small">Checks failed.</div>`;
                    if (rawJson) rawJson.textContent = JSON.stringify(data, null, 2);
                }
            })
            .catch(err => {
                completeProgressBar();
                updateConnectionStatusDot(false);
                if (manual) showToast('Error connecting to Hermes.', 'danger');
                if (manual && refreshIcon) refreshIcon.className = 'fa-solid fa-arrows-rotate';
                if (badge) {
                    badge.textContent = 'Offline';
                    badge.className = 'badge bg-danger ms-1';
                }
                const alertEl = document.getElementById('hHealthAlert');
                if (alertEl) {
                    alertEl.className = 'alert alert-danger border-danger py-2 px-3 mb-3 d-flex justify-content-between align-items-center';
                    document.getElementById('hHealthStatusText').textContent = 'CONNECTION ERROR';
                    document.getElementById('hHealthVersionBadge').textContent = 'error';
                }
            });
    }

    // --- Modal Sessions Explorer ---
    function loadHermesSessionsModal(page = 1) {
        startProgressBar();
        currentSessionsPage = page;
        const list = document.getElementById('hSessionsList');
        const badge = document.getElementById('hSessionsCountBadge');
        const icon = document.getElementById('hSessionsRefreshIcon');
        const pageIndicator = document.getElementById('hSessionsPageIndicator');
        const prevBtn = document.getElementById('hSessionsPrevBtn');
        const nextBtn = document.getElementById('hSessionsNextBtn');

        if (icon) icon.className = 'fa-solid fa-arrows-rotate fa-spin';

        fetch('{{ route("hub.hedra-soul.hermes.sessions") }}?page=' + page + '&limit=10')
            .then(res => res.json())
            .then(data => {
                completeProgressBar();
                if (icon) icon.className = 'fa-solid fa-arrows-rotate';
                if (data.success && data.sessions) {
                    if (badge) badge.textContent = `${data.pagination.total} Session(s)`;
                    totalSessionsPages = data.pagination.last_page || 1;
                    
                    if (pageIndicator) pageIndicator.textContent = `Page ${data.pagination.current_page} of ${totalSessionsPages} (${data.pagination.total} total)`;
                    if (prevBtn) prevBtn.disabled = data.pagination.current_page <= 1;
                    if (nextBtn) nextBtn.disabled = data.pagination.current_page >= totalSessionsPages;

                    if (list) {
                        if (data.sessions.length === 0) {
                            list.innerHTML = `<div class="text-center py-4 text-muted">No active sessions found.</div>`;
                            return;
                        }
                        list.innerHTML = data.sessions.map(s => {
                            const isCurrent = String(s.id) === String(activeHermesSessionId);
                            const activeBadge = isCurrent ? `<span class="badge bg-primary ms-2"><i class="fa-solid fa-check me-1"></i> ACTIVE</span>` : '';
                            const borderClass = isCurrent ? 'border-primary bg-primary bg-opacity-10' : 'border-secondary bg-dark bg-opacity-50';
                            const encodedTitle = encodeURIComponent(s.title || s.id);

                            return `
                                <div class="list-group-item bg-dark text-light border ${borderClass} rounded p-3 d-flex justify-content-between align-items-center mb-2">
                                    <div style="max-width: 70%;">
                                        <div class="d-flex align-items-center mb-1">
                                            <strong class="text-light fs-6 me-2">${escapeHtml(s.title)}</strong>
                                            <span class="badge bg-secondary me-1" style="font-size: 0.65rem;">${escapeHtml(s.topic)}</span>
                                            <span class="badge bg-success" style="font-size: 0.65rem;">${escapeHtml(s.status)}</span>
                                            ${activeBadge}
                                        </div>
                                        <div class="small text-muted mb-1 text-truncate">${escapeHtml(s.summary)}</div>
                                        <div class="small text-white-50" style="font-size: 0.75rem;">
                                            <i class="fa-solid fa-comments me-1 text-info"></i> Messages: ${s.task_count} | 
                                            <i class="fa-solid fa-clock me-1 text-warning"></i> ${escapeHtml(s.updated_at_human)} | 
                                            <i class="fa-solid fa-robot me-1 text-primary"></i> Mode: ${escapeHtml(s.last_autonomy_mode)}
                                        </div>
                                    </div>
                                    <div>
                                        ${isCurrent ? 
                                            `<button class="btn btn-sm btn-success rounded-pill px-3" disabled><i class="fa-solid fa-check-circle me-1"></i> Selected</button>` : 
                                            `<button class="btn btn-sm btn-outline-primary rounded-pill px-3" data-session-id="${s.id}" data-session-title="${encodedTitle}" onclick="handleModalSessionClick(this)">
                                                <i class="fa-solid fa-arrow-right-to-bracket me-1"></i> Select & Activate
                                             </button>`
                                        }
                                    </div>
                                </div>
                            `;
                        }).join('');
                    }
                }
            })
            .catch(err => {
                completeProgressBar();
                if (icon) icon.className = 'fa-solid fa-arrows-rotate';
                if (list) list.innerHTML = `<div class="alert alert-danger py-2 px-3 small">Failed to load sessions: ${escapeHtml(err.message)}</div>`;
            });
    }

    function changeSessionsPage(delta) {
        const newPage = currentSessionsPage + delta;
        if (newPage >= 1 && newPage <= totalSessionsPages) {
            loadHermesSessionsModal(newPage);
        }
    }

    // --- Session Activation & Message History ---
    function selectAndLoadSession(sessionId, sessionTitle) {
        startProgressBar();
        activeHermesSessionId = sessionId;
        localStorage.setItem('hs_active_session', sessionId);
        localStorage.setItem('hs_active_session_title', sessionTitle || '');
        
        document.querySelectorAll('#soulSessionsList .session-item').forEach(el => el.classList.remove('active'));
        const currentItem = document.getElementById('session-item-' + sessionId);
        if (currentItem) currentItem.classList.add('active');

        const btnTitle = document.getElementById('hSessionBtnTitle');
        if (btnTitle) btnTitle.textContent = sessionTitle;

        const chatTitle = document.getElementById('currentChatSessionTitle');
        if (chatTitle) chatTitle.textContent = 'Session: ' + sessionTitle;

        fetch('{{ route("hub.hedra-soul.hermes.select-session") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ session_id: sessionId, session_title: sessionTitle })
        });

        loadSessionMessages(sessionId);
    }

    function selectHermesSession(sessionId, sessionTitle) {
        selectAndLoadSession(sessionId, sessionTitle);
        const modalEl = document.getElementById('HSessionsModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
    }

    function loadSessionMessages(sessionId) {
        startProgressBar();
        const history = document.getElementById('soulChatHistory');
        if (!history) return;

        // B1: Skeleton Loader
        history.innerHTML = Array(3).fill().map(() => `
            <div class="chat-bubble agent animate-pulse-glow" style="width: ${Math.random() * 40 + 40}%; height: 60px; background: rgba(255,255,255,0.05); border-color: transparent;"></div>
        `).join('');

        fetch('/hub/hedra-soul/hermes/sessions/' + sessionId + '/messages')
            .then(res => res.json())
            .then(data => {
                completeProgressBar();
                if (data.success && data.messages) {
                    if (data.messages.length === 0) {
                        history.innerHTML = `
                            <div class="text-center text-muted py-5" id="noMessagesPlaceholder">
                                <i class="fa-solid fa-comments fa-2x mb-3 text-secondary d-block"></i>
                                No message history available for this session yet.
                            </div>
                        `;
                        return;
                    }

                    // Setup Marked with Highlight.js
                    if (typeof marked !== 'undefined') {
                        marked.setOptions({
                            highlight: function(code, lang) {
                                if (lang === 'mermaid') {
                                    return code;
                                }
                                if (lang && hljs.getLanguage(lang)) {
                                    return hljs.highlight(code, { language: lang }).value;
                                }
                                return hljs.highlightAuto(code).value;
                            },
                            breaks: true
                        });
                    }

                    function parseContent(content) {
                        if (!content) return '';
                        if (typeof marked !== 'undefined') {
                            return marked.parse(content);
                        }
                        return escapeHtml(content).replace(/\n/g, '<br>');
                    }

                    history.innerHTML = data.messages.map(m => {
                        const contentHtml = parseContent(m.content);
                        const copyBtn = `<button class="btn btn-sm btn-outline-secondary py-0 px-2 float-end ms-2" onclick="navigator.clipboard.writeText(decodeURIComponent('${encodeURIComponent(m.content)}'))" title="Copy"><i class="fa-regular fa-copy"></i></button>`;
                        
                        if (m.role === 'user') {
                            return `
                                <div class="chat-bubble user animate-fade-in">
                                    <div class="mb-1"><strong class="text-light" style="font-size: 0.75rem;"><i class="fa-solid fa-user me-1 text-indigo"></i> USER</strong></div>
                                    <div class="msg-content">${contentHtml}</div>
                                    <div class="text-end text-white-50 mt-1" style="font-size: 0.7rem;">${escapeHtml(m.timestamp_human)}</div>
                                </div>
                            `;
                        } else if (m.role === 'assistant' || m.role === 'agent') {
                            let toolCallsHtml = '';
                            if (m.raw_payload) {
                                try {
                                    const payload = typeof m.raw_payload === 'string' ? JSON.parse(m.raw_payload) : m.raw_payload;
                                    if (payload.tool_calls && payload.tool_calls.length > 0) {
                                        toolCallsHtml = payload.tool_calls.map(tc => {
                                            const toolName = tc.function?.name || tc.name || 'tool';
                                            const args = tc.function?.arguments || tc.arguments || '{}';
                                            const safeId = 'tc_' + Math.random().toString(36).substr(2, 9);
                                            return `
                                                <div class="card bg-dark border-secondary mt-2 mb-2">
                                                    <div class="card-header py-1 px-2 border-secondary d-flex justify-content-between align-items-center cursor-pointer" data-bs-toggle="collapse" data-bs-target="#${safeId}">
                                                        <span class="small text-info fw-bold"><i class="fa-solid fa-microchip me-1"></i> Agent triggered: ${escapeHtml(toolName)}</span>
                                                        <i class="fa-solid fa-chevron-down text-muted small"></i>
                                                    </div>
                                                    <div id="${safeId}" class="collapse">
                                                        <div class="card-body p-0" style="max-width: 100%; overflow: hidden;">
                                                            <pre class="m-0 p-2 small text-light bg-dark" style="max-height: 200px; overflow-y: auto; overflow-x: hidden; white-space: pre-wrap; word-break: break-all; text-align: left; direction: ltr;"><code>${escapeHtml(typeof args === 'string' ? args : JSON.stringify(args, null, 2))}</code></pre>
                                                        </div>
                                                    </div>
                                                </div>
                                            `;
                                        }).join('');
                                    }
                                } catch (e) {}
                            }

                            return `
                                <div class="chat-bubble agent animate-fade-in position-relative">
                                    <div class="mb-1">
                                        <strong class="text-info" style="font-size: 0.75rem;"><i class="fa-solid fa-robot me-1"></i> HERMES AGENT</strong>
                                        ${copyBtn}
                                    </div>
                                    <div class="msg-content markdown-body">${contentHtml}</div>
                                    ${toolCallsHtml}
                                    <div class="text-end text-white-50 mt-1" style="font-size: 0.7rem;">${escapeHtml(m.timestamp_human)}</div>
                                </div>
                            `;
                        } else if (m.role === 'tool') {
                            let toolName = 'Unknown Tool';
                            let toolResult = m.content;
                            if (m.raw_payload) {
                                try {
                                    const payload = typeof m.raw_payload === 'string' ? JSON.parse(m.raw_payload) : m.raw_payload;
                                    toolName = payload.tool_name || payload.name || toolName;
                                    if (payload.content) toolResult = payload.content;
                                } catch (e) {}
                            }
                            const safeId = 'tr_' + Math.random().toString(36).substr(2, 9);
                            return `
                                <div class="chat-bubble system animate-fade-in p-0 overflow-hidden border-secondary">
                                    <div class="bg-dark border-bottom border-secondary p-2 d-flex justify-content-between align-items-center cursor-pointer" data-bs-toggle="collapse" data-bs-target="#${safeId}">
                                        <strong class="text-warning" style="font-size: 0.75rem;"><i class="fa-solid fa-gear me-1"></i> SYSTEM [TOOL RESULT: ${escapeHtml(toolName)}]</strong>
                                        <div>
                                            ${copyBtn}
                                            <i class="fa-solid fa-chevron-down text-muted small ms-2"></i>
                                        </div>
                                    </div>
                                    <div id="${safeId}" class="collapse">
                                        <div class="p-2 bg-dark bg-opacity-50" style="max-width: 100%; overflow: hidden;">
                                            <pre class="m-0 small text-muted" style="max-height: 250px; overflow-y: auto; overflow-x: hidden; white-space: pre-wrap; word-break: break-all; text-align: left; direction: ltr;"><code>${escapeHtml(toolResult)}</code></pre>
                                        </div>
                                    </div>
                                </div>
                            `;
                        } else {
                            return `
                                <div class="chat-bubble system animate-fade-in position-relative">
                                    <strong class="text-warning" style="font-size: 0.75rem;"><i class="fa-solid fa-gear me-1"></i> SYSTEM [${escapeHtml(m.role.toUpperCase())}]</strong>
                                    ${copyBtn}
                                    <div class="small mt-1 msg-content markdown-body">${contentHtml}</div>
                                </div>
                            `;
                        }
                    }).join('');

                    if (typeof mermaid !== 'undefined') {
                        mermaid.run({ querySelector: '.language-mermaid' }).catch(e => console.error(e));
                    }

                    setTimeout(() => {
                        history.scrollTop = history.scrollHeight;
                    }, 50);
                }
            })
            .catch(err => {
                completeProgressBar();
                console.error('Error loading session messages:', err);
            });
    }

    function loadActiveSessionMessagesNow() {
        if (activeHermesSessionId) {
            loadSessionMessages(activeHermesSessionId);
        }
    }

    function triggerForceSync() {
        startProgressBar();
        fetch('{{ route("hub.hedra-soul.hermes.test-connection") }}')
            .then(() => {
                completeProgressBar();
                if (activeHermesSessionId) loadSessionMessages(activeHermesSessionId);
            })
            .catch(() => completeProgressBar());
    }

    let userScrolledUp = false;

    function handleChatScroll() {
        const history = document.getElementById('soulChatHistory');
        const fabBottom = document.getElementById('jumpToBottomBtn');
        const fabTop = document.getElementById('jumpToTopBtn');
        const unread = document.getElementById('unreadIndicator');
        if (!history || !fabBottom || !fabTop) return;

        const isNearBottom = history.scrollHeight - history.scrollTop - history.clientHeight < 50;
        const isNearTop = history.scrollTop < 100;

        if (isNearBottom) {
            userScrolledUp = false;
            fabBottom.classList.add('d-none');
            if (unread) unread.classList.add('d-none');
        } else {
            userScrolledUp = true;
            fabBottom.classList.remove('d-none');
        }

        if (isNearTop) {
            fabTop.classList.add('d-none');
        } else {
            fabTop.classList.remove('d-none');
        }
    }

    function scrollToBottom(force = false) {
        const history = document.getElementById('soulChatHistory');
        if (!history) return;
        
        if (force || !userScrolledUp) {
            history.scrollTo({ top: history.scrollHeight, behavior: force ? 'smooth' : 'auto' });
            userScrolledUp = false;
            const fab = document.getElementById('jumpToBottomBtn');
            const unread = document.getElementById('unreadIndicator');
            if (fab) fab.classList.add('d-none');
            if (unread) unread.classList.add('d-none');
        } else if (userScrolledUp) {
            const unread = document.getElementById('unreadIndicator');
            if (unread) unread.classList.remove('d-none');
        }
    }

    function scrollToTop() {
        const history = document.getElementById('soulChatHistory');
        if (history) {
            history.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }

    function autoResizeTextarea(el) {
        el.style.height = 'auto';
        el.style.height = (el.scrollHeight) + 'px';
    }

    function handleComposerKeyDown(event) {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            submitChatMessage();
            event.target.style.height = 'auto';
        }
    }

    function submitChatMessage() {
        const input = document.getElementById('soulChatComposerText');
        const sendBtn = document.getElementById('soulSendBtn');
        const text = input.value.trim();
        if (!text) return;
        if (!activeHermesSessionId) {
            showToast('Please select or create a session first.', 'warning');
            return;
        }

        startProgressBar();
        input.value = '';
        input.style.height = 'auto';

        if (sendBtn) {
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i>';
        }

        // Append user bubble instantly for optimistic UI
        const history = document.getElementById('soulChatHistory');
        const userBubble = document.createElement('div');
        userBubble.className = 'chat-bubble user animate-fade-in';
        userBubble.innerHTML = `
            <div class="mb-1"><strong class="text-light" style="font-size: 0.75rem;"><i class="fa-solid fa-user me-1 text-indigo"></i> USER</strong></div>
            <div class="msg-content">${escapeHtml(text).replace(/\n/g, '<br>')}</div>
            <div class="text-end text-white-50 mt-1" style="font-size: 0.7rem;">Just now</div>
        `;
        history.appendChild(userBubble);
        scrollToBottom(true);

        fetch('{{ route("hub.hedra-soul.hermes.send-message") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                session_id: activeHermesSessionId,
                message: text
            })
        })
        .then(res => res.json())
        .then(data => {
            completeProgressBar();
            if (sendBtn) {
                sendBtn.disabled = false;
                sendBtn.innerHTML = '<i class="fa-solid fa-paper-plane"></i>';
            }
            if (data.success) {
                // If the controller returned the reply directly, render it immediately
                if (data.reply) {
                    const history = document.getElementById('soulChatHistory');
                    const agentBubble = document.createElement('div');
                    agentBubble.className = 'chat-bubble agent animate-fade-in';

                    let replyHtml = '';
                    if (typeof marked !== 'undefined') {
                        try { replyHtml = marked.parse(data.reply); } catch(e) { replyHtml = escapeHtml(data.reply).replace(/\n/g, '<br>'); }
                    } else {
                        replyHtml = escapeHtml(data.reply).replace(/\n/g, '<br>');
                    }

                    agentBubble.innerHTML = `
                        <div class="mb-1">
                            <strong class="text-info" style="font-size: 0.75rem;"><i class="fa-solid fa-robot me-1"></i> HERMES AGENT</strong>
                        </div>
                        <div class="msg-content markdown-body">${replyHtml}</div>
                        <div class="text-end text-white-50 mt-1" style="font-size: 0.7rem;">Just now</div>
                    `;
                    history.appendChild(agentBubble);
                    scrollToBottom(true);

                    // Highlight code blocks if hljs is available
                    if (typeof hljs !== 'undefined') {
                        agentBubble.querySelectorAll('pre code').forEach(block => hljs.highlightElement(block));
                    }
                }

                // Reload in background to sync DB state (removes duplicates, adds metadata)
                setTimeout(() => loadSessionMessages(activeHermesSessionId), 1500);
            } else {
                showToast('Failed to send message: ' + (data.error || 'Unknown error'), 'danger');
            }
        })
        .catch(err => {
            completeProgressBar();
            if (sendBtn) {
                sendBtn.disabled = false;
                sendBtn.innerHTML = '<i class="fa-solid fa-paper-plane"></i>';
            }
            showToast('Network error while sending message.', 'danger');
        });
    }

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    }

    function testHermesApi() {
        const btn = document.getElementById('btnTestHermes');
        const resultDiv = document.getElementById('hermesTestResult');
        const apiUrl = document.getElementById('hermes_api_url').value.trim();
        const apiKey = document.getElementById('hermes_api_key').value.trim();
        
        if (!apiUrl) return;

        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin me-1"></i> Testing...';
        resultDiv.className = 'alert alert-info border-info d-block py-2 px-3 small';
        resultDiv.innerHTML = 'Connecting to Hermes Gateway...';

        fetch('{{ route("hub.hedra-soul.hermes.test-connection") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ api_url: apiUrl, api_key: apiKey })
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-vial me-1"></i> Test Connection';
            if (data.success) {
                resultDiv.className = 'alert alert-success border-success d-block py-2 px-3 small';
                resultDiv.innerHTML = '<i class="fa-solid fa-check-circle me-1"></i> Connection successful! Models available: ' + (data.models?.length || 0);
            } else {
                resultDiv.className = 'alert alert-danger border-danger d-block py-2 px-3 small';
                resultDiv.innerHTML = '<i class="fa-solid fa-triangle-exclamation me-1"></i> Error: ' + escapeHtml(data.error || 'Connection failed.');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-vial me-1"></i> Test Connection';
            resultDiv.className = 'alert alert-danger border-danger d-block py-2 px-3 small';
            resultDiv.innerHTML = '<i class="fa-solid fa-triangle-exclamation me-1"></i> Request failed: ' + err;
        });
    }

    function saveHermesConfig() {
        const btn = document.getElementById('btnSaveHermes');
        const apiUrl = document.getElementById('hermes_api_url').value.trim();
        const apiKey = document.getElementById('hermes_api_key').value.trim();
        const defaultAgent = document.getElementById('hermes_default_agent').value.trim();
        
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin me-1"></i> Saving...';

        fetch('{{ route("hub.hedra-soul.hermes.save") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ api_url: apiUrl, api_key: apiKey, default_agent: defaultAgent })
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-save me-1"></i> Save';
            if (data.success) {
                const resultDiv = document.getElementById('hermesTestResult');
                resultDiv.className = 'alert alert-success border-success d-block py-2 px-3 small mt-3';
                resultDiv.innerHTML = '<i class="fa-solid fa-check-circle me-1"></i> Settings saved successfully.';
                setTimeout(() => window.location.reload(), 1000);
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-save me-1"></i> Save';
            console.error(err);
        });
    }

    // Modal listeners
    document.addEventListener('DOMContentLoaded', () => {
        const savedSession = localStorage.getItem('hs_active_session');
        const savedSessionTitle = localStorage.getItem('hs_active_session_title');
        
        if (savedSession) {
            activeHermesSessionId = savedSession;
            const chatTitle = document.getElementById('currentChatSessionTitle');
            if (chatTitle) chatTitle.textContent = 'Session: ' + (savedSessionTitle || savedSession);
            
            const btnTitle = document.getElementById('hSessionBtnTitle');
            if (btnTitle) btnTitle.textContent = (savedSessionTitle || savedSession);
        }

        checkHermesHealth();
        loadActiveSessionMessagesNow();

        const sessionsModalEl = document.getElementById('HSessionsModal');
        if (sessionsModalEl) {
            sessionsModalEl.addEventListener('show.bs.modal', () => {
                loadHermesSessionsModal(1);
            });
        }

        // Keyboard Navigation for Sessions
        document.getElementById('sessionSearchInput')?.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                e.preventDefault();
                const items = Array.from(document.querySelectorAll('#soulSessionsList .session-item')).filter(el => el.style.display !== 'none');
                if (!items.length) return;
                
                const activeIndex = items.findIndex(el => el.classList.contains('focused-item'));
                if (activeIndex !== -1) items[activeIndex].classList.remove('focused-item');
                
                let nextIndex = 0;
                if (e.key === 'ArrowDown') {
                    nextIndex = activeIndex < items.length - 1 ? activeIndex + 1 : 0;
                } else {
                    nextIndex = activeIndex > 0 ? activeIndex - 1 : items.length - 1;
                }
                
                items[nextIndex].classList.add('focused-item');
                items[nextIndex].scrollIntoView({ block: 'nearest' });
            } else if (e.key === 'Enter') {
                e.preventDefault();
                const focused = document.querySelector('#soulSessionsList .session-item.focused-item');
                if (focused) focused.click();
            }
        });

        // Auto-poll telemetry & messages every 15s
        setInterval(() => {
            checkHermesHealth();
        }, 15000);
    });
</script>
@endpush
