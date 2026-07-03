# NotificationHub - Architecture & Code Explanation

Complete breakdown of how NotificationHub works and what each part does.

## System Overview

```
┌─────────────────────────────────────────────────────────────┐
│  User sees notification in real-time                        │
│  └─ Button in top navbar (resources/views/layouts/app.blade.php)
└─────────────────────────────────────────────────────────────┘
                            ▲
                            │
┌─────────────────────────────────────────────────────────────┐
│  NotificationHub JavaScript Object                          │
│  File: resources/views/components/notification-hub.blade.php│
│  Manages UI, state, and event handling                      │
└─────────────────────────────────────────────────────────────┘
                            ▲
                            │
┌─────────────────────────────────────────────────────────────┐
│  Laravel Echo (WebSocket Client)                            │
│  Listens on: notifications.{userId} channel                 │
│  Event: 'notification.received'                             │
└─────────────────────────────────────────────────────────────┘
                            ▲
                            │
┌─────────────────────────────────────────────────────────────┐
│  Reverb Server (WebSocket Broker)                           │
│  File: config/broadcasting.php                              │
│  Port: 6001                                                 │
└─────────────────────────────────────────────────────────────┘
                            ▲
                            │
┌─────────────────────────────────────────────────────────────┐
│  Backend Event Broadcasting                                 │
│  File: app/Events/NotificationBroadcasted.php               │
│  Dispatched from controllers/jobs                           │
└─────────────────────────────────────────────────────────────┘
```

---

## Part 1: The Event (Backend)

**File**: `app/Events/NotificationBroadcasted.php`

```php
<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NotificationBroadcasted implements ShouldBroadcast
{
    // Store data passed to constructor
    public function __construct(
        public int $userId,
        public array $notification,
        public string $type = 'info'
    ) {}

    // Define who receives this event
    public function broadcastOn(): array
    {
        return [new PrivateChannel("notifications.{$this->userId}")];
    }

    // Format the data sent to frontend
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

    // The event name that JavaScript listens for
    public function broadcastAs(): string
    {
        return 'notification.received';
    }
}
```

### What This Does:

1. **Implements ShouldBroadcast** → Laravel knows to send this via WebSocket
2. **Takes userId** → Notification only goes to specific user
3. **Private Channel** → Only authenticated user can receive
4. **broadcastWith()** → Formats data for frontend
5. **broadcastAs()** → Sets event name JavaScript listens for

### Usage:

```php
// In a controller
use App\Events\NotificationBroadcasted;

public function sendNotification(Request $request)
{
    NotificationBroadcasted::dispatch(
        userId: $request->user()->id,
        notification: [
            'title' => 'Hello',
            'body' => 'This is a notification',
            'icon' => asset('logo.png'),
            'actions' => [
                ['action' => 'open', 'title' => 'Open'],
            ],
        ],
        type: 'success'
    );
}
```

---

## Part 2: The Frontend Component (Blade + JavaScript)

**File**: `resources/views/components/notification-hub.blade.php`

### HTML Structure

```html
<div id="notification-hub">
    <!-- The bell icon button -->
    <button id="notification-toggle">
        <i class="fa-regular fa-bell"></i>
        <span id="notif-badge">0</span>  <!-- Unread count -->
    </button>

    <!-- Dropdown that opens when clicked -->
    <div id="notification-dropdown">
        <!-- List of notifications -->
        <div id="notifications-container">
            <!-- JavaScript renders notifications here -->
        </div>
    </div>
</div>
```

### JavaScript: The NotificationHub Object

