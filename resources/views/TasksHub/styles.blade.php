<style>
/* Tasks Hub Base Variables & Design Tokens */
:root {
    --tasks-primary: #6366F1;
    --tasks-success: #10B981;
    --tasks-warning: #F59E0B;
    --tasks-danger: #EF4444;
    --tasks-info: #3B82F6;
    --tasks-purple: #8B5CF6;
    --tasks-cyan: #06B6D4;
    
    --tasks-bg-dark: #0A0E1A;
    --tasks-surface: #111827;
    --tasks-glass-bg: rgba(255, 255, 255, 0.03);
    --tasks-glass-border: rgba(255, 255, 255, 0.08);
    
    --tasks-topnav-height: 55px;
    --tasks-bottombar-height: 55px;
}

/* Glassmorphism Classes */
.tasks-glass-card {
    background: var(--tasks-glass-bg);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid var(--tasks-glass-border);
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
}

/* Glassmorphic Command Bar 2.0 (2-Tier Architecture) */
.command-bar-glass {
    background: linear-gradient(135deg, rgba(15, 23, 42, 0.9) 0%, rgba(10, 15, 30, 0.95) 100%);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-top: 1px solid rgba(99, 102, 241, 0.3);
    border-bottom: 1px solid rgba(99, 102, 241, 0.15);
    border-radius: 16px;
    padding: 16px 20px !important;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.05);
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
}

/* 2-Tier Rows */
.command-bar-row-primary {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding-bottom: 12px;
    margin-bottom: 10px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.command-bar-row-secondary {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding-top: 4px;
    width: 100%;
}

.cmd-footer-panel {
    flex: 1;
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 6px;
    align-items: center;
}

.cmd-footer-panel .filter-chip,
.cmd-footer-panel .status-metric-pill {
    width: 100% !important;
    height: 28px !important;
    padding: 0 6px !important;
    box-sizing: border-box !important;
    justify-content: center !important;
    text-align: center;
    white-space: nowrap;
}

/* Command Bar Buttons */
.cmd-bar-btn {
    height: 32px !important;
    border-radius: 20px !important;
    padding: 0 14px !important;
    font-size: 0.73rem !important;
    font-weight: 600 !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 6px !important;
    line-height: 1 !important;
}

.cmd-bar-icon-btn {
    width: 32px !important;
    height: 32px !important;
    padding: 0 !important;
    border-radius: 50% !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 0.75rem !important;
}

/* Omnibox Pill Search Input */
.tasks-search-pill-container {
    position: relative;
    display: flex;
    align-items: center;
    height: 32px;
}
.tasks-search-pill {
    background: rgba(15, 23, 42, 0.75) !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 20px !important;
    padding-left: 32px !important;
    padding-right: 55px !important;
    color: #F8FAFC !important;
    font-size: 0.73rem !important;
    height: 32px !important;
    line-height: 32px !important;
    transition: all 0.25s ease;
}
.tasks-search-pill:focus {
    border-color: rgba(99, 102, 241, 0.8) !important;
    box-shadow: 0 0 12px rgba(99, 102, 241, 0.35) !important;
    background: rgba(15, 23, 42, 0.95) !important;
}
.tasks-search-icon {
    position: absolute;
    left: 12px;
    color: #64748B;
    font-size: 0.75rem;
    pointer-events: none;
}
.tasks-search-clear {
    position: absolute;
    right: 35px;
    color: #94A3B8;
    cursor: pointer;
    font-size: 0.75rem;
    display: none;
    transition: color 0.15s ease;
}
.tasks-search-clear:hover {
    color: #EF4444;
}
.tasks-search-kbd {
    position: absolute;
    right: 10px;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 4px;
    padding: 1px 5px;
    font-size: 0.6rem;
    color: #94A3B8;
    font-family: 'JetBrains Mono', monospace;
    pointer-events: none;
}

/* Quick Filter Chips */
.filter-chip {
    height: 28px !important;
    padding: 0 10px !important;
    border-radius: 16px !important;
    font-size: 0.7rem !important;
    font-weight: 500;
    cursor: pointer;
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.07);
    color: #94A3B8;
    transition: all 0.2s ease;
    user-select: none;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 5px;
    line-height: 1 !important;
}
.filter-chip:hover {
    background: rgba(99, 102, 241, 0.15);
    color: #E2E8F0;
    border-color: rgba(99, 102, 241, 0.3);
}
.filter-chip.active {
    background: rgba(99, 102, 241, 0.25);
    color: #818CF8;
    border-color: rgba(99, 102, 241, 0.5);
    font-weight: 600;
    box-shadow: 0 0 8px rgba(99, 102, 241, 0.2);
}

