<!-- ═════════════════════════════════════════════════════════════════════
     NOTIFICATION HUB & APPROVAL CENTER COMPONENT
     ═════════════════════════════════════════════════════════════════════ -->
<div id="notification-hub" class="position-relative" style="z-index: 1050; overflow: visible !important;">
    <!-- Notification Bell Button -->
    <button 
        id="notification-toggle" 
        class="btn btn-sm position-relative d-flex align-items-center justify-content-center" 
        type="button"
        aria-expanded="false"
        style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); border-radius: 8px; width: 36px; height: 36px; color: var(--text-secondary); cursor: pointer; padding: 0; transition: all 0.2s ease;">
        <i class="fa-regular fa-bell" style="font-size: 0.9rem;"></i>
        <!-- Unread Badge -->
        <span 
            id="notif-badge" 
            class="notif-badge position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger shadow-sm" 
            style="font-size: 0.58rem; display: none; padding: 3px 6px; font-weight: 700; border: 1.5px solid rgba(11,17,28,0.9); z-index: 1051;">
            0
        </span>
    </button>

    <!-- Dropdown Notification & Approval Center -->
    <div class="dropdown-menu dropdown-menu-end notification-dropdown shadow-lg" 
         id="notification-dropdown"
         style="width: 420px; max-height: 560px; background: rgba(11, 17, 28, 0.98); backdrop-filter: blur(20px); border: 1px solid rgba(99, 102, 241, 0.35); border-radius: 14px; box-shadow: 0 30px 80px rgba(0,0,0,0.95), 0 0 40px rgba(99, 102, 241, 0.25); display: none; overflow: hidden; padding: 0; z-index: 999999 !important;">
        
        <!-- Header Controls & Tabs -->
        <div style="padding: 12px 16px; background: rgba(255, 255, 255, 0.03); border-bottom: 1px solid rgba(255, 255, 255, 0.08); display: flex; align-items: center; justify-content: space-between;">
            <div class="d-flex align-items-center gap-2">
                <!-- Tab: Notifications -->
                <button type="button" class="btn btn-sm text-light fw-bold px-2 py-1 active-notif-tab" id="tab-btn-notifs" onclick="notificationHub.switchTab('notifications')" style="font-size: 0.82rem; background: rgba(99,102,241,0.2); border-radius: 6px; border: 1px solid rgba(99,102,241,0.4);">
                    <i class="fa-solid fa-bell me-1 text-primary"></i> Alerts <span id="tab-badge-notifs" class="badge bg-primary ms-1" style="font-size: 0.65rem;">0</span>
                </button>

                <!-- Tab: Approvals -->
                <button type="button" class="btn btn-sm text-muted fw-bold px-2 py-1" id="tab-btn-approvals" onclick="notificationHub.switchTab('approvals')" style="font-size: 0.82rem; background: rgba(255,255,255,0.04); border-radius: 6px; border: 1px solid rgba(255,255,255,0.08);">
                    <i class="fa-solid fa-shield-halved me-1 text-warning"></i> Approvals <span id="tab-badge-approvals" class="badge bg-warning text-dark ms-1" style="font-size: 0.65rem;">0</span>
                </button>
            </div>

            <div class="d-flex align-items-center gap-2">
                <!-- Test Alert Trigger Button -->
                <button 
                    id="test-notifications-btn"
                    type="button" 
                    class="btn btn-link btn-sm text-info p-0 text-decoration-none font-mono" 
                    style="font-size: 0.72rem;"
                    title="Generate sample notification & approval card for testing"
                    onclick="notificationHub.generateTest()">
                    <i class="fa-solid fa-flask me-1"></i> Test
                </button>

                <span class="text-muted opacity-25">|</span>

                <!-- Clear Button -->
                <button 
                    id="clear-notifications-btn"
                    type="button" 
                    class="btn btn-link btn-sm text-muted p-0 text-decoration-none font-mono" 
                    style="font-size: 0.72rem;"
                    onclick="notificationHub.clearAll()">
                    <i class="fa-solid fa-broom me-1"></i> Clear
                </button>
            </div>
        </div>

        <!-- Tab Panel: Notifications Stream -->
        <div id="panel-notifications" style="max-height: 420px; overflow-y: auto; padding: 4px 0;">
            <div id="notifications-container">
                <div style="padding: 30px 20px; text-align: center; color: var(--text-muted);">
                    <i class="fa-regular fa-bell-slash fs-3 mb-2 opacity-50 d-block"></i>
                    <small>No active notifications</small>
                </div>
            </div>
        </div>

        <!-- Tab Panel: Approvals Stream -->
        <div id="panel-approvals" style="max-height: 420px; overflow-y: auto; padding: 4px 0; display: none;">
            <div id="approvals-container">
                <div style="padding: 30px 20px; text-align: center; color: var(--text-muted);">
                    <i class="fa-solid fa-shield-cat fs-3 mb-2 opacity-50 d-block text-warning"></i>
                    <small>No pending approval requests</small>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div style="padding: 10px 16px; background: rgba(255, 255, 255, 0.02); border-top: 1px solid rgba(255,255,255,0.06); text-align: center;">
            <a href="{{ route('hub.notifications') }}" style="color: var(--nexus-blue); text-decoration: none; font-size: 0.8rem; font-weight: 500;">
                View All Notifications & History <i class="fa-solid fa-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
