# Vue 3 + Blade Integration Guide

Complete guide to integrating Vue 3 with Laravel Blade templates for real-time features while keeping server-side rendering.

## Table of Contents

1. [Overview](#overview)
2. [Why Vue 3 + Blade?](#why-vue-3--blade)
3. [The NotificationHub System](#the-notificationhub-system)
4. [Implementation Guide](#implementation-guide)
5. [Real-World Examples](#real-world-examples)
6. [Best Practices](#best-practices)

---

## Overview

You're using a **hybrid approach**:
- **Blade** renders static HTML on server (traditional server-side rendering)
- **Vue 3** mounts on specific elements for interactive/real-time features
- **Laravel Echo** handles WebSocket communication
- **Reverb** broadcasts real-time events

This gives you the best of both worlds:
✅ Fast initial page load (Blade)  
✅ Real-time updates (Vue 3)  
✅ No SPA complexity  
✅ SEO-friendly  

---

## Why Vue 3 + Blade?

### The Problem

Using only Blade:
- Can't update UI in real-time without page reload
- Can't listen to WebSocket events automatically
- AJAX calls require manual setup for each interaction

Using only Vue 3 (SPA):
- Slow initial load (bundle size ~35KB)
- Need to build entire app as SPA
- All routing in frontend, not server

### The Solution: Hybrid Approach

```
┌─────────────────────────────────────┐
│   Laravel Blade Template             │  (Server-side rendered)
│   (Static HTML, Forms, Layout)       │
└─────────────────────────────────────┘
            ↓
┌─────────────────────────────────────┐
│   Vue 3 Mounted Components           │  (Client-side interactive)
│   (Notifications, Real-time, Chat)   │
└─────────────────────────────────────┘
            ↓
┌─────────────────────────────────────┐
│   Laravel Echo + Reverb              │  (WebSocket events)
│   (Real-time data streaming)         │
└─────────────────────────────────────┘
```

---

## The NotificationHub System

### How NotificationHub Works

**File**: `resources/views/components/notification-hub.blade.php`

**Step-by-Step Flow:**

1. **Initialization** (DOM loads)
   ```javascript
   notificationHub.init(userId)  // Runs automatically
   ```

2. **Echo Listener Setup**
   ```javascript
   Echo.private(`notifications.${userId}`)
       .listen('notification.received', (data) => {
           notificationHub.addNotification(data)
       })
   ```

3. **Event Broadcast** (Backend sends)
   ```php
   NotificationBroadcasted::dispatch(
       userId: $user->id,
       notification: ['title' => 'Hello', 'body' => 'New message']
   )
   ```

4. **Reverb Receives** → Broadcasts to user's channel

5. **Echo Listens** → Calls callback with data

6. **NotificationHub Processes** → Adds to list, updates UI

7. **User Sees** → Notification appears in dropdown + browser notification

### Architecture

```javascript
┌─ NotificationHub Object ─────────────────┐
│                                          │
│  Properties:                             │
│  - notifications: []  (All notifications)│
│  - unreadCount: 0     (Badge counter)    │
│  - userId: 1          (Current user)     │
│  - isOpen: false      (Dropdown state)   │
│                                          │
│  Methods:                                │
│  - init(userId)       (Setup listener)   │
│  - addNotification()  (New notification) │
│  - render()           (Update UI)        │
│  - markAsRead()       (Mark as read)     │
│  - clearAll()         (Delete all)       │
│                                          │
└──────────────────────────────────────────┘
         ↓
    ┌─ Echo (Laravel Echo) ─┐
    │ Listens for events    │
    │ on private channel    │
    └───────────────────────┘
         ↓
    ┌─ Reverb Server ────┐
    │ Broadcasts events  │
    │ to WebSocket       │
    └────────────────────┘
         ↓
    ┌─ Backend Event ──────────┐
    │ NotificationBroadcasted  │
    │ dispatched on server     │
    └──────────────────────────┘
```

### Key Features

#### 1. Real-Time Updates
```javascript
// Notification arrives instantly via WebSocket
Echo.private(`notifications.${userId}`)
    .listen('notification.received', (data) => {
        // Update happens immediately (milliseconds)
        notificationHub.addNotification(data)
    })
```

#### 2. Browser Notifications
```javascript
// Native browser notification (works even in background)
new Notification('Order Shipped', {
    icon: '/logo.png',
    body: 'Your order is on the way'
})
```

#### 3. Unread Count Badge
```javascript
// Badge shows count
this.unreadCount++  // Counter increases
this.render()       // UI updates
// Shows "1", "2", "99+"
```

#### 4. Dropdown UI
```javascript
// Responsive notification list
// - Click to mark as read
// - Click action buttons
// - Relative timestamps (Just now, 2m ago, etc.)
// - Type-based icons and colors
```

---

## Implementation Guide

### 1. Adding NotificationHub to Your Layout

**File**: `resources/views/layouts/app.blade.php`

Already done! We replaced the old bell icon with:
```blade
<!-- Notifications Hub -->
@include('components.notification-hub')
```

### 2. Sending Notifications from Backend

**In a Controller:**

```php
<?php

namespace App\Http\Controllers\Web;

use App\Events\NotificationBroadcasted;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function ship(Order $order)
    {
        // Ship the order
        $order->update(['status' => 'shipped']);

        // Send real-time notification
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
            ],
            type: 'success'
        );

        return back()->with('status', 'Order shipped!');
    }
}
```

### 3. Sending Notifications from Jobs

**In a Queued Job:**

```php
<?php

namespace App\Jobs;

use App\Events\NotificationBroadcasted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessOrderJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public $order)
    {}

    public function handle(): void
    {
        // Process order
        $this->order->process();

        // Notify user
        NotificationBroadcasted::dispatch(
            userId: $this->order->user_id,
            notification: [
                'title' => 'Order Processed',
                'body' => 'Your order is being prepared',
                'type' => 'info',
            ],
            type: 'info'
        );
    }
}
```

### 4. Sending from Scheduled Tasks

**In a Scheduled Command:**

```php
<?php

namespace App\Console\Commands;

use App\Events\NotificationBroadcasted;
use App\Models\User;
use Illuminate\Console\Command;

class SendDailyReminder extends Command
{
    protected $signature = 'app:send-daily-reminder';

    public function handle(): int
    {
        User::where('active', true)->each(function ($user) {
            NotificationBroadcasted::dispatch(
                userId: $user->id,
                notification: [
                    'title' => 'Daily Reminder',
                    'body' => 'Check your pending tasks',
                    'type' => 'info',
                ],
                type: 'info'
            );
        });

        return self::SUCCESS;
    }
}
```

### 5. Testing Notifications

**Using Artisan Tinker:**

```bash
php artisan tinker
```

```php
use App\Events\NotificationBroadcasted;
use App\Models\User;

$user = User::first();  // or User::where('email', 'admin@nexus.local')->first()

NotificationBroadcasted::dispatch(
    userId: $user->id,
    notification: [
        'title' => 'Test Notification',
        'body' => 'This is a test message',
        'type' => 'success',
        'actions' => [
            ['action' => 'view', 'title' => 'View'],
            ['action' => 'dismiss', 'title' => 'Dismiss'],
        ],
    ],
    type: 'success'
);
```

---

## Real-World Examples

### Example 1: Task Assignment Notification

**Scenario**: Manager assigns task to team member

**Backend** (In a Controller):

```php
public function assignTask(Task $task, User $assignee)
{
    $task->update([
        'assigned_to' => $assignee->id,
        'status' => 'assigned'
    ]);

    // Send notification
    NotificationBroadcasted::dispatch(
        userId: $assignee->id,
        notification: [
            'title' => "New Task: {$task->title}",
            'body' => "Assigned by {$task->assignedBy->name}",
            'type' => 'info',
            'icon' => asset('images/task.png'),
            'actions' => [
                ['action' => 'view', 'title' => 'View Task'],
                ['action' => 'accept', 'title' => 'Accept'],
            ],
        ],
        type: 'info'
    );

    return response()->json(['success' => true]);
}
```

**Frontend** (Blade - No Vue needed):
```blade
<button onclick="assignTask({{ $task->id }})">Assign Task</button>

<!-- NotificationHub automatically shows the notification -->
```

### Example 2: Real-Time Chat Notifications

**For complex real-time features, mount Vue component:**

**File**: `resources/views/components/chat-box.blade.php`

```blade
<!-- Mount Vue 3 component here -->
<div id="chat-app"></div>

@push('scripts')
<script>
    import { createApp } from 'vue'
    import ChatBox from './ChatBox.vue'

    // Only mount Vue for this component
    createApp(ChatBox).mount('#chat-app')
</script>
@endpush
```

### Example 3: Multiple Notifications at Once

```php
// Notify multiple users about event
use App\Models\User;

$users = User::where('team_id', $team->id)->get();

foreach ($users as $user) {
    NotificationBroadcasted::dispatch(
        userId: $user->id,
        notification: [
            'title' => 'Team Announcement',
            'body' => $announcement->message,
            'type' => 'warning',
        ],
        type: 'warning'
    );
}
```

---

## Best Practices

### 1. Use NotificationHub for Simple Real-Time Features

✅ **Good Use Cases:**
- Notifications and alerts
- Status updates
- Activity feeds
- Simple counts/badges
- Toast messages

```php
NotificationBroadcasted::dispatch(
    userId: $user->id,
    notification: ['title' => 'Status Changed', 'body' => 'Your profile is now public'],
    type: 'success'
);
```

### 2. Use Full Vue 3 Component for Complex Features

❌ **Avoid Vue for:**
- Simple notifications (use NotificationHub)
- Form submissions (use Blade form + AJAX)
- Page navigation (use Blade routes)

✅ **Use Vue for:**
- Real-time collaborative editing
- Live chat interfaces
- Complex interactive dashboards
- Drag-and-drop boards
- Real-time data visualization

```blade
<!-- Mount separate Vue app for complex feature -->
<div id="real-time-chat-app"></div>

<script>
    import { createApp } from 'vue'
    import RealtimeChatApp from './RealtimeChatApp.vue'
    createApp(RealtimeChatApp).mount('#real-time-chat-app')
</script>
```

### 3. Keep Your Blade Clean

Don't mix Vue logic into Blade templates:

```blade
<!-- ❌ BAD: Vue logic in Blade -->
<div v-if="isOpen">@yield('content')</div>

<!-- ✅ GOOD: Clean Blade -->
<div id="notification-hub"></div>

<!-- Vue mounts on this element -->
<script>
    notificationHub.init(userId)
</script>
```

### 4. Share User ID Securely

**In your Blade layout:**

```blade
<!-- Share user ID with frontend -->
<meta name="user-id" content="{{ auth()->id() }}">

<!-- JavaScript picks it up -->
<script>
    const userId = document.querySelector('meta[name="user-id"]').content
    notificationHub.init(userId)
</script>
```

### 5. Handle Offline Gracefully

```javascript
// NotificationHub handles disconnects automatically
Echo.leave(`notifications.${userId}`)

// And reconnects when online
Echo.join(`notifications.${userId}`)
```

### 6. Respect User Preferences

```javascript
// Ask permission for browser notifications
if (Notification.permission === 'default') {
    Notification.requestPermission()
}

// Only show if permission granted
if (Notification.permission === 'granted') {
    new Notification('...')
}
```

---

## Troubleshooting

### Notifications Not Appearing?

**Check 1: Is Reverb running?**
```bash
php artisan reverb:start
```

**Check 2: Is Echo connected?**
```javascript
// In browser console
console.log(window.Echo)  // Should show Echo instance
```

**Check 3: Check browser console for errors**
```javascript
// Open DevTools > Console
// Look for error messages about WebSocket connection
```

**Check 4: Verify channel authorization**

```php
// routes/channels.php
Broadcast::channel('notifications.{userId}', function (User $user, int $userId) {
    return (int) $user->id === (int) $userId;  // Should return true
});
```

### Default User Not Logging In?

**Check your .env:**
```env
APP_ENV=local  # Must be local or development
```

**Check if user exists:**
```bash
php artisan tinker
User::where('email', 'admin@nexus.local')->first()
# If null, middleware will create it
```

### Browser Notifications Not Working?

**Check permission:**
```javascript
console.log(Notification.permission)  // Should be 'granted'
```

**Request permission:**
```javascript
Notification.requestPermission()
```

---

## Summary

### The Hybrid Approach in Your Project

1. **Blade** handles:
   - Page layouts and structure
   - Forms and input elements
   - Server-side data rendering
   - Link navigation

2. **Vue 3 (NotificationHub)** handles:
   - Real-time notification updates
   - WebSocket event listening
   - Interactive dropdown UI
   - Notification state management

3. **Echo + Reverb** handles:
   - WebSocket connections
   - Real-time data broadcasting
   - Channel subscriptions and authorization

### Migration Path

**Now**: NotificationHub for simple real-time features  
**Later**: Add Vue components for specific interactive features  
**Future**: Could migrate to full Vue SPA if needed  

This keeps your codebase simple and maintainable while adding powerful real-time capabilities exactly where you need them.

---

## Next Steps

1. **Start Reverb**: `php artisan reverb:start`
2. **Test Notifications**: Send test via Tinker (example above)
3. **Integrate in Controllers**: Send notifications when events happen
4. **Add Vue Component**: When you need complex interactivity

---

**Files Reference:**

| File | Purpose |
|------|---------|
| `app/Http/Middleware/AutoLoginDevMiddleware.php` | Default user setup |
| `resources/views/components/notification-hub.blade.php` | Notification UI + JavaScript |
| `resources/views/layouts/app.blade.php` | Main layout (updated) |
| `app/Events/NotificationBroadcasted.php` | Broadcast event |
| `app/Http/Controllers/NotificationBroadcastController.php` | API endpoints |

**Related Documentation:**

- [REALTIME_NOTIFICATIONS.md](./REALTIME_NOTIFICATIONS.md) - Complete API reference
- [REALTIME_NOTIFICATIONS_QUICK.md](./REALTIME_NOTIFICATIONS_QUICK.md) - Quick start guide

---

**Status**: ✅ Production Ready  
**Last Updated**: 2026-07-03