/* Interactive Quiet Status Metric Pills */
.status-metric-pill {
    height: 28px !important;
    padding: 0 10px !important;
    border-radius: 16px !important;
    font-size: 0.7rem !important;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 5px;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.06);
    color: #94A3B8;
    user-select: none;
    line-height: 1 !important;
}
.status-metric-pill:hover {
    background: rgba(255, 255, 255, 0.08);
    color: #F1F5F9;
    border-color: rgba(255, 255, 255, 0.12);
}
.status-metric-pill.active-filter {
    background: rgba(99, 102, 241, 0.2) !important;
    border-color: rgba(99, 102, 241, 0.5) !important;
    color: #A5B4FC !important;
    font-weight: 600;
}
.status-pill-total .pill-num { color: #CBD5E1; }
.status-pill-running .pill-num { color: #818CF8; }
.status-pill-failed .pill-num { color: #FCA5A5; }
.status-pill-blocked .pill-num { color: #FCD34D; }
.status-pill-completed .pill-num { color: #6EE7B7; }

/* Interactive Live Sync Button */
.live-sync-btn {
    height: 32px !important;
    border-radius: 20px !important;
    padding: 0 14px !important;
    font-size: 0.73rem !important;
    font-weight: 600 !important;
    background: rgba(15, 23, 42, 0.7) !important;
    border: 1px solid rgba(16, 185, 129, 0.35) !important;
    color: #F8FAFC !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 7px !important;
    line-height: 1 !important;
    transition: all 0.25s ease !important;
    cursor: pointer;
    user-select: none;
}
.live-sync-btn:hover {
    background: rgba(16, 185, 129, 0.15) !important;
    border-color: rgba(16, 185, 129, 0.7) !important;
    box-shadow: 0 0 12px rgba(16, 185, 129, 0.3) !important;
    transform: translateY(-1px);
}
.live-sync-btn.paused {
    border-color: rgba(245, 158, 11, 0.4) !important;
    color: #FCD34D !important;
}
.live-sync-btn.paused:hover {
    background: rgba(245, 158, 11, 0.15) !important;
    border-color: rgba(245, 158, 11, 0.7) !important;
    box-shadow: 0 0 12px rgba(245, 158, 11, 0.3) !important;
}

/* Button Micro-Animations */
.btn-new-task-animated i {
    transition: transform 0.3s ease;
}
.btn-new-task-animated:hover i {
    transform: rotate(90deg) scale(1.15);
}

.live-pulse-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #10B981;
    display: inline-block;
    box-shadow: 0 0 8px #10B981;
    animation: live-pulse-ripple 1.8s infinite;
}
.live-pulse-dot.paused {
    background: #F59E0B;
    box-shadow: 0 0 8px #F59E0B;
    animation: live-pulse-amber-ripple 1.8s infinite;
}

@keyframes live-pulse-ripple {
    0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { box-shadow: 0 0 0 7px rgba(16, 185, 129, 0); }
    100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}

@keyframes live-pulse-amber-ripple {
    0% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.7); }
    70% { box-shadow: 0 0 0 7px rgba(245, 158, 11, 0); }
    100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); }
}
@keyframes live-pulse {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}

/* Dynamic Bulk Overlay Bar */
.command-bulk-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(30, 27, 75, 0.96) 0%, rgba(15, 23, 42, 0.98) 100%);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    z-index: 10;
    display: none;
    align-items: center;
    justify-content: space-between;
    padding: 0 16px;
    border-radius: 12px;
    animation: fadeInDown 0.25s ease-out forwards;
}
@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-8px); }
    to { opacity: 1; transform: translateY(0); }
}

