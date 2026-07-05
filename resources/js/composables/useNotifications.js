import { ref, computed, onMounted, onUnmounted } from 'vue';
import NotificationService from '../services/NotificationService.js';

/**
 * Vue 3 Composable for handling real-time notifications
 * Usage in component:
 *
 * import { useNotifications } from '@/composables/useNotifications'
 *
 * setup() {
 *   const { notifications, unreadCount, init, sendNotification } = useNotifications()
 *
 *   onMounted(() => {
 *     init(userId)
 *   })
 *
 *   return { notifications, unreadCount, sendNotification }
 * }
 */

export function useNotifications() {
    const notifications = ref([]);
    const unreadCount = ref(0);
    const isInitialized = ref(false);
    let unsubscribe = null;

    /**
     * Initialize notification service
     */
    const init = (userId) => {
        if (isInitialized.value) {
            return;
        }

        NotificationService.init(userId);

        // Subscribe to notification events
        unsubscribe = NotificationService.subscribe((event) => {
            updateNotifications();

            // Handle specific event types
            if (event.type === 'click') {
                onNotificationClick(event.notification);
            } else if (event.type === 'action') {
                onNotificationAction(event.notification, event.action);
            } else if (event.type === 'close') {
                onNotificationClose(event.notification);
            } else if (event.type === 'marked-read') {
                updateNotifications();
            } else if (event.type === 'cleared') {
                updateNotifications();
            }
        });

        isInitialized.value = true;
    };

    /**
     * Update local notifications list
     */
    const updateNotifications = () => {
        notifications.value = NotificationService.getNotifications();
        unreadCount.value = NotificationService.getUnreadCount();
    };

    /**
     * Handle notification click
     */
    const onNotificationClick = (notification) => {
        console.log('Notification clicked:', notification);
        markAsRead(notification.id);
    };

    /**
     * Handle notification action click
     */
    const onNotificationAction = (notification, action) => {
        console.log('Notification action clicked:', { notification, action });
        markAsRead(notification.id);

        // Dispatch custom event that components can listen to
        window.dispatchEvent(
            new CustomEvent('notification:action', {
                detail: { notification, action },
            })
        );
    };

    /**
     * Handle notification close
     */
    const onNotificationClose = (notification) => {
        console.log('Notification closed:', notification);
    };

    /**
     * Mark notification as read
     */
    const markAsRead = (notificationId) => {
        NotificationService.markAsRead(notificationId);
        updateNotifications();
    };

    /**
     * Clear all notifications
     */
    const clearAll = () => {
        NotificationService.clearAll();
        updateNotifications();
    };

    /**
     * Send a notification
     */
    const sendNotification = async (userId, notificationData) => {
        try {
            return await NotificationService.send(userId, notificationData);
        } catch (error) {
            console.error('Error sending notification:', error);
            throw error;
        }
    };

    /**
     * Send notifications to multiple users
     */
    const sendBatchNotifications = async (userIds, notificationData) => {
        try {
            return await NotificationService.sendBatch(userIds, notificationData);
        } catch (error) {
            console.error('Error sending batch notifications:', error);
            throw error;
        }
    };

    /**
     * Request browser notification permission
     */
    const requestPermission = () => {
        NotificationService.requestNotificationPermission();
    };

    /**
     * Check if browser notifications are supported
     */
    const isSupported = computed(() => {
        return NotificationService.isBrowserNotificationSupported();
    });

    /**
     * Check if notification permission is granted
     */
    const isPermissionGranted = computed(() => {
        return NotificationService.isNotificationPermissionGranted();
    });

    // Cleanup on unmount
    onUnmounted(() => {
        if (unsubscribe) {
            unsubscribe();
        }
    });

    return {
        notifications,
        unreadCount,
        isInitialized,
        isSupported,
        isPermissionGranted,
        init,
        markAsRead,
        clearAll,
        sendNotification,
        sendBatchNotifications,
        requestPermission,
    };
}