</div>

<!-- Notification Detail View Modal -->
<div class="modal fade" id="notification-detail-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-light" style="background: rgba(11, 17, 28, 0.98); backdrop-filter: blur(25px); border: 1px solid rgba(99, 102, 241, 0.4); border-radius: 16px; box-shadow: 0 25px 60px rgba(0,0,0,0.85);">
            <div class="modal-header border-bottom border-secondary border-opacity-25" style="padding: 16px 20px;">
                <div class="d-flex align-items-center gap-3">
                    <span id="notif-detail-icon" class="notification-type-icon"></span>
                    <div>
                        <h5 class="modal-title fw-bold text-light mb-0" id="notif-detail-title" style="font-size: 1.05rem;">Notification Details</h5>
                        <small class="text-muted" id="notif-detail-time" style="font-size: 0.75rem;"></small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div id="notif-detail-badge"></div>
                    <span class="badge bg-secondary bg-opacity-25 text-muted font-mono" style="font-size: 0.65rem;">
                        <i class="fa-solid fa-shield-halved me-1 text-info"></i> System Verified
                    </span>
                </div>
                <div class="p-3 mb-3 rounded" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); font-size: 0.88rem; color: #e2e8f0; line-height: 1.6;" id="notif-detail-body">
                </div>
                <div id="notif-detail-actions" class="d-flex gap-2 justify-content-end mt-4">
                </div>
            </div>
        </div>
    </div>
</div>

<style>
#notification-hub {
    overflow: visible !important;
}

.notification-dropdown {
    position: absolute !important;
    top: calc(100% + 8px) !important;
    right: 0 !important;
    left: auto !important;
    z-index: 999999 !important;
    transform: none !important;
}

.notification-item {
    padding: 12px 16px;
    border-bottom: 1px solid rgba(255,255,255,0.04);
    transition: background 0.2s ease;
    cursor: pointer;
}

.notification-item:hover {
    background: rgba(255,255,255,0.03);
}

.notification-item.unread {
    background: rgba(99,102,241,0.08);
    border-left: 3px solid #6366f1;
}

.approval-item {
    padding: 12px 16px;
    border-bottom: 1px solid rgba(255,255,255,0.05);
    background: rgba(234,179,8,0.04);
    border-left: 3px solid #eab308;
    transition: background 0.2s ease;
}

.approval-item.approved {
    background: rgba(34,197,94,0.04);
    border-left: 3px solid #22c55e;
}

.approval-item.rejected {
    background: rgba(239,68,68,0.04);
    border-left: 3px solid #ef4444;
}

.notification-type-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    font-size: 0.85rem;
    flex-shrink: 0;
}