.tasks-glass-panel {
    background: rgba(17, 24, 39, 0.85);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border-bottom: 1px solid var(--tasks-glass-border);
    border-top: 1px solid var(--tasks-glass-border);
}

/* Typography */
.font-inter { font-family: 'Inter', sans-serif; }
.font-mono { font-family: 'JetBrains Mono', monospace; }

/* Status Badges */
.task-status-badge {
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.task-status-todo { background: rgba(255,255,255,0.1); color: #FFF; }
.task-status-running { background: rgba(99, 102, 241, 0.15); color: var(--tasks-primary); border: 1px solid rgba(99, 102, 241, 0.3); }
.task-status-running i { animation: pulse-opacity 1.5s infinite; }
.task-status-completed { background: rgba(16, 185, 129, 0.15); color: var(--tasks-success); border: 1px solid rgba(16, 185, 129, 0.3); }
.task-status-failed { background: rgba(239, 68, 68, 0.15); color: var(--tasks-danger); border: 1px solid rgba(239, 68, 68, 0.3); }
.task-status-blocked { background: rgba(245, 158, 11, 0.15); color: var(--tasks-warning); border: 1px solid rgba(245, 158, 11, 0.3); }
.task-status-cancelled { background: rgba(156, 163, 175, 0.15); color: #9CA3AF; border: 1px solid rgba(156, 163, 175, 0.3); }

/* Animations */
@keyframes pulse-opacity {
    0% { opacity: 1; }
    50% { opacity: 0.4; }
    100% { opacity: 1; }
}
@keyframes slide-in-right {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

/* Layout Overrides */
.main-content {
    display: flex !important;
    flex-direction: column !important;
    flex: 1 !important;
}
.tasks-hub-container {
    display: flex;
    flex-direction: column;
    flex: 1;
    min-height: 100%;
    height: 100%;
    overflow: hidden;
    background-color: var(--tasks-bg-dark);
    position: relative;
    border-radius: 12px;
    border: 1px solid var(--tasks-glass-border);
}
.tasks-main-content {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
    padding-bottom: calc(var(--tasks-bottombar-height) + 16px);
}
/* ---------------------------------------------------
    RESPONSIVE DESIGN (F25)
----------------------------------------------------- */
@media (max-width: 991.98px) {
    /* Collapse Sidebar */
    .tasks-sidebar {
        width: 60px !important;
        position: absolute;
        z-index: 1050;
        left: -60px; /* Hidden by default on mobile, triggered by menu button if implemented */
        transition: left 0.3s ease;
    }
    .tasks-sidebar.show { left: 0; }
    
    .tasks-main-content {
        margin-left: 0 !important;
    }

    /* Top Nav tweaks */
    .tasks-topnav {
        flex-wrap: nowrap;
        overflow-x: auto;
        padding-bottom: 5px;
    }
    .tasks-topnav::-webkit-scrollbar { display: none; }
    
    /* Kanban Board columns */
    .kanban-column {
        width: 280px !important;
    }

    /* Modals */
    .modal-dialog {
        margin: 0.5rem;
    }
    
    /* Stat Cards Grid */
    #stat-cards-container .col {
        flex: 0 0 50%;
        max-width: 50%;
    }
}

@media (max-width: 575.98px) {
    /* Bottom Bar Adjustments for Small Screens */
    .tasks-bottombar {
        font-size: 0.65rem;
        flex-wrap: wrap;
        height: auto;
        padding: 8px !important;
    }
    
    /* DataTables Responsiveness */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        text-align: left !important;
        float: none !important;
        margin-bottom: 8px;
    }

    /* Stat Cards Grid */
    #stat-cards-container .col {
        flex: 0 0 100%;
        max-width: 100%;
    }
    
    /* Quick View Sidebar width */
    #quick-view-panel {
        width: 100% !important;
        right: -100%;
    }
/* True Fullscreen Mode - Hides App Sidebar, Topbar & Statusbar */
body.taskshub-fullscreen-active #sidebar-wrapper,
body.taskshub-fullscreen-active #main-topbar,
body.taskshub-fullscreen-active #nexus-statusbar {
    display: none !important;
}
body.taskshub-fullscreen-active #page-content-wrapper {
    padding: 0 !important;
    margin: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    max-width: 100vw !important;
}
body.taskshub-fullscreen-active .tasks-hub-container {
    padding: 12px 16px !important;
    height: 100vh !important;
    overflow-y: auto;
}
/* Dynamic Width Redistribution for Visible Kanban Columns */
#kanban-columns-container {
    display: flex !important;
    width: 100% !important;
    min-width: 100% !important;
}

