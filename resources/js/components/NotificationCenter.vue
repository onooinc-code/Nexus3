<template>
    <div class="notification-center">
        <!-- Notification Bell Icon with Badge -->
        <div class="notification-icon" @click="toggleDropdown">
            <svg
                class="w-6 h-6"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                />
            </svg>
            <span v-if="unreadCount > 0" class="notification-badge">
                {{ unreadCount > 99 ? '99+' : unreadCount }}
            </span>
        </div>

        <!-- Dropdown Menu -->
        <Transition name="fade">
            <div v-if="isOpen" class="notification-dropdown">
                <!-- Header -->
                <div class="notification-header">
                    <h3 class="notification-title">Notifications</h3>
                    <button
                        v-if="notifications.length > 0"
                        @click="clearAll"
                        class="clear-btn"
                        title="Clear all notifications"
                    >
                        Clear
                    </button>
                </div>

                <!-- Notification List -->
                <div class="notification-list">
                    <template v-if="notifications.length > 0">
                        <div
                            v-for="notification in notifications"
                            :key="notification.id"
                            class="notification-item"
                            :class="{
                                unread: !notification.read,
                                [notification.type]: true,
                            }"
                        >
                            <!-- Icon and Content -->
                            <div class="notification-content">
                                <div class="notification-header-item">
                                    <h4 class="notification-item-title">
                                        {{ notification.title }}
                                    </h4>
                                    <span class="notification-time">
                                        {{ formatTime(notification.timestamp) }}
                                    </span>
                                </div>
                                <p class="notification-item-body">
                                    {{ notification.body }}
                                </p>

                                <!-- Actions -->
                                <div
                                    v-if="notification.actions.length > 0"
                                    class="notification-actions"
                                >
                                    <button
                                        v-for="(action, index) in notification.actions"
                                        :key="index"
                                        @click="handleAction(notification, action)"
                                        class="action-btn"
                                    >
                                        {{ action.title || action.label }}
                                    </button>
                                </div>
                            </div>

                            <!-- Close Button -->
                            <button
                                @click="markAsRead(notification.id)"
                                class="notification-close"
                                title="Mark as read"
                            >
                                ✕
                            </button>
                        </div>
                    </template>

                    <!-- Empty State -->
                    <div v-else class="empty-state">
                        <p>No notifications yet</p>
                    </div>
                </div>

                <!-- Permission Request -->
                <div
                    v-if="isSupported && !isPermissionGranted"
                    class="permission-banner"
                >
                    <p>Enable browser notifications for real-time updates</p>
                    <button @click="requestPermission" class="permission-btn">
                        Enable
                    </button>
                </div>
            </div>
        </Transition>

        <!-- Overlay -->
        <div v-if="isOpen" class="notification-overlay" @click="closeDropdown" />
    </div>
</template>

<script setup>
import { ref, onMounted, onClickOutside } from 'vue';
import { useNotifications } from '../composables/useNotifications';

const props = defineProps({
    userId: {
        type: Number,
        required: true,
    },
});

const {
    notifications,
    unreadCount,
    isSupported,
    isPermissionGranted,
    init,
    markAsRead,
    clearAll,
    requestPermission,
} = useNotifications();

const isOpen = ref(false);
const dropdownRef = ref(null);

onMounted(() => {
    init(props.userId);
});

onClickOutside(dropdownRef, () => {
    closeDropdown();
});

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
};

const closeDropdown = () => {
    isOpen.value = false;
};

const formatTime = (timestamp) => {
    const date = new Date(timestamp);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    if (diffHours < 24) return `${diffHours}h ago`;
    if (diffDays < 7) return `${diffDays}d ago`;

    return date.toLocaleDateString();
};

const handleAction = (notification, action) => {
    markAsRead(notification.id);

    // Dispatch custom event for action handling
    window.dispatchEvent(
        new CustomEvent('notification:action', {
            detail: { notification, action },
        })
    );
};
</script>

<style scoped>
.notification-center {
    position: relative;
    display: inline-block;
}

.notification-icon {
    position: relative;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 0.375rem;
    transition: background-color 0.2s;
}

.notification-icon:hover {
    background-color: rgba(0, 0, 0, 0.05);
}

.notification-badge {
    position: absolute;
    top: -0.25rem;
    right: -0.25rem;
    background-color: #ef4444;
    color: white;
    border-radius: 9999px;
    padding: 0.125rem 0.375rem;
    font-size: 0.75rem;
    font-weight: bold;
    min-width: 1.25rem;
    text-align: center;
}

.notification-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 0.5rem;
    width: 24rem;
    max-height: 32rem;
    background: white;
    border-radius: 0.5rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    z-index: 50;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.notification-header {
    padding: 1rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.notification-title {
    margin: 0;
    font-size: 1.125rem;
    font-weight: 600;
}

.clear-btn {
    background: none;
    border: none;
    color: #6b7280;
    cursor: pointer;
    font-size: 0.875rem;
    text-decoration: underline;
    padding: 0;
}

.clear-btn:hover {
    color: #374151;
}

.notification-list {
    overflow-y: auto;
    flex: 1;
}

.notification-item {
    padding: 1rem;
    border-bottom: 1px solid #f3f4f6;
    display: flex;
    gap: 0.75rem;
    align-items: flex-start;
    transition: background-color 0.2s;
}

.notification-item:hover {
    background-color: #f9fafb;
}

.notification-item.unread {
    background-color: #eff6ff;
}

.notification-item.info {
    border-left: 3px solid #3b82f6;
}

.notification-item.success {
    border-left: 3px solid #10b981;
}

.notification-item.warning {
    border-left: 3px solid #f59e0b;
}

.notification-item.error {
    border-left: 3px solid #ef4444;
}

.notification-content {
    flex: 1;
    min-width: 0;
}

.notification-header-item {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    gap: 0.5rem;
    margin-bottom: 0.25rem;
}

.notification-item-title {
    margin: 0;
    font-size: 0.9375rem;
    font-weight: 600;
}

.notification-time {
    font-size: 0.75rem;
    color: #9ca3af;
    white-space: nowrap;
}

.notification-item-body {
    margin: 0.25rem 0 0.5rem 0;
    font-size: 0.875rem;
    color: #4b5563;
    line-height: 1.4;
}

.notification-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.action-btn {
    padding: 0.375rem 0.75rem;
    font-size: 0.8125rem;
    background-color: #3b82f6;
    color: white;
    border: none;
    border-radius: 0.25rem;
    cursor: pointer;
    transition: background-color 0.2s;
}

.action-btn:hover {
    background-color: #2563eb;
}

.notification-close {
    flex-shrink: 0;
    background: none;
    border: none;
    color: #d1d5db;
    cursor: pointer;
    font-size: 1rem;
    padding: 0;
    width: 1.5rem;
    height: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.25rem;
    transition: background-color 0.2s;
}

.notification-close:hover {
    background-color: #f3f4f6;
    color: #4b5563;
}

.empty-state {
    padding: 2rem;
    text-align: center;
    color: #9ca3af;
}

.permission-banner {
    padding: 1rem;
    background-color: #fef3c7;
    border-top: 1px solid #fcd34d;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
}

.permission-banner p {
    margin: 0;
    font-size: 0.875rem;
    color: #78350f;
}

.permission-btn {
    padding: 0.375rem 0.75rem;
    background-color: #f59e0b;
    color: white;
    border: none;
    border-radius: 0.25rem;
    cursor: pointer;
    font-size: 0.875rem;
    transition: background-color 0.2s;
}

.permission-btn:hover {
    background-color: #d97706;
}

.notification-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 40;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
