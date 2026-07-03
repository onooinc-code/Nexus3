# Notifications Integration - Quick Start for Blade Users

Your Nexus project now has **real-time notifications integrated into the header** with support for Laravel Blade templates.

## ✅ What's Done

1. **Default User Setup** - Auto-logs in as `admin@nexus.local` in development
2. **Notification Button in Header** - Beautiful bell icon with unread badge
3. **Real-Time Dropdown** - Notifications appear instantly via WebSocket
4. **Browser Notifications** - Native browser alerts (even when tab is hidden)
5. **Blade + Vue 3 Hybrid** - Server-side rendering + client-side interactivity

## 🚀 How to Use

### 1. Start the WebSocket Server

```bash
php artisan reverb:start
```

This keeps running. Open another terminal for commands.

### 2. Send a Notification from Your Code

**In any Controller:**

```php
<?php

namespace App\Http\Controllers\Web;

use App\Events\NotificationBroadcasted;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function shipOrder(Request $request)
    {
        // Your business logic...
        
        // Send real-time notification
        NotificationBroadcasted::dispatch(
            userId: auth()->id(),
            notification: [
                'title' => 'Order Shipped',
                'body' => 'Your order is on the way!',
                'type' => 'success',  // info, success, warning, error
            ],
            type: 'success'
        );

        return back()->with('status', 'Order shipped!');
    }
}
```

### 3. Test with Artisan

```bash
php artisan tinker

# Send test notification
use App\Events\NotificationBroadcasted;
NotificationBroadcasted::dispatch(
    userId: 1,
    notification: [
        'title' => 'Test Notification',
        'body' => 'This is a test message',
        'type' => 'success',
    ],
    type: 'success'
);
exit;
```

User will see it appear in top-right instantly! 🎉

## 📁 Files Updated/Created

```
app/
├── Http/
│   └── Middleware/
│       └── AutoLoginDevMiddleware.php ✏️ (Updated - default user)
└── Events/
    └── NotificationBroadcasted.php ✅ (Already exists)

resources/
└── views/
    ├── components/
    │   └── notification-hub.blade.php ✨ (NEW - UI + JavaScript)
    └── layouts/
        └── app.blade.php ✏️ (Updated - includes notification-hub)

Documentation/
├── VUE3_BLADE_INTEGRATION.md ✨ (Complete guide)
├── NOTIFICATIONHUB_EXPLAINED.md ✨ (Deep dive)
├── REALTIME_NOTIFICATIONS.md ✅ (API reference)
└── REALTIME_NOTIFICATIONS_QUICK.md ✅ (Quick reference)
```

## 🔔 The NotificationHub Component

Located in: `resources/views/components/notification-hub.blade.php`

### What It Does

```
User clicks bell icon
    ↓
Dropdown opens showing all notifications
    ↓
Click notification to mark as read
    ↓
Badge updates with unread count
    ↓
Also shows native browser notification
```

### Features

✅ **Real-time updates** via WebSocket  
✅ **Unread badge** with counter  
✅ **Responsive dropdown** with smooth animations  
✅ **Browser notifications** (system alerts)  
✅ **Notification types** (info, success, warning, error)  
✅ **Relative timestamps** (Just now, 2m ago, etc.)  
✅ **Action buttons** (for interactive notifications)  
✅ **Clear all** button  
✅ **Stores last 50** notifications in memory  

### The JavaScript Inside

```javascript
const notificationHub = {
    notifications: [],      // All notifications
    unreadCount: 0,        // Badge counter
    
    // Key methods:
    init(userId)           // Initialize on page load
    addNotification(data)  // Add new notification
    markAsRead(id)         // Mark as read (click)
    clearAll()            // Delete all notifications
    render()              // Update UI
}

// Auto-initializes when page loads
document.addEventListener('DOMContentLoaded', () => {
    notificationHub.init(userId)
})
```

## 🎯 Common Scenarios

### Scenario 1: Send on User Action

```blade
<!-- In your Blade template -->
<form action="{{ route('orders.ship', $order) }}" method="POST">
    @csrf
    <button type="submit">Ship Order</button>
</form>

<!-- In Controller -->
public function ship(Order $order)
{
    $order->update(['status' => 'shipped']);

    NotificationBroadcasted::dispatch(
        userId: $order->user_id,
        notification: [
            'title' => "Order #{$order->id} Shipped",
            'body' => 'Track your package now',
            'type' => 'success',
        ],
        type: 'success'
    );

    return redirect('/orders')->with('status', 'Order shipped!');
}
```

### Scenario 2: Notify Multiple Users

```php
use App\Models\User;
use App\Events\NotificationBroadcasted;

// Notify all team members
$team = Team::find($teamId);

foreach ($team->users as $user) {
    NotificationBroadcasted::dispatch(
        userId: $user->id,
        notification: [
            'title' => 'Team Assignment',
            'body' => 'You have been assigned to ' . $task->title,
            'type' => 'info',
        ],
        type: 'info'
    );
}
```

### Scenario 3: From a Queued Job

```php
<?php

namespace App\Jobs;

use App\Events\NotificationBroadcasted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessPaymentJob implements ShouldQueue
{
    use Queueable;

    public function handle()
    {
        // Do processing...
        
        // Notify user when done
        NotificationBroadcasted::dispatch(
            userId: $this->payment->user_id,
            notification: [
                'title' => 'Payment Confirmed',
                'body' => 'Your payment of $99 has been processed',
                'type' => 'success',
            ],
            type: 'success'
        );
    }
}
```

### Scenario 4: With Action Buttons

