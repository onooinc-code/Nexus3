<!-- Notification Center Component (Blade + Vue 3 Hybrid) -->
<div id="notification-hub" class="position-relative">
    <!-- Notification Bell Button -->
    <button 
        id="notification-toggle" 
        class="btn btn-sm position-relative" 
        type="button"
        aria-expanded="false"
        style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; color: var(--text-secondary); cursor: pointer; padding: 0;">
        <i class="fa-regular fa-bell" style="font-size: 0.85rem;"></i>
        <!-- Unread Badge -->
        <span 
            id="notif-badge" 
            class="notif-badge position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary" 
            style="font-size: 0.55rem; display: none;">
            0
        </span>
    </button>

    <!-- Dropdown Notification List -->
    <div class="dropdown-menu dropdown-menu-end notification-dropdown" 
         id="notification-dropdown"
         style="width: 360px; max-height: 500px; overflow-y: auto; background: rgba(9,15,25,0.92); border: 1px solid rgba(255,255,255,0.08); border-radius: 10px; box-shadow: 0 20px 60px rgba(0,0,0,0.35); display: none;">
        
        <!-- Header -->
        <div style="padding: 12px 16px; border-bottom: 1px solid rgba(255,255,255,0.06); display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-bell" style="font-size: 0.9rem; color: var(--text-secondary);"></i>
                <span style="font-weight: 600; color: var(--text-primary); font-size: 0.9rem;">Notifications</span>
            </div>
            <button 
                id="clear-notifications-btn"
                type="button" 
                class="btn btn-link btn-sm" 
                style="color: var(--text-secondary); text-decoration: none; padding: 0; font-size: 0.75rem;"
                onclick="notificationHub.clearAll()">
                Clear
            </button>
        </div>

        <!-- Notifications List -->
        <div id="notifications-container" style="padding: 8px 0;">
            <!-- Vue 3 will render notifications here -->
            <div style="padding: 20px; text-align: center; color: var(--text-muted);">
                <i class="fa-regular fa-bell-slash" style="font-size: 1.5rem; margin-bottom: 10px; opacity: 0.5; display: block;"></i>
                <small>No notifications</small>
            </div>
        </div>

        <!-- Footer -->
        <div style="padding: 12px 16px; border-top: 1px solid rgba(255,255,255,0.06); text-align: center;">
            <a href="{{ route('hub.notifications') }}" style="color: var(--nexus-blue); text-decoration: none; font-size: 0.82rem;">
                View All Notifications
            </a>
        </div>
    </div>
</div>

<style>
.notification-dropdown {
    position: absolute !important;
    top: 100% !important;
    right: 0 !important;
    left: auto !important;
    margin-top: 8px;
    z-index: 1050 !important;
}

.notification-item {
    padding: 12px 16px;
    border-bottom: 1px solid rgba(255,255,255,0.03);
    transition: background 0.2s ease;
    cursor: pointer;
}

.notification-item:hover {
    background: rgba(255,255,255,0.02);
}

.notification-item.unread {
    background: rgba(59,130,246,0.08);
}

.notification-type-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    font-size: 0.85rem;
    flex-shrink: 0;
}

.notification-type-info {
    background: rgba(59,130,246,0.15);
    color: #3b82f6;
}

.notification-type-success {
    background: rgba(34,197,94,0.15);
    color: #22c55e;
}

.notification-type-warning {
    background: rgba(251,146,60,0.15);
    color: #fb923c;
}

.notification-type-error {
    background: rgba(239,68,68,0.15);
    color: #ef4444;
}

.notif-badge {
    background: #ef4444 !important;
    font-weight: 600;
    min-width: 20px;
    padding: 2px 4px;
    display: flex !important;
    align-items: center;
    justify-content: center;
}

.notification-time {
    font-size: 0.75rem;
    color: var(--text-muted);
    margin-top: 4px;
}

.notification-actions {
    margin-top: 8px;
    display: flex;
    gap: 8px;
}

.notification-action-btn {
    font-size: 0.75rem;
    padding: 4px 8px;
    border-radius: 4px;
    background: rgba(59,130,246,0.1);
    color: #3b82f6;
    border: none;
    cursor: pointer;
    transition: background 0.2s;
}

.notification-action-btn:hover {
    background: rgba(59,130,246,0.2);
}
</style>