.notification-type-info { background: rgba(59,130,246,0.15); color: #3b82f6; }
.notification-type-success { background: rgba(34,197,94,0.15); color: #22c55e; }
.notification-type-warning { background: rgba(251,146,60,0.15); color: #fb923c; }
.notification-type-error { background: rgba(239,68,68,0.15); color: #ef4444; }

.notification-time {
    font-size: 0.72rem;
    color: var(--text-muted);
    margin-top: 4px;
}
</style>

<script>
window.notificationHub = {
    notifications: [],
    approvals: [],
    unreadCount: 0,
    activeTab: 'notifications',
    isOpen: false,
    eventsInitialized: false,

    init(userId) {
        this.userId = userId;
        this.setupDOMEvents();
        this.fetchData();
        this.setupEchoListener();
    },

    switchTab(tab) {
        this.activeTab = tab;
        const btnNotifs = document.getElementById('tab-btn-notifs');
        const btnApprovals = document.getElementById('tab-btn-approvals');
        const panelNotifs = document.getElementById('panel-notifications');
        const panelApprovals = document.getElementById('panel-approvals');

        if (tab === 'notifications') {
            if (btnNotifs) {
                btnNotifs.className = 'btn btn-sm text-light fw-bold px-2 py-1';
                btnNotifs.style.background = 'rgba(99,102,241,0.2)';
                btnNotifs.style.border = '1px solid rgba(99,102,241,0.4)';
            }
            if (btnApprovals) {
                btnApprovals.className = 'btn btn-sm text-muted fw-bold px-2 py-1';
                btnApprovals.style.background = 'rgba(255,255,255,0.04)';
                btnApprovals.style.border = '1px solid rgba(255,255,255,0.08)';
            }
            if (panelNotifs) panelNotifs.style.display = 'block';
            if (panelApprovals) panelApprovals.style.display = 'none';
        } else {
            if (btnApprovals) {
                btnApprovals.className = 'btn btn-sm text-warning fw-bold px-2 py-1';
                btnApprovals.style.background = 'rgba(234,179,8,0.2)';
                btnApprovals.style.border = '1px solid rgba(234,179,8,0.4)';
            }
            if (btnNotifs) {
                btnNotifs.className = 'btn btn-sm text-muted fw-bold px-2 py-1';
                btnNotifs.style.background = 'rgba(255,255,255,0.04)';
                btnNotifs.style.border = '1px solid rgba(255,255,255,0.08)';
            }
            if (panelNotifs) panelNotifs.style.display = 'none';
            if (panelApprovals) panelApprovals.style.display = 'block';
        }
    },

    setupDOMEvents() {
        if (this.eventsInitialized) return;
        this.eventsInitialized = true;

        const toggle = document.getElementById('notification-toggle');
        const dropdown = document.getElementById('notification-dropdown');

        if (toggle && dropdown) {
            toggle.addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggleDropdown();
            });

            document.addEventListener('click', (e) => {
                if (!e.target.closest('#notification-hub')) {
                    this.closeDropdown();
                }
            });
        }
    },

    async fetchData() {
        try {
            const res = await fetch('/hub/notifications/data', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (res.ok) {
                const data = await res.json();
                if (data.success) {
                    this.approvals = data.approvals || [];
                    const serverNotifs = data.notifications || [];
                    const currentNotifs = Array.isArray(this.notifications) ? [...this.notifications] : [];
                    
                    // Merge server notifications with local notifications
                    const serverIds = new Set(serverNotifs.map(n => String(n.id)));
                    const localOnly = currentNotifs.filter(n => !serverIds.has(String(n.id)));
                    
                    // Keep read state if local item was marked read
                    const merged = [...localOnly, ...serverNotifs].map(n => {
                        const existingLocal = currentNotifs.find(loc => String(loc.id) === String(n.id));
                        if (existingLocal && existingLocal.is_read) {
                            n.is_read = true;
                        }
                        return n;
                    });

                    this.notifications = merged;
                    this.render();
                }
            }
        } catch (e) {
            console.warn('[NotificationHub] Fetch error:', e);
        }
    },

    async generateTest() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const btn = document.getElementById('test-notifications-btn');
        if (btn) btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Test';

        try {
            const res = await fetch('/hub/notifications/generate-test', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await res.json();
            if (res.ok && data.success) {
                await this.fetchData();
                this.switchTab('approvals');
            } else {
                alert('Test generation error: ' + (data.error || 'Unknown error'));
            }
        } catch(e) {
            alert('Error creating test notification: ' + e.message);
        } finally {
            if (btn) btn.innerHTML = '<i class="fa-solid fa-flask me-1"></i> Test';
        }
    },

    setupEchoListener(retryCount = 0) {
        if (window.Echo === null) return;
        if (!window.Echo || typeof window.Echo.private !== 'function') {
            if (retryCount < 5) setTimeout(() => this.setupEchoListener(retryCount + 1), 500);
            return;
        }

        try {
            window.Echo.private(`notifications.${this.userId}`)
                .listen('notification.received', (data) => {
                    this.addNotification(data);
                    this.showBrowserNotification(data);
                });
        } catch (e) {}
    },

    addNotification(data) {
        const notif = {
            id: data.id || `notif-${Date.now()}`,
            type: data.type || 'info',
            title: data.title || 'System Notification',
            body: data.body || '',
            actions: data.actions || [],
            timestamp: data.timestamp || new Date().toISOString(),
            is_read: false,
        };

        this.notifications.unshift(notif);
        this.render();
    },

    showBrowserNotification(data) {
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification(data.title || 'Nexus Notification', {
                body: data.body || '',
                icon: data.icon || '/favicon.ico',
                tag: data.id
            });
        }
    },

    toggleDropdown() {
        this.isOpen ? this.closeDropdown() : this.openDropdown();
    },

    openDropdown() {
        const dropdown = document.getElementById('notification-dropdown');
        if (dropdown) {
            dropdown.style.display = 'block';
            this.isOpen = true;
            this.fetchData();
        }
    },

    closeDropdown() {
        const dropdown = document.getElementById('notification-dropdown');
        if (dropdown) {
            dropdown.style.display = 'none';
            this.isOpen = false;
        }
    },

    async markAsRead(notifId) {
        const notif = this.notifications.find(n => String(n.id) === String(notifId));
        if (notif && !notif.is_read) {
            notif.is_read = true;
            this.render();

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            try {
                await fetch(`/hub/notifications/${notifId}/read`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
            } catch(e) {}
        }
    },

    showDetails(notifId) {
        const notif = this.notifications.find(n => String(n.id) === String(notifId));
        if (!notif) return;

        // Mark as read when opened
        this.markAsRead(notif.id);

        const titleEl = document.getElementById('notif-detail-title');
        const bodyEl = document.getElementById('notif-detail-body');
        const timeEl = document.getElementById('notif-detail-time');
        const badgeEl = document.getElementById('notif-detail-badge');
        const iconEl = document.getElementById('notif-detail-icon');
        const actionsEl = document.getElementById('notif-detail-actions');

        const type = notif.notification_type || notif.type || 'info';
        const typeClass = `notification-type-${type}`;

        if (titleEl) titleEl.textContent = notif.title || 'Notification Details';
        if (bodyEl) bodyEl.textContent = notif.body || 'No detailed content provided.';
        if (timeEl) timeEl.textContent = this.getRelativeTime(notif.created_at || notif.timestamp);

        if (iconEl) {
            iconEl.className = `notification-type-icon ${typeClass}`;
            iconEl.innerHTML = `<i class="fa-solid ${this.getTypeIcon(type)}"></i>`;
        }

        if (badgeEl) {
            badgeEl.innerHTML = `
                <span class="badge bg-primary bg-opacity-25 text-primary text-uppercase font-mono me-1" style="font-size:0.68rem;">${type}</span>
                <span class="badge bg-secondary bg-opacity-25 text-secondary text-uppercase font-mono" style="font-size:0.68rem;">Priority: ${notif.priority || 'normal'}</span>
            `;
        }

        if (actionsEl) {
            let buttonsHtml = '';
            const buttons = notif.action_buttons || notif.actions || [];
            if (Array.isArray(buttons) && buttons.length > 0) {
                buttonsHtml = buttons.map(btn => `
                    <a href="${btn.url || '#'}" class="btn btn-sm btn-primary px-3 py-1" style="font-size: 0.8rem;">
                        ${btn.label || 'Open Action'} <i class="fa-solid fa-arrow-right ms-1"></i>
                    </a>
                `).join('');
            }
            actionsEl.innerHTML = buttonsHtml + `
                <button type="button" class="btn btn-sm btn-outline-secondary px-3 py-1" data-bs-dismiss="modal" style="font-size: 0.8rem;">Close</button>
            `;
        }

        const modalEl = document.getElementById('notification-detail-modal');
        if (modalEl && typeof bootstrap !== 'undefined') {
            if (modalEl.parentElement !== document.body) {
                document.body.appendChild(modalEl);
            }
            const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
            bsModal.show();
        }
    },

    async respondApproval(approvalId, action) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        try {
            const res = await fetch(`/hub/approvals/${approvalId}/respond`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ action })
            });
            const data = await res.json();
            if (res.ok && data.success) {
                const approval = this.approvals.find(a => a.id === approvalId);
                if (approval) {
                    approval.status = action === 'approve' ? 'approved' : 'rejected';
                    approval.decided_at = new Date().toISOString();
                }
                this.render();
            } else {
                alert('Approval action failed: ' + (data.error || 'Unknown error'));
            }
        } catch (e) {
            alert('Error updating approval: ' + e.message);
        }
    },

    async clearAll() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        try {
            await fetch('/hub/notifications/clear-all', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
        } catch (e) {}

        this.notifications.forEach(n => n.is_read = true);
        this.render();
    },

    getRelativeTime(timestamp) {
        if (!timestamp) return 'Just now';
        const now = new Date();
        const date = new Date(timestamp);
        const seconds = Math.floor((now - date) / 1000);

        if (isNaN(seconds) || seconds < 60) return 'Just now';
        if (seconds < 3600) return `${Math.floor(seconds / 60)}m ago`;
        if (seconds < 86400) return `${Math.floor(seconds / 3600)}h ago`;
        return `${Math.floor(seconds / 86400)}d ago`;
    },

    getTypeIcon(type) {
        const icons = {
            info: 'fa-circle-info',
            success: 'fa-circle-check',
            warning: 'fa-triangle-exclamation',
            error: 'fa-circle-xmark',
        };
        return icons[type] || icons.info;
    },

    getRiskBadge(riskLevel) {
        const badges = {
            low: '<span class="badge bg-info bg-opacity-25 text-info font-mono" style="font-size: 0.65rem;">Low Risk</span>',
            medium: '<span class="badge bg-warning bg-opacity-25 text-warning font-mono" style="font-size: 0.65rem;">Medium Risk</span>',
            high: '<span class="badge bg-danger bg-opacity-25 text-danger font-mono" style="font-size: 0.65rem;">High Risk</span>',
            critical: '<span class="badge bg-danger text-light font-bold font-mono" style="font-size: 0.65rem;">Critical Risk</span>',
        };
        return badges[riskLevel] || badges.medium;
    },

    render() {
        const notifContainer = document.getElementById('notifications-container');
        const approvalContainer = document.getElementById('approvals-container');
        const badge = document.getElementById('notif-badge');
        const badgeNotifs = document.getElementById('tab-badge-notifs');
        const badgeApprovals = document.getElementById('tab-badge-approvals');

        const unreadNotifsCount = this.notifications.filter(n => !n.is_read).length;
        const pendingApprovalsCount = this.approvals.filter(a => a.status === 'pending').length;
        this.unreadCount = unreadNotifsCount + pendingApprovalsCount;

        if (badge) {
            if (this.unreadCount > 0) {
                badge.textContent = this.unreadCount > 99 ? '99+' : this.unreadCount;
                badge.style.display = 'block';
            } else {
                badge.style.display = 'none';
            }
        }

        if (badgeNotifs) badgeNotifs.textContent = unreadNotifsCount;
        if (badgeApprovals) badgeApprovals.textContent = pendingApprovalsCount;

        if (notifContainer) {
            if (this.notifications.length === 0) {
                notifContainer.innerHTML = `
                    <div style="padding: 30px 20px; text-align: center; color: var(--text-muted);">
                        <i class="fa-regular fa-bell-slash fs-3 mb-2 opacity-50 d-block"></i>
                        <small>No active notifications</small>
                    </div>`;
            } else {
                notifContainer.innerHTML = this.notifications.map(n => {
                    const typeClass = `notification-type-${n.notification_type || n.type || 'info'}`;
                    const readBadge = n.is_read 
                        ? '<span class="badge bg-secondary bg-opacity-25 text-muted font-mono" style="font-size:0.62rem;"><i class="fa-solid fa-check-double me-1 text-muted"></i> Read</span>'
                        : '<span class="badge bg-primary bg-opacity-25 text-primary font-mono" style="font-size:0.62rem;"><i class="fa-solid fa-circle me-1" style="font-size:0.45rem;"></i> Unread</span>';
                    
                    return `
                        <div class="notification-item ${n.is_read ? '' : 'unread'}" onclick="notificationHub.showDetails('${n.id}')">
                            <div class="d-flex gap-3">
                                <div class="notification-type-icon ${typeClass}">
                                    <i class="fa-solid ${this.getTypeIcon(n.notification_type || n.type)}"></i>
                                </div>
                                <div style="flex: 1;">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="fw-bold text-light" style="font-size: 0.85rem;">${n.title || 'Notification'}</div>
                                        ${readBadge}
                                    </div>
                                    <div class="text-secondary" style="font-size: 0.78rem; margin-top: 2px;">${n.body || ''}</div>
                                    <div class="notification-time">${this.getRelativeTime(n.created_at || n.timestamp)}</div>
                                </div>
                            </div>
                        </div>`;
                }).join('');
            }
        }

        if (approvalContainer) {
            if (this.approvals.length === 0) {
                approvalContainer.innerHTML = `
                    <div style="padding: 30px 20px; text-align: center; color: var(--text-muted);">
                        <i class="fa-solid fa-shield-cat fs-3 mb-2 opacity-50 d-block text-warning"></i>
                        <small>No pending approval requests</small>
                    </div>`;
            } else {
                approvalContainer.innerHTML = this.approvals.map(app => {
                    const isPending = app.status === 'pending';
                    const isApproved = app.status === 'approved';
                    const isRejected = app.status === 'rejected';
                    
                    let cardClass = 'approval-item';
                    if (isApproved) cardClass += ' approved';
                    if (isRejected) cardClass += ' rejected';

                    let statusBadge = '';
                    if (isPending) {
                        statusBadge = '<span class="badge bg-warning bg-opacity-25 text-warning font-mono" style="font-size:0.65rem;"><i class="fa-solid fa-clock me-1"></i> Pending Approval</span>';
                    } else if (isApproved) {
                        statusBadge = '<span class="badge bg-success bg-opacity-25 text-success font-mono" style="font-size:0.65rem;"><i class="fa-solid fa-check-double me-1"></i> Approved</span>';
                    } else if (isRejected) {
                        statusBadge = '<span class="badge bg-danger bg-opacity-25 text-danger font-mono" style="font-size:0.65rem;"><i class="fa-solid fa-xmark me-1"></i> Rejected</span>';
                    }

                    return `
                        <div class="${cardClass}">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <div class="fw-bold text-light" style="font-size: 0.85rem;">
                                    <i class="fa-solid fa-microchip me-1 text-warning"></i> Approval Request #${app.id}
                                </div>
                                <div class="d-flex align-items-center gap-1.5">
                                    ${this.getRiskBadge(app.risk_level)}
                                    ${statusBadge}
                                </div>
                            </div>
                            <div class="text-light" style="font-size: 0.8rem; line-height: 1.3;">
                                ${app.action_description || 'Action requiring human verification.'}
                            </div>
                            ${app.agent_reasoning ? `<div class="text-muted font-mono mt-1" style="font-size: 0.7rem;">Reason: ${app.agent_reasoning}</div>` : ''}
                            
                            ${isPending ? `
                                <div class="d-flex align-items-center gap-2 mt-2">
                                    <button type="button" class="btn btn-success btn-sm px-3 py-0.5" style="font-size: 0.72rem;" onclick="notificationHub.respondApproval(${app.id}, 'approve')">
                                        <i class="fa-solid fa-check me-1"></i> Approve
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm px-3 py-0.5" style="font-size: 0.72rem;" onclick="notificationHub.respondApproval(${app.id}, 'reject')">
                                        <i class="fa-solid fa-xmark me-1"></i> Reject
                                    </button>
                                </div>
                            ` : `
                                <div class="text-muted font-mono mt-1" style="font-size: 0.68rem;">
                                    Decided: ${this.getRelativeTime(app.decided_at || app.updated_at)}
                                </div>
                            `}
                        </div>`;
                }).join('');
            }
        }
    }
};

document.addEventListener('DOMContentLoaded', () => {
    const modalEl = document.getElementById('notification-detail-modal');
    if (modalEl && modalEl.parentElement !== document.body) {
        document.body.appendChild(modalEl);
    }
    const userId = document.querySelector('meta[name="user-id"]')?.content || 1;
    window.notificationHub.init(userId);
});
</script>
