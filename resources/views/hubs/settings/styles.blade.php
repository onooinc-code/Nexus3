@push('styles')
<style>
.nav-pills .nav-link.settings-sidebar-btn,
.nav-pills .settings-sidebar-btn,
.settings-sidebar-btn {
    display: flex !important;
    flex-direction: row !important;
    direction: ltr !important;
    align-items: center !important;
    justify-content: flex-start !important;
    flex-wrap: nowrap !important;
    white-space: nowrap !important;
    gap: 8px !important;
    width: 100% !important;
    padding: 10px 14px;
    border: 1px solid rgba(255,255,255,0.03);
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.02); /* Glass layer */
    backdrop-filter: blur(8px);
    color: var(--text-secondary, #8b949e);
    font-size: 0.95rem;
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    text-align: left;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    margin-bottom: 8px;
    line-height: 1;
}
.settings-sidebar-btn:hover {
    background: rgba(255,255,255,0.06);
    color: #e6edf3;
    border-color: rgba(255,255,255,0.1);
    transform: translateX(4px);
}
.settings-sidebar-btn.active {
    background: rgba(59,130,246,0.15) !important;
    color: #ffffff !important;
    border-color: rgba(59,130,246,0.3) !important;
    box-shadow: inset 4px 0 0 #58a6ff, 0 4px 12px rgba(0,0,0,0.1);
}
.settings-sidebar-btn .btn-icon {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    flex-shrink: 0;
    background: rgba(255,255,255,0.05);
    transition: all 0.2s;
}
.settings-sidebar-btn.active .btn-icon {
    background: rgba(59,130,246,0.25);
    color: #58a6ff;
}
.setting-card {
    background: rgba(13,17,23,0.5);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 14px;
    padding: 22px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
}
.setting-card:hover {
    border-color: rgba(59,130,246,0.4);
    background: rgba(13,17,23,0.75);
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.2);
}
.setting-key-label {
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.7rem;
    color: #8b949e;
    background: rgba(255,255,255,0.05);
    padding: 3px 8px;
    border-radius: 6px;
    border: 1px solid rgba(255,255,255,0.05);
}
.setting-desc-label {
    font-size: 0.9rem;
    font-weight: 600;
    color: #e6edf3;
    line-height: 1.4;
}
.nx-input {
    background: rgba(9,15,25,0.8) !important;
    border: 1px solid rgba(255,255,255,0.1) !important;
    color: #e6edf3 !important;
    border-radius: 8px !important;
    font-size: 0.85rem !important;
    padding: 8px 14px !important;
    width: 100%;
    height: 38px;
    transition: border-color 0.2s, box-shadow 0.2s !important;
}
.nx-input:focus {
    border-color: rgba(59,130,246,0.5) !important;
    box-shadow: 0 0 0 3px rgba(59,130,246,0.1) !important;
    outline: none !important;
}
.nx-switch .form-check-input {
    width: 2.2em;
    height: 1.1em;
    background-color: rgba(255,255,255,0.1);
    border-color: rgba(255,255,255,0.15);
    cursor: pointer;
}
.nx-switch .form-check-input:checked {
    background-color: #3b82f6;
    border-color: #3b82f6;
}
.btn-save-nx {
    background: rgba(255, 255, 255, 0.02);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255,255,255,0.03);
    color: #8b949e;
    font-weight: 600;
    font-size: 0.83rem;
    padding: 9px 22px;
    border-radius: 9px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.btn-save-nx:hover { background: rgba(59,130,246,0.15); border-color: rgba(59,130,246,0.3); color: #ffffff; box-shadow: 0 4px 12px rgba(0,0,0,0.1); transform: translateY(-1px); }
.btn-save-nx:disabled { opacity: 0.5; transform: none; }
.drag-handle { cursor: grab; color: #4b5563; font-size: 0.7rem; }
.drag-handle:hover { color: #58a6ff; }
.ui-sortable-helper {
    background: rgba(30, 41, 59, 0.95) !important;
    border: 1px solid rgba(59,130,246,0.5) !important;
    box-shadow: 0 15px 35px rgba(0,0,0,0.5) !important;
    border-radius: 12px;
    transform: scale(1.02) rotate(1deg);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    backdrop-filter: blur(10px);
    z-index: 9999 !important;
}
.ui-sortable-placeholder {
    background: rgba(255,255,255,0.02) !important;
    border: 2px dashed rgba(59,130,246,0.3) !important;
    visibility: visible !important;
    border-radius: 12px !important;
    height: 60px !important;
}
.custom-dialog {
    background: rgba(22, 27, 34, 0.95) !important;
    backdrop-filter: blur(16px);
    border: 1px solid rgba(59, 130, 246, 0.3) !important;
    color: white !important;
    border-radius: 15px !important;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6) !important;
}
.custom-dialog .ui-dialog-titlebar {
    background: transparent !important;
    border: none !important;
    border-bottom: 1px solid rgba(255,255,255,0.06) !important;
    padding: 15px 20px !important;
}
.custom-dialog .ui-dialog-title {
    color: #58a6ff !important;
    font-weight: 600 !important;
    font-family: 'Outfit', sans-serif !important;
}
.custom-dialog .ui-dialog-buttonpane {
    background: transparent !important;
    border-top: 1px solid rgba(255,255,255,0.06) !important;
    padding: 10px 20px !important;
}
.custom-dialog .ui-dialog-buttonset button {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    color: #e6edf3;
    border-radius: 8px;
    padding: 6px 16px;
    font-family: 'Inter', sans-serif;
    transition: all 0.2s;
    cursor: pointer;
}
.custom-dialog .ui-dialog-buttonset button:hover {
    background: rgba(255,255,255,0.1);
}
.custom-dialog .ui-dialog-buttonset button.btn-primary-action {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    border-color: #3b82f6;
    font-weight: 600;
}
.custom-dialog .ui-dialog-buttonset button.btn-danger-action {
    background: linear-gradient(135deg, #ef4444, #b91c1c);
    border-color: #ef4444;
    font-weight: 600;
}
.custom-dialog .ui-widget-content {
    background: transparent !important;
    padding: 20px !important;
    color: #8b949e !important;
}

/* Enhanced Animated Badge */
@keyframes cyberPulse {
    0% {
        box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.6);
        transform: scale(1);
    }
    50% {
        box-shadow: 0 0 12px 4px rgba(147, 51, 234, 0.3);
        transform: scale(1.08);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.1);
        transform: scale(1);
    }
}

@keyframes borderGlow {
    0% { border-color: rgba(59, 130, 246, 0.5); }
    50% { border-color: rgba(147, 51, 234, 0.8); }
    100% { border-color: rgba(59, 130, 246, 0.5); }
}

@keyframes shimmer {
    0% { left: -100%; }
    100% { left: 200%; }
}

.animated-badge {
    background: linear-gradient(135deg, rgba(59,130,246,0.25), rgba(147,51,234,0.25));
    backdrop-filter: blur(4px);
    color: #ffffff;
    border: 1px solid rgba(59,130,246,0.5);
    font-size: 0.75rem;
    border-radius: 20px;
    padding: 4px 10px;
    font-weight: 700;
    text-shadow: 0 0 5px rgba(255,255,255,0.4);
    animation: cyberPulse 2s ease-in-out infinite, borderGlow 3s linear infinite;
    position: relative;
    overflow: hidden;
    z-index: 1;
}

.animated-badge::before {
    content: '';
    position: absolute;
    top: 0; left: -100%;
    width: 50%; height: 100%;
    background: linear-gradient(to right, transparent, rgba(255,255,255,0.4), transparent);
    transform: skewX(-25deg);
    animation: shimmer 3s infinite;
    z-index: -1;
}
</style>
@endpush