```javascript
const notificationHub = {
    // ── STATE ──
    notifications: [],      // Store all notifications
    unreadCount: 0,         // Count of unread notifications
    userId: null,           // Current user ID
    isOpen: false,          // Is dropdown open?

    // ── INITIALIZATION ──
    init(userId) {
        this.userId = userId;

        // Connect to WebSocket channel
        if (window.Echo) {
            Echo.private(`notifications.${userId}`)
                .listen('notification.received', (data) => {
                    // When new notification arrives, add it
                    this.addNotification(data);
                    // And show browser notification
                    this.showBrowserNotification(data);
                });
        }

        this.setupDOMEvents();
    },

    // ── HANDLE NEW NOTIFICATION ──
    addNotification(data) {
        // Create notification object
        const notification = {
            id: data.id,
            type: data.type,          // 'info', 'success', 'warning', 'error'
            title: data.title,
            body: data.body,
            icon: data.icon,
            actions: data.actions,    // Action buttons array
            timestamp: data.timestamp,
            read: false,              // Not read yet
        };

        // Add to top of list (newest first)
        this.notifications.unshift(notification);

        // Increment unread count
        this.unreadCount++;

        // Keep only last 50 notifications (memory limit)
        if (this.notifications.length > 50) {
            this.notifications = this.notifications.slice(0, 50);
        }

        // Update UI
        this.render();
    },

    // ── BROWSER NOTIFICATIONS ──
    showBrowserNotification(data) {
        // Check if browser supports notifications
        if ('Notification' in window && Notification.permission === 'granted') {
            // Create native browser notification
            new Notification(data.title, {
                icon: data.icon,
                body: data.body,
                tag: data.id,  // Prevents duplicates
                timestamp: new Date(data.timestamp).getTime(),
            });
        }
    },

    // ── UI INTERACTIONS ──
    toggleDropdown() {
        if (this.isOpen) {
            this.closeDropdown();
        } else {
            this.openDropdown();
        }
    },

    openDropdown() {
        document.getElementById('notification-dropdown').style.display = 'block';
        this.isOpen = true;
    },

    closeDropdown() {
        document.getElementById('notification-dropdown').style.display = 'none';
        this.isOpen = false;
    },

    // ── MARK AS READ ──
    markAsRead(notificationId) {
        // Find notification
        const notif = this.notifications.find(n => n.id === notificationId);

        if (notif && !notif.read) {
            notif.read = true;  // Mark as read
            this.unreadCount--;  // Decrease count
            this.render();       // Update UI
        }
    },

    // ── CLEAR ALL ──
    clearAll() {
        this.notifications = [];   // Empty list
        this.unreadCount = 0;      // Reset count
        this.render();             // Update UI
    },

    // ── RENDER (Update the UI) ──
    render() {
        const container = document.getElementById('notifications-container');
        const badge = document.getElementById('notif-badge');

        // Update badge
        if (this.unreadCount > 0) {
            badge.textContent = this.unreadCount > 99 ? '99+' : this.unreadCount;
            badge.style.display = 'block';  // Show badge
        } else {
            badge.style.display = 'none';   // Hide badge
        }

        // If no notifications
        if (this.notifications.length === 0) {
            container.innerHTML = '<div>No notifications</div>';
            return;
        }

        // Render each notification
        container.innerHTML = this.notifications.map(notif => `
            <div class="notification-item ${notif.read ? '' : 'unread'}">
                <div class="notification-type-icon ${notif.type}">
                    <i class="fa-solid ${this.getTypeIcon(notif.type)}"></i>
                </div>
                <div>
                    <div class="title">${notif.title}</div>
                    <div class="body">${notif.body}</div>
                    <div class="time">${this.getRelativeTime(notif.timestamp)}</div>

                    ${notif.actions.length > 0 ? `
                        <div class="actions">
                            ${notif.actions.map(action => `
                                <button>${action.title}</button>
                            `).join('')}
                        </div>
                    ` : ''}
                </div>
            </div>
        `).join('');
    },

    // ── HELPER FUNCTIONS ──
    getRelativeTime(timestamp) {
        const now = new Date();
        const date = new Date(timestamp);
        const seconds = Math.floor((now - date) / 1000);

        if (seconds < 60) return 'Just now';
        if (seconds < 3600) return `${Math.floor(seconds / 60)}m ago`;
        if (seconds < 86400) return `${Math.floor(seconds / 3600)}h ago`;
        return `${Math.floor(seconds / 604800)}d ago`;
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
};

// Auto-initialize when page loads
document.addEventListener('DOMContentLoaded', () => {
    const userId = /* get from meta tag or auth data */;
    notificationHub.init(userId);
});
```

---

## Part 3: How It All Works Together

### Step-by-Step Execution

**Step 1: Page Loads**
```javascript
// Blade includes the component
@include('components.notification-hub')

// JavaScript runs
document.addEventListener('DOMContentLoaded', () => {
    notificationHub.init(userId)  // Initialize
})
```

**Step 2: NotificationHub Subscribes to Channel**
```javascript
Echo.private(`notifications.${userId}`)
    .listen('notification.received', (data) => {
        // This callback runs when event is broadcasted
    })
```

**Step 3: Backend Dispatches Event**
```php
// In a controller
NotificationBroadcasted::dispatch(
    userId: 1,
    notification: ['title' => 'Hello', 'body' => 'Test'],
    type: 'success'
);
```

**Step 4: Event Goes Through System**
```
Backend: NotificationBroadcasted event
    ↓ (serialized)
Reverb Server: Receives on notifications.1 channel
    ↓ (WebSocket broadcast)
Echo Client: Receives on notifications.1 channel
    ↓ (calls listener callback)
NotificationHub: .listen callback runs
    ↓ (new data)
addNotification(data): Adds to notifications array
    ↓ (updates state)
render(): Updates HTML/UI
    ↓ (browser shows)
User: Sees notification in dropdown
User: Also gets browser notification (toast)
```

### Real Example: Order Shipment

