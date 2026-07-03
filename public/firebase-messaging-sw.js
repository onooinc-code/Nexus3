importScripts('https://www.gstatic.com/firebasejs/9.23.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.23.0/firebase-messaging-compat.js');

const fetchFcmConfig = async () => {
    try {
        const response = await fetch('/api/v1/notifications/fcm-config');
        if (!response.ok) {
            throw new Error(`FCM config fetch failed: ${response.status}`);
        }

        const json = await response.json();
        return json.data || {};
    } catch (error) {
        console.error('Failed to load FCM config in service worker:', error);
        return {};
    }
};

const initFirebaseMessaging = async () => {
    const fcmConfig = await fetchFcmConfig();
    if (!fcmConfig.apiKey || !fcmConfig.messagingSenderId) {
        console.warn('Firebase config missing in service worker. Skipping initialization.');
        return null;
    }

    const firebaseConfig = {
        apiKey: fcmConfig.apiKey,
        authDomain: fcmConfig.authDomain,
        projectId: fcmConfig.projectId,
        storageBucket: fcmConfig.storageBucket,
        messagingSenderId: fcmConfig.messagingSenderId,
        appId: fcmConfig.appId,
        measurementId: fcmConfig.measurementId,
    };

    if (!firebase.apps.length) {
        firebase.initializeApp(firebaseConfig);
    }

    return firebase.messaging();
};

let messagingPromise = initFirebaseMessaging();

self.addEventListener('push', async (event) => {
    const messaging = await messagingPromise;
    if (!messaging) {
        return;
    }

    if (event.data) {
        const payload = event.data.json();
        const notification = payload.notification || {};
        const title = notification.title || 'Notification';
        const options = {
            body: notification.body || '',
            icon: notification.icon || '/favicon.ico',
            badge: notification.badge || '/favicon.ico',
            data: payload.data || {},
            requireInteraction: notification.requireInteraction || false,
            tag: payload.messageId || payload.data?.id || `notif-${Date.now()}`,
        };

        event.waitUntil(self.registration.showNotification(title, options));
    }
});

messagingPromise.then((messaging) => {
    if (!messaging) {
        return;
    }

    messaging.onBackgroundMessage((payload) => {
        const notification = payload.notification || {};
        const title = notification.title || 'Notification';
        const options = {
            body: notification.body || '',
            icon: notification.icon || '/favicon.ico',
            badge: notification.badge || '/favicon.ico',
            data: payload.data || {},
            requireInteraction: notification.requireInteraction || false,
            tag: payload.messageId || payload.data?.id || `notif-${Date.now()}`,
        };

        self.registration.showNotification(title, options);
    });
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const clickAction = event.notification?.data?.click_action || '/';
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
            for (const client of windowClients) {
                if (client.url.includes(clickAction) && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(clickAction);
            }
        })
    );
});