<script>
// Global notification hub object
const notificationHub = {
    notifications: [],
    unreadCount: 0,
    isOpen: false,

    // Initialize notification system
    init(userId) {
        console.log('[NotificationHub] Initializing for user:', userId);
        this.userId = userId;

        // Setup DOM events once
        this.setupDOMEvents();

        // Start listening for Echo notifications
        this.setupEchoListener();
    },

    // Setup Echo listener with retry if the script loads later
    setupEchoListener() {
        if (!window.Echo) {
            console.warn('[NotificationHub] Echo not ready, retrying in 400ms.');
            setTimeout(() => this.setupEchoListener(), 400);
            return;
        }

        window.Echo.private(`notifications.${this.userId}`)
            .listen('notification.received', (data) => {
                console.log('[NotificationHub] Received notification:', data);
                this.addNotification(data);
                this.showBrowserNotification(data);
            })
            .error((error) => {
                console.error('[NotificationHub] Echo subscription error:', error);
            });
    },

    // Setup DOM interaction
    setupDOMEvents() {
        if (this.eventsInitialized) {
            return;
        }

        this.eventsInitialized = true;
        const toggle = document.getElementById('notification-toggle');
        const dropdown = document.getElementById('notification-dropdown');

        if (toggle && dropdown) {
            toggle.addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggleDropdown();
            });

            // Close dropdown on outside click
            document.addEventListener('click', (e) => {
                if (!e.target.closest('#notification-hub')) {
                    this.closeDropdown();
                }
            });
        }
    },

    // Add notification to list
    addNotification(data) {
        const notification = {
            id: data.id || `notif-${Date.now()}`,
            type: data.type || 'info',
            title: data.title || 'Notification',
            body: data.body || '',
            icon: data.icon || null,
            actions: data.actions || [],
            timestamp: data.timestamp || new Date().toISOString(),
            read: false,
        };

        this.notifications.unshift(notification);
        this.unreadCount++;

        // Keep only last 50 notifications
        if (this.notifications.length > 50) {
            this.notifications = this.notifications.slice(0, 50);
        }

        this.render();
    },

    // Show browser notification
    showBrowserNotification(data) {
        if ('Notification' in window && Notification.permission === 'granted') {
            const options = {
                icon: data.icon || '/logo.png',
                body: data.body,
                badge: data.badge,
                tag: data.id,
                requireInteraction: data.requireInteraction || false,
                timestamp: new Date(data.timestamp).getTime(),
            };

            if (data.actions && data.actions.length > 0) {
                options.actions = data.actions;
            }

            new Notification(data.title, options);
        }
    },

    // Toggle dropdown visibility
    toggleDropdown() {
        this.isOpen ? this.closeDropdown() : this.openDropdown();
    },

    // Open dropdown
    openDropdown() {
        const dropdown = document.getElementById('notification-dropdown');
        if (dropdown) {
            dropdown.style.display = 'block';
            this.isOpen = true;
        }
    },

    // Close dropdown
    closeDropdown() {
        const dropdown = document.getElementById('notification-dropdown');
        if (dropdown) {
            dropdown.style.display = 'none';
            this.isOpen = false;
        }
    },

    // Mark notification as read
    markAsRead(notificationId) {
        const notif = this.notifications.find(n => n.id === notificationId);
        if (notif && !notif.read) {
            notif.read = true;
            this.unreadCount = Math.max(0, this.unreadCount - 1);
            this.render();
        }
    },

    // Clear all notifications
    clearAll() {
        this.notifications = [];
        this.unreadCount = 0;
        this.render();
    },

    // Get formatted time
    getRelativeTime(timestamp) {
        const now = new Date();
        const date = new Date(timestamp);
        const seconds = Math.floor((now - date) / 1000);
        
        if (seconds < 60) return 'Just now';
        if (seconds < 3600) return `${Math.floor(seconds / 60)}m ago`;
        if (seconds < 86400) return `${Math.floor(seconds / 3600)}h ago`;
        if (seconds < 604800) return `${Math.floor(seconds / 86400)}d ago`;
        
        return date.toLocaleDateString();
    },

    // Get icon for notification type
    getTypeIcon(type) {
        const icons = {
            info: 'fa-circle-info',
            success: 'fa-circle-check',
            warning: 'fa-triangle-exclamation',
            error: 'fa-circle-xmark',
        };
        return icons[type] || icons.info;
    },

    // Render notifications
    render() {
        const container = document.getElementById('notifications-container');
        const badge = document.getElementById('notif-badge');

        if (!container) return;

        // Update badge
        if (badge) {
            if (this.unreadCount > 0) {
                badge.textContent = this.unreadCount > 99 ? '99+' : this.unreadCount;
                badge.style.display = 'block';
            } else {
                badge.style.display = 'none';
            }
        }

        // Render notifications
        if (this.notifications.length === 0) {
            container.innerHTML = `
                <div style="padding: 20px; text-align: center; color: var(--text-muted);">
                    <i class="fa-regular fa-bell-slash" style="font-size: 1.5rem; margin-bottom: 10px; opacity: 0.5; display: block;"></i>
                    <small>No notifications</small>
                </div>
            `;
            return;
        }

        container.innerHTML = this.notifications.map(notif => {
            const typeClass = `notification-type-${notif.type}`;
            return `
                <div class="notification-item ${notif.read ? '' : 'unread'}" onclick="notificationHub.markAsRead('${notif.id}')">
                    <div style="display: flex; gap: 12px;">
                        <div class="notification-type-icon ${typeClass}">
                            <i class="fa-solid ${this.getTypeIcon(notif.type)}"></i>
                        </div>
                        <div style="flex: 1;">
                            <div style="font-weight: 600; color: var(--text-primary); font-size: 0.9rem;">
                                ${notif.title}
                            </div>
                            <div style="color: var(--text-secondary); font-size: 0.85rem; margin-top: 3px;">
                                ${notif.body}
                            </div>
                            ${notif.actions && notif.actions.length > 0 ? `
                                <div class="notification-actions">
                                    ${notif.actions.map(action => `
                                        <button class="notification-action-btn" onclick="event.stopPropagation();">
                                            ${action.title}
                                        </button>
                                    `).join('')}
                                </div>
                            ` : ''}
                            <div class="notification-time">${this.getRelativeTime(notif.timestamp)}</div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    },
};

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    const userId = document.querySelector('meta[name="user-id"]')?.content || 
                   (window.auth && window.auth.user && window.auth.user.id) ||
                   1;
    notificationHub.init(userId);
});

// Request notification permission
if ('Notification' in window && Notification.permission === 'default') {
    Notification.requestPermission();
}
</script>
