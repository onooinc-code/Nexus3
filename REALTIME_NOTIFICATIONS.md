# Real-Time Notifications with Laravel Echo & Reverb

Complete guide to implementing real-time push notifications using Laravel Echo and Reverb WebSocket server.

## Table of Contents

1. [Overview](#overview)
2. [Architecture](#architecture)
3. [Setup & Configuration](#setup--configuration)
4. [Backend Implementation](#backend-implementation)
5. [Frontend Implementation](#frontend-implementation)
6. [API Reference](#api-reference)
7. [Usage Examples](#usage-examples)
8. [Testing](#testing)
9. [Troubleshooting](#troubleshooting)

---

## Overview

The real-time notification system uses:
- **Laravel Echo**: Client-side library for WebSocket communication
- **Reverb**: Laravel's official WebSocket server
- **Broadcast Events**: Server-side event broadcasting
- **Web Notifications API**: Browser native notifications
- **Vue 3 Composables**: Frontend integration

### Key Features

✅ Real-time WebSocket notifications  
✅ Browser native notifications  
✅ Action button support  
✅ User channel authorization  
✅ Notification history  
✅ Unread count tracking  
✅ Vue 3 composable integration  
✅ Batch notification sending  

---

## Architecture

```
Backend Events (NotificationBroadcasted)
    ↓
Reverb WebSocket Server
    ↓
Laravel Echo (Client)
    ↓
NotificationService (JavaScript)
    ↓
Vue Composable (useNotifications)
    ↓
Vue Component (NotificationCenter.vue) + Web Notifications API
```

### Data Flow

1. **Event Dispatch**: Backend dispatches `NotificationBroadcasted` event
2. **Broadcasting**: Event is broadcast via Reverb to user's channel
3. **Echo Listener**: Client-side Echo listener receives event
4. **Service Handler**: NotificationService processes notification
5. **Display**: Vue component and/or browser notification shows notification

---

## Setup & Configuration

### 1. Environment Variables

Verify `.env` has Reverb configuration:

```env
BROADCAST_DRIVER=reverb
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=127.0.0.1
REVERB_PORT=6001
REVERB_SCHEME=https
```

### 2. Broadcasting Configuration

Already configured in `config/broadcasting.php`:

```php
'default' => env('BROADCAST_DRIVER', 'reverb'),

'connections' => [
    'reverb' => [
        'driver' => 'reverb',
        'host' => env('REVERB_HOST', '127.0.0.1'),
        'port' => env('REVERB_PORT', 6001),
        'scheme' => env('REVERB_SCHEME', 'https'),
        'key' => env('REVERB_APP_KEY'),
        'secret' => env('REVERB_APP_SECRET'),
        'app_id' => env('REVERB_APP_ID'),
    ],
]
```

### 3. Channel Authorization

Configured in `routes/channels.php`:

```php
Broadcast::channel('notifications.{userId}', function (User $user, int $userId) {
    return (int) $user->id === (int) $userId;
});
```

---

## Backend Implementation

### 1. Broadcast Event

**File**: `app/Events/NotificationBroadcasted.php`

```php
class NotificationBroadcasted implements ShouldBroadcast
{
    public function __construct(
        public int $userId,
        public array $notification,
        public string $type = 'info'
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("notifications.{$this->userId}")];
    }

    public function broadcastWith(): array
    {
        return [
            'id' => uniqid(),
            'type' => $this->type,
            'title' => $this->notification['title'] ?? 'Notification',
            'body' => $this->notification['body'] ?? '',
            'icon' => $this->notification['icon'] ?? null,
            'actions' => $this->notification['actions'] ?? [],
            'timestamp' => now()->toIso8601String(),
        ];
    }

    public function broadcastAs(): string
    {
        return 'notification.received';
    }
}
```

### 2. API Controller

**File**: `app/Http/Controllers/NotificationBroadcastController.php`

#### Send Single Notification

```php
public function send(Request $request): JsonResponse
{
    $validated = $request->validate([
        'user_id' => 'required|integer',
        'title' => 'required|string|max:255',
        'body' => 'required|string',
        'type' => 'in:info,warning,success,error|default:info',
        'icon' => 'nullable|string|url',
        'actions' => 'nullable|array',
        'data' => 'nullable|array',
    ]);

    NotificationBroadcasted::dispatch(
        userId: $validated['user_id'],
        notification: $validated,
        type: $validated['type']
    );

    return response()->json(['success' => true]);
}
```

#### Send Batch Notifications

```php
public function sendBatch(Request $request): JsonResponse
{
    $validated = $request->validate([
        'user_ids' => 'required|array',
        'title' => 'required|string',
        'body' => 'required|string',
        'type' => 'in:info,warning,success,error',
    ]);

    foreach ($validated['user_ids'] as $userId) {
        NotificationBroadcasted::dispatch(
            userId: $userId,
            notification: $validated,
            type: $validated['type']
        );
    }

    return response()->json(['success' => true, 'sent' => count($validated['user_ids'])]);
}
```

### 3. Routes

**File**: `routes/api.php`

```php
Route::group(['prefix' => 'v1/notifications', 'middleware' => ['api', 'auth:sanctum']], function () {
    Route::post('/broadcast', [NotificationBroadcastController::class, 'send'])
        ->name('notifications.broadcast');
    Route::post('/broadcast-batch', [NotificationBroadcastController::class, 'sendBatch'])
        ->name('notifications.broadcast.batch');
});
```

### 4. Dispatching from Your Code

```php
use App\Events\NotificationBroadcasted;

// In a controller, job, or anywhere
NotificationBroadcasted::dispatch(
    userId: $user->id,
    notification: [
        'title' => 'Order Shipped',
        'body' => 'Your order #123 has been shipped',
        'icon' => asset('logo.png'),
        'actions' => [
            ['action' => 'track', 'title' => 'Track Order'],
            ['action' => 'details', 'title' => 'View Details'],
        ],
    ],
    type: 'success'
);
```

---

## Frontend Implementation

### 1. Echo Initialization

**File**: `resources/js/echo.js`

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,
    wssPort: import.meta.env.VITE_REVERB_PORT,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME) === 'https',
    enabledTransports: ['ws', 'wss'],
});
```

### 2. Notification Service

**File**: `resources/js/services/NotificationService.js`

Handles:
- Real-time event listening via Echo
- Browser notification display
- Notification history
- Action handling

### 3. Vue Composable

**File**: `resources/js/composables/useNotifications.js`

```javascript
import { useNotifications } from '@/composables/useNotifications'

export default {
    setup() {
        const {
            notifications,
            unreadCount,
            init,
            sendNotification,
            markAsRead,
            clearAll,
        } = useNotifications()

        onMounted(() => {
            init(userId) // Initialize with current user ID
        })

        return {
            notifications,
            unreadCount,
            sendNotification,
            markAsRead,
            clearAll,
        }
    }
}
```

### 4. Vue Component

**File**: `resources/js/components/NotificationCenter.vue`

Complete notification UI with:
- Notification bell icon with badge
- Dropdown notification list
- Notification actions
- Empty states
- Browser permission request

### Usage in Vue

```vue
<template>
    <div class="navbar">
        <!-- Your other nav items -->
        <NotificationCenter :userId="user.id" />
    </div>
</template>

<script setup>
import NotificationCenter from '@/components/NotificationCenter.vue'

const user = ref({
    id: 1,
})
</script>
```

---

## API Reference

### Send Notification

**Endpoint**: `POST /api/v1/notifications/broadcast`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body**:
```json
{
    "user_id": 1,
    "title": "Notification Title",
    "body": "Notification message body",
    "type": "info",
    "icon": "https://example.com/icon.png",
    "badge": "https://example.com/badge.png",
    "actions": [
        {
            "action": "open",
            "title": "Open"
        },
        {
            "action": "dismiss",
            "title": "Dismiss"
        }
    ],
    "data": {
        "customField": "customValue"
    },
    "requireInteraction": false
}
```

**Response**:
```json
{
    "success": true,
    "message": "Notification broadcast successfully",
    "notification": {
        "title": "Notification Title",
        "body": "Notification message body",
        "type": "info"
    }
}
```

### Send Batch Notifications

**Endpoint**: `POST /api/v1/notifications/broadcast-batch`

**Request Body**:
```json
{
    "user_ids": [1, 2, 3],
    "title": "Batch Notification",
    "body": "Message for multiple users",
    "type": "warning",
    "actions": [...]
}
```

**Response**:
```json
{
    "success": true,
    "message": "Notification sent to 3 user(s)",
    "sent": 3,
    "failed": [],
    "total": 3
}
```

---

## Usage Examples

### Example 1: Order Status Notification

```php
// In your OrderStatusController
use App\Events\NotificationBroadcasted;

public function updateStatus(Order $order)
{
    $order->update(['status' => 'shipped']);

    NotificationBroadcasted::dispatch(
        userId: $order->user_id,
        notification: [
            'title' => "Order #{$order->id} Shipped",
            'body' => 'Your order is on the way!',
            'type' => 'success',
            'icon' => asset('images/shipped.png'),
            'actions' => [
                ['action' => 'track', 'title' => 'Track Package'],
                ['action' => 'details', 'title' => 'View Details'],
            ],
            'data' => [
                'order_id' => $order->id,
                'url' => route('orders.show', $order),
            ],
        ],
        type: 'success'
    );

    return response()->json(['success' => true]);
}
```

### Example 2: Handling Notification Actions

```vue
<script setup>
import { onMounted } from 'vue'

onMounted(() => {
    // Listen for notification action events
    window.addEventListener('notification:action', (event) => {
        const { notification, action } = event.detail

        if (action.action === 'track') {
            window.location.href = notification.data.url
        } else if (action.action === 'details') {
            router.push(`/orders/${notification.data.order_id}`)
        }
    })
})
</script>
```

### Example 3: Vue Component Sending Notification

```vue
<template>
    <button @click="sendNotification">Send Test Notification</button>
</template>

<script setup>
import { useNotifications } from '@/composables/useNotifications'

const { sendNotification: sendNotif } = useNotifications()

const sendNotification = async () => {
    try {
        const response = await sendNotif(currentUserId, {
            title: 'Test Notification',
            body: 'This is a test notification',
            type: 'info',
            actions: [
                { action: 'open', title: 'Open' },
            ],
        })
        console.log('Sent:', response)
    } catch (error) {
        console.error('Error:', error)
    }
}
</script>
```

---

## Testing

### Test with Artisan Command

```bash
# Send to current user (existing command)
php artisan app:send-pushbullet-test-notification --action=hedra

# Test action buttons
php artisan app:test-action-notifications --type=approval

# Test browser notifications
php artisan app:test-browser-notification --type=interactive
```

### Test with cURL

```bash
curl -X POST https://soulyeg.online/api/v1/notifications/broadcast \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 1,
    "title": "Test Notification",
    "body": "This is a test",
    "type": "info",
    "actions": [
      {"action": "open", "title": "Open"}
    ]
  }'
```

### Test with Laravel Tinker

```php
php artisan tinker

use App\Events\NotificationBroadcasted;

NotificationBroadcasted::dispatch(
    userId: 1,
    notification: [
        'title' => 'Test',
        'body' => 'Testing real-time notifications',
        'type' => 'success',
    ],
    type: 'success'
);
```

---

## Troubleshooting

### Notifications Not Arriving

**Check 1: Reverb Status**
```bash
# Verify Reverb is running
php artisan reverb:start

# Check in browser DevTools: Network > WS should show WebSocket connection
```

**Check 2: Channel Authorization**
```php
// routes/channels.php - Verify user can access channel
Broadcast::channel('notifications.{userId}', function (User $user, int $userId) {
    return (int) $user->id === (int) $userId; // Should return true
});
```

**Check 3: Echo Configuration**
```javascript
// resources/js/echo.js
console.log('Echo configured:', window.Echo)
// Should show Echo instance with configured broadcaster
```

**Check 4: Logs**
```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Check browser console
# DevTools > Console should show connection status
```

### Browser Notifications Not Showing

**Check 1: Permission Granted**
```javascript
console.log(Notification.permission) // Should be 'granted'
```

**Check 2: Service Worker**
```javascript
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.ready.then(() => {
        console.log('Service Worker ready')
    })
}
```

**Check 3: Browser Settings**
- Chrome: Settings > Privacy > Notifications
- Firefox: Preferences > Privacy > Permissions > Notifications
- Safari: System Preferences > Notifications

### WebSocket Connection Failed

**Solution 1: Check Reverb Port**
```php
// config/broadcasting.php
'reverb' => [
    'port' => env('REVERB_PORT', 6001),
]
```

**Solution 2: Firewall/Proxy**
- Ensure port 6001 (or configured port) is accessible
- Check firewall rules
- Check reverse proxy configuration

**Solution 3: HTTPS/WSS**
```env
REVERB_SCHEME=https  # Must be https for secure connections
```

---

## Best Practices

1. **Always Authorize Channels**
   ```php
   Broadcast::channel('notifications.{userId}', function (User $user, int $userId) {
       return (int) $user->id === (int) $userId;
   });
   ```

2. **Use Appropriate Notification Types**
   - `info`: General information
   - `success`: Successful operations
   - `warning`: Important warnings
   - `error`: Error messages

3. **Include Actionable Buttons**
   ```php
   'actions' => [
       ['action' => 'approve', 'title' => 'Approve'],
       ['action' => 'reject', 'title' => 'Reject'],
   ]
   ```

4. **Track Notification State**
   ```php
   // Use composable to track read/unread
   const { notifications, unreadCount } = useNotifications()
   ```

5. **Handle Action Events**
   ```javascript
   window.addEventListener('notification:action', (event) => {
       // Handle user action
   })
   ```

---

## Files Reference

| File | Purpose |
|------|---------|
| `app/Events/NotificationBroadcasted.php` | Broadcast event |
| `app/Http/Controllers/NotificationBroadcastController.php` | API controller |
| `routes/channels.php` | Channel authorization |
| `routes/api.php` | API routes |
| `resources/js/services/NotificationService.js` | JS notification service |
| `resources/js/composables/useNotifications.js` | Vue composable |
| `resources/js/components/NotificationCenter.vue` | Vue component |
| `resources/js/echo.js` | Echo initialization |
| `config/broadcasting.php` | Broadcasting config |

---

## Additional Resources

- [Laravel Broadcasting](https://laravel.com/docs/broadcasting)
- [Laravel Reverb](https://laravel.com/docs/reverb)
- [Laravel Echo](https://laravel.com/docs/echo)
- [Web Notifications API](https://developer.mozilla.org/en-US/docs/Web/API/Notifications_API)
- [Vue 3 Composables](https://vuejs.org/guide/extras/composition-api-faq.html)

---

**Last Updated:** 2026-07-03  
**Status:** Production Ready ✅
