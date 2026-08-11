<style>
/* ---------------------------------------------------
    1. CSS VARIABLES (Glassmorphism & Branding)
----------------------------------------------------- */
:root {
    --wf-primary: #3b82f6;      /* Blue */
    --wf-primary-hover: #2563eb;
    --wf-accent: #10b981;       /* Emerald */
    --wf-bg-dark: #0f111a;
    --wf-panel-bg: rgba(22, 27, 34, 0.65);
    --wf-panel-border: rgba(255, 255, 255, 0.1);
    --wf-text-main: #e2e8f0;
    --wf-text-muted: #94a3b8;
    --wf-bottombar-height: 40px;
    --wf-glass-blur: blur(12px);
    
    /* Status Colors */
    --wf-status-success: #10b981;
    --wf-status-danger: #ef4444;
    --wf-status-warning: #f59e0b;
    --wf-status-info: #3b82f6;
}

/* ---------------------------------------------------
    2. GLOBAL LAYOUT
----------------------------------------------------- */
.workflows-hub-wrapper {
    position: relative;
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    background-color: var(--wf-bg-dark);
    color: var(--wf-text-main);
    overflow: hidden;
}

.workflows-main-content {
    flex: 1;
    overflow-y: auto;
    padding-bottom: calc(var(--wf-bottombar-height) + 20px);
    scrollbar-width: thin;
    scrollbar-color: var(--wf-panel-border) transparent;
}
.workflows-main-content::-webkit-scrollbar { width: 6px; }
.workflows-main-content::-webkit-scrollbar-thumb { background-color: var(--wf-panel-border); border-radius: 4px; }

/* ---------------------------------------------------
    3. GLASSMORPHISM PANELS
----------------------------------------------------- */
.wf-glass-panel {
    background: var(--wf-panel-bg);
    backdrop-filter: var(--wf-glass-blur);
    -webkit-backdrop-filter: var(--wf-glass-blur);
    border: 1px solid var(--wf-panel-border);
    border-radius: 12px;
    box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
}

.wf-glass-header {
    border-bottom: 1px solid var(--wf-panel-border);
    background: rgba(255,255,255,0.02);
    padding: 12px 16px;
    font-weight: 600;
}

/* ---------------------------------------------------
    4. TOP NAVIGATION
----------------------------------------------------- */
.workflows-topnav {
    background: var(--wf-panel-bg);
    backdrop-filter: var(--wf-glass-blur);
    border-bottom: 1px solid var(--wf-panel-border);
    padding: 10px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    z-index: 1000;
}

.wf-nav-tabs {
    display: flex;
    gap: 15px;
}

.wf-nav-btn {
    background: transparent;
    border: none;
    color: var(--wf-text-muted);
    font-size: 0.95rem;
    font-weight: 500;
    padding: 8px 12px;
    border-radius: 6px;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}
.wf-nav-btn:hover {
    color: var(--wf-text-main);
    background: rgba(255,255,255,0.05);
}
.wf-nav-btn.active {
    color: var(--wf-primary);
    background: rgba(59, 130, 246, 0.1);
    box-shadow: inset 0 0 0 1px rgba(59, 130, 246, 0.2);
}

/* ---------------------------------------------------
    5. ANIMATIONS
----------------------------------------------------- */
@keyframes fadeInSlideUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.wf-animate-in {
    animation: fadeInSlideUp 0.3s ease-out forwards;
}

/* ---------------------------------------------------
    6. MOBILE RESPONSIVENESS
----------------------------------------------------- */
@media (max-width: 991.98px) {
    .workflows-topnav {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
        padding-bottom: 5px;
    }
    .wf-nav-tabs {
        width: 100%;
        overflow-x: auto;
        flex-wrap: nowrap;
        padding-bottom: 8px;
    }
    .wf-nav-tabs::-webkit-scrollbar { display: none; }
}
</style>
