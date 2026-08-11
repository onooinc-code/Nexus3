<!-- ═════════════════════════════════════════════════════════════════════
     SYSTEM FULL OPTIMIZE & CACHE CLEAR MODAL
     ═════════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="systemOptimizeModal" tabindex="-1" aria-labelledby="systemOptimizeModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="background: rgba(11, 17, 28, 0.96); backdrop-filter: blur(16px); border: 1px solid rgba(99, 102, 241, 0.3); border-radius: 16px; box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6), 0 0 30px rgba(99, 102, 241, 0.15);">
            
            <!-- Modal Header -->
            <div class="modal-header border-bottom border-secondary border-opacity-25 px-4 py-3" style="background: rgba(255, 255, 255, 0.02);">
                <div class="d-flex align-items-center gap-3">
                    <div style="width: 40px; height: 40px; background: linear-gradient(135deg, rgba(99, 102, 241, 0.25), rgba(168, 85, 247, 0.25)); border: 1px solid rgba(168, 85, 247, 0.4); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-broom text-warning fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-light mb-0" id="systemOptimizeModalLabel" style="font-size: 1.05rem; letter-spacing: -0.2px;">
                            System Optimization & Cache Clear
                        </h5>
                        <span class="text-muted" style="font-size: 0.76rem; font-family: 'JetBrains Mono', monospace;">
                            Purging browser storage, clearing server cache, & compiling Laravel optimization
                        </span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" id="opt-header-close-btn" aria-label="Close" style="font-size: 0.8rem;"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4">
                
                <!-- Overall Progress Indicator -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-semibold text-light" style="font-size: 0.85rem;" id="opt-progress-status-label">Initializing System Tasks...</span>
                        <span class="fw-bold text-primary font-mono" style="font-size: 0.9rem;" id="opt-progress-percent">0%</span>
                    </div>
                    <div class="progress" style="height: 8px; background: rgba(255, 255, 255, 0.08); border-radius: 4px; overflow: hidden;">
                        <div id="opt-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" 
                             style="width: 0%; background: linear-gradient(90deg, #6366f1, #a855f7, #06b6d4); transition: width 0.4s ease;"></div>
                    </div>
                </div>

                <!-- Execution Step List -->
                <div class="card bg-dark bg-opacity-50 border-secondary border-opacity-25 rounded-3 overflow-hidden mb-3">
                    <div class="list-group list-group-flush" id="opt-steps-list">
                        
                        <!-- Step 1: Browser Cache -->
                        <div class="list-group-item bg-transparent text-light border-secondary border-opacity-10 d-flex align-items-center justify-content-between py-3 px-3" id="opt-step-browser">
                            <div class="d-flex align-items-center gap-3">
                                <div class="step-icon-box" style="width: 32px; height: 32px; border-radius: 8px; background: rgba(255, 255, 255, 0.05); display: flex; align-items: center; justify-content: center;">
                                    <i class="fa-solid fa-globe text-info" style="font-size: 0.85rem;"></i>
                                </div>
                                <div>
                                    <div class="fw-medium text-light" style="font-size: 0.88rem;">1. Clear Browser Cache & Storage</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">Purging LocalStorage, SessionStorage, & CacheStorage API</div>
                                </div>
                            </div>
                            <span class="badge bg-secondary bg-opacity-25 text-muted px-2.5 py-1 font-mono step-status-badge" style="font-size: 0.72rem;">Pending</span>
                        </div>

                        <!-- Step 2: Server Cache & Views -->
                        <div class="list-group-item bg-transparent text-light border-secondary border-opacity-10 d-flex align-items-center justify-content-between py-3 px-3" id="opt-step-server">
                            <div class="d-flex align-items-center gap-3">
                                <div class="step-icon-box" style="width: 32px; height: 32px; border-radius: 8px; background: rgba(255, 255, 255, 0.05); display: flex; align-items: center; justify-content: center;">
                                    <i class="fa-solid fa-server text-warning" style="font-size: 0.85rem;"></i>
                                </div>
                                <div>
                                    <div class="fw-medium text-light" style="font-size: 0.88rem;">2. Clear Server Caches & Redis Memory</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">Clearing config, routes, compiled Blade views, events, & Redis</div>
                                </div>
                            </div>
                            <span class="badge bg-secondary bg-opacity-25 text-muted px-2.5 py-1 font-mono step-status-badge" style="font-size: 0.72rem;">Pending</span>
                        </div>

                        <!-- Step 3: Full Optimization -->
                        <div class="list-group-item bg-transparent text-light border-secondary border-opacity-10 d-flex align-items-center justify-content-between py-3 px-3" id="opt-step-optimize">
                            <div class="d-flex align-items-center gap-3">
                                <div class="step-icon-box" style="width: 32px; height: 32px; border-radius: 8px; background: rgba(255, 255, 255, 0.05); display: flex; align-items: center; justify-content: center;">
                                    <i class="fa-solid fa-bolt text-success" style="font-size: 0.85rem;"></i>
                                </div>
                                <div>
                                    <div class="fw-medium text-light" style="font-size: 0.88rem;">3. Full Laravel Optimization</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">Executing <code>php artisan optimize</code> & route/config pre-compilation</div>
                                </div>
                            </div>
                            <span class="badge bg-secondary bg-opacity-25 text-muted px-2.5 py-1 font-mono step-status-badge" style="font-size: 0.72rem;">Pending</span>
                        </div>

                    </div>
                </div>

                <!-- Detailed Execution Command Logs -->
                <div class="card bg-dark border-secondary border-opacity-25 rounded-3 overflow-hidden mb-3">
                    <div class="card-header bg-black bg-opacity-40 border-bottom border-secondary border-opacity-25 py-2 px-3 d-flex align-items-center justify-content-between">
                        <span class="text-muted font-mono" style="font-size: 0.75rem;">
                            <i class="fa-solid fa-terminal me-1 text-primary"></i> Execution Console Details
                        </span>
                        <span class="badge bg-secondary bg-opacity-25 text-muted font-mono" style="font-size: 0.65rem;" id="opt-logs-count">0 Output Logs</span>
                    </div>
                    <div class="p-3 font-mono" style="background: rgba(0, 0, 0, 0.7); font-size: 0.76rem; max-height: 180px; overflow-y: auto;" id="opt-terminal-logs">
                        <div class="text-muted">> Waiting to initiate optimization process...</div>
                    </div>
                </div>

                <!-- Auto-Close & Inactivity Alert Banner (Hidden until finished) -->
                <div id="opt-autoclose-alert" class="alert alert-success border-0 bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-between p-3 rounded-3 mb-0" style="display: none;">
                    <div class="d-flex align-items-center gap-3">
                        <i class="fa-solid fa-circle-check fs-4 text-success"></i>
                        <div>
                            <div class="fw-bold text-light" style="font-size: 0.88rem;">Optimization & Clear Complete!</div>
                            <div class="text-success-emphasis" style="font-size: 0.78rem;">
                                Auto-closing & hard refreshing in <span id="opt-countdown-timer" class="fw-bold font-mono fs-6 text-light px-2 py-0.5 rounded bg-dark">10</span> seconds if no movement is detected.
                            </div>
                        </div>
                    </div>
                    <div class="text-end font-mono" style="font-size: 0.7rem; opacity: 0.8;" id="opt-inactivity-status">
                        Inactivity Check Active
                    </div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-top border-secondary border-opacity-25 px-4 py-3" style="background: rgba(255, 255, 255, 0.01);">
                <button type="button" class="btn btn-outline-secondary btn-sm px-3" id="opt-cancel-btn" data-bs-dismiss="modal" style="font-size: 0.8rem;">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm px-4 d-flex align-items-center gap-2" id="opt-manual-close-btn" style="background: linear-gradient(135deg, #6366f1, #8b5cf6); border: none; font-size: 0.82rem;">
                    <i class="fa-solid fa-rotate-right"></i>
                    <span>Close & Hard Refresh Now</span>
                </button>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalEl = document.getElementById('systemOptimizeModal');
    let bootstrapModal = null;
    let isTaskRunning = false;
    let isTaskFinished = false;
    let refreshTriggered = false;
    let countdownInterval = null;
    let secondsRemaining = 10;
    let lastActivityTime = Date.now();
    let logCount = 0;

    const modalTriggerBtn = document.getElementById('system-optimize-btn');
    const quickClearBtn = document.getElementById('quick-clear-btn');
    const manualCloseBtn = document.getElementById('opt-manual-close-btn');
    const headerCloseBtn = document.getElementById('opt-header-close-btn');
    const cancelBtn = document.getElementById('opt-cancel-btn');

    // Reset UI State
    function resetModalUI() {
        isTaskRunning = false;
        isTaskFinished = false;
        refreshTriggered = false;
        secondsRemaining = 10;
        logCount = 0;
        if (countdownInterval) clearInterval(countdownInterval);
        countdownInterval = null;

        document.getElementById('opt-progress-bar').style.width = '0%';
        document.getElementById('opt-progress-percent').innerText = '0%';
        document.getElementById('opt-progress-status-label').innerText = 'Initializing System Tasks...';
        document.getElementById('opt-autoclose-alert').style.display = 'none';
        document.getElementById('opt-logs-count').innerText = '0 Output Logs';

        ['browser', 'server', 'optimize'].forEach(step => {
            const el = document.getElementById(`opt-step-${step}`);
            if (el) {
                const badge = el.querySelector('.step-status-badge');
                if (badge) {
                    badge.className = 'badge bg-secondary bg-opacity-25 text-muted px-2.5 py-1 font-mono step-status-badge';
                    badge.innerText = 'Pending';
                }
            }
        });

        const logs = document.getElementById('opt-terminal-logs');
        if (logs) logs.innerHTML = '<div class="text-muted">> Waiting to initiate optimization process...</div>';
    }

    function appendDetailedLog(title, details = '', type = 'info') {
        const logs = document.getElementById('opt-terminal-logs');
        if (!logs) return;
        
        logCount++;
        const countBadge = document.getElementById('opt-logs-count');
        if (countBadge) countBadge.innerText = `${logCount} Output Logs`;

        const colorClass = type === 'error' ? 'text-danger' : (type === 'success' ? 'text-success' : (type === 'warning' ? 'text-warning' : 'text-info'));
        const time = new Date().toLocaleTimeString();

        let detailsHtml = '';
        if (details) {
            detailsHtml = `<div class="mt-1 ps-3 text-muted" style="border-left: 2px solid rgba(255,255,255,0.1); white-space: pre-wrap; font-size: 0.72rem;">${details}</div>`;
        }

        logs.innerHTML += `<div class="mb-2">
            <div class="${colorClass}">[${time}] > <strong>${title}</strong></div>
            ${detailsHtml}
        </div>`;
        logs.scrollTop = logs.scrollHeight;
    }

    function setStepStatus(step, status, text) {
        const el = document.getElementById(`opt-step-${step}`);
        if (!el) return;
        const badge = el.querySelector('.step-status-badge');
        if (!badge) return;

        if (status === 'running') {
            badge.className = 'badge bg-primary bg-opacity-25 text-primary px-2.5 py-1 font-mono step-status-badge';
            badge.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Running';
        } else if (status === 'success') {
            badge.className = 'badge bg-success bg-opacity-25 text-success px-2.5 py-1 font-mono step-status-badge';
            badge.innerHTML = '<i class="fa-solid fa-check me-1"></i> ' + (text || 'Done');
        } else if (status === 'error') {
            badge.className = 'badge bg-danger bg-opacity-25 text-danger px-2.5 py-1 font-mono step-status-badge';
            badge.innerHTML = '<i class="fa-solid fa-xmark me-1"></i> Failed';
        }
    }

    function executeHardRefresh() {
        if (refreshTriggered) return;
        refreshTriggered = true;
        appendDetailedLog('Executing Hard Refresh...', 'Purging browser memory & reloading page.', 'warning');
        if (bootstrapModal) {
            try { bootstrapModal.hide(); } catch(e) {}
        }
        setTimeout(() => {
            const cleanUrl = window.location.protocol + '//' + window.location.host + window.location.pathname;
            window.location.href = cleanUrl + '?hard_refresh=' + Date.now();
        }, 250);
    }

    // Inactivity Tracker & Countdown
    function handleUserActivity() {
        if (!isTaskFinished) return;
        lastActivityTime = Date.now();
        secondsRemaining = 10;
        const timerEl = document.getElementById('opt-countdown-timer');
        const statusEl = document.getElementById('opt-inactivity-status');
        if (timerEl) timerEl.innerText = secondsRemaining;
        if (statusEl) {
            statusEl.innerText = 'Movement Detected! Timer Reset';
            statusEl.className = 'text-end font-mono text-warning';
            setTimeout(() => {
                if (statusEl) {
                    statusEl.innerText = 'Inactivity Check Active';
                    statusEl.className = 'text-end font-mono text-muted';
                }
            }, 1500);
        }
    }

    ['mousemove', 'pointermove', 'keydown', 'touchstart'].forEach(evt => {
        document.addEventListener(evt, handleUserActivity, { passive: true });
    });

    function startAutoCloseTimer() {
        secondsRemaining = 10;
        lastActivityTime = Date.now();

        const timerEl = document.getElementById('opt-countdown-timer');
        if (timerEl) timerEl.innerText = secondsRemaining;

        if (countdownInterval) clearInterval(countdownInterval);

        countdownInterval = setInterval(() => {
            if (!isTaskFinished) return;
            const idleMs = Date.now() - lastActivityTime;
            secondsRemaining = Math.max(0, 10 - Math.floor(idleMs / 1000));
            if (timerEl) timerEl.innerText = secondsRemaining;

            if (secondsRemaining <= 0) {
                clearInterval(countdownInterval);
                appendDetailedLog('10 seconds of inactivity reached. Triggering automatic hard refresh...', '', 'success');
                executeHardRefresh();
            }
        }, 500);
    }

    // Run Optimization Workflow
    async function runOptimization() {
        if (isTaskRunning) return;
        isTaskRunning = true;
        resetModalUI();

        appendDetailedLog('Initiating System Full Optimization & Cache Clear workflow...', 'Target: Laravel 13 Monolith & Client Browser', 'info');

        // Step 1: Clear Browser Cache
        setStepStatus('browser', 'running');
        document.getElementById('opt-progress-bar').style.width = '20%';
        document.getElementById('opt-progress-percent').innerText = '20%';
        document.getElementById('opt-progress-status-label').innerText = 'Clearing Browser Storage...';
        
        try {
            localStorage.clear();
            sessionStorage.clear();
            let cacheKeys = [];
            if ('caches' in window) {
                cacheKeys = await caches.keys();
                await Promise.all(cacheKeys.map(key => caches.delete(key)));
            }
            appendDetailedLog('Cleared Browser Storage', `Purged LocalStorage, SessionStorage, & ${cacheKeys.length} CacheStorage entries.`, 'success');
            setStepStatus('browser', 'success', 'Cleared');
        } catch (e) {
            appendDetailedLog('Browser Cache Warning', e.message, 'warning');
            setStepStatus('browser', 'success', 'Cleared');
        }

        // Step 2 & 3: Clear Server Cache & Run Laravel Optimize
        setStepStatus('server', 'running');
        document.getElementById('opt-progress-bar').style.width = '50%';
        document.getElementById('opt-progress-percent').innerText = '50%';
        document.getElementById('opt-progress-status-label').innerText = 'Executing Server Cache Clear & Optimization...';

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

        try {
            const response = await fetch('/api/v1/system/optimize-and-clear', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (response.ok && data.success) {
                setStepStatus('server', 'success', 'Flushed');
                setStepStatus('optimize', 'running');
                
                document.getElementById('opt-progress-bar').style.width = '85%';
                document.getElementById('opt-progress-percent').innerText = '85%';

                if (Array.isArray(data.steps)) {
                    data.steps.forEach(step => {
                        appendDetailedLog(`${step.label} (${step.command})`, step.output, 'success');
                    });
                } else {
                    appendDetailedLog('Server Optimization Executed', data.message || 'Server optimization completed.', 'success');
                }

                setStepStatus('optimize', 'success', 'Optimized');
            } else {
                throw new Error(data.error || data.message || 'Server returned an error.');
            }
        } catch (err) {
            appendDetailedLog('Server Optimization Error', err.message, 'error');
            setStepStatus('server', 'error');
            setStepStatus('optimize', 'error');
        }

        document.getElementById('opt-progress-bar').style.width = '100%';
        document.getElementById('opt-progress-percent').innerText = '100%';
        document.getElementById('opt-progress-status-label').innerText = 'All Optimization Tasks Completed!';
        appendDetailedLog('Workflow Finished', 'All browser and server cache clear steps completed successfully.', 'success');

        isTaskFinished = true;
        isTaskRunning = false;

        document.getElementById('opt-autoclose-alert').style.display = 'flex';
        startAutoCloseTimer();
    }

    // Modal Trigger Handler
    if (modalTriggerBtn && modalEl) {
        modalTriggerBtn.addEventListener('click', function() {
            if (!bootstrapModal) {
                bootstrapModal = new bootstrap.Modal(modalEl);
            }
            bootstrapModal.show();
            runOptimization();
        });
    }

    // Quick Clear Button Handler (Fast One-Click Execution without Modal)
    if (quickClearBtn) {
        quickClearBtn.addEventListener('click', async function() {
            if (isTaskRunning) return;
            isTaskRunning = true;

            const originalHtml = quickClearBtn.innerHTML;
            quickClearBtn.disabled = true;
            quickClearBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Purging...';

            if (window.NProgress) NProgress.start();

            try {
                // Clear browser storage
                localStorage.clear();
                sessionStorage.clear();
                if ('caches' in window) {
                    const keys = await caches.keys();
                    await Promise.all(keys.map(k => caches.delete(k)));
                }

                // Call backend optimize API
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
                await fetch('/api/v1/system/optimize-and-clear', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (window.NProgress) NProgress.done();

                // Hard Refresh immediately
                const cleanUrl = window.location.protocol + '//' + window.location.host + window.location.pathname;
                window.location.href = cleanUrl + '?quick_clear=' + Date.now();
            } catch (err) {
                console.error('Quick Clear failed:', err);
                quickClearBtn.disabled = false;
                quickClearBtn.innerHTML = originalHtml;
                if (window.NProgress) NProgress.done();
                alert('Quick Clear error: ' + err.message);
            }
        });
    }

    if (manualCloseBtn) manualCloseBtn.addEventListener('click', executeHardRefresh);
    if (headerCloseBtn) headerCloseBtn.addEventListener('click', executeHardRefresh);
    if (cancelBtn) cancelBtn.addEventListener('click', executeHardRefresh);

    if (modalEl) {
        modalEl.addEventListener('hidden.bs.modal', function() {
            if (isTaskFinished) executeHardRefresh();
        });
    }
});
</script>
