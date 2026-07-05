/**
 * Notification Service
 * Handles real-time notifications via Laravel Echo and Web Notifications API
 */

class NotificationService {
    constructor() {
        this.notifications = [];
        this.listeners = [];
        this.userId = null;
        this.enabled = false;
    }

    /**
     * Initialize the notification service
     */
    init(userId) {
        this.userId = userId;
        this.setupEchoListener();
        this.requestNotificationPermission();
        this.enabled = true;
    }

    /**
     * Setup Laravel Echo listener for real-time notifications
     */
    setupEchoListener() {
        if (!window.Echo || !this.userId) {
            console.warn('Echo not available or userId not set');
            return;
        }

        window.Echo.private(`notifications.${this.userId}`)
            .listen('notification.received', (data) => {
                this.handleNotification(data);
            })
            .error((error) => {
                console.error('Echo subscription error:', error);
            });

        console.log(`Notification listener set up for user ${this.userId}`);
    }

    /**
     * Handle incoming notification
     */
    handleNotification(data) {
        const notification = {
            id: data.id || `notif-${Date.now()}`,
            title: data.title || 'Notification',
            body: data.body || '',
            icon: data.icon || null,
            badge: data.badge || null,
            type: data.type || 'info',
            actions: data.actions || [],
            data: data.data || {},
            requireInteraction: data.requireInteraction || false,
            timestamp: data.timestamp || new Date().toISOString(),
            read: false,
        };

        // Add to notifications list
        this.notifications.unshift(notification);

        // Limit stored notifications to last 50
        if (this.notifications.length > 50) {
            this.notifications = this.notifications.slice(0, 50);
        }

        // Notify all listeners
        this.notifyListeners(notification);

        // Show browser notification if supported and enabled
        if (this.isBrowserNotificationSupported()) {
            this.showBrowserNotification(notification);
        }

        // Log notification
        console.log('Notification received:', notification);
    }

    /**
     * Show browser native notification
     */
    showBrowserNotification(notification) {
        if (!window.Notification || !this.isNotificationPermissionGranted()) {
            return;
        }

        const options = {
            body: notification.body,
            icon: notification.icon || '/favicon.ico',
            badge: notification.badge || '/favicon.ico',
            tag: notification.id,
            requireInteraction: notification.requireInteraction,
            data: {
                id: notification.id,
                ...notification.data,
            },
        };

        // Add actions if available
        if (notification.actions && notification.actions.length > 0) {
            options.actions = notification.actions.map((action, index) => ({
                action: action.action || `action-${index}`,
                title: action.title || 'Action',
                icon: action.icon || undefined,
            }));
        }

        try {
            const notif = new Notification(notification.title, options);

            // Handle notification click
            notif.onclick = () => {
                window.focus();
                this.notifyListeners({ type: 'click', notification });
                notif.close();
            };

            // Handle action clicks
            notif.onaction = (event) => {
                this.notifyListeners({
                    type: 'action',
                    notification,
                    action: event.action,
                });
                notif.close();
            };

            // Handle close
            notif.onclose = () => {
                this.notifyListeners({
                    type: 'close',
                    notification,
                });
            };
        } catch (error) {
            console.error('Failed to show browser notification:', error);
        }
    }

    /**
     * Request notification permission from user
     */
    requestNotificationPermission() {
        if (!this.isBrowserNotificationSupported()) {
            console.warn('Browser notifications not supported');
            return;
        }

        if (Notification.permission === 'granted') {
            return;
        }

        if (Notification.permission !== 'denied') {
            Notification.requestPermission().then((permission) => {
                if (permission === 'granted') {
                    console.log('Notification permission granted');
                }
            });
        }
    }

    /**
     * Check if browser notification permission is granted
     */
    isNotificationPermissionGranted() {
        if (!this.isBrowserNotificationSupported()) {
            return false;
        }
        return Notification.permission === 'granted';
    }

    /**
     * Check if browser supports notifications
     */
    isBrowserNotificationSupported() {
        return 'Notification' in window && 'serviceWorker' in navigator;
    }

    /**
     * Subscribe to notification events
     */
    subscribe(callback) {
        this.listeners.push(callback);

        // Return unsubscribe function
        return () => {
            this.listeners = this.listeners.filter((cb) => cb !== callback);
        };
    }

    /**
     * Notify all listeners of a notification event
     */
    notifyListeners(notification) {
        this.listeners.forEach((callback) => {
            try {
                callback(notification);
            } catch (error) {
                console.error('Error in notification listener:', error);
            }
        });
    }

    /**
     * Get all notifications
     */
    getNotifications() {
        return this.notifications;
    }

    /**
     * Get unread notifications count
     */
    getUnreadCount() {
        return this.notifications.filter((n) => !n.read).length;
    }

    /**
     * Mark notification as read
     */
    markAsRead(notificationId) {
        const notification = this.notifications.find((n) => n.id === notificationId);
        if (notification) {
            notification.read = true;
            this.notifyListeners({ type: 'marked-read', notification });
        }
    }

    /**
     * Clear all notifications
     */
    clearAll() {
        this.notifications = [];
        this.notifyListeners({ type: 'cleared' });
    }

    /**
     * Send a notification to the server
     */
    async send(userId, notificationData) {
        try {
            const response = await fetch('/api/v1/notifications/broadcast', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    user_id: userId,
                    ...notificationData,
                }),
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            console.error('Failed to send notification:', error);
            throw error;
        }
    }

    /**
     * Send notifications to multiple users
     */
    async sendBatch(userIds, notificationData) {
        try {
            const response = await fetch('/api/v1/notifications/broadcast-batch', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    user_ids: userIds,
                    ...notificationData,
                }),
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            console.error('Failed to send batch notifications:', error);
            throw error;
        }
    }
}

// Export singleton instance
export default new NotificationService();
