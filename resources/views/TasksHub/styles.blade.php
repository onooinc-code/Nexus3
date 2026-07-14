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
.tasks-hub-container {
    display: flex;
    flex-direction: column;
    height: calc(100vh - var(--navbar-height, 60px));
    overflow: hidden;
    background-color: var(--tasks-bg-dark);
    position: relative;
}
.tasks-main-content {
    flex: 1;
    overflow-y: auto;
    padding-bottom: calc(var(--tasks-bottombar-height) + 20px);
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
    .dataTables_wrapper .dataTables_filter, 
    .dataTables_wrapper .dataTables_length {
        text-align: left !important;
        margin-bottom: 10px;
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
}
</style>