```php
// 1. Controller dispatches event
public function shipOrder(Order $order)
{
    $order->ship();

    NotificationBroadcasted::dispatch(
        userId: $order->user_id,
        notification: [
            'title' => "Order #{$order->id} Shipped!",
            'body' => 'Tracking: ' . $order->tracking_number,
            'icon' => asset('images/shipped.png'),
        ],
        type: 'success'
    );
}

// 2. Event broadcasts to Reverb
// 3. Reverb sends to WebSocket client
// 4. Echo receives and calls notificationHub.listen callback
// 5. NotificationHub adds notification to list
// 6. render() updates UI
// 7. User sees:
//    - Bell badge becomes "1"
//    - Dropdown shows "Order #123 Shipped!"
//    - Browser notification appears
//    - Relative time shows "Just now"
```

---

## Key Concepts

### Private Channels

```php
new PrivateChannel("notifications.{$userId}")
```

- Only user with matching ID can receive
- Secure - no one else gets your notifications
- Authorization checked in `routes/channels.php`

### Echo Listeners

```javascript
Echo.private(`notifications.${userId}`)
    .listen('notification.received', (data) => {
        // data comes from broadcastWith()
    })
```

- `'notification.received'` matches `broadcastAs()`
- Callback runs instantly when event broadcasts
- No polling needed (real-time via WebSocket)

### State Management

```javascript
notificationHub = {
    notifications: [],    // List of all notifications
    unreadCount: 0,      // For badge display
    userId: 1,           // Current user
    isOpen: false,       // UI state
}
```

- Simple object (no complex frameworks)
- State updated with `this.property = value`
- UI synced with `this.render()`

### Rendering

```javascript
render() {
    // Update badge
    badge.textContent = this.unreadCount
    
    // Update list
    container.innerHTML = this.notifications.map(...)
}
```

- Called whenever state changes
- Regenerates HTML from current state
- Simple but effective

---

## Configuration

### User ID From Multiple Sources

```javascript
// Option 1: Meta tag
<meta name="user-id" content="{{ auth()->id() }}">
const userId = document.querySelector('meta[name="user-id"]').content

// Option 2: Global variable
<script>
    window.userId = {{ auth()->id() }};
</script>

// Option 3: Auth object
const userId = window.auth.user.id
```

### Echo Configuration (Already Set Up)

**File**: `resources/views/layouts/app.blade.php`

```javascript
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: '{{ config("broadcasting.connections.reverb.key") }}',
    wsHost: '{{ config("broadcasting.connections.reverb.host") }}',
    wsPort: {{ config("broadcasting.connections.reverb.port") }},
    forceTLS: false,
});
```

---

## Debugging

### Check if NotificationHub is Initialized

```javascript
// In browser console
console.log(notificationHub)
// Should show object with notifications array and methods
```

### Check if Echo is Connected

```javascript
console.log(window.Echo)
// Should show Echo instance

// Check subscriptions
console.log(window.Echo.channels)
```

### Test Notification

```javascript
// Manually trigger a notification
notificationHub.addNotification({
    id: 'test-1',
    type: 'success',
    title: 'Test Notification',
    body: 'This is a test',
    timestamp: new Date().toISOString(),
    actions: [],
});
```

### Check Channel Authorization

```bash
# In logs
tail -f storage/logs/laravel.log

# Should NOT show authorization errors for notifications channel
```

---

## Common Issues & Fixes

### "NotificationHub is not defined"
- Component not included in layout
- Or included after script tags close
- **Fix**: Ensure `@include('components.notification-hub')` in layout

### WebSocket disconnects
- Reverb server not running
- Firewall blocking port 6001
- **Fix**: `php artisan reverb:start` and check firewall

### Badge doesn't show
- `unreadCount` not increasing
- `render()` not called
- **Fix**: Check browser console for errors

### Notifications appear but not real-time
- Echo not listening
- Channel authorization failing
- **Fix**: Check browser DevTools > Network > WS

---

## Testing Checklist

✅ Reverb running: `php artisan reverb:start`  
✅ Component included in layout  
✅ User ID passed correctly  
✅ Echo connected (browser console)  
✅ Channel authorization working  
✅ Event dispatched from backend  
✅ Notification appears in UI  
✅ Unread badge increments  
✅ Browser notification appears  
✅ Mark as read works  

---

## Files Overview

| File | Purpose | Key Method |
|------|---------|-----------|
| `app/Events/NotificationBroadcasted.php` | Event to broadcast | `broadcastWith()` |
| `resources/views/components/notification-hub.blade.php` | UI + JavaScript | `notificationHub.init()` |
| `routes/channels.php` | Authorization | `Broadcast::channel()` |
| `config/broadcasting.php` | Reverb config | Already configured |

---

## Summary

**NotificationHub** is:
1. **Simple** - Single JavaScript object
2. **Real-time** - Uses WebSocket via Echo
3. **Secure** - Private channels per user
4. **Integrated** - Works with existing Blade layout
5. **Powerful** - Supports actions, types, browser notifications

**Usage Flow:**
```
Backend Event → Reverb Broadcast → Echo Listener → NotificationHub → UI Update
```

**All real-time, no page reload needed!**

---

**Status**: ✅ Ready to use  
**Last Updated**: 2026-07-03