```php
NotificationBroadcasted::dispatch(
    userId: $user->id,
    notification: [
        'title' => 'New Order Pending Approval',
        'body' => 'Order #123 is waiting for your approval',
        'type' => 'warning',
        'actions' => [
            ['action' => 'approve', 'title' => 'Approve'],
            ['action' => 'reject', 'title' => 'Reject'],
        ],
    ],
    type: 'warning'
);
```

## 🔐 Default User

When running in development (`APP_ENV=local`):

- **Email**: `admin@nexus.local`
- **Password**: `admin`
- **Auto-logs in**: No login needed

The middleware `AutoLoginDevMiddleware` handles this automatically. The first time it runs, it creates the user if it doesn't exist.

**Files:**
- `app/Http/Middleware/AutoLoginDevMiddleware.php`
- `bootstrap/app.php` (already configured)

## 🐛 Troubleshooting

### Notifications Not Appearing?

**Step 1: Is Reverb running?**
```bash
# Terminal where you ran: php artisan reverb:start
# Should show: "Starting Reverb server..."
```

**Step 2: Check browser console (DevTools > Console)**
```javascript
console.log(notificationHub)  // Should show object with notifications
console.log(window.Echo)       // Should show Echo instance
```

**Step 3: Try manual test**
```javascript
// In browser console, manually add a notification
notificationHub.addNotification({
    id: 'test-1',
    type: 'success',
    title: 'Test',
    body: 'Testing notifications',
    timestamp: new Date().toISOString(),
    actions: []
})

// You should see it appear immediately
```

**Step 4: Check logs**
```bash
tail -f storage/logs/laravel.log
# Look for errors related to broadcasting or Reverb
```

### "Admin user can't log in"?

```bash
# Manually create the user
php artisan tinker

use App\Models\User;
User::create([
    'name' => 'Admin',
    'email' => 'admin@nexus.local',
    'password' => bcrypt('admin'),
    'email_verified_at' => now(),
])

exit
```

Then refresh the page.

### Browser notifications not showing?

```javascript
// Check permission in browser console
console.log(Notification.permission)  // Should be 'granted'

// Request permission if needed
if (Notification.permission === 'default') {
    Notification.requestPermission()
}
```

## 📚 Full Documentation

For detailed information, see:

| Document | Purpose |
|----------|---------|
| [VUE3_BLADE_INTEGRATION.md](./VUE3_BLADE_INTEGRATION.md) | How Vue 3 works with Blade |
| [NOTIFICATIONHUB_EXPLAINED.md](./NOTIFICATIONHUB_EXPLAINED.md) | How NotificationHub works (step-by-step) |
| [REALTIME_NOTIFICATIONS.md](./REALTIME_NOTIFICATIONS.md) | Complete API reference |
| [REALTIME_NOTIFICATIONS_QUICK.md](./REALTIME_NOTIFICATIONS_QUICK.md) | Quick code snippets |

## 🎓 Understanding the Flow

**Simple Version:**
```
1. You call NotificationBroadcasted::dispatch()
2. Reverb receives it and broadcasts
3. Echo listens and gets the data
4. NotificationHub.addNotification() adds it to list
5. render() updates the HTML
6. User sees notification appear in dropdown
```

**Technical Version:**
```
Controller/Job
    ↓ Event Dispatch
NotificationBroadcasted
    ↓ Serialization
Reverb WebSocket Server
    ↓ Broadcasting (Pub/Sub)
Laravel Echo (Client)
    ↓ Event Listener Callback
notificationHub.listen() callback
    ↓ Add to array
this.notifications.push(data)
    ↓ Render UI
this.render()
    ↓ Update HTML/DOM
Browser Repaints
    ↓ User Sees
Notification in dropdown + Browser alert
```

## ✅ Checklist

- [ ] Reverb is running (`php artisan reverb:start`)
- [ ] Component is included in layout (already done)
- [ ] User is logged in as admin@nexus.local (auto-done)
- [ ] Send test notification from Tinker
- [ ] See notification appear in header
- [ ] See unread badge increment
- [ ] See browser notification popup
- [ ] Click notification to mark as read
- [ ] Badge decrements

## 🚀 Next Steps

1. **Start Reverb** in one terminal: `php artisan reverb:start`
2. **Open app** in browser: `https://soulyeg.online`
3. **Test from Tinker** using example above
4. **Integrate into your controllers** to send real notifications
5. **Deploy** when ready

## 💡 Pro Tips

### Tip 1: Batch Notifications
```php
$users = User::where('status', 'active')->get();
foreach ($users as $user) {
    NotificationBroadcasted::dispatch(
        userId: $user->id,
        notification: ['title' => 'Hello', 'body' => 'Bulk message'],
        type: 'info'
    );
    // Small delay to avoid overloading
    usleep(100000);  // 0.1 second
}
```

### Tip 2: Store in Database (Optional)
```php
// Save notification to database for history
Notification::create([
    'user_id' => $user->id,
    'title' => $notification['title'],
    'body' => $notification['body'],
    'type' => $type,
]);

// Then broadcast
NotificationBroadcasted::dispatch($user->id, $notification, $type);
```

### Tip 3: Notifications in Console Commands
```php
<?php

namespace App\Console\Commands;

use App\Events\NotificationBroadcasted;
use Illuminate\Console\Command;

class SendDailyDigest extends Command
{
    public function handle(): int
    {
        User::active()->each(function ($user) {
            NotificationBroadcasted::dispatch(
                userId: $user->id,
                notification: [
                    'title' => 'Daily Digest',
                    'body' => 'Your activity summary is ready',
                    'type' => 'info',
                ],
                type: 'info'
            );
        });

        return self::SUCCESS;
    }
}
```

---

**Status**: ✅ Complete and ready to use  
**Last Updated**: 2026-07-03  
**Environment**: Development (auto-login enabled)  
**WebSocket**: Reverb (real-time)  
**Rendering**: Blade + JavaScript (hybrid)
