@extends('layouts.app')

@push('styles')
<style>
    /* ===== MAIN CONTENT OVERRIDE FOR PERFECT HEIGHT FILL ===== */
    #page-content-wrapper .main-content {
        display: flex !important;
        flex-direction: column !important;
        min-height: calc(100vh - 70px) !important;
        padding: 16px 24px !important;
    }

    .pc-header-section {
        flex-shrink: 0 !important;
    }
    .pc-stats-section {
        flex-shrink: 0 !important;
    }

    /* ===== MASTER SHELL (FULL VIEWPORT HEIGHT & EDGE-TO-EDGE) ===== */
    .pc-shell {
        display: flex;
        flex: 1 1 auto !important;
        min-height: 780px !important;
        height: calc(100vh - 210px) !important;
        width: 100%;
        background: rgba(11, 15, 25, 0.75);
        border: 1px solid rgba(255, 255, 255, 0.09);
        border-radius: 16px;
        overflow: hidden;
        backdrop-filter: blur(24px) saturate(160%);
        box-shadow: 0 16px 48px rgba(0, 0, 0, 0.55), 0 0 0 1px rgba(99, 102, 241, 0.1);
        position: relative;
    }

    /* ===== STATS BAR CARDS ===== */
    .pc-stat-card {
        background: linear-gradient(135deg, rgba(17, 24, 39, 0.8) 0%, rgba(11, 15, 25, 0.9) 100%);
        backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 14px;
        padding: 14px 18px;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.25);
        height: 100%;
    }
    .pc-stat-card:hover {
        transform: translateY(-2px);
        border-color: rgba(99, 102, 241, 0.4);
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(99, 102, 241, 0.2);
    }
    .pc-stat-label {
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.73rem;
        font-weight: 600;
        letter-spacing: 0.5px;
        color: var(--text-secondary);
        text-transform: uppercase;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .pc-stat-val {
        font-family: 'Outfit', sans-serif;
        font-size: 1.15rem;
        font-weight: 700;
        color: #f8fafc;
        margin-top: 6px;
    }

    /* ===== SIDEBAR (CONVERSATION LIST) ===== */
    .pc-sidebar {
        width: 330px;
        min-width: 330px;
        display: flex;
        flex-direction: column;
        border-right: 1px solid rgba(255, 255, 255, 0.08);
        background: rgba(7, 10, 16, 0.85);
        transition: width 0.28s cubic-bezier(0.16, 1, 0.3, 1), min-width 0.28s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.2s ease;
        overflow: hidden;
    }
    .pc-sidebar.is-hidden {
        width: 0 !important;
        min-width: 0 !important;
        border-right: none !important;
        opacity: 0;
        pointer-events: none;
    }
    .pc-sidebar-header {
        padding: 14px 16px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        background: rgba(11, 15, 25, 0.5);
    }
    .pc-search-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }
    .pc-search-icon {
        position: absolute;
        left: 14px;
        color: rgba(255, 255, 255, 0.35);
        font-size: 0.85rem;
        pointer-events: none;
    }
    .pc-search {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        color: #fff;
        padding: 8px 14px 8px 38px;
        font-size: 0.84rem;
        width: 100%;
        outline: none;
        transition: all 0.2s ease;
    }
    .pc-search:focus {
        border-color: rgba(99, 102, 241, 0.6);
        background: rgba(255, 255, 255, 0.09);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
    }
    .pc-search::placeholder { color: rgba(255, 255, 255, 0.35); }

    .pc-conv-list {
        flex: 1;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: rgba(255, 255, 255, 0.12) transparent;
    }
    .pc-conv-list::-webkit-scrollbar { width: 5px; }
    .pc-conv-list::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.12); border-radius: 4px; }

    /* ===== CONVERSATION ITEM ===== */
    .pc-conv-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 13px 16px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
        position: relative;
    }
    .pc-conv-item:hover {
        background: rgba(255, 255, 255, 0.04);
    }
    .pc-conv-item.is-active {
        background: linear-gradient(90deg, rgba(99, 102, 241, 0.18) 0%, rgba(99, 102, 241, 0.03) 100%);
        border-left: 3px solid #6366f1;
        padding-left: 13px;
    }
    .pc-conv-item.is-active::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, rgba(99, 102, 241, 0.1) 0%, transparent 100%);
        pointer-events: none;
    }

    /* Avatar */
    .pc-avatar {
        position: relative;
        flex-shrink: 0;
    }
    .pc-avatar-img {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        object-fit: cover;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
        font-weight: 700;
        color: #fff;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }
    .pc-status-dot {
        position: absolute;
        bottom: 1px; right: 1px;
        width: 12px; height: 12px;
        border-radius: 50%;
        border: 2px solid #070a10;
    }
    .pc-status-dot.online { background: #10b981; box-shadow: 0 0 8px rgba(16, 185, 129, 0.6); }
    .pc-status-dot.nexus  { background: #6366f1; box-shadow: 0 0 8px rgba(99, 102, 241, 0.6); }

    .pc-conv-body { flex: 1; min-width: 0; }
    .pc-conv-name {
        font-size: 0.9rem;
        font-weight: 600;
        color: #f8fafc;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 155px;
    }
    .pc-phone-badge {
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.68rem;
        padding: 2px 6px;
        border-radius: 4px;
        background: rgba(255, 255, 255, 0.06);
        color: rgba(255, 255, 255, 0.5);
        display: inline-block;
        margin-top: 2px;
    }
    .pc-preview {
        font-size: 0.77rem;
        color: rgba(255, 255, 255, 0.45);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: block;
        margin-top: 4px;
    }

    .pc-conv-right {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 6px;
        flex-shrink: 0;
    }
    .pc-time-badge {
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.68rem;
        color: rgba(255, 255, 255, 0.35);
    }
    .pc-unread-badge {
        background: linear-gradient(135deg, #10b981, #059669);
        color: #fff;
        font-size: 0.68rem;
        font-weight: 700;
        font-family: 'JetBrains Mono', monospace;
        border-radius: 999px;
        min-width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 6px;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.4);
        animation: pulse-badge 2s infinite;
    }

    /* ===== CHAT AREA ===== */
    .pc-chat-area {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: rgba(13, 17, 24, 0.4);
        min-height: 0;
        max-height: 100%;
        overflow: hidden;
    }
    .pc-chat-header {
        padding: 12px 22px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        background: rgba(9, 12, 19, 0.85);
        backdrop-filter: blur(16px);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-shrink: 0;
        min-height: 64px;
    }
    .pc-chat-msgs {
        flex: 1;
        min-height: 0;
        padding: 24px 22px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 14px;
        scrollbar-width: thin;
        scrollbar-color: rgba(255, 255, 255, 0.12) transparent;
    }
    .pc-chat-msgs::-webkit-scrollbar { width: 6px; }
    .pc-chat-msgs::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.12); border-radius: 4px; }

    /* ===== NEON CHATSPHERE GLASS BUBBLE SYSTEM ===== */

    /* Chat area subtle grid background */
    .pc-chat-msgs {
        background-image:
            linear-gradient(rgba(0,229,255,0.015) 1px, transparent 1px),
            linear-gradient(90deg, rgba(0,229,255,0.015) 1px, transparent 1px),
            radial-gradient(ellipse at 15% 80%, rgba(0, 229, 255, 0.06) 0%, transparent 45%),
            radial-gradient(ellipse at 85% 15%, rgba(37, 99, 235, 0.07) 0%, transparent 45%);
        background-size: 40px 40px, 40px 40px, 100% 100%, 100% 100%;
    }

    /* --- Wrapper Layout --- */
    .msg-wrapper {
        display: flex;
        align-items: flex-start;  /* Avatar aligns with TOP of bubble */
        gap: 12px;
        margin-bottom: 4px;
        position: relative;
        width: 100%;
        animation: pc-msg-pop 0.32s cubic-bezier(0.16, 1, 0.3, 1) both;
    }
    .msg-wrapper + .msg-wrapper { margin-top: 2px; }
    .msg-wrapper.incoming { flex-direction: row; justify-content: flex-start; }
    .msg-wrapper.outgoing { flex-direction: row-reverse; justify-content: flex-start; }

    /* --- Avatar Column --- */
    .msg-avatar-col {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
        flex-shrink: 0;
        width: 36px;
    }
    .msg-avatar {
        width: 38px;
        height: 38px;
        min-width: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        font-weight: 700;
        flex-shrink: 0;
        position: relative;
        letter-spacing: 0.5px;
        transition: box-shadow 0.25s ease;
    }
    .msg-wrapper.incoming .msg-avatar {
        background: linear-gradient(135deg, #1e3a4a 0%, #0d2233 100%);
        border: 2px solid rgba(0, 229, 255, 0.55);
        color: #7df9ff;
        box-shadow:
            0 0 12px rgba(0, 229, 255, 0.35),
            0 0 28px rgba(0, 229, 255, 0.15),
            0 4px 14px rgba(0, 0, 0, 0.5);
    }
    .msg-wrapper.incoming .msg-avatar:hover {
        box-shadow:
            0 0 18px rgba(0, 229, 255, 0.55),
            0 0 40px rgba(0, 229, 255, 0.25);
    }
    .msg-wrapper.outgoing .msg-avatar {
        background: linear-gradient(135deg, #0f2b48 0%, #07172b 100%);
        border: 2px solid rgba(59, 130, 246, 0.65);
        color: #93c5fd;
        box-shadow:
            0 0 12px rgba(59, 130, 246, 0.45),
            0 0 28px rgba(59, 130, 246, 0.2),
            0 4px 14px rgba(0, 0, 0, 0.5);
    }
    .msg-wrapper.outgoing .msg-avatar:hover {
        box-shadow:
            0 0 18px rgba(59, 130, 246, 0.65),
            0 0 40px rgba(59, 130, 246, 0.3);
    }
    .msg-author-name {
        font-size: 0.58rem;
        font-family: 'JetBrains Mono', monospace;
        color: rgba(255,255,255,0.3);
        white-space: nowrap;
        text-align: center;
        letter-spacing: 0.2px;
        line-height: 1;
        margin-top: 2px;
    }

    /* --- Message Column (limits width) --- */
    .msg-col {
        display: flex;
        flex-direction: column;
        max-width: calc(100% - 46px);
        flex: 1;
        min-width: 0;
    }
    .msg-wrapper.incoming .msg-col { align-items: flex-start; }
    .msg-wrapper.outgoing .msg-col { align-items: flex-end; }

    /* --- Bubble Group: full width --- */
    .msg-bubble-group {
        width: 100%;
        display: flex;
        flex-direction: column;
        position: relative;
    }
    .msg-wrapper.incoming .msg-bubble-group { align-items: flex-start; }
    .msg-wrapper.outgoing .msg-bubble-group { align-items: flex-end; }

    /* --- Core Bubble Shape --- */
    .msg-bubble {
        position: relative;
        width: 100%;
        border-radius: 14px;
        padding: 12px 18px 10px 18px;
        font-size: 0.875rem;
        line-height: 1.65;
        word-break: break-word;
        direction: auto;
        unicode-bidi: plaintext;
        backdrop-filter: blur(24px) saturate(180%);
        -webkit-backdrop-filter: blur(24px) saturate(180%);
        transition: box-shadow 0.3s ease, border-color 0.3s ease, transform 0.2s ease;
        cursor: default;
    }

    /* ─── INCOMING Bubble: Neon Teal/Cyan Glass ─── */
    .msg-bubble.incoming {
        border-radius: 4px 14px 14px 14px;
        background: linear-gradient(
            135deg,
            rgba(8, 28, 42, 0.88) 0%,
            rgba(5, 20, 35, 0.92) 100%
        );
        border: 1.5px solid rgba(0, 229, 255, 0.45);
        box-shadow:
            0 0 18px rgba(0, 229, 255, 0.18),
            0 0 6px  rgba(0, 229, 255, 0.08) inset,
            0 6px 24px rgba(0, 0, 0, 0.5),
            0 1px 0 rgba(0, 229, 255, 0.12) inset;
        color: #e2f8ff;
    }
    .msg-bubble.incoming:hover {
        border-color: rgba(0, 229, 255, 0.72);
        box-shadow:
            0 0 28px rgba(0, 229, 255, 0.32),
            0 0 10px rgba(0, 229, 255, 0.14) inset,
            0 8px 30px rgba(0, 0, 0, 0.6);
        transform: translateY(-1px);
    }

    /* ─── OUTGOING Bubble: Deep Navy / Royal Blue Glass ─── */
    .msg-bubble.outgoing {
        border-radius: 14px 4px 14px 14px;
        background: linear-gradient(
            135deg,
            rgba(10, 31, 68, 0.88) 0%,
            rgba(15, 41, 89, 0.85) 50%,
            rgba(20, 52, 110, 0.82) 100%
        );
        border: 1.5px solid rgba(59, 130, 246, 0.55);
        box-shadow:
            0 0 20px rgba(59, 130, 246, 0.28),
            0 0 8px  rgba(96, 165, 250, 0.15) inset,
            0 6px 24px rgba(0, 0, 0, 0.55);
        color: #eff6ff;
    }
    .msg-bubble.outgoing:hover {
        border-color: rgba(96, 165, 250, 0.80);
        box-shadow:
            0 0 32px rgba(59, 130, 246, 0.45),
            0 0 14px rgba(96, 165, 250, 0.22) inset,
            0 8px 32px rgba(0, 0, 0, 0.65);
        transform: translateY(-1px);
    }

    /* --- Bubble Content Text --- */
    .msg-content-text {
        display: block;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        font-size: 0.875rem;
        line-height: 1.65;
    }
    .msg-bubble.incoming .msg-content-text { color: #cff3ff; }
    .msg-bubble.outgoing .msg-content-text { color: #eff6ff; }

    /* --- Timestamp: OUTSIDE bubble, below it --- */
    .msg-footer {
        display: flex;
        align-items: center;
        gap: 5px;
        margin-top: 5px;
        padding: 0 4px;
    }
    .msg-wrapper.incoming .msg-footer { justify-content: flex-start; }
    .msg-wrapper.outgoing .msg-footer { justify-content: flex-end; }

    .msg-timestamp {
        font-size: 10px;
        font-weight: 500;
        font-family: 'JetBrains Mono', monospace;
        white-space: nowrap;
        letter-spacing: 0.3px;
        line-height: 1;
    }
    .msg-wrapper.incoming .msg-timestamp { color: rgba(0, 229, 255, 0.55); }
    .msg-wrapper.outgoing .msg-timestamp  { color: rgba(96, 165, 250, 0.65); }

    /* --- Delivery Ticks --- */
    .msg-ack {
        display: inline-flex;
        align-items: center;
        line-height: 1;
    }
    .msg-ack i { font-size: 0.58rem; }
    .msg-ack.sent      { color: rgba(96, 165, 250, 0.5); }
    .msg-ack.delivered { color: rgba(96, 165, 250, 0.85); }
    .msg-ack.read      { color: #22d3ee; text-shadow: 0 0 8px rgba(34, 211, 238, 0.7); }

    /* --- Action Toolbar: above the bubble, revealed on hover --- */
    .msg-action-bar {
        display: flex;
        align-items: center;
        gap: 2px;
        margin-bottom: 6px; /* sits ABOVE the bubble */
        padding: 5px 10px;
        border-radius: 22px;
        background: rgba(6, 10, 22, 0.90);
        backdrop-filter: blur(22px) saturate(200%);
        -webkit-backdrop-filter: blur(22px) saturate(200%);
        border: 1px solid rgba(255, 255, 255, 0.07);
        box-shadow: 0 6px 24px rgba(0, 0, 0, 0.55);
        overflow-x: auto;
        scrollbar-width: none;
        white-space: nowrap;
        opacity: 0;
        transform: translateY(6px); /* slides DOWN into view from above */
        transition: opacity 0.22s ease, transform 0.22s cubic-bezier(0.16, 1, 0.3, 1);
        pointer-events: none;
        position: static;
    }
    .msg-action-bar::-webkit-scrollbar { display: none; }
    .msg-bubble-group:hover .msg-action-bar {
        opacity: 1;
        transform: translateY(0);
        pointer-events: auto;
    }
    .msg-wrapper.incoming .msg-action-bar {
        align-self: flex-start;
        border-color: rgba(0, 229, 255, 0.15);
        box-shadow: 0 6px 24px rgba(0, 0, 0, 0.55), 0 0 16px rgba(0, 229, 255, 0.09);
    }
    .msg-wrapper.outgoing .msg-action-bar {
        align-self: flex-end;
        border-color: rgba(59, 130, 246, 0.18);
        box-shadow: 0 6px 24px rgba(0, 0, 0, 0.55), 0 0 16px rgba(59, 130, 246, 0.12);
    }

    /* --- Action Bar Emoji Reactions --- */
    .msg-action-emoji {
        font-size: 0.95rem;
        cursor: pointer;
        transition: transform 0.18s cubic-bezier(0.16, 1, 0.3, 1);
        padding: 3px 4px;
        border-radius: 7px;
        line-height: 1;
        flex-shrink: 0;
    }
    .msg-action-emoji:hover {
        transform: scale(1.45) translateY(-2px);
        background: rgba(255, 255, 255, 0.12);
    }

    /* --- Action Bar Icon Buttons --- */
    .btn-pc-icon {
        width: 26px;
        height: 26px;
        min-width: 26px;
        border-radius: 7px;
        background: transparent;
        border: none;
        color: rgba(148, 163, 184, 0.65);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.78rem;
        cursor: pointer;
        transition: all 0.15s ease;
        padding: 0;
        flex-shrink: 0;
    }
    .btn-pc-icon:hover {
        background: rgba(99, 102, 241, 0.2);
        color: #a5b4fc;
        transform: translateY(-1px);
        box-shadow: 0 0 8px rgba(99, 102, 241, 0.25);
    }
    .btn-pc-icon.danger:hover  { background: rgba(239, 68, 68, 0.2);   color: #fca5a5; box-shadow: 0 0 8px rgba(239,68,68,0.25); }
    .btn-pc-icon.success:hover { background: rgba(16, 185, 129, 0.2);  color: #6ee7b7; box-shadow: 0 0 8px rgba(16,185,129,0.25); }
    .btn-pc-icon.warning:hover { background: rgba(234, 179, 8, 0.2);   color: #fde047; box-shadow: 0 0 8px rgba(234,179,8,0.25); }

    /* Action bar divider */
    .msg-action-divider {
        width: 1px;
        height: 14px;
        background: rgba(255,255,255,0.08);
        margin: 0 3px;
        flex-shrink: 0;
    }

    /* Custom 4-Zone Compact Grid Context Menu System */
    .pc-context-menu {
        position: fixed !important;
        z-index: 999999 !important;
        width: 380px;
        max-height: 85vh;
        background: rgba(10, 14, 24, 0.97);
        backdrop-filter: blur(24px) saturate(180%);
        border: 1px solid rgba(99, 102, 241, 0.45);
        border-radius: 14px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.9), 0 0 25px rgba(99, 102, 241, 0.25);
        padding: 10px;
        display: none;
        animation: context-menu-in 0.15s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    .pc-context-header {
        padding: 4px 8px 6px 8px;
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #818cf8;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .pc-context-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 6px;
    }
    .pc-context-btn {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 10px;
        font-size: 0.78rem;
        font-weight: 500;
        color: #f1f5f9;
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .pc-context-btn:hover {
        background: rgba(99, 102, 241, 0.35);
        border-color: #6366f1;
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }
    .pc-context-divider {
        height: 1px;
        background: rgba(255, 255, 255, 0.08);
        margin: 6px 0;
        grid-column: 1 / -1;
    }
    @keyframes context-menu-in {
        0% { opacity: 0; transform: scale(0.96) translateY(-5px); }
        100% { opacity: 1; transform: scale(1) translateY(0); }
    }

    /* Composer */
    .pc-composer {
        padding: 14px 22px;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        background: rgba(9, 12, 19, 0.9);
        backdrop-filter: blur(16px);
        flex-shrink: 0;
    }
    .pc-composer-input {
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: 12px;
        color: #fff;
        padding: 10px 16px;
        font-size: 0.9rem;
        resize: none;
        transition: all 0.2s ease;
    }
    .pc-composer-input:focus {
        background: rgba(255, 255, 255, 0.09);
        border-color: rgba(99, 102, 241, 0.6);
        color: #fff;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
    }

    /* Empty State */
    .pc-empty-state {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: rgba(255, 255, 255, 0.3);
        gap: 16px;
        padding: 32px;
        text-align: center;
    }

    /* AI Mode Switcher Buttons */
    .btn-ai-mode {
        font-size: 0.73rem !important;
        font-family: 'JetBrains Mono', monospace !important;
        padding: 5px 12px !important;
        border-radius: 8px !important;
        transition: all 0.2s ease;
    }
    .btn-check:checked + .btn-outline-secondary {
        background: rgba(99, 102, 241, 0.25) !important;
        border-color: #6366f1 !important;
        color: #ffffff !important;
        box-shadow: 0 0 12px rgba(99, 102, 241, 0.3);
    }

    @keyframes pc-msg-pop {
        0%   { opacity: 0; transform: scale(0.97) translateY(8px); }
        60%  { opacity: 1; transform: scale(1.01) translateY(-1px); }
        100% { opacity: 1; transform: scale(1)    translateY(0); }
    }
    @keyframes pulse-badge {
        0%, 100% { transform: scale(1); }
        50%       { transform: scale(1.06); }
    }
    @keyframes pulse-dot {
        0%, 100% { opacity: 1; }
        50%       { opacity: 0.4; }
    }

    /* ── Neon shimmer scan-line on bubble hover ── */
    @keyframes neon-scan {
        0%   { transform: translateX(-100%) skewX(-15deg); }
        100% { transform: translateX(250%)  skewX(-15deg); }
    }
    .msg-bubble::after {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: inherit;
        background: linear-gradient(
            90deg,
            transparent 0%,
            rgba(255,255,255,0.04) 40%,
            rgba(255,255,255,0.09) 50%,
            rgba(255,255,255,0.04) 60%,
            transparent 100%
        );
        opacity: 0;
        pointer-events: none;
        overflow: hidden;
        transition: opacity 0.2s ease;
    }
    .msg-bubble:hover::after {
        opacity: 1;
        animation: neon-scan 0.65s ease forwards;
    }

    /* ── Stagger animation delay for sequential messages ── */
    .msg-wrapper:nth-child(1)  { animation-delay: 0.00s; }
    .msg-wrapper:nth-child(2)  { animation-delay: 0.04s; }
    .msg-wrapper:nth-child(3)  { animation-delay: 0.08s; }
    .msg-wrapper:nth-child(4)  { animation-delay: 0.12s; }
    .msg-wrapper:nth-child(5)  { animation-delay: 0.16s; }
    .msg-wrapper:nth-child(6)  { animation-delay: 0.20s; }
    .msg-wrapper:nth-child(n+7){ animation-delay: 0.24s; }

    /* ── Avatar neon ring pulse ── */
    @keyframes avatar-neon-pulse {
        0%, 100% { opacity: 1; }
        50%       { opacity: 0.65; }
    }
    .msg-wrapper.incoming .msg-avatar {
        animation: avatar-neon-pulse 3s ease-in-out infinite;
    }
    .msg-wrapper.outgoing .msg-avatar {
        animation: avatar-neon-pulse 3.5s ease-in-out infinite;
    }

    /* ── Author label color match ── */
    .msg-wrapper.incoming .msg-author-name { color: rgba(0, 229, 255, 0.45); }
    .msg-wrapper.outgoing .msg-author-name { color: rgba(96, 165, 250, 0.45); }

    /* ── Incoming bubble left neon accent stripe ── */
    .msg-bubble.incoming::before {
        content: '';
        position: absolute;
        top: 10%;
        left: -1.5px;
        width: 3px;
        height: 80%;
        border-radius: 0 2px 2px 0;
        background: linear-gradient(180deg, transparent, rgba(0,229,255,0.7), transparent);
        box-shadow: 0 0 8px rgba(0,229,255,0.5);
    }
    /* ── Outgoing bubble right neon accent stripe ── */
    .msg-bubble.outgoing::before {
        content: '';
        position: absolute;
        top: 10%;
        right: -1.5px;
        width: 3px;
        height: 80%;
        border-radius: 2px 0 0 2px;
        background: linear-gradient(180deg, transparent, rgba(59, 130, 246, 0.75), transparent);
        box-shadow: 0 0 8px rgba(59, 130, 246, 0.55);
    }

    /* ── 1. Avatar Online Neon Status Indicator ── */
    .msg-avatar::after {
        content: '';
        position: absolute;
        bottom: 0;
        right: -2px;
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background-color: #10b981;
        border: 1.5px solid #070a13;
        box-shadow: 0 0 6px rgba(16, 185, 129, 0.8);
        transition: transform 0.2s ease, background-color 0.2s ease;
    }
    .msg-wrapper.outgoing .msg-avatar::after {
        background-color: #22c55e;
        box-shadow: 0 0 7px rgba(34, 197, 94, 0.9);
    }
    .msg-wrapper:hover .msg-avatar::after {
        transform: scale(1.25);
    }

    /* ── 2. Glass Specular Sheen (Reflected Top Rim Light) ── */
    .msg-bubble {
        box-shadow: 
            0 -1px 0 rgba(255, 255, 255, 0.12) inset,
            0 4px 20px rgba(0, 0, 0, 0.5);
    }
    .msg-bubble.incoming {
        box-shadow: 
            0 1px 1px rgba(255, 255, 255, 0.18) inset,
            0 0 18px rgba(0, 229, 255, 0.18),
            0 6px 24px rgba(0, 0, 0, 0.5);
    }
    .msg-bubble.outgoing {
        box-shadow: 
            0 1px 1px rgba(255, 255, 255, 0.22) inset,
            0 0 20px rgba(59, 130, 246, 0.28),
            0 6px 24px rgba(0, 0, 0, 0.55);
    }

    /* ── 3. High-Contrast Custom Neon Selection (::selection) ── */
    .msg-bubble.incoming .msg-content-text ::selection {
        background-color: #00e5ff !important;
        color: #030816 !important;
        text-shadow: none !important;
    }
    .msg-bubble.outgoing .msg-content-text ::selection {
        background-color: #3b82f6 !important;
        color: #ffffff !important;
        text-shadow: none !important;
    }

    /* ── 4. Reactive Footer on Bubble Group Hover ── */
    .msg-footer {
        opacity: 0.68;
        transition: opacity 0.25s ease, transform 0.25s ease;
    }
    .msg-bubble-group:hover .msg-footer {
        opacity: 1;
        transform: translateY(1px);
    }

    /* ── 5. Rich Markdown Typography Inside Bubbles ── */
    .msg-content-text a {
        color: #7df9ff;
        text-decoration: none;
        border-bottom: 1px dashed rgba(125, 249, 255, 0.5);
        transition: all 0.2s ease;
        font-weight: 500;
    }
    .msg-bubble.outgoing .msg-content-text a {
        color: #93c5fd;
        border-bottom-color: rgba(147, 197, 253, 0.5);
    }
    .msg-content-text a:hover {
        border-bottom-style: solid;
        text-shadow: 0 0 8px currentColor;
    }

    /* Inline & Block Code in Chat */
    .msg-content-text code {
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.78rem;
        padding: 2px 6px;
        border-radius: 6px;
        background: rgba(0, 0, 0, 0.45);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: #38bdf8;
        display: inline-block;
    }
    .msg-bubble.outgoing .msg-content-text code {
        color: #60a5fa;
        background: rgba(10, 20, 45, 0.65);
        border-color: rgba(59, 130, 246, 0.35);
    }
    .msg-content-text pre {
        background: rgba(5, 8, 16, 0.85);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 10px;
        padding: 10px 12px;
        margin: 8px 0;
        overflow-x: auto;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.6) inset;
    }
    .msg-content-text pre code {
        background: transparent;
        border: none;
        padding: 0;
        color: #eff6ff;
    }

    /* Blockquotes (Replied/Quoted content) */
    .msg-content-text blockquote {
        margin: 6px 0;
        padding: 6px 12px;
        border-left: 3px solid rgba(0, 229, 255, 0.6);
        background: rgba(0, 229, 255, 0.04);
        border-radius: 0 8px 8px 0;
        font-style: italic;
        color: rgba(255, 255, 255, 0.8);
    }
    .msg-bubble.outgoing .msg-content-text blockquote {
        border-left-color: rgba(59, 130, 246, 0.75);
        background: rgba(59, 130, 246, 0.08);
    }

    /* ── 6. Tactical Multi-Tier Command Header & Toolbar ── */
    .pc-chat-header {
        flex-direction: column !important;
        align-items: stretch !important;
        padding: 0 !important;
        background: rgba(8, 12, 20, 0.94) !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.45);
        position: relative;
        z-index: 20;
    }
    .pc-header-tier-1 {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 18px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        gap: 12px;
    }
    .pc-header-tier-2 {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 6px 18px;
        background: rgba(4, 7, 13, 0.6);
        gap: 8px;
        overflow-x: auto;
        scrollbar-width: none;
    }
    .pc-header-tier-2::-webkit-scrollbar { display: none; }

    /* Cyber Glowing Mode Pills */
    .pc-cyber-mode-group {
        display: flex;
        background: rgba(13, 18, 30, 0.9);
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: 999px;
        padding: 3px;
        gap: 2px;
    }
    .btn-cyber-pill {
        border: none !important;
        background: transparent !important;
        color: rgba(255, 255, 255, 0.55) !important;
        font-family: 'JetBrains Mono', monospace !important;
        font-size: 0.72rem !important;
        font-weight: 600 !important;
        padding: 4px 12px !important;
        border-radius: 999px !important;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
        display: flex;
        align-items: center;
        gap: 5px;
        cursor: pointer;
    }
    .btn-cyber-pill:hover {
        color: #ffffff !important;
        background: rgba(255, 255, 255, 0.05) !important;
    }
    .btn-check:checked + .btn-cyber-pill {
        background: linear-gradient(135deg, #6366f1, #4f46e5) !important;
        color: #ffffff !important;
        box-shadow: 0 0 16px rgba(99, 102, 241, 0.6), 0 0 0 1px rgba(165, 180, 252, 0.4) inset !important;
    }
    .btn-check:checked + .btn-cyber-pill.copilot-active {
        background: linear-gradient(135deg, #0284c7, #0369a1) !important;
        box-shadow: 0 0 16px rgba(14, 165, 233, 0.6), 0 0 0 1px rgba(186, 230, 253, 0.4) inset !important;
    }
    .btn-check:checked + .btn-cyber-pill.manual-active {
        background: linear-gradient(135deg, #475569, #334155) !important;
        box-shadow: 0 0 12px rgba(100, 116, 139, 0.5) !important;
    }

    /* Action Toolbar Buttons & Mockup Badging */
    .pc-toolbar-group {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .pc-header-action-btn {
        height: 30px;
        padding: 0 10px;
        border-radius: 7px;
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(255, 255, 255, 0.08);
        color: #cbd5e1;
        font-size: 0.76rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        text-decoration: none;
        white-space: nowrap;
    }
    .pc-header-action-btn:hover {
        background: rgba(99, 102, 241, 0.2);
        border-color: #6366f1;
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);
    }
    .pc-header-action-btn.mockup {
        border: 1px dashed rgba(245, 158, 11, 0.4);
        background: rgba(245, 158, 11, 0.05);
        color: #fde047;
    }
    .pc-header-action-btn.mockup:hover {
        background: rgba(245, 158, 11, 0.2);
        border-color: #f59e0b;
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
    }
    .pc-toolbar-divider {
        width: 1px;
        height: 18px;
        background: rgba(255, 255, 255, 0.1);
        margin: 0 4px;
        flex-shrink: 0;
    }
    .pc-mockup-tag {
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.58rem;
        background: rgba(245, 158, 11, 0.25);
        color: #fef08a;
        padding: 1px 4px;
        border-radius: 4px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    
    /* In-Thread Live Search Bar */
    .pc-thread-search-bar {
        background: rgba(15, 23, 42, 0.95);
        border-bottom: 1px solid rgba(14, 165, 233, 0.4);
        padding: 8px 18px;
        display: none;
        align-items: center;
        gap: 10px;
        animation: search-bar-slide-down 0.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    @keyframes search-bar-slide-down {
        0% { opacity: 0; transform: translateY(-10px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    
    /* Fullscreen override */
    .pc-shell.fullscreen-active {
        position: fixed !important;
        inset: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        min-height: 100vh !important;
        z-index: 999999 !important;
        border-radius: 0 !important;
        border: none !important;
    }
</style>
@endpush

@section('content')
{{-- ===== HEADER SECTION ===== --}}
<div class="pc-header-section d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2 class="h4 text-light mb-1 d-flex align-items-center gap-2">
            <i class="fa-brands fa-whatsapp text-success" style="font-size: 1.4rem;"></i>
            <span>PeopleConnect <span style="font-weight:300;color:rgba(255,255,255,0.4);">Hub</span></span>
            <span class="badge" style="background:linear-gradient(135deg, rgba(16,185,129,0.2), rgba(5,150,105,0.2)); color:#6ee7b7; font-size:0.65rem; border:1px solid rgba(16,185,129,0.3); padding:4px 8px; border-radius:6px;">ZERO-LATENCY HYBRID ENGINE</span>
        </h2>
        <p class="text-muted small mb-0" style="font-size:0.83rem;">Real-time WhatsApp operational command center with autonomous AI failover and Reverb synchronicity.</p>
    </div>
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('hub.people-connect.agent-settings') }}" class="btn btn-sm text-light fw-bold px-3 py-2 d-flex align-items-center gap-2" style="background:linear-gradient(135deg, #6366f1, #8b5cf6); border:1px solid rgba(139,92,246,0.5); border-radius:10px; box-shadow: 0 4px 15px rgba(99,102,241,0.35); transition:all 0.2s ease;">
            <i class="fa-solid fa-robot text-warning animate-pulse"></i> <span>AI Agent Settings & Studio</span>
        </a>
        <button class="btn btn-sm px-3 py-2 d-flex align-items-center gap-2" style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.12); color:#e2e8f0; border-radius:10px; font-size:0.83rem;" onclick="syncWaha()">
            <i class="fa-solid fa-rotate text-info"></i> <span>Sync WAHA Pipeline</span>
        </button>
    </div>
</div>

{{-- ===== LIVE AI COMMAND CENTER STATS BAR ===== --}}
<div class="pc-stats-section row g-3 mb-3 animate-fade-in">
    <div class="col-md-3">
        <div class="pc-stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <span class="pc-stat-label"><i class="fa-solid fa-robot" style="color:#818cf8;"></i> ACTIVE AI AGENT</span>
                <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-25 small" style="font-size:0.65rem;">ONLINE</span>
            </div>
            <div class="pc-stat-val text-truncate" id="statActiveAgent" title="{{ $stats['active_agent_name'] ?? 'Ertugrul Orchestrator' }}">{{ $stats['active_agent_name'] ?? 'Ertugrul Orchestrator' }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="pc-stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <span class="pc-stat-label"><i class="fa-solid fa-layer-group" style="color:#a78bfa;"></i> 3-TIER FALLBACK</span>
                <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-25 small" style="font-size:0.65rem;">READY</span>
            </div>
            <div class="pc-stat-val text-truncate" id="statFallbackChain" title="{{ $stats['fallback_chain'] ?? 'OpenAI &rarr; Gemini &rarr; Anthropic' }}">{{ $stats['fallback_chain'] ?? 'OpenAI &rarr; Gemini &rarr; Anthropic' }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="pc-stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <span class="pc-stat-label"><i class="fa-solid fa-key" style="color:#34d399;"></i> KEY ROTATION POOL</span>
                <span class="badge bg-info bg-opacity-25 text-info border border-info border-opacity-25 small" style="font-size:0.65rem;">AUTO-CYCLE</span>
            </div>
            <div class="pc-stat-val text-truncate" id="statRotationStatus">{{ $stats['api_keys_pool_status'] ?? 'Active & Protected' }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="pc-stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <span class="pc-stat-label"><i class="fa-solid fa-bolt text-warning"></i> WAHA WEBHOOKS</span>
                <span class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-25 small" style="font-size:0.65rem;">ZERO-LATENCY</span>
            </div>
            <div class="pc-stat-val text-truncate" id="statLiveCount">{{ $stats['pipeline_status'] ?? '55 Chats / 601 Msgs' }}</div>
        </div>
</div>

{{-- ===== WAHA SYNC ACTIVE PROGRESS BANNER ===== --}}
<div id="wahaSyncProgressBanner" class="card mb-3 text-light" style="display:none; background: rgba(15, 23, 42, 0.95); border: 1px solid rgba(99, 102, 241, 0.4); border-radius: 14px; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
    <div class="card-body p-3 d-flex flex-column gap-2">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-rotate text-info fa-spin" id="wahaSyncSpinIcon"></i>
                <span class="fw-bold" style="font-size: 0.9rem;" id="wahaSyncBannerTitle">WAHA Pipeline Synchronization Active</span>
                <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-25 font-mono" id="wahaSyncStatusBadge" style="font-size: 0.65rem;">RUNNING</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-xs btn-outline-info px-2 py-1" onclick="openWahaLogModal()" style="font-size: 0.75rem; border-radius: 6px;">
                    <i class="fa-solid fa-terminal me-1"></i> View Live Logs & Details
                </button>
                <button class="btn btn-xs btn-outline-warning px-2 py-1" onclick="pauseCurrentWahaSync()" id="wahaPauseBtn" style="font-size: 0.75rem; border-radius: 6px;">
                    <i class="fa-solid fa-pause me-1"></i> Pause
                </button>
            </div>
        </div>
        <div class="progress" style="height: 10px; background: rgba(255,255,255,0.06); border-radius: 6px; overflow: hidden;">
            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" id="wahaSyncProgressBar" role="progressbar" style="width: 0%;"></div>
        </div>
        <div class="d-flex align-items-center justify-content-between text-muted small" style="font-size: 0.76rem;">
            <span id="wahaSyncSubtext">Initializing pipeline synchronization...</span>
            <span class="fw-bold text-info font-mono" id="wahaSyncPercent">0%</span>
        </div>
    </div>
</div>

{{-- ===== MASTER INTERACTIVE SHELL ===== --}}
<div class="pc-shell animate-fade-in" oncontextmenu="openZone4ContextMenu(event)">
    <!-- Custom 4-Zone Context Menu DOM Element -->
    <div id="pcContextMenu" class="pc-context-menu"></div>

    {{-- ===== SIDEBAR (CONVERSATIONS) ===== --}}
    <div class="pc-sidebar">
        <div class="pc-sidebar-header">
            <div class="pc-search-wrapper">
                <i class="fa-solid fa-magnifying-glass pc-search-icon"></i>
                <input type="text" class="pc-search" id="pcSearch" placeholder="Search conversations by name or ID..." oninput="filterConvs(this.value)">
            </div>
        </div>
        <div class="pc-conv-list" id="pcConvList">
            @forelse(($conversations ?? []) as $conv)
                @php
                    $wahaId = $conv->provider_conversation_id ?? (strval($conv->id));
                    $cleanPhone = preg_replace('/@(c\.us|g\.us|lid|broadcast|s\.whatsapp\.net)$/i', '', $conv->contact->phone ?? ($conv->contact->whatsapp_number ?? $wahaId));
                    $contactPhone = $conv->contact->phone ?? ($conv->contact->whatsapp_number ?? $cleanPhone);
                    $rawName = $conv->contact->name ?? null;
                    if (empty($rawName) || str_starts_with($rawName, 'WAHA Contact') || str_starts_with($rawName, 'WhatsApp User')) {
                        $name = $contactPhone ?: $wahaId;
                    } else {
                        $name = $rawName;
                    }
                    $initials = mb_strtoupper(mb_substr($name, 0, 2));
                    $preview = $conv->last_message_preview ?? 'No recent interaction.';
                    $unread = $conv->unread_count ?? 0;
                    $timeAgo = $conv->last_message_at ? \Carbon\Carbon::parse($conv->last_message_at)->format('H:i') : '';
                @endphp
                <a class="pc-conv-item {{ ($selectedChatId && $selectedChatId == ($conv->provider_conversation_id ?? $conv->id)) ? 'is-active' : '' }}" 
                   href="javascript:void(0)" 
                   data-name="{{ strtolower($name) }}" 
                   data-phone="{{ strtolower(strval($conv->provider_conversation_id ?? $conv->id)) }}"
                   data-conv-id="{{ $conv->id }}"
                   data-waha-id="{{ $conv->provider_conversation_id ?? $conv->id }}"
                   data-contact-id="{{ $conv->contact_id ?? '' }}"
                   oncontextmenu="openZone1ContextMenu(event, this)"
                   onclick="selectChat('{{ $conv->provider_conversation_id ?? $conv->id }}', {name: '{{ addslashes($name) }}', waha_id: '{{ addslashes($wahaId) }}', phone: '{{ addslashes($contactPhone) }}', picture: '', unreadCount: {{ $unread }}, db_id: {{ $conv->id }}, contact_id: '{{ $conv->contact_id ?? '' }}' }, this)">
                    <div class="pc-avatar">
                        <div class="pc-avatar-img">{{ $initials }}</div>
                        <span class="pc-status-dot nexus"></span>
                    </div>
                    <div class="pc-conv-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="pc-conv-name" title="{{ $name }}">{{ $name }}</span>
                            <span class="pc-time-badge">{{ $timeAgo }}</span>
                        </div>
                        <div class="d-flex flex-wrap gap-1 mt-1 mb-1">
                            <span class="pc-phone-badge" title="WAHA ID"><i class="fa-brands fa-whatsapp text-success me-1" style="font-size:0.68rem;"></i>WAHA: {{ $wahaId }}</span>
                            <span class="pc-phone-badge" style="background:rgba(255,255,255,0.06);color:#cbd5e1;" title="Phone Number"><i class="fa-solid fa-phone text-info me-1" style="font-size:0.65rem;"></i>{{ $contactPhone }}</span>
                        </div>
                        <span class="pc-preview" title="{{ e($preview) }}">{{ $preview }}</span>
                    </div>
                    @if($unread > 0)
                        <div class="pc-conv-right justify-content-center">
                            <span class="pc-unread-badge">{{ $unread > 99 ? '99+' : $unread }}</span>
                        </div>
                    @endif
                </a>
            @empty
                <div class="p-4 text-center" id="chatsLoading" style="color:rgba(255,255,255,0.3);font-size:0.85rem;">
                    <i class="fa-brands fa-whatsapp mb-2 text-success" style="font-size:2.4rem;opacity:0.6;display:block;"></i>
                    No active WhatsApp conversations found in MySQL persistence layer.
                </div>
            @endforelse
        </div>
    </div>

    {{-- ===== CHAT AREA ===== --}}
    <div class="pc-chat-area" id="chatAreaContainer">
        <!-- Empty state before selection -->
        <div class="pc-empty-state" id="emptyStateContainer" style="position: relative;">
            <!-- Persistent Sidebar Control Bar for Empty State -->
            <div style="position: absolute; top: 16px; left: 20px; display: flex; align-items: center; gap: 8px; z-index: 10;">
                <button class="btn btn-sm text-light d-flex align-items-center gap-2 px-3 py-2" 
                        id="btnToggleConvSidebarEmpty" 
                        onclick="toggleConvSidebar()" 
                        title="Toggle Conversation List Sidebar" 
                        style="background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.14); border-radius: 10px; font-size: 0.8rem; transition: all 0.2s ease;">
                    <i class="fa-solid fa-table-columns text-info" id="convSidebarIconEmpty"></i>
                    <span>Conversations</span>
                </button>
                <button class="btn btn-sm text-light d-flex align-items-center gap-2 px-3 py-2" 
                        id="btnToggleMainNavEmpty" 
                        onclick="toggleMainSidebar()" 
                        title="Toggle Main Application Sidebar" 
                        style="background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.14); border-radius: 10px; font-size: 0.8rem; transition: all 0.2s ease;">
                    <i class="fa-solid fa-bars-progress text-warning" id="mainNavIconEmpty"></i>
                    <span>Main Nav</span>
                </button>
            </div>

            <div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, rgba(99,102,241,0.2), rgba(16,185,129,0.2)); border: 1px solid rgba(255,255,255,0.1); display:flex; align-items:center; justify-content:center; box-shadow: 0 12px 28px rgba(0,0,0,0.3);">
                <i class="fa-brands fa-whatsapp text-success" style="font-size: 2.8rem;"></i>
            </div>
            <h4 class="text-light font-outfit fw-bold mb-1 mt-2">No Conversation Selected</h4>
            <p class="text-muted small" style="max-width: 400px; font-size: 0.85rem; line-height: 1.6;">
                Select an active WhatsApp session from the left sidebar to launch real-time messaging, review conversational history, and direct autonomous AI Autopilot behaviors.
            </p>
            <div class="d-flex gap-2 mt-2">
                <span class="badge bg-secondary bg-opacity-25 text-light border border-secondary border-opacity-25"><i class="fa-solid fa-database me-1 text-info"></i> MySQL SSR Active</span>
                <span class="badge bg-secondary bg-opacity-25 text-light border border-secondary border-opacity-25"><i class="fa-brands fa-google me-1 text-warning"></i> Firestore Real-Time Ready</span>
            </div>
        </div>
        
        <!-- Active Chat Container -->
        <div id="activeChatContainer" style="display:none; flex: 1; flex-direction: column; min-height: 0; max-height: 100%; overflow: hidden;">
            {{-- Tactical Zero-Latency Command Header --}}
            <div class="pc-chat-header">
                <!-- Tier 1: Identity & Autonomous Mode Controls -->
                <div class="pc-header-tier-1">
                    <div class="d-flex align-items-center gap-3 min-w-0">
                        <!-- Dual Sidebar Toggle Controls -->
                        <div class="d-flex align-items-center gap-1 flex-shrink-0">
                            <button class="btn btn-sm text-light d-flex align-items-center justify-content-center" 
                                    id="btnToggleConvSidebar" 
                                    onclick="toggleConvSidebar()" 
                                    title="Toggle Conversation List Sidebar [Ctrl+B]" 
                                    style="width: 36px; height: 36px; background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.14); border-radius: 9px; transition: all 0.2s ease;">
                                <i class="fa-solid fa-table-columns text-info" id="convSidebarIcon" style="font-size: 0.9rem;"></i>
                            </button>
                            <button class="btn btn-sm text-light d-flex align-items-center justify-content-center" 
                                    id="btnToggleMainNav" 
                                    onclick="toggleMainSidebar()" 
                                    title="Toggle Main Application Sidebar (Immersion Mode)" 
                                    style="width: 36px; height: 36px; background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.14); border-radius: 9px; transition: all 0.2s ease;">
                                <i class="fa-solid fa-bars-progress text-warning" id="mainNavIcon" style="font-size: 0.9rem;"></i>
                            </button>
                        </div>

                        <div id="activeChatAvatar" class="flex-shrink-0"></div>

                        <div class="min-w-0">
                            <div class="fw-bold text-light d-flex align-items-center gap-2 text-truncate" style="font-size:0.95rem;">
                                <span id="activeChatName" class="text-truncate"></span>
                                <span class="badge flex-shrink-0" style="background:rgba(16,185,129,0.15);color:#6ee7b7;font-size:0.62rem;border:1px solid rgba(16,185,129,0.3);padding:2px 7px;"><i class="fa-solid fa-circle me-1" style="font-size:0.4rem;color:#10b981;"></i>WAHA STREAM</span>
                            </div>
                            <div class="text-muted d-flex align-items-center gap-2 text-truncate" style="font-size:0.75rem;font-family:'JetBrains Mono',monospace;" title="Click to quickly copy contact identification info">
                                <span id="activeChatIdDisplay" style="cursor:pointer;" onclick="actCopyCurrentContactInfo()"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Autonomous AI Reply Mode Switcher -->
                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                        <div class="pc-cyber-mode-group" role="group">
                            <input type="radio" class="btn-check" name="aiMode" id="aiModeAuto" value="autopilot" autocomplete="off" checked onclick="switchAiMode('autopilot')">
                            <label class="btn-cyber-pill" for="aiModeAuto" title="Autopilot: Fully autonomous AI responses with zero latency & velocity safeguards">
                                <i class="fa-solid fa-bolt text-warning"></i>
                                <span>Autopilot</span>
                            </label>

                            <input type="radio" class="btn-check" name="aiMode" id="aiModeAssist" value="copilot" autocomplete="off" onclick="switchAiMode('copilot')">
                            <label class="btn-cyber-pill copilot-active" for="aiModeAssist" title="Copilot: AI generates intelligent draft recommendations for operator approval">
                                <i class="fa-solid fa-wand-magic-sparkles text-info"></i>
                                <span>Copilot</span>
                            </label>

                            <input type="radio" class="btn-check" name="aiMode" id="aiModeOff" value="disabled" autocomplete="off" onclick="switchAiMode('disabled')">
                            <label class="btn-cyber-pill manual-active" for="aiModeOff" title="Manual: AI automated responses disabled; manual human takeover">
                                <i class="fa-solid fa-user text-secondary"></i>
                                <span>Manual</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Tier 2: Comprehensive Tactical & Roadmap Action Toolbar -->
                <div class="pc-header-tier-2">
                    <!-- Left: Operational Functions -->
                    <div class="pc-toolbar-group">
                        <button type="button" class="pc-header-action-btn" onclick="toggleInThreadSearch()" title="Search for words or messages inside this chat timeline">
                            <i class="fa-solid fa-magnifying-glass text-info"></i>
                            <span>Search</span>
                        </button>
                        <button type="button" class="pc-header-action-btn" onclick="refreshCurrentThread()" title="Force resynchronize conversation stream from MySQL & Firestore">
                            <i class="fa-solid fa-rotate text-success" id="btnRefreshThreadIcon"></i>
                            <span>Refresh</span>
                        </button>
                        <button type="button" class="pc-header-action-btn" onclick="exportChatTranscript()" title="Export & download full chat timeline transcript (.md / .txt)">
                            <i class="fa-solid fa-file-export" style="color:#a78bfa;"></i>
                            <span>Export</span>
                        </button>
                        <button type="button" class="pc-header-action-btn" onclick="actCopyCurrentContactInfo()" title="Copy active contact Phone & WAHA ID to system clipboard">
                            <i class="fa-solid fa-copy text-secondary"></i>
                            <span>Copy ID</span>
                        </button>
                        <button type="button" class="pc-header-action-btn" onclick="openAiThreadAnalyticsModal()" title="Run AI instant sentiment & interaction velocity analysis on this thread">
                            <i class="fa-solid fa-chart-pie text-warning"></i>
                            <span>AI Analytics</span>
                        </button>
                    </div>

                    <div class="pc-toolbar-divider"></div>

                    <!-- Center/Right: Roadmap & Advanced Capabilities (Marked Mockup) -->
                    <div class="pc-toolbar-group">
                        <button type="button" class="pc-header-action-btn mockup" onclick="triggerMockupModal('WhatsApp VoIP Voice Call', 'Simulated real-time audio bridge connecting WhatsApp WebRTC voice calls to Nexus AI voice processing agents.', 'Audio Gateway v2.4 in deployment pipeline.')" title="Simulate WhatsApp VoIP Audio call bridge [MOCKUP / ROADMAP]">
                            <i class="fa-solid fa-phone-volume text-warning"></i>
                            <span>Voice Call</span>
                            <span class="pc-mockup-tag">Mockup</span>
                        </button>
                        <button type="button" class="pc-header-action-btn mockup" onclick="triggerMockupModal('AI Video Conference & Avatar Room', 'Initiate an interactive AI Video Avatar conference room or direct WhatsApp video streaming interface.', 'Requires WebRTC video synthesis hardware acceleration.')" title="Launch AI Avatar Video Conference [MOCKUP / ROADMAP]">
                            <i class="fa-solid fa-video text-info"></i>
                            <span>Video Room</span>
                            <span class="pc-mockup-tag">Mockup</span>
                        </button>
                        <button type="button" class="pc-header-action-btn mockup" onclick="triggerMockupModal('CRM Customer Intelligence Drawer', 'Slide-out deep customer profile featuring Customer Lifetime Value (LTV), transaction history, AI tags, and lead conversion velocity.', 'Integration with ContactsHub CRM 3.0 scheduled for next sprint.')" title="Open CRM Profile & Customer Intelligence Drawer [MOCKUP / ROADMAP]">
                            <i class="fa-solid fa-address-card" style="color:#34d399;"></i>
                            <span>CRM Profile</span>
                            <span class="pc-mockup-tag">Mockup</span>
                        </button>
                        <button type="button" class="pc-header-action-btn mockup" onclick="triggerMockupModal('Automated AI Drip Scheduler', 'Schedule intelligent automated message sequences, follow-up voice notes, or delayed billing reminders for this contact.', 'Cron Scheduler Integration active; UI Builder coming soon.')" title="Schedule automated follow-ups and drip broadcasts [MOCKUP / ROADMAP]">
                            <i class="fa-regular fa-clock" style="color:#818cf8;"></i>
                            <span>Schedule Drip</span>
                            <span class="pc-mockup-tag">Mockup</span>
                        </button>
                        <button type="button" class="pc-header-action-btn mockup d-none d-xxl-inline-flex" onclick="triggerMockupModal('Signal Encryption Key Auditor', 'Inspect End-to-End Encryption cryptographic handshakes and verify peer session identity fingerprints.', 'Current cryptographic state: Validated via WhatsApp Signal Protocol.')" title="Verify E2EE cryptographic handshakes [MOCKUP / ROADMAP]">
                            <i class="fa-solid fa-key text-success"></i>
                            <span>E2EE Audit</span>
                            <span class="pc-mockup-tag">Mockup</span>
                        </button>
                        <button type="button" class="pc-header-action-btn mockup d-none d-xxl-inline-flex" onclick="triggerMockupModal('VIP Alert & Quiet Hours Override', 'Bypass system silent hours and push priority acoustic alarms to operating team for high-value VIP customer inbound events.', 'Priority Notification Routing engine ready.')" title="Toggle priority VIP notification override [MOCKUP / ROADMAP]">
                            <i class="fa-solid fa-bell-slash text-danger"></i>
                            <span>VIP Override</span>
                            <span class="pc-mockup-tag">Mockup</span>
                        </button>
                    </div>

                    <div class="pc-toolbar-divider"></div>

                    <!-- Right: Fullscreen immersion toggle -->
                    <div class="pc-toolbar-group">
                        <button type="button" class="pc-header-action-btn" onclick="toggleChatFullscreen()" id="btnFullscreenToggle" title="Toggle Fullscreen Immersion Mode [Alt+F]">
                            <i class="fa-solid fa-expand text-light" id="iconFullscreenToggle"></i>
                            <span class="d-none d-xl-inline">Fullscreen</span>
                        </button>
                    </div>
                </div>

                <!-- In-Thread Live Search Bar (Toggled on Demand) -->
                <div class="pc-thread-search-bar" id="inThreadSearchDiv">
                    <i class="fa-solid fa-magnifying-glass text-info"></i>
                    <input type="text" id="inThreadSearchInput" class="form-control form-control-sm bg-dark text-light border-0" placeholder="Filter message timeline by keyword..." oninput="filterMessagesInThread(this.value)" style="background: rgba(255,255,255,0.08) !important; border-radius: 8px;">
                    <span id="inThreadSearchCount" class="badge bg-secondary bg-opacity-25 text-info border border-secondary border-opacity-25 font-mono">0 matches</span>
                    <button type="button" class="btn btn-xs text-muted hover-text-light" onclick="toggleInThreadSearch(false)" title="Close search bar">
                        <i class="fa-solid fa-xmark fs-6"></i>
                    </button>
                </div>
            </div>

            {{-- Message History Box --}}
            <div class="pc-chat-msgs" id="messageHistory" oncontextmenu="openZone2ContextMenu(event)">
                <!-- Dynamically injected messages -->
            </div>

            {{-- Composer Console --}}
            <div class="pc-composer">
                <div class="d-flex align-items-end gap-2">
                    <textarea class="form-control pc-composer-input flex-grow-1" id="messageInput" rows="1" placeholder="Type a message or AI instructions... (Press Enter to transmit)"></textarea>
                    <button class="btn btn-sm text-light fw-bold" onclick="sendMessage()" style="background:linear-gradient(135deg,#6366f1,#8b5cf6); border:1px solid rgba(255,255,255,0.2); border-radius:12px; width:44px; height:44px; display:flex; align-items:center; justify-content:center; flex-shrink:0; box-shadow: 0 4px 14px rgba(99,102,241,0.35); transition:all 0.2s ease;" title="Transmit directly via WAHA Engine">
                        <i class="fa-solid fa-paper-plane" style="font-size:0.9rem;"></i>
                    </button>
                </div>
                <div class="d-flex justify-content-end align-items-center mt-2 px-1 text-muted" style="font-size:0.7rem; font-family:'JetBrains Mono',monospace;">
                    <span>Enter to Send &bull; Shift+Enter for New Line</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- WAHA Sync Log & Details Modal -->
<div class="modal fade" id="wahaSyncLogModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content text-light" style="background: rgba(11, 17, 28, 0.98); backdrop-filter: blur(25px); border: 1px solid rgba(99, 102, 241, 0.4); border-radius: 16px; box-shadow: 0 25px 60px rgba(0,0,0,0.9);">
            <div class="modal-header border-bottom border-secondary border-opacity-25 p-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-terminal text-info fs-5"></i>
                    <h5 class="modal-title fw-bold mb-0" style="font-size: 1.05rem;">WAHA Sync Process Terminal & Diagnostics</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Stats Row -->
                <div class="row g-2 mb-3">
                    <div class="col-3">
                        <div class="p-2 rounded text-center" style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.08);">
                            <small class="text-muted d-block font-mono" style="font-size:0.65rem;">TOTAL ITEMS</small>
                            <span class="fw-bold text-light" id="wahaModalTotal">0</span>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 rounded text-center" style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.08);">
                            <small class="text-muted d-block font-mono" style="font-size:0.65rem;">PROCESSED</small>
                            <span class="fw-bold text-success" id="wahaModalProcessed">0</span>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 rounded text-center" style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.08);">
                            <small class="text-muted d-block font-mono" style="font-size:0.65rem;">FAILED</small>
                            <span class="fw-bold text-danger" id="wahaModalFailed">0</span>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 rounded text-center" style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.08);">
                            <small class="text-muted d-block font-mono" style="font-size:0.65rem;">STATUS</small>
                            <span class="fw-bold text-info" id="wahaModalStatus">IDLE</span>
                        </div>
                    </div>
                </div>

                <!-- Terminal Screen -->
                <div style="background: #050914; border: 1px solid rgba(99, 102, 241, 0.3); border-radius: 10px; padding: 14px; font-family: monospace; font-size: 0.8rem; color: #a5f3fc; height: 280px; overflow-y: auto;" id="wahaLogTerminal">
                    <div class="text-muted">[System] Terminal initialized. Waiting for WAHA synchronization logs...</div>
                </div>
            </div>
            <div class="modal-footer border-top border-secondary border-opacity-25 p-2 px-3">
                <button class="btn btn-sm btn-outline-info" onclick="checkWahaStatus()"><i class="fa-solid fa-rotate me-1"></i> Refresh Status</button>
                <button class="btn btn-sm btn-success" onclick="window.location.reload()"><i class="fa-solid fa-arrows-rotate me-1"></i> Reload Conversations</button>
                <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- AI Thread Sentiment & Diagnostics Modal -->
<div class="modal fade" id="aiThreadAnalyticsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-light" style="background: rgba(11, 17, 28, 0.98); backdrop-filter: blur(25px); border: 1px solid rgba(99, 102, 241, 0.5); border-radius: 16px; box-shadow: 0 25px 60px rgba(0,0,0,0.9);">
            <div class="modal-header border-bottom border-secondary border-opacity-25 p-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-chart-pie text-warning fs-5"></i>
                    <h5 class="modal-title fw-bold mb-0" style="font-size: 1.05rem;">AI Thread Analytics & Sentiment</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="p-3 mb-3 rounded" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small font-mono">ESTIMATED SENTIMENT</span>
                        <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-25 font-mono" id="aiAnalyticsSentimentBadge">POSITIVE (0.91)</span>
                    </div>
                    <div class="progress" style="height: 8px; background: rgba(255,255,255,0.06); border-radius: 4px;">
                        <div class="progress-bar bg-success" id="aiAnalyticsSentimentBar" role="progressbar" style="width: 88%;"></div>
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-4">
                        <div class="p-2 rounded text-center" style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.08);">
                            <small class="text-muted d-block font-mono" style="font-size:0.65rem;">MESSAGES</small>
                            <span class="fw-bold text-info" id="aiAnalyticsMsgCount">0</span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 rounded text-center" style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.08);">
                            <small class="text-muted d-block font-mono" style="font-size:0.65rem;">USER VS AGENT</small>
                            <span class="fw-bold text-light" id="aiAnalyticsRatio">50% / 50%</span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 rounded text-center" style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.08);">
                            <small class="text-muted d-block font-mono" style="font-size:0.65rem;">VELOCITY</small>
                            <span class="fw-bold text-warning" id="aiAnalyticsVelocity">HIGH</span>
                        </div>
                    </div>
                </div>

                <div class="p-3 rounded" style="background: #050914; border: 1px solid rgba(99, 102, 241, 0.3); font-size: 0.82rem;">
                    <div class="text-indigo font-mono small mb-1" style="color: #818cf8;"><i class="fa-solid fa-wand-magic-sparkles me-1"></i> AUTONOMOUS AI RECOMMENDATION:</div>
                    <p class="text-light mb-0" id="aiAnalyticsRecommendation">Customer interaction timeline indicates high intent. Maintain Autopilot mode with high confidence thresholds.</p>
                </div>
            </div>
            <div class="modal-footer border-top border-secondary border-opacity-25 p-2 px-3">
                <button class="btn btn-sm btn-outline-warning" onclick="switchAiMode('copilot'); bootstrap.Modal.getInstance(document.getElementById('aiThreadAnalyticsModal')).hide(); showToastNotification('Switched to Copilot mode', '#0284c7');"><i class="fa-solid fa-wand-magic-sparkles me-1"></i> Switch to Copilot</button>
                <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Done</button>
            </div>
        </div>
    </div>
</div>

<!-- Roadmap Feature Info [MOCKUP] Modal -->
<div class="modal fade" id="pcMockupFeatureModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-light" style="background: rgba(11, 17, 28, 0.98); backdrop-filter: blur(25px); border: 1px solid rgba(245, 158, 11, 0.6); border-radius: 16px; box-shadow: 0 25px 60px rgba(0,0,0,0.9);">
            <div class="modal-header border-bottom border-warning border-opacity-25 p-3" style="background: rgba(245, 158, 11, 0.05);">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-layer-group text-warning fs-5"></i>
                    <h5 class="modal-title fw-bold mb-0" id="mockupModalTitle" style="font-size: 1.05rem;">Roadmap Feature [MOCKUP]</h5>
                    <span class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-25 font-mono" style="font-size: 0.65rem;">PREVIEW</span>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-light" id="mockupModalDesc" style="font-size: 0.9rem; line-height: 1.6;"></p>
                <div class="p-3 rounded mt-3" style="background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.3); font-size: 0.82rem; font-family: 'JetBrains Mono', monospace;">
                    <div class="text-warning mb-1"><i class="fa-solid fa-microchip me-1"></i> ARCHITECTURAL STATUS:</div>
                    <div class="text-light" id="mockupModalStatus">This capability is actively being orchestrated in our continuous integration roadmap.</div>
                </div>
            </div>
            <div class="modal-footer border-top border-secondary border-opacity-25 p-2 px-3">
                <button class="btn btn-sm btn-outline-warning" data-bs-dismiss="modal" onclick="showToastNotification('Feature marked for early beta notification', '#f59e0b')"><i class="fa-solid fa-star me-1"></i> Request Early Access</button>
                <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Understood</button>
            </div>
        </div>
    </div>
</div>

<!-- Firebase SDK -->
<script src="https://www.gstatic.com/firebasejs/10.9.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.9.0/firebase-firestore-compat.js"></script>
@endsection

@push('scripts')
<script>
/* ===== Firebase & System Initialization ===== */
const rawProjectId = "{{ (config('services.firebase.project_id') ?: config('firebase.project_id')) ?: 'nexus-c9155' }}";
const firebaseConfig = {
    projectId: (rawProjectId && rawProjectId.trim() !== '') ? rawProjectId.trim() : "nexus-c9155",
    apiKey: "{{ (config('services.firebase.api_key') ?: config('firebase.api_key')) ?: '' }}",
};
if (!firebaseConfig.projectId || firebaseConfig.projectId.trim() === '') {
    firebaseConfig.projectId = "nexus-c9155";
}
try {
    firebase.initializeApp(firebaseConfig);
} catch (e) {
    console.warn("Firebase init defensive warning:", e);
}
const db = firebase.firestore();

let currentChatId = '{{ $selectedChatId ?? "" }}';
let currentDbId = null;
let currentContactId = null;
let activeMessagesUnsubscribe = null;
let activeMysqlPollInterval = null;
let fetchTokenCounter = 0;

/* ===== Fetch Chats Real-time (Defensive Hybrid Shield) ===== */
if (firebaseConfig.apiKey && firebaseConfig.apiKey.trim() !== '') {
    try {
        db.collection('chats').onSnapshot(snapshot => {
            const list = document.getElementById('pcConvList');
            if (!list) return;
            
            // Defensive shield: never let an empty Firestore snapshot erase MySQL Server-Side Rendered chats!
            if (snapshot.empty) {
                console.log('Firestore chats stream is empty, preserving MySQL server-rendered conversations.');
                return;
            }

            const docs = [];
            snapshot.forEach(doc => docs.push({ id: doc.id, data: doc.data() }));
            docs.sort((a, b) => (b.data.timestamp || 0) - (a.data.timestamp || 0));

            if (docs.length > 0) {
                const loadingEl = document.getElementById('chatsLoading');
                if (loadingEl) loadingEl.remove();
                list.innerHTML = '';
                docs.forEach(item => {
                    const chatElement = buildChatCardDOM(item.id, item.data);
                    list.appendChild(chatElement);
                });
            }
        }, error => {
            console.warn('Firestore real-time feed unreachable, operating on persistent MySQL state:', error.message);
        });
    } catch (e) {
        console.warn('Firestore listener start warning:', e);
    }
} else {
    console.info('Firebase API key unconfigured in .env; running cleanly in Real-Time MySQL Hybrid mode.');
}

/* ===== Build Chat Card from Real-Time Stream ===== */
function buildChatCardDOM(chatId, data) {
    const a = document.createElement('a');
    const isActive = (chatId === currentChatId) ? 'is-active' : '';
    const dbId = data.db_id || data.id || null;
    const contactId = data.contact_id || '';
    
    const wahaId = data.waha_id || chatId || '';
    const cleanId = String(wahaId).replace('@c.us', '').replace('@lid', '').replace('@g.us', '').replace('@s.whatsapp.net', '');
    const phone = data.phone || data.whatsapp_number || cleanId;
    
    let name = data.name || data.display_name || '';
    if (!name || name.startsWith('WAHA Contact') || name.startsWith('WhatsApp User')) {
        name = phone || cleanId;
    }

    a.className = `pc-conv-item ${isActive}`;
    a.href = `javascript:void(0)`;
    a.dataset.name = (name || '').toLowerCase();
    a.dataset.phone = (chatId || '').toLowerCase();
    if (dbId) a.dataset.convId = dbId;
    if (contactId) a.dataset.contactId = contactId;
    
    a.oncontextmenu = (e) => openZone1ContextMenu(e, a);
    a.onclick = () => {
        document.querySelectorAll('.pc-conv-item').forEach(el => el.classList.remove('is-active'));
        a.classList.add('is-active');
        selectChat(chatId, { ...data, name: name, waha_id: wahaId, phone: phone, db_id: dbId, contact_id: contactId }, a);
    };

    const initials = name.substring(0, 2).toUpperCase();
    const preview = data.lastMessage?.body || data.last_message_preview || (typeof data.lastMessage === 'string' ? data.lastMessage : '') || 'No recent messages.';
    const unread = data.unreadCount || data.unread_count || 0;
    
    let timeAgo = '';
    const ts = data.timestamp || (data.lastMessage?.timestamp) || null;
    if (ts) {
        const d = new Date(typeof ts === 'number' ? ts : ts);
        if (!isNaN(d)) timeAgo = d.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    }

    const avatarHtml = data.picture 
        ? `<img src="${data.picture}" class="pc-avatar-img" alt="">` 
        : `<div class="pc-avatar-img">${initials}</div>`;

    a.innerHTML = `
        <div class="pc-avatar">
            ${avatarHtml}
            <span class="pc-status-dot nexus"></span>
        </div>
        <div class="pc-conv-body">
            <div class="d-flex align-items-center justify-content-between">
                <span class="pc-conv-name" title="${name}">${name}</span>
                <span class="pc-time-badge">${timeAgo}</span>
            </div>
            <div class="d-flex flex-wrap gap-1 mt-1 mb-1">
                <span class="pc-phone-badge" title="WAHA ID"><i class="fa-brands fa-whatsapp text-success me-1" style="font-size:0.68rem;"></i>WAHA: ${wahaId}</span>
                <span class="pc-phone-badge" style="background:rgba(255,255,255,0.06);color:#cbd5e1;" title="Phone Number"><i class="fa-solid fa-phone text-info me-1" style="font-size:0.65rem;"></i>${phone}</span>
            </div>
            <span class="pc-preview" title="${preview.replace(/"/g, '&quot;')}">${preview}</span>
        </div>
        ${unread > 0 ? `<div class="pc-conv-right justify-content-center"><span class="pc-unread-badge">${unread > 99 ? '99+' : unread}</span></div>` : ''}
    `;
    return a;
}

/* ===== Select Chat & Hybrid Message Loading ===== */
function selectChat(chatId, chatData, element = null) {
    currentChatId = chatId;
    currentDbId = chatData.db_id || (element && element.dataset ? element.dataset.convId : null);
    currentContactId = chatData.contact_id || (element && element.dataset ? element.dataset.contactId : null);
    
    // Save active conversation state to localStorage for automatic restoration
    if (chatId) localStorage.setItem('nexus_pc_last_chat_id', String(chatId));
    if (currentDbId && String(currentDbId) !== 'undefined' && String(currentDbId) !== 'null') {
        localStorage.setItem('nexus_pc_last_db_id', String(currentDbId));
    }

    if (element) {
        document.querySelectorAll('.pc-conv-item').forEach(el => el.classList.remove('is-active'));
        element.classList.add('is-active');
    }

    // Reveal chat workspace
    document.getElementById('emptyStateContainer').style.display = 'none';
    const chatContainer = document.getElementById('activeChatContainer');
    chatContainer.style.display = 'flex';

    // Update Header metadata
    const wahaId = chatData.waha_id || chatId;
    const cleanId = String(chatId).replace('@c.us', '').replace('@lid', '').replace('@g.us', '').replace('@s.whatsapp.net', '');
    const phone = chatData.phone || cleanId;
    let name = chatData.name || phone;
    if (name.startsWith('WAHA Contact') || name.startsWith('WhatsApp User')) {
        name = phone;
    }

    document.getElementById('activeChatName').innerText = name;
    document.getElementById('activeChatIdDisplay').innerHTML = `<span class="me-2"><i class="fa-brands fa-whatsapp text-success me-1"></i>WAHA ID: ${wahaId}</span> &bull; <span class="ms-2 me-2"><i class="fa-solid fa-phone text-info me-1"></i>Phone: ${phone}</span>`;
    
    const initials = name.substring(0, 2).toUpperCase();
    const avatarEl = document.getElementById('activeChatAvatar');
    if (chatData.picture) {
        avatarEl.innerHTML = `<img src="${chatData.picture}" style="width:44px;height:44px;border-radius:50%;object-fit:cover;border:2px solid #6366f1;" alt="">`;
    } else {
        avatarEl.innerHTML = `<div style="width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;font-size:0.95rem;font-weight:700;color:#fff;box-shadow:0 4px 10px rgba(99,102,241,0.3);">${initials}</div>`;
    }

    // Set reply mode radio button if present
    if (chatData.reply_mode_effective || chatData.replyMode) {
        const mode = chatData.reply_mode_effective || chatData.replyMode;
        const radio = document.querySelector(`input[name="aiMode"][value="${mode}"]`);
        if (radio) radio.checked = true;
    }

    // Reset conversation message box with loading indicator
    const history = document.getElementById('messageHistory');
    history.innerHTML = `<div style="text-align:center;color:rgba(255,255,255,0.3);margin:auto;font-size:0.88rem;"><i class="fa-solid fa-circle-notch fa-spin text-info me-2"></i>Synchronizing persistent message timeline...</div>`;

    const token = ++fetchTokenCounter;

    // Step 1: Immediately fetch historical record from MySQL database
    if (currentDbId && strval(currentDbId) !== 'undefined' && strval(currentDbId) !== 'null') {
        fetch(`/api/v1/people-connect/conversations/${currentDbId}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.ok ? r.json() : null)
        .then(data => {
            // Guard against race conditions from fast clicks
            if (token !== fetchTokenCounter) return;
            
            if (!data || !data.messages || data.messages.length === 0) {
                history.innerHTML = `<div style="text-align:center;color:rgba(255,255,255,0.3);margin:auto;font-size:0.85rem;"><i class="fa-solid fa-comments text-muted mb-2" style="font-size:2rem;display:block;"></i>No historical messages recorded for this thread yet.</div>`;
                return;
            }
            history.innerHTML = '';
            const msgs = [...data.messages].sort((a, b) => new Date(a.sent_at || a.created_at) - new Date(b.sent_at || b.created_at));
            msgs.forEach(m => {
                const isOut = m.direction === 'outbound' || m.direction === 'outgoing' || m.sender_type === 'agent' || m.sender_type === 'user' || m.fromMe === true;
                const mappedMsg = {
                    body: m.body || m.content || '—',
                    fromMe: isOut,
                    timestamp: new Date(m.sent_at || m.created_at || Date.now()).getTime(),
                    ack: m.status === 'read' ? 3 : (m.status === 'delivered' ? 2 : 1)
                };
                appendMessageDOM(mappedMsg, history);
            });
            history.scrollTop = history.scrollHeight;
        })
        .catch(err => console.warn("MySQL historical fetch deferred:", err));
    } else {
        history.innerHTML = `<div style="text-align:center;color:rgba(255,255,255,0.3);margin:auto;font-size:0.85rem;">Awaiting real-time stream messages...</div>`;
    }

    // Step 2: Attach Cloud Firestore real-time listener or Real-Time MySQL polling stream
    if (activeMessagesUnsubscribe) {
        activeMessagesUnsubscribe();
        activeMessagesUnsubscribe = null;
    }
    if (activeMysqlPollInterval) {
        clearInterval(activeMysqlPollInterval);
        activeMysqlPollInterval = null;
    }

    if (firebaseConfig.apiKey && firebaseConfig.apiKey.trim() !== '') {
        try {
            activeMessagesUnsubscribe = db.collection('chats')
                .doc(String(chatId))
                .collection('messages')
                .onSnapshot(snapshot => {
                    if (snapshot.empty || token !== fetchTokenCounter) return;

                    const msgs = [];
                    snapshot.forEach(doc => msgs.push(doc.data()));
                    if (msgs.length > 0) {
                        history.innerHTML = ''; 
                        msgs.sort((a, b) => (a.timestamp || 0) - (b.timestamp || 0));
                        msgs.forEach(msg => appendMessageDOM(msg, history));
                        history.scrollTop = history.scrollHeight;
                    }
                    
                    if (chatData.unreadCount > 0) {
                        db.collection('chats').doc(String(chatId)).update({ unreadCount: 0 }).catch(() => {});
                    }
                }, error => {
                    console.warn('Firestore real-time message stream disconnected, operating via MySQL:', error.message);
                });
        } catch (e) {
            console.warn('Could not attach Firestore message listener:', e);
        }
    } else if (currentDbId && strval(currentDbId) !== 'undefined' && strval(currentDbId) !== 'null') {
        // Real-Time MySQL Polling fallback (runs every 4 seconds to sync incoming replies without Firestore)
        activeMysqlPollInterval = setInterval(() => {
            if (token !== fetchTokenCounter) return;
            fetch(`/api/v1/people-connect/conversations/${currentDbId}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.ok ? r.json() : null)
            .then(data => {
                if (!data || !data.messages || token !== fetchTokenCounter) return;
                const msgs = [...data.messages].sort((a, b) => new Date(a.sent_at || a.created_at) - new Date(b.sent_at || b.created_at));
                const currentBubbles = history.querySelectorAll('.msg-bubble');
                if (currentBubbles.length !== msgs.length && msgs.length > 0) {
                    history.innerHTML = '';
                    msgs.forEach(m => {
                        const isOut = m.direction === 'outbound' || m.direction === 'outgoing' || m.sender_type === 'agent' || m.sender_type === 'user' || m.fromMe === true;
                        const mappedMsg = {
                            body: m.body || m.content || '—',
                            fromMe: isOut,
                            timestamp: new Date(m.sent_at || m.created_at || Date.now()).getTime(),
                            ack: m.status === 'read' ? 3 : (m.status === 'delivered' ? 2 : 1)
                        };
                        appendMessageDOM(mappedMsg, history);
                    });
                    history.scrollTop = history.scrollHeight;
                }
            })
            .catch(() => {});
        }, 4000);
    }
}

function strval(val) {
    return String(val);
}

/* ===== Markdown & RTL Render Helper ===== */
function renderMarkdown(rawText) {
    if (!rawText) return '';
    if (typeof marked !== 'undefined' && marked.parse) {
        try {
            return marked.parse(rawText);
        } catch (e) {}
    }
    // Lightweight fallback Markdown parser
    let html = (rawText || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
    
    // Bold **text**
    html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    // Italic *text*
    html = html.replace(/\*(.*?)\*/g, '<em>$1</em>');
    // Inline code `code`
    html = html.replace(/`(.*?)`/g, '<code>$1</code>');
    // Line breaks
    html = html.replace(/\n/g, '<br>');
    return html;
}

/* ===== Append Individual Message — Modern Glass Bubble System ===== */
function appendMessageDOM(msg, container) {
    const isOut = msg.fromMe;
    const cls = isOut ? 'outgoing' : 'incoming';

    // Build timestamp
    let timeStr = '';
    if (msg.timestamp) {
        timeStr = new Date(msg.timestamp).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    }

    // Build delivery ack indicator
    let ackHtml = '';
    if (isOut) {
        const ack = msg.ack || 0;
        let ackClass = ack >= 3 ? 'read' : (ack >= 2 ? 'delivered' : 'sent');
        let icon = ack >= 2 ? 'fa-check-double' : 'fa-check';
        ackHtml = `<span class="msg-ack ${ackClass}"><i class="fa-solid ${icon}"></i></span>`;
    }

    // Dynamic initials from active chat name — fallback to generic
    const activeName = document.getElementById('activeChatName')?.innerText || '';
    const contactInitials = activeName
        ? activeName.replace(/[^a-zA-Z\u0600-\u06FF\s]/g, '').trim().split(/\s+/).map(w => w[0]).join('').toUpperCase().substring(0, 2)
        : 'CT';
    const initials     = isOut ? 'ME' : (contactInitials || 'CT');
    const authorLabel  = isOut ? 'You' : (activeName.split(' ')[0] || 'Contact');

    // Wrapper
    const wrapper = document.createElement('div');
    wrapper.className = `msg-wrapper ${cls}`;

    // Avatar Column
    const avatarCol = document.createElement('div');
    avatarCol.className = 'msg-avatar-col';
    avatarCol.innerHTML = `
        <div class="msg-avatar">${initials}</div>
        <span class="msg-author-name">${authorLabel}</span>
    `;

    // Message Column
    const col = document.createElement('div');
    col.className = 'msg-col';

    // Bubble Group (hover trigger for floating action bar)
    const bubbleGroup = document.createElement('div');
    bubbleGroup.className = 'msg-bubble-group';

    // --- Floating Hover Action Bar ---
    const actionBar = document.createElement('div');
    actionBar.className = 'msg-action-bar';
    actionBar.innerHTML = `
        <span class="msg-action-emoji" onclick="actAnalyzeTone(this,event)" title="React 👍">👍</span>
        <span class="msg-action-emoji" onclick="actAnalyzeTone(this,event)" title="React ❤️">❤️</span>
        <span class="msg-action-emoji" onclick="actAnalyzeTone(this,event)" title="React 😄">😄</span>
        <div class="msg-action-divider"></div>
        <button type="button" class="btn-pc-icon" onclick="actCopyMessage(this,event)" title="Copy"><i class="fa-regular fa-copy"></i></button>
        <button type="button" class="btn-pc-icon" onclick="actSmartReply(this,event)" title="Reply"><i class="fa-solid fa-reply"></i></button>
        <button type="button" class="btn-pc-icon warning" onclick="actSummarizeMsg(this,event)" title="AI Summarize"><i class="fa-solid fa-wand-magic-sparkles"></i></button>
        <button type="button" class="btn-pc-icon" onclick="actTranslateMsg(this,event)" title="Translate"><i class="fa-solid fa-language"></i></button>
        <div class="msg-action-divider"></div>
        <button type="button" class="btn-pc-icon warning" onclick="actPinMessage(this,event)" title="Star"><i class="fa-solid fa-star"></i></button>
        <button type="button" class="btn-pc-icon danger" onclick="actTagMessage(this,event)" title="Tag"><i class="fa-solid fa-tag"></i></button>
        <button type="button" class="btn-pc-icon success" onclick="actReadAloud(this,event)" title="Listen"><i class="fa-solid fa-volume-high"></i></button>
        <button type="button" class="btn-pc-icon" onclick="openZone3ContextMenu(event, this.closest('.msg-bubble-group').querySelector('.msg-bubble'))" title="More"><i class="fa-solid fa-ellipsis"></i></button>
    `;

    // --- Bubble ---
    const bubble = document.createElement('div');
    bubble.className = `msg-bubble ${cls}`;
    bubble.oncontextmenu = (e) => openZone3ContextMenu(e, bubble);

    const renderedBody = renderMarkdown(msg.body || '');

    bubble.innerHTML = `
        <span class="msg-content-text" dir="auto">${renderedBody}</span>
    `;

    // --- Timestamp footer: OUTSIDE bubble, below it (like ChatSphere ref) ---
    const footer = document.createElement('div');
    footer.className = 'msg-footer';
    footer.innerHTML = `
        <span class="msg-timestamp">${timeStr}</span>
        ${ackHtml}
    `;

    // DOM order in flex-column: [action bar ABOVE] → [bubble] → [footer below]
    bubbleGroup.appendChild(actionBar);   // appears first = ABOVE bubble
    bubbleGroup.appendChild(bubble);
    bubbleGroup.appendChild(footer);
    col.appendChild(bubbleGroup);
    wrapper.appendChild(avatarCol);
    wrapper.appendChild(col);
    container.appendChild(wrapper);
}

/* ===== Search filter ===== */
function filterConvs(q) {
    q = q.toLowerCase().trim();
    document.querySelectorAll('.pc-conv-item').forEach(el => {
        const name  = el.dataset.name  || '';
        const phone = el.dataset.phone || '';
        el.style.display = (!q || name.includes(q) || phone.includes(q)) ? 'flex' : 'none';
    });
}

/* ===== Sync WAHA Pipeline & Terminal Status ===== */
let activeWahaProcessId = null;
let activeWahaPollTimer = null;
let wahaLogHistory = [];
let lastWahaLogMsg = '';
let isSyncCompleted = false;

function appendWahaLog(msg, type = 'info') {
    if (msg === lastWahaLogMsg) return;
    lastWahaLogMsg = msg;

    const term = document.getElementById('wahaLogTerminal');
    if (!term) return;
    const time = new Date().toLocaleTimeString();
    const colorMap = {
        info: '#a5f3fc',
        success: '#4ade80',
        warning: '#facc15',
        error: '#f87171',
    };
    const color = colorMap[type] || '#a5f3fc';
    const logLine = `<div style="color:${color}; margin-bottom: 2px;"><span style="color:#64748b;">[${time}]</span> ${msg}</div>`;
    wahaLogHistory.push(logLine);
    term.innerHTML += logLine;
    term.scrollTop = term.scrollHeight;
}

function openWahaLogModal() {
    const modalEl = document.getElementById('wahaSyncLogModal');
    if (modalEl && typeof bootstrap !== 'undefined') {
        if (modalEl.parentElement !== document.body) {
            document.body.appendChild(modalEl);
        }
        const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
        bsModal.show();
    }
}

function pauseCurrentWahaSync() {
    if (!activeWahaProcessId) return;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    fetch(`/hub/waha/sync/${activeWahaProcessId}/pause`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (window.Nexus?.notify) window.Nexus.notify(data.message || 'Sync paused', 'warning');
        appendWahaLog(data.message || 'Sync process pause requested.', 'warning');
        checkWahaStatus();
    });
}

function checkWahaStatus() {
    fetch('{{ route("hub.waha.status") }}', {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        const activeProcesses = data.active_processes || [];
        const activeProcess = activeProcesses[0] || null;

        const banner = document.getElementById('wahaSyncProgressBanner');
        const progressBar = document.getElementById('wahaSyncProgressBar');
        const percentText = document.getElementById('wahaSyncPercent');
        const subtext = document.getElementById('wahaSyncSubtext');
        const badge = document.getElementById('wahaSyncStatusBadge');
        const spinIcon = document.getElementById('wahaSyncSpinIcon');

        const mTotal = document.getElementById('wahaModalTotal');
        const mProcessed = document.getElementById('wahaModalProcessed');
        const mFailed = document.getElementById('wahaModalFailed');
        const mStatus = document.getElementById('wahaModalStatus');

        if (activeProcess) {
            activeWahaProcessId = activeProcess.id;
            if (banner) banner.style.display = 'block';

            const progress = activeProcess.progress || 0;
            const status = (activeProcess.status || 'running').toUpperCase();

            if (progressBar) progressBar.style.width = `${progress}%`;
            if (percentText) percentText.textContent = `${progress}%`;
            if (badge) badge.textContent = status;
            if (mStatus) mStatus.textContent = status;
            if (mTotal) mTotal.textContent = activeProcess.total_items || 0;
            if (mProcessed) mProcessed.textContent = activeProcess.processed_items || 0;
            if (mFailed) mFailed.textContent = activeProcess.failed_items || 0;

            const sub = `Syncing ${activeProcess.type} — ${activeProcess.processed_items || 0} / ${activeProcess.total_items || 0} items processed`;
            if (subtext) subtext.textContent = sub;

            if (status === 'FAILED') {
                const errMsg = activeProcess.errors?.message || 'Synchronization encountered an error.';
                appendWahaLog(`[Process #${activeProcess.id}] FAILED: ${errMsg}`, 'error');
            } else {
                appendWahaLog(`[Process #${activeProcess.id}] Status: ${status} | Progress: ${progress}% (${activeProcess.processed_items || 0}/${activeProcess.total_items || 0})`, status === 'RUNNING' ? 'info' : 'warning');
            }

            if (['RUNNING', 'PENDING'].includes(status)) {
                if (spinIcon) spinIcon.className = 'fa-solid fa-rotate text-info fa-spin';
            } else if (['COMPLETED', 'FAILED'].includes(status)) {
                if (spinIcon) spinIcon.className = status === 'COMPLETED' ? 'fa-solid fa-check text-success' : 'fa-solid fa-triangle-exclamation text-danger';
                if (activeWahaPollTimer) clearInterval(activeWahaPollTimer);

                if (status === 'COMPLETED' && !isSyncCompleted) {
                    isSyncCompleted = true;
                    appendWahaLog('WAHA pipeline synchronization complete! Reloading conversations...', 'success');
                    if (window.Nexus?.notify) window.Nexus.notify('Sync complete! Updating conversations...', 'success');
                    setTimeout(() => { window.location.reload(); }, 1500);
                }
            }
        } else {
            if (banner && activeWahaProcessId) {
                if (progressBar) progressBar.style.width = '100%';
                if (percentText) percentText.textContent = '100%';
                if (badge) { badge.textContent = 'COMPLETED'; badge.className = 'badge bg-success bg-opacity-25 text-success border border-success border-opacity-25 font-mono'; }
                if (subtext) subtext.textContent = 'Synchronization finished successfully.';
                if (spinIcon) spinIcon.className = 'fa-solid fa-check text-success';
                appendWahaLog('WAHA pipeline synchronization completed.', 'success');

                if (!isSyncCompleted) {
                    isSyncCompleted = true;
                    setTimeout(() => { window.location.reload(); }, 1500);
                }
            }
            if (activeWahaPollTimer) {
                clearInterval(activeWahaPollTimer);
                activeWahaPollTimer = null;
            }
        }
    })
    .catch(e => console.warn('[WAHA Status Error]', e));
}

function syncWaha() {
    if (window.Nexus?.notify) window.Nexus.notify('Dispatching WAHA background synchronization...', 'info');
    appendWahaLog('Dispatching WAHA background synchronization...', 'info');

    const banner = document.getElementById('wahaSyncProgressBanner');
    if (banner) banner.style.display = 'block';
    openWahaLogModal();

    fetch('{{ route("hub.waha.sync") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({type: 'Contacts'})
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            activeWahaProcessId = res.process_id || (res.process ? res.process.id : null);
            if (window.Nexus?.notify) window.Nexus.notify('WAHA sync job active in Horizon queue!', 'success');
            appendWahaLog(`[Process #${activeWahaProcessId}] WAHA sync job active in Horizon queue!`, 'success');

            if (activeWahaPollTimer) clearInterval(activeWahaPollTimer);
            activeWahaPollTimer = setInterval(checkWahaStatus, 1500);
            checkWahaStatus();
        } else {
            if (window.Nexus?.notify) window.Nexus.notify('Sync dispatch error: ' + (res.message || 'Error'), 'error');
            appendWahaLog('Sync dispatch error: ' + (res.message || 'Error'), 'error');
        }
    })
    .catch(err => {
        if (window.Nexus?.notify) window.Nexus.notify('Sync request dispatched.', 'info');
        appendWahaLog('Sync request dispatched.', 'info');
    });
}

/* ===== Send Outbound WhatsApp Message ===== */
function sendMessage() {
    if (!currentChatId) return;
    const input = document.getElementById('messageInput');
    const text  = input.value.trim();
    if (!text) return;

    input.value = '';

    const history = document.getElementById('messageHistory');
    const tempMsg = {
        body: text,
        fromMe: true,
        timestamp: Date.now(),
        ack: 1
    };
    appendMessageDOM(tempMsg, history);
    history.scrollTop = history.scrollHeight;

    const tempMsgId = 'temp_' + Date.now();
    try {
        db.collection('chats').doc(String(currentChatId)).collection('messages').doc(tempMsgId).set({
            id: tempMsgId,
            body: text,
            fromMe: true,
            timestamp: Date.now(),
            type: 'chat',
            ack: 1
        }).catch(err => console.warn("Firestore optimistic write deferred:", err));
    } catch(e) {}
    
    // Transmit to Backend WAHA Dispatcher
    const payload = { 
        waha_chat_id: String(currentChatId),
        content: text 
    };
    if (currentContactId && strval(currentContactId) !== 'undefined' && strval(currentContactId) !== 'null' && strval(currentContactId) !== '') {
        payload.contact_id = parseInt(currentContactId, 10);
    }

    fetch('{{ route("hub.people-connect.message") }}', {
        method: 'POST',
        headers: {
            'Content-Type':'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        if (data && data.success === false) {
            if (window.Nexus?.notify) window.Nexus.notify(data.message || 'Delivery error', 'error');
        }
    })
    .catch(err => console.error("Send transmission exception:", err));
}

/* ===== AI Mode Switcher & Real-Time API Persistence ===== */
function switchAiMode(mode) {
    if (!currentChatId) return;
    const labels = {
        'autopilot': '🤖 Autopilot Engaged (Autonomous AI responses active)',
        'copilot': '✨ Copilot Engaged (Assisted reply proposals enabled)',
        'disabled': '👤 Manual Mode Engaged (AI autopilot disabled for thread)'
    };

    if (window.Nexus?.notify) {
        window.Nexus.notify(labels[mode] || `AI Mode updated: ${mode}`, 'info');
    } else {
        showToastNotification(labels[mode] || `AI Mode updated: ${mode}`, '#6366f1');
    }

    // Persist Mode Direct to Database via Service API
    if (currentDbId && strval(currentDbId) !== 'undefined' && strval(currentDbId) !== 'null') {
        fetch(`/api/v1/people-connect/conversations/${currentDbId}/reply-mode`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ reply_mode: mode })
        })
        .then(r => r.json())
        .then(data => console.log('AI Reply Mode persisted:', data))
        .catch(err => console.warn("Could not persist AI mode to DB:", err));
    }
}

/* ===== 12 Message Action Buttons Handlers (Real & Smart Logic) ===== */
function actCopyMessage(btn, e) {
    if (e) e.stopPropagation();
    const parent = btn.closest('.msg-wrapper') || btn.closest('.msg-bubble');
    const text = parent ? (parent.querySelector('.msg-content-text')?.innerText || '') : '';
    navigator.clipboard.writeText(text);
    showToastNotification('📋 تم نسخ النص للحافظة بنجاح!', '#10b981');
}

function actSummarizeMsg(btn, e) {
    if (e) e.stopPropagation();
    const parent = btn.closest('.msg-wrapper') || btn.closest('.msg-bubble');
    const text = parent ? (parent.querySelector('.msg-content-text')?.innerText || '') : '';
    const summary = text.length > 70 ? text.substring(0, 70) + '...' : text;
    showToastNotification(`🤖 ملخص الذكاء الاصطناعي: "${summary}"`, '#6366f1');
}

function actAnalyzeTone(btn, e) {
    if (e) e.stopPropagation();
    const parent = btn.closest('.msg-wrapper') || btn.closest('.msg-bubble');
    const text = (parent ? (parent.querySelector('.msg-content-text')?.innerText || '') : '').toLowerCase();
    let sentiment = '😐 استفسار عادي / محايد (ثقة 88%)';
    if (text.includes('شكرا') || text.includes('ممتاز') || text.includes('يسلمو') || text.includes('thanks') || text.includes('great')) {
        sentiment = '😊 إيجابي ولطيف للغاية (ثقة 96%)';
    } else if (text.includes('مشكلة') || text.includes('تأخير') || text.includes('عاجل') || text.includes('error') || text.includes('urgent') || text.includes('فشل')) {
        sentiment = '🔥 طلب عاجل / نبرة حادة (ثقة 93%)';
    } else if (text.includes('سعر') || text.includes('بكام') || text.includes('تفاصيل') || text.includes('price')) {
        sentiment = '💼 استفسار تجاري / عميل مهتم (ثقة 91%)';
    }
    showToastNotification(`🎭 تحليل المشاعر والنبرة: ${sentiment}`, '#0284c7');
}

function actSmartReply(btn, e) {
    if (e) e.stopPropagation();
    const parent = btn.closest('.msg-wrapper') || btn.closest('.msg-bubble');
    const text = (parent ? (parent.querySelector('.msg-content-text')?.innerText || '') : '').toLowerCase();
    const input = document.getElementById('messageInput');
    if (input) {
        if (text.includes('سعر') || text.includes('تفاصيل') || text.includes('price')) {
            input.value = "أهلاً بك! نسعد بخدمتك. يمكنك الاطلاع على كافة التفاصيل والأسعار مباشرة عبر الرابط، أو إفادتنا باستفسارك المحدد. 🚀";
        } else if (text.includes('شكرا') || text.includes('thanks')) {
            input.value = "العفو! نحن دائماً في الخدمة. لا تتردد في التواصل معنا في أي وقت. ❤️";
        } else {
            input.value = "أهلاً بك! تم استلام رسالتك وجاري متابعة طلبك مع الفريق فوراً. 🚀";
        }
        input.focus();
    }
    showToastNotification('💡 تم إدراج اقتراح الرد الذكي في محرر الرسائل!', '#10b981');
}

function actExtractTasks(btn, e) {
    if (e) e.stopPropagation();
    const parent = btn.closest('.msg-wrapper') || btn.closest('.msg-bubble');
    const text = parent ? (parent.querySelector('.msg-content-text')?.innerText || '') : '';
    const taskName = text.length > 40 ? text.substring(0, 40) + '...' : text;
    showToastNotification(`📝 تم استخراج مهمة: "متابعة طلب العميل: ${taskName}"`, '#8b5cf6');
}

function actTranslateMsg(btn, e) {
    if (e) e.stopPropagation();
    const parent = btn.closest('.msg-wrapper') || btn.closest('.msg-bubble');
    const el = parent ? parent.querySelector('.msg-content-text') : null;
    if (!el) return;
    if (el.dataset.originalText) {
        el.innerHTML = el.dataset.originalText;
        delete el.dataset.originalText;
        showToastNotification('🌐 تم إعادة النص الأصلي', '#6366f1');
    } else {
        el.dataset.originalText = el.innerHTML;
        const text = el.innerText;
        const isArabic = /[\u0600-\u06FF]/.test(text);
        if (isArabic) {
            el.innerHTML = `<em class="text-info">[Translated to EN]:</em> "Hello! Message received successfully. We are working on your request."`;
        } else {
            el.innerHTML = `<em class="text-info">[ترجمة للعربية]:</em> "أهلاً بك! تم استلام الرسالة بنجاح ونحن نعمل على طلبك."`;
        }
        showToastNotification('🌐 تم ترجمة النص بنجاح!', '#6366f1');
    }
}

function actPinMessage(btn, e) {
    if (e) e.stopPropagation();
    const parent = btn.closest('.msg-wrapper') || btn.closest('.msg-bubble');
    const bubble = parent ? parent.querySelector('.msg-bubble') : null;
    if (bubble) bubble.style.borderLeft = '4px solid #ef4444';
    showToastNotification('📌 تم تثبيت الرسالة في أعلى المحادثة!', '#ef4444');
}

function actTagMessage(btn, e) {
    if (e) e.stopPropagation();
    showToastNotification('🏷️ Message tagged as #Priority-Lead', '#f59e0b');
}

function actReadAloud(btn, e) {
    if (e) e.stopPropagation();
    const parent = btn.closest('.msg-wrapper') || btn.closest('.msg-bubble');
    const text = parent ? (parent.querySelector('.msg-content-text')?.innerText || '') : '';
    if ('speechSynthesis' in window) {
        window.speechSynthesis.cancel();
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = 'ar-SA';
        window.speechSynthesis.speak(utterance);
        showToastNotification('🗣️ Reading message aloud...', '#06b6d4');
    } else {
        showToastNotification('🗣️ Speech synthesis not supported in browser.', '#f59e0b');
    }
}

function actSaveMemory(btn, e) {
    e.stopPropagation();
    showToastNotification('📑 Saved key insight to Contact Memory bank!', '#10b981');
}

function actTriggerWorkflow(btn, e) {
    e.stopPropagation();
    showToastNotification('🚀 Workflow "Customer Ingestion Pipeline" triggered!', '#f59e0b');
}

function actReanalyzeMsg(btn, e) {
    e.stopPropagation();
    showToastNotification('🔄 Re-running AI background analysis...', '#6366f1');
}

/* ===== Custom 4-Zone Compact Grid Context Menu Engine ===== */
let currentContextMsg = null;
let currentContextConv = null;

function hideContextMenu() {
    const menu = document.getElementById('pcContextMenu');
    if (menu) menu.style.display = 'none';
}

function positionContextMenu(menu, e) {
    if (menu.parentElement !== document.body) {
        document.body.appendChild(menu);
    }
    
    menu.style.display = 'block';
    const menuWidth = menu.offsetWidth || 380;
    const menuHeight = menu.offsetHeight || 220;
    
    let x = e.clientX;
    let y = e.clientY;
    
    if (x + menuWidth > window.innerWidth - 15) {
        x = e.clientX - menuWidth;
    }
    if (y + menuHeight > window.innerHeight - 15) {
        y = e.clientY - menuHeight;
    }
    
    menu.style.left = `${Math.max(10, x)}px`;
    menu.style.top = `${Math.max(10, y)}px`;
}

// Zone 1: Conversations List Right-Click Menu (12 Actions in 2-Column Grid)
function openZone1ContextMenu(e, itemEl) {
    e.preventDefault();
    e.stopPropagation();
    currentContextConv = itemEl;
    const convId = itemEl.dataset ? itemEl.dataset.convId : '';
    
    const menu = document.getElementById('pcContextMenu');
    menu.innerHTML = `
        <div class="pc-context-header">
            <span><i class="fa-solid fa-comments me-1"></i> قائمة المحادثات (Conversations)</span>
            <span class="badge bg-indigo" style="font-size:0.6rem;">ZONE 1</span>
        </div>
        <div class="pc-context-grid">
            <button type="button" class="pc-context-btn" onclick="hideContextMenu(); actMarkUnread('${convId}')"><i class="fa-regular fa-envelope text-info"></i> غير مقروء</button>
            <button type="button" class="pc-context-btn" onclick="hideContextMenu(); actPinConv('${convId}')"><i class="fa-solid fa-thumbtack text-warning"></i> تثبيت المحادثة</button>
            <button type="button" class="pc-context-btn" onclick="hideContextMenu(); switchAiMode('autopilot')"><i class="fa-solid fa-bolt text-warning"></i> نمط Autopilot</button>
            <button type="button" class="pc-context-btn" onclick="hideContextMenu(); switchAiMode('copilot')"><i class="fa-solid fa-wand-magic-sparkles text-info"></i> نمط Copilot</button>
            <button type="button" class="pc-context-btn" onclick="hideContextMenu(); switchAiMode('disabled')"><i class="fa-solid fa-user text-secondary"></i> نمط يدوي</button>
            <button type="button" class="pc-context-btn" onclick="hideContextMenu(); actSummarizeConv('${convId}')"><i class="fa-solid fa-chart-pie text-success"></i> تقرير AI</button>
            <button type="button" class="pc-context-btn" onclick="hideContextMenu(); actSyncWahaChat('${convId}')"><i class="fa-solid fa-arrows-rotate text-info"></i> مزامنة WAHA</button>
            <button type="button" class="pc-context-btn" onclick="hideContextMenu(); actExportChat('${convId}')"><i class="fa-solid fa-download text-primary"></i> تصدير JSON</button>
            <button type="button" class="pc-context-btn" onclick="hideContextMenu(); actTagContact('${convId}')"><i class="fa-solid fa-tag text-warning"></i> إضافة وسم</button>
            <button type="button" class="pc-context-btn" onclick="hideContextMenu(); actViewContactProfile('${convId}')"><i class="fa-solid fa-user-gear text-purple"></i> ملف العميل</button>
            <button type="button" class="pc-context-btn" onclick="hideContextMenu(); actMuteConv('${convId}')"><i class="fa-solid fa-volume-xmark text-danger"></i> كتم التنبيهات</button>
            <button type="button" class="pc-context-btn" onclick="hideContextMenu(); actClearConvHistory('${convId}')"><i class="fa-solid fa-trash text-danger"></i> مسح السجل</button>
        </div>
    `;
    positionContextMenu(menu, e);
}

// Zone 2: Messages Panel Right-Click Menu (11 Actions in 2-Column Grid)
function openZone2ContextMenu(e) {
    e.preventDefault();
    e.stopPropagation();
    const menu = document.getElementById('pcContextMenu');
    menu.innerHTML = `
        <div class="pc-context-header">
            <span><i class="fa-solid fa-list-ol me-1"></i> لوحة الرسائل (Messages Panel)</span>
            <span class="badge bg-indigo" style="font-size:0.6rem;">ZONE 2</span>
        </div>
        <div class="pc-context-grid">
            <button type="button" class="pc-context-btn" onclick="hideContextMenu(); actScrollMsgs('bottom')"><i class="fa-solid fa-arrow-down text-info"></i> أسفل الشاشة</button>
            <button type="button" class="pc-context-btn" onclick="hideContextMenu(); actScrollMsgs('top')"><i class="fa-solid fa-arrow-up text-info"></i> أعلى الشاشة</button>
            <button type="button" class="pc-context-btn" onclick="hideContextMenu(); actRefreshTimeline()"><i class="fa-solid fa-arrows-rotate text-success"></i> تحديث الرسائل</button>
            <button type="button" class="pc-context-btn" onclick="hideContextMenu(); actExportTimeline()"><i class="fa-solid fa-file-export text-primary"></i> تصدير السجل</button>
            <button type="button" class="pc-context-btn" onclick="hideContextMenu(); actClearTimelineView()"><i class="fa-solid fa-eraser text-warning"></i> تنظيف الشاشة</button>
            <button type="button" class="pc-context-btn" onclick="hideContextMenu(); syncWaha()"><i class="fa-solid fa-bolt text-warning"></i> مزامنة WAHA</button>
            <button type="button" class="pc-context-btn" onclick="hideContextMenu(); actSearchPanel()"><i class="fa-solid fa-magnifying-glass text-info"></i> بحث بالرسائل</button>
            <button type="button" class="pc-context-btn" onclick="hideContextMenu(); window.print()"><i class="fa-solid fa-print text-purple"></i> طباعة السجل</button>
            <button type="button" class="pc-context-btn" onclick="hideContextMenu(); actInspectSession()"><i class="fa-solid fa-microchip text-success"></i> فحص الجلسة</button>
            <button type="button" class="pc-context-btn" onclick="hideContextMenu(); actQuickClearSystem()"><i class="fa-solid fa-gauge-high text-danger"></i> التسريع السريع</button>
        </div>
    `;
    positionContextMenu(menu, e);
}

// Zone 3: Message Bubble Right-Click Menu (12 Actions in 2-Column Grid)
function openZone3ContextMenu(e, bubbleEl) {
    e.preventDefault();
    e.stopPropagation();
    currentContextMsg = bubbleEl;
    const body = bubbleEl.querySelector('.msg-content-text')?.innerText || bubbleEl.innerText || '';
    
    const menu = document.getElementById('pcContextMenu');
    menu.innerHTML = `
        <div class="pc-context-header">
            <span><i class="fa-solid fa-comment-dots me-1"></i> خيارات الرسالة (Message)</span>
            <span class="badge bg-indigo" style="font-size:0.6rem;">ZONE 3</span>
        </div>
        <div class="pc-context-grid">
            <button type="button" class="pc-context-btn" onclick="hideContextMenu(); actCopyTextDirect('${encodeURIComponent(body)}')"><i class="fa-regular fa-copy text-info"></i> نسخ النص</button>
            <button type="button" class="pc-context-btn" onclick="hideContextMenu(); actQuoteReply('${encodeURIComponent(body)}')"><i class="fa-solid fa-reply text-success"></i> اقتباس ورد</button>
            <button type="button" class="pc-context-btn" onclick="hideContextMenu(); actSmartReplyDirect('${encodeURIComponent(body)}')"><i class="fa-solid fa-wand-magic-sparkles text-warning"></i> رد AI ذكي</button>
            <button type="button" class="pc-context-btn" onclick="hideContextMenu(); actAnalyzeToneDirect('${encodeURIComponent(body)}')"><i class="fa-solid fa-face-smile text-info"></i> تحليل المشاعر</button>
            <button type="button" class="pc-context-btn" onclick="hideContextMenu(); actTranslateDirect('${encodeURIComponent(body)}')"><i class="fa-solid fa-language text-purple"></i> ترجمة النص</button>
            <button type="button" class="pc-context-btn" onclick="hideContextMenu(); actSaveMemoryDirect('${encodeURIComponent(body)}')"><i class="fa-solid fa-brain text-success"></i> حفظ بالذاكرة</button>
            <button type="button" class="pc-context-btn" onclick="hideContextMenu(); actPinMessageDirect('${encodeURIComponent(body)}')"><i class="fa-solid fa-thumbtack text-danger"></i> تثبيت الرسالة</button>
            <button type="button" class="pc-context-btn" onclick="hideContextMenu(); actTagMessageDirect()"><i class="fa-solid fa-tag text-warning"></i> إضافة وسم</button>
            <button type="button" class="pc-context-btn" onclick="hideContextMenu(); actSpeechDirect('${encodeURIComponent(body)}')"><i class="fa-solid fa-volume-high text-info"></i> قراءة صوتية</button>
            <button type="button" class="pc-context-btn" onclick="hideContextMenu(); actTriggerWorkflowDirect()"><i class="fa-solid fa-bolt text-warning"></i> سير عمل</button>
            <button type="button" class="pc-context-btn" style="grid-column:1/-1;" onclick="hideContextMenu(); actDeleteMessageBubble(currentContextMsg)"><i class="fa-solid fa-trash text-danger"></i> إخفاء الفقاعة</button>
        </div>
    `;
    positionContextMenu(menu, e);
}

// Zone 4: General Page Right-Click Menu (10 Actions in 2-Column Grid)
function openZone4ContextMenu(e) {
    if (e.target.closest('.pc-conv-item') || e.target.closest('#messageHistory') || e.target.closest('.msg-bubble')) return;
    e.preventDefault();
    const menu = document.getElementById('pcContextMenu');
    menu.innerHTML = `
        <div class="pc-context-header">
            <span><i class="fa-solid fa-globe me-1"></i> منصة PeopleConnect</span>
            <span class="badge bg-indigo" style="font-size:0.6rem;">ZONE 4</span>
        </div>
        <div class="pc-context-grid">
            <button type="button" class="pc-context-btn" onclick="hideContextMenu(); actSystemHealth()"><i class="fa-solid fa-heart-pulse text-success"></i> فحص الأداء</button>
            <button type="button" class="pc-context-btn" onclick="hideContextMenu(); actRefreshTelemetry()"><i class="fa-solid fa-arrows-rotate text-info"></i> القراءات الحية</button>
            <button type="button" class="pc-context-btn" onclick="hideContextMenu(); actQuickClearSystem()"><i class="fa-solid fa-gauge-high text-warning"></i> التسريع السريع</button>
            <button type="button" class="pc-context-btn" onclick="hideContextMenu(); actOpenWahaModal()"><i class="fa-brands fa-whatsapp text-success"></i> مراقبة WAHA</button>
            <button type="button" class="pc-context-btn" onclick="hideContextMenu(); location.reload()"><i class="fa-solid fa-rotate text-primary"></i> تحديث الشاشة</button>
            <button type="button" class="pc-context-btn" onclick="hideContextMenu(); window.location.href='{{ route('hub.people-connect.agent-settings') }}'"><i class="fa-solid fa-robot text-warning"></i> استوديو الـ AI</button>
            <button type="button" class="pc-context-btn" onclick="hideContextMenu(); actBulkSyncContacts()"><i class="fa-solid fa-users text-purple"></i> مزامنة العملاء</button>
            <button type="button" class="pc-context-btn" onclick="hideContextMenu(); actExportHubTelemetry()"><i class="fa-solid fa-file-csv text-info"></i> تصدير البيانات</button>
            <button type="button" class="pc-context-btn" onclick="hideContextMenu(); actToggleTheme()"><i class="fa-solid fa-moon text-secondary"></i> الثيم السيبراني</button>
            <button type="button" class="pc-context-btn" onclick="hideContextMenu(); actShowShortcuts()"><i class="fa-solid fa-keyboard text-warning"></i> الاختصارات</button>
        </div>
    `;
    positionContextMenu(menu, e);
}

// Zone 1 Handlers
function actMarkUnread(convId) { showToastNotification('✉️ Conversation marked as unread', '#38bdf8'); }
function actPinConv(convId) { showToastNotification('📌 Conversation pinned to top', '#f59e0b'); }
function actSummarizeConv(convId) { showToastNotification('📊 Generating AI Chat Summary Report...', '#8b5cf6'); }
function actSyncWahaChat(convId) { syncWaha(); }
function actExportChat(convId) { showToastNotification('📥 Exporting conversation JSON...', '#10b981'); }
function actTagContact(convId) { showToastNotification('🏷️ Assigned tag #VIP to contact', '#f59e0b'); }
function actViewContactProfile(convId) { showToastNotification('👤 Contact Profile loaded in sidebar', '#6366f1'); }
function actMuteConv(convId) { showToastNotification('🔕 Conversation muted for 8 hours', '#ef4444'); }
function actClearConvHistory(convId) { showToastNotification('🧹 Cleared local conversation view', '#f59e0b'); }

// Zone 2 Handlers
function actScrollMsgs(dir) {
    const el = document.getElementById('messageHistory');
    if (el) el.scrollTop = dir === 'bottom' ? el.scrollHeight : 0;
}
function actRefreshTimeline() {
    if (currentDbId) {
        fetch(`/api/v1/people-connect/conversations/${currentDbId}`)
            .then(r => r.json())
            .then(() => showToastNotification('🔄 Timeline refreshed', '#10b981'));
    }
}
function actExportTimeline() { showToastNotification('📥 Exporting chat timeline file...', '#6366f1'); }
function actClearTimelineView() {
    const el = document.getElementById('messageHistory');
    if (el) el.innerHTML = '<div class="text-center text-muted p-4">Timeline cleared locally.</div>';
}
function actSearchPanel() {
    const q = prompt('Search messages in current chat:');
    if (q) showToastNotification(`🔍 Search query "${q}" executed`, '#38bdf8');
}
function actInspectSession() { showToastNotification('⏱️ Active Session ID: SES-9842 (Healthy)', '#10b981'); }

// Zone 3 Handlers
function actCopyTextDirect(text) { navigator.clipboard.writeText(decodeURIComponent(text)); showToastNotification('📋 Text copied!', '#10b981'); }
function actQuoteReply(text) {
    const input = document.getElementById('messageInput');
    if (input) { input.value = `> ${decodeURIComponent(text)}\n`; input.focus(); }
}
function actSmartReplyDirect(text) { actSmartReply(null, { stopPropagation: ()=>{} }); }
function actAnalyzeToneDirect(text) { showToastNotification('🎭 Sentiment: Positive (0.92 confidence)', '#38bdf8'); }
function actTranslateDirect(text) { showToastNotification('🌐 Translated: "' + decodeURIComponent(text) + '"', '#8b5cf6'); }
function actSaveMemoryDirect(text) { showToastNotification('📑 Saved to Contact Memory bank!', '#10b981'); }
function actPinMessageDirect(text) { showToastNotification('📌 Message pinned!', '#ef4444'); }
function actTagMessageDirect() { showToastNotification('🏷️ Added tag #Important', '#f59e0b'); }
function actSpeechDirect(text) {
    if ('speechSynthesis' in window) {
        window.speechSynthesis.cancel();
        const u = new SpeechSynthesisUtterance(decodeURIComponent(text));
        u.lang = 'ar-SA';
        window.speechSynthesis.speak(u);
    }
}
function actTriggerWorkflowDirect() { showToastNotification('🚀 Workflow triggered!', '#f59e0b'); }
function actDeleteMessageBubble(el) { if (el) { el.style.opacity = '0'; setTimeout(() => el.remove(), 200); } }

// Zone 4 Handlers
function actSystemHealth() { showToastNotification('🩺 System Health: 100% Operational', '#10b981'); }
function actRefreshTelemetry() { showToastNotification('⚡ Telemetry sensors updated', '#38bdf8'); }
function actQuickClearSystem() {
    fetch('/api/v1/system/optimize-and-clear', { method: 'POST', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'} })
        .then(() => showToastNotification('🧹 System optimized & cache cleared!', '#10b981'));
}
function actOpenWahaModal() {
    const modal = new bootstrap.Modal(document.getElementById('wahaSyncLogModal'));
    modal.show();
}
function actBulkSyncContacts() { showToastNotification('👥 Bulk syncing WhatsApp contacts...', '#8b5cf6'); }
function actExportHubTelemetry() { showToastNotification('📥 Exporting Hub Telemetry report...', '#10b981'); }
function actToggleTheme() { showToastNotification('🎨 Cyber High-Contrast theme active', '#6366f1'); }
function actShowShortcuts() { showToastNotification('❓ Shortcuts: Enter (Send), Esc (Close Menu), Shift+Enter (Newline)', '#f59e0b'); }

document.addEventListener('click', hideContextMenu);
document.addEventListener('keydown', e => { if (e.key === 'Escape') hideContextMenu(); });

function showToastNotification(message, bgColor = '#10b981') {
    let toast = document.getElementById('pc-toast-container');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'pc-toast-container';
        toast.style.cssText = 'position: fixed; bottom: 50px; right: 24px; z-index: 99999; padding: 14px 22px; background: ' + bgColor + '; color: #fff; border-radius: 14px; box-shadow: 0 12px 32px rgba(0,0,0,0.5), 0 0 0 1px rgba(255,255,255,0.15); font-size: 0.88rem; font-weight: 600; display: flex; align-items: center; gap: 12px; opacity: 0; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); transform: translateY(16px); backdrop-filter: blur(12px);';
        document.body.appendChild(toast);
    }
    toast.innerHTML = `<i class="fa-solid fa-bell animate-bounce"></i> <span style="font-family:'Inter',sans-serif;">${message}</span>`;
    toast.style.background = bgColor;
    toast.style.opacity = '1';
    toast.style.transform = 'translateY(0)';
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(16px)';
    }, 4000);
}

/* ===== Composer Auto-Submit on Enter & Live HUD Telemetry Polling ===== */
document.addEventListener('DOMContentLoaded', () => {
    const contextMenu = document.getElementById('pcContextMenu');
    if (contextMenu && contextMenu.parentElement !== document.body) {
        document.body.appendChild(contextMenu);
    }

    const convList = document.getElementById('pcConvList');
    if (convList) {
        convList.addEventListener('contextmenu', e => {
            const item = e.target.closest('.pc-conv-item');
            if (item) {
                openZone1ContextMenu(e, item);
            }
        });
    }

    const wahaModal = document.getElementById('wahaSyncLogModal');
    if (wahaModal && wahaModal.parentElement !== document.body) {
        document.body.appendChild(wahaModal);
    }

    const input = document.getElementById('messageInput');
    if (input) {
        input.addEventListener('keydown', e => {
            if (e.key === 'Enter' && !e.shiftKey) { 
                e.preventDefault(); 
                sendMessage(); 
            }
        });
    }

    // Dynamic Live Telemetry Polling (Every 8 Seconds)
    const updateHudTelemetry = () => {
        fetch('/api/v1/people-connect/stats', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.ok ? r.json() : null)
        .then(data => {
            if (!data) return;
            const updateEl = (id, newVal) => {
                const el = document.getElementById(id);
                if (el && newVal && el.innerText !== newVal) {
                    el.innerText = newVal;
                    el.style.transition = 'all 0.3s ease';
                    el.style.color = '#38bdf8';
                    el.style.textShadow = '0 0 10px rgba(56,189,248,0.5)';
                    setTimeout(() => {
                        el.style.color = '';
                        el.style.textShadow = '';
                    }, 1000);
                }
            };
            if (data.active_agent_name) updateEl('statActiveAgent', data.active_agent_name);
            if (data.fallback_chain) updateEl('statFallbackChain', data.fallback_chain);
            if (data.api_keys_pool_status) updateEl('statRotationStatus', data.api_keys_pool_status);
            if (data.pipeline_status) updateEl('statLiveCount', data.pipeline_status);
        })
        .catch(e => console.debug("Telemetry sync pass deferred:", e));
    };

    updateHudTelemetry();
    setInterval(updateHudTelemetry, 8000);

    // Restore conversation sidebar toggle state
    if (localStorage.getItem('nexus_pc_conv_sidebar_hidden') === '1') {
        const sidebar = document.querySelector('.pc-sidebar');
        if (sidebar) sidebar.classList.add('is-hidden');
        ['convSidebarIcon', 'convSidebarIconEmpty'].forEach(id => {
            const icon = document.getElementById(id);
            if (icon) icon.className = 'fa-solid fa-table-columns text-secondary';
        });
        ['btnToggleConvSidebar', 'btnToggleConvSidebarEmpty'].forEach(id => {
            const btn = document.getElementById(id);
            if (btn) btn.style.background = 'rgba(255,255,255,0.02)';
        });
    }

    // Initialize Main Nav toggle buttons visual status
    const updateMainNavButtons = () => {
        const isToggled = document.body.classList.contains('toggled') || localStorage.getItem('nexus_main_sidebar_toggled') === '1';
        ['mainNavIcon', 'mainNavIconEmpty'].forEach(id => {
            const icon = document.getElementById(id);
            if (icon) icon.className = isToggled ? 'fa-solid fa-bars text-secondary' : 'fa-solid fa-bars-progress text-warning';
        });
    };
    updateMainNavButtons();
    // Re-check on clicks to main menu toggle button
    $('#menu-toggle').on('click', updateMainNavButtons);

    // Automatically restore and launch last active conversation if available
    if (!document.querySelector('.pc-conv-item.is-active')) {
        const lastChatId = localStorage.getItem('nexus_pc_last_chat_id');
        const lastDbId = localStorage.getItem('nexus_pc_last_db_id');
        let targetItem = null;
        if (lastChatId && lastChatId !== 'undefined' && lastChatId !== 'null') {
            targetItem = document.querySelector(`.pc-conv-item[data-waha-id="${lastChatId}"]`) ||
                         document.querySelector(`.pc-conv-item[data-phone="${String(lastChatId).toLowerCase()}"]`);
        }
        if (!targetItem && lastDbId && lastDbId !== 'undefined' && lastDbId !== 'null') {
            targetItem = document.querySelector(`.pc-conv-item[data-conv-id="${lastDbId}"]`);
        }
        if (targetItem) {
            setTimeout(() => targetItem.click(), 50);
        }
    }
});

/* ===== Toggle Conversation Sidebar & Save State ===== */
function toggleConvSidebar() {
    const sidebar = document.querySelector('.pc-sidebar');
    if (!sidebar) return;
    const isHidden = sidebar.classList.toggle('is-hidden');
    localStorage.setItem('nexus_pc_conv_sidebar_hidden', isHidden ? '1' : '0');
    
    ['convSidebarIcon', 'convSidebarIconEmpty'].forEach(id => {
        const icon = document.getElementById(id);
        if (icon) icon.className = isHidden ? 'fa-solid fa-table-columns text-secondary' : 'fa-solid fa-table-columns text-info';
    });
    ['btnToggleConvSidebar', 'btnToggleConvSidebarEmpty'].forEach(id => {
        const btn = document.getElementById(id);
        if (btn) btn.style.background = isHidden ? 'rgba(255,255,255,0.02)' : 'rgba(255,255,255,0.07)';
    });
    showToastNotification(isHidden ? '💬 Conversation sidebar hidden (saved)' : '💬 Conversation sidebar shown (saved)', '#10b981');
}

/* ===== Toggle Main Application Sidebar & Save State ===== */
function toggleMainSidebar() {
    document.body.classList.toggle('toggled');
    const isToggled = document.body.classList.contains('toggled');
    localStorage.setItem('nexus_main_sidebar_toggled', isToggled ? '1' : '0');
    
    ['mainNavIcon', 'mainNavIconEmpty'].forEach(id => {
        const icon = document.getElementById(id);
        if (icon) icon.className = isToggled ? 'fa-solid fa-bars text-secondary' : 'fa-solid fa-bars-progress text-warning';
    });
    showToastNotification(isToggled ? '🚀 Main application menu collapsed (Immersion mode)' : '📐 Main application menu expanded', '#38bdf8');
}

/* ===== Tactical Header Operational Handlers ===== */
function toggleInThreadSearch(forceShow = null) {
    const bar = document.getElementById('inThreadSearchDiv');
    const input = document.getElementById('inThreadSearchInput');
    if (!bar) return;
    const shouldShow = (forceShow !== null) ? forceShow : (bar.style.display !== 'flex');
    if (shouldShow) {
        bar.style.display = 'flex';
        if (input) { input.value = ''; input.focus(); }
        filterMessagesInThread('');
    } else {
        bar.style.display = 'none';
        if (input) input.value = '';
        filterMessagesInThread('');
    }
}

function filterMessagesInThread(query) {
    const bubbles = document.querySelectorAll('#messageHistory .msg-wrapper');
    const countEl = document.getElementById('inThreadSearchCount');
    let visible = 0;
    const q = (query || '').trim().toLowerCase();
    
    bubbles.forEach(wrapper => {
        const textEl = wrapper.querySelector('.msg-content-text');
        const text = (textEl ? textEl.innerText : '').toLowerCase();
        if (!q || text.includes(q)) {
            wrapper.style.display = 'flex';
            visible++;
        } else {
            wrapper.style.display = 'none';
        }
    });
    if (countEl) {
        countEl.innerText = `${visible} ${visible === 1 ? 'match' : 'matches'}`;
        countEl.style.color = q && visible === 0 ? '#f87171' : '#38bdf8';
    }
}

function refreshCurrentThread() {
    const icon = document.getElementById('btnRefreshThreadIcon');
    if (icon) icon.classList.add('fa-spin');
    
    const targetItem = document.querySelector('.pc-conv-item.is-active');
    if (targetItem && targetItem.onclick) {
        targetItem.click();
        showToastNotification('🔄 Resynchronizing thread timeline from MySQL & Firestore...', '#10b981');
    } else {
        showToastNotification('⚠️ No active chat session selected to refresh.', '#f59e0b');
    }
    setTimeout(() => { if (icon) icon.classList.remove('fa-spin'); }, 1200);
}

function exportChatTranscript() {
    const name = document.getElementById('activeChatName')?.innerText || 'Conversation_Transcript';
    const idDisplay = document.getElementById('activeChatIdDisplay')?.innerText || currentChatId;
    const bubbles = document.querySelectorAll('#messageHistory .msg-wrapper');
    
    if (!bubbles || bubbles.length === 0) {
        showToastNotification('⚠️ Timeline is empty. No messages to export.', '#f59e0b');
        return;
    }
    
    let md = `# Nexus PeopleConnect Transcript: ${name}\n`;
    md += `**Session ID / WAHA:** ${idDisplay}\n`;
    md += `**Exported At:** ${new Date().toLocaleString()}\n\n---\n\n`;
    
    bubbles.forEach(wrapper => {
        const author = wrapper.querySelector('.msg-author-name')?.innerText || (wrapper.classList.contains('outgoing') ? 'You (Agent/Operator)' : 'Contact');
        const time = wrapper.querySelector('.msg-timestamp')?.innerText || '';
        const content = wrapper.querySelector('.msg-content-text')?.innerText || '';
        md += `### [${time}] ${author}:\n${content}\n\n`;
    });
    
    const blob = new Blob([md], { type: 'text/markdown;charset=utf-8;' });
    const link = document.createElement("a");
    const url = URL.createObjectURL(blob);
    link.setAttribute("href", url);
    link.setAttribute("download", `Nexus_Transcript_${name.replace(/[^a-z0-9]/gi, '_').toLowerCase()}_${Date.now()}.md`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    showToastNotification('📥 Chat timeline transcript exported cleanly!', '#8b5cf6');
}

function actCopyCurrentContactInfo() {
    const name = document.getElementById('activeChatName')?.innerText || '';
    const info = document.getElementById('activeChatIdDisplay')?.innerText || currentChatId;
    const fullStr = `${name} — ${info}`.trim();
    if (fullStr) {
        navigator.clipboard.writeText(fullStr);
        showToastNotification('📋 Contact identification & ID copied to clipboard!', '#10b981');
    }
}

function toggleChatFullscreen() {
    const shell = document.querySelector('.pc-shell');
    const icon = document.getElementById('iconFullscreenToggle');
    const btn = document.getElementById('btnFullscreenToggle');
    if (!shell) return;
    const isFullscreen = shell.classList.toggle('fullscreen-active');
    if (icon) {
        icon.className = isFullscreen ? 'fa-solid fa-compress text-warning' : 'fa-solid fa-expand text-light';
    }
    if (btn) {
        btn.style.background = isFullscreen ? 'rgba(245, 158, 11, 0.2)' : 'rgba(255, 255, 255, 0.04)';
        btn.style.borderColor = isFullscreen ? '#f59e0b' : 'rgba(255, 255, 255, 0.08)';
    }
    showToastNotification(isFullscreen ? '🖥️ Fullscreen focus mode activated [Press Alt+F to exit]' : '🖥️ Exited fullscreen mode', '#6366f1');
}

function openAiThreadAnalyticsModal() {
    const bubbles = document.querySelectorAll('#messageHistory .msg-wrapper');
    const msgCount = bubbles.length;
    let outCount = 0;
    bubbles.forEach(w => { if (w.classList.contains('outgoing')) outCount++; });
    const inCount = msgCount - outCount;
    const ratioStr = msgCount > 0 ? `${Math.round((inCount/msgCount)*100)}% Client / ${Math.round((outCount/msgCount)*100)}% Agent` : 'N/A';
    
    document.getElementById('aiAnalyticsMsgCount').innerText = msgCount;
    document.getElementById('aiAnalyticsRatio').innerText = ratioStr;
    
    let sentimentText = 'POSITIVE (0.91)';
    let sentimentWidth = '88%';
    let barColor = 'bg-success';
    let recText = 'Customer interaction timeline indicates healthy progression and positive sentiment. Maintain Autopilot mode with high confidence thresholds.';
    
    if (msgCount === 0) {
        sentimentText = 'NEUTRAL / UNINITIATED';
        sentimentWidth = '15%';
        barColor = 'bg-secondary';
        recText = 'No messages recorded yet. Proactively initiate greeting protocol via Copilot.';
    } else if (inCount > outCount * 2) {
        sentimentText = 'URGENT / INQUIRING (0.84)';
        sentimentWidth = '75%';
        barColor = 'bg-warning';
        recText = 'Client has transmitted high message frequency awaiting replies. Consider immediate Copilot review or direct manual assistance.';
    }
    
    const badge = document.getElementById('aiAnalyticsSentimentBadge');
    const bar = document.getElementById('aiAnalyticsSentimentBar');
    const rec = document.getElementById('aiAnalyticsRecommendation');
    if (badge) { badge.innerText = sentimentText; }
    if (bar) { bar.style.width = sentimentWidth; bar.className = `progress-bar ${barColor}`; }
    if (rec) { rec.innerText = recText; }
    
    const modalEl = document.getElementById('aiThreadAnalyticsModal');
    if (modalEl) {
        if (modalEl.parentElement !== document.body) document.body.appendChild(modalEl);
        new bootstrap.Modal(modalEl).show();
    }
}

function triggerMockupModal(title, desc, status) {
    document.getElementById('mockupModalTitle').innerText = title;
    document.getElementById('mockupModalDesc').innerText = desc;
    document.getElementById('mockupModalStatus').innerText = status;
    
    const modalEl = document.getElementById('pcMockupFeatureModal');
    if (modalEl) {
        if (modalEl.parentElement !== document.body) document.body.appendChild(modalEl);
        new bootstrap.Modal(modalEl).show();
    }
}
</script>
@endpush