.kanban-column:not(.d-none) {
    display: flex !important;
    flex: 1 1 0px !important;
    min-width: 0 !important;
    width: auto !important;
    transition: flex 0.3s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.25s ease, transform 0.25s ease !important;
}

.kanban-column.d-none {
    display: none !important;
    flex: 0 0 0 !important;
    width: 0 !important;
    min-width: 0 !important;
    max-width: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
    border: none !important;
    opacity: 0 !important;
    pointer-events: none !important;
}

.btn-hide-column {
    opacity: 0.35;
    transition: all 0.2s ease;
    padding: 2px 6px;
    border-radius: 4px;
    color: #94A3B8;
    background: transparent;
    border: none;
}
.kanban-column:hover .btn-hide-column {
    opacity: 0.85;
}
.btn-hide-column:hover {
    opacity: 1 !important;
    color: #EF4444 !important;
    background: rgba(239, 68, 68, 0.15) !important;
}

.btn-maximize-column {
    opacity: 0.35;
    transition: all 0.2s ease;
    padding: 2px 6px;
    border-radius: 4px;
    color: #94A3B8;
    background: transparent;
    border: none;
}
.kanban-column:hover .btn-maximize-column {
    opacity: 0.85;
}
.btn-maximize-column:hover {
    opacity: 1 !important;
    color: #38BDF8 !important;
    background: rgba(56, 189, 248, 0.15) !important;
}
.btn-maximize-column.active {
    opacity: 1 !important;
    color: #38BDF8 !important;
    background: rgba(56, 189, 248, 0.2) !important;
}

/* Maximized Column 100% Width Studio Grid Layout */
.kanban-column.column-maximized:not(.d-none) {
    flex: 1 1 100% !important;
    max-width: 100% !important;
    width: 100% !important;
    min-width: 100% !important;
    margin: 0 !important;
}

.kanban-column.column-maximized .kanban-cards-container {
    white-space: normal !important;
    display: grid !important;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)) !important;
    gap: 1rem !important;
    align-content: start !important;
    padding: 0.5rem !important;
}

/* Enhanced Rich Card Styling */
.kanban-card {
    background: rgba(15, 23, 42, 0.75) !important;
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-left: 4px solid #38BDF8 !important;
    border-radius: 10px !important;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
    position: relative;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2) !important;
}

.kanban-card:hover {
    transform: translateY(-2px);
    border-color: rgba(56, 189, 248, 0.4) !important;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4), 0 0 15px rgba(56, 189, 248, 0.15) !important;
}

.kanban-card[data-status="todo"] { border-left-color: #38BDF8 !important; }
.kanban-card[data-status="in-progress"] { border-left-color: #F59E0B !important; }
.kanban-card[data-status="blocked"] { border-left-color: #EC4899 !important; }
.kanban-card[data-status="completed"] { border-left-color: #10B981 !important; }
.kanban-card[data-status="failed"] { border-left-color: #EF4444 !important; }

.subtask-item {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.06);
    border-radius: 6px;
    padding: 6px 10px;
    margin-bottom: 4px;
    transition: all 0.2s ease;
}
.subtask-item:hover {
    background: rgba(255, 255, 255, 0.06);
}
.subtask-item.completed span {
    text-decoration: line-through;
    opacity: 0.5;
}
</style>
