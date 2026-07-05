# Real-Time Notifications - Quick Reference

Complete real-time notification system using Laravel Echo, Reverb, and Vue 3.

## 🚀 Quick Start

### 1. Backend: Dispatch Notification

```php
use App\Events\NotificationBroadcasted;

// In controller, job, or anywhere
NotificationBroadcasted::dispatch(
    userId: 1,
    notification: [
        'title' => 'Hello',
        'body' => 'Real-time notification',
        'type' => 'success',
        'actions' => [
            ['action' => 'open', 'title' => 'Open'],
        ],
    ],
    type: 'success'
);
```

### 2. Frontend: Use Composable

```vue
<script setup>
import { useNotifications } from '@/composables/useNotifications'
import NotificationCenter from '@/components/NotificationCenter.vue'

const { notifications, unreadCount } = useNotifications()

onMounted(() => {
    useNotifications().init(userId)
})
</script>

<template>
    <div>
        <NotificationCenter :userId="userId" />
        <p>Unread: {{ unreadCount }}</p>
    </div>
</template>
```

## 📡 API Endpoints

### Send Single Notification

```bash
POST /api/v1/notifications/broadcast
Content-Type: application/json
Authorization: Bearer {token}

{
    "user_id": 1,
    "title": "Notification",
    "body": "Message body",
    "type": "info",
    "actions": [
        {"action": "open", "title": "Open"}
    ]
}
```

### Send to Multiple Users

```bash
POST /api/v1/notifications/broadcast-batch
Content-Type: application/json

{
    "user_ids": [1, 2, 3],
    "title": "Bulk Notification",
    "body": "Message",
    "type": "warning"
}
```

## 📁 File Structure

```
Backend:
├── app/Events/NotificationBroadcasted.php          # Broadcast event
├── app/Http/Controllers/NotificationBroadcastController.php  # API controller
├── routes/api.php                                  # API routes
├── routes/channels.php                             # Channel authorization
└── config/broadcasting.php                         # Broadcasting config

Frontend:
├── resources/js/echo.js                           # Echo setup
├── resources/js/services/NotificationService.js   # Service
├── resources/js/composables/useNotifications.js   # Vue composable
└── resources/js/components/NotificationCenter.vue # UI component
```

## 🎯 Notification Types

- `info` - Blue (ℹ️)
- `success` - Green (✓)
- `warning` - Amber (⚠️)
- `error` - Red (✗)

## 🔧 Configuration

### Environment Variables

```env
BROADCAST_DRIVER=reverb
REVERB_APP_KEY=your-key
REVERB_APP_SECRET=your-secret
REVERB_APP_ID=your-id
REVERB_HOST=127.0.0.1
REVERB_PORT=6001
REVERB_SCHEME=https
```

### Start Reverb Server

```bash
php artisan reverb:start
```

## 📚 Usage Examples

### Example 1: Order Notification

```php
NotificationBroadcasted::dispatch(
    userId: $order->user_id,
    notification: [
        'title' => "Order #{$order->id} Shipped",
        'body' => 'Your order is on the way',
        'type' => 'success',
        'icon' => asset('images/shipped.png'),
        'actions' => [
            ['action' => 'track', 'title' => 'Track'],
            ['action' => 'details', 'title' => 'Details'],
        ],
        'data' => ['order_id' => $order->id],
    ],
    type: 'success'
);
```

### Example 2: Handle Action Click

```javascript
window.addEventListener('notification:action', (event) => {
    const { notification, action } = event.detail
    
    if (action.action === 'track') {
        router.push(`/orders/${notification.data.order_id}/track`)
    }
})
```

### Example 3: Request Permission

```javascript
import { useNotifications } from '@/composables/useNotifications'

const { requestPermission, isPermissionGranted } = useNotifications()

if (!isPermissionGranted.value) {
    requestPermission()
}
```

## 🧪 Testing

### Test with Artisan

```bash
# Send to a user
php artisan tinker
NotificationBroadcasted::dispatch(1, ['title' => 'Test', 'body' => 'Test'], 'info');
exit;
```

### Test with cURL

```bash
curl -X POST https://soulyeg.online/api/v1/notifications/broadcast \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 1,
    "title": "Test",
    "body": "Testing",
    "type": "info"
  }'
```

## ✨ Features

✅ Real-time WebSocket delivery  
✅ Browser native notifications  
✅ Action buttons with callbacks  
✅ Notification history  
✅ Unread count tracking  
✅ User channel authorization  
✅ Batch sending  
✅ Custom data support  
✅ Vue 3 composable integration  

## 🔐 Security

- **Channel Authorization**: User can only receive their own notifications
- **Token Authentication**: API requires Sanctum token
- **User Verification**: Backend verifies user ID matches authenticated user

```php
Broadcast::channel('notifications.{userId}', function (User $user, int $userId) {
    return (int) $user->id === (int) $userId; // Only user's own channel
});
```

## 🚨 Troubleshooting

### WebSocket Not Connecting

```bash
# Check Reverb is running
php artisan reverb:start

# Check firewall allows port 6001
lsof -i :6001

# Check logs
tail -f storage/logs/laravel.log
```

### Browser Notifications Not Showing

```javascript
// Check permission
console.log(Notification.permission) // Should be 'granted'

// Request if needed
Notification.requestPermission()
```

### Channel Authorization Failed

```php
// Verify in routes/channels.php
Broadcast::channel('notifications.{userId}', function (User $user, int $userId) {
    // This should return true for authorized users
    dd($user->id, $userId);
});
```

## 📖 Full Documentation

See [REALTIME_NOTIFICATIONS.md](REALTIME_NOTIFICATIONS.md) for complete documentation.

## 🔗 API Response Examples

### Success Response

```json
{
    "success": true,
    "message": "Notification broadcast successfully",
    "notification": {
        "title": "Order Shipped",
        "body": "Your order is on the way",
        "type": "success"
    }
}
```

### Batch Response

```json
{
    "success": true,
    "message": "Notification sent to 3 user(s)",
    "sent": 3,
    "failed": [],
    "total": 3
}
```

## 🎨 Notification Center Component

The `NotificationCenter.vue` component provides:

- 🔔 Notification bell icon with badge
- 📬 Dropdown notification list
- 🎯 Action buttons for each notification
- ⏰ Time-relative timestamps
- 🗑️ Clear all notifications
- 🔒 Permission request banner
- 📱 Responsive design

### Integration

```vue
<template>
    <header>
        <h1>My App</h1>
        <NotificationCenter :userId="currentUser.id" />
    </header>
</template>

<script setup>
import NotificationCenter from '@/components/NotificationCenter.vue'
</script>
```

## 🔄 Event Flow

```
1. Backend: NotificationBroadcasted::dispatch(userId, notification)
                    ↓
2. Reverb: Broadcasts to notifications.{userId} channel
                    ↓
3. Echo: Receives 'notification.received' event
                    ↓
4. NotificationService: handleNotification()
                    ↓
5. Display: Browser notification + Vue component
                    ↓
6. Action: notifyListeners() → Vue components + custom events
```

---

**Last Updated:** 2026-07-03  
**Domain:** https://soulyeg.online  
**Status:** ✅ Production Ready
