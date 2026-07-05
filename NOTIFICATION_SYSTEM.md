# Nexus Notification System

Complete guide to the Nexus notification system, including Pushbullet integration and browser notifications.

## Table of Contents

1. [Overview](#overview)
2. [Notification Channels](#notification-channels)
3. [Setup and Configuration](#setup-and-configuration)
4. [Usage Guide](#usage-guide)
5. [Pushbullet Notifications](#pushbullet-notifications)
6. [Browser Notifications](#browser-notifications)
7. [Testing](#testing)
8. [Examples](#examples)
9. [Troubleshooting](#troubleshooting)

---

## Overview

The Nexus notification system provides a flexible, multi-channel approach to sending notifications to users. Currently supported channels include:

- **Pushbullet**: Send notifications to all devices via Pushbullet
- **Browser Notifications**: Send native Chrome/modern browser notifications
- **Database**: Store notifications in the database for later retrieval

### Architecture

```
Notification Classes (app/Notifications/)
    ↓
Notification Channels (app/Notifications/Channels/)
    ↓
Delivery Systems
    ├─ Pushbullet API
    ├─ Browser (Web Notifications API)
    └─ Database
```

---

## Notification Channels

### 1. PushbulletChannel

Delivers notifications to all Pushbullet devices via the Pushbullet API.

**Features:**
- Sends to all devices automatically
- Supports action URLs
- Device-specific targeting (optional)
- Error logging and handling

**Class:** `App\Notifications\Channels\PushbulletChannel`

### 2. BrowserNotificationChannel

Queues notifications for browser display using the Web Notifications API.

**Features:**
- Native browser notifications
- Action buttons support
- Icon and badge customization
- User permission handling

**Class:** `App\Notifications\Channels\BrowserNotificationChannel`

---

## Setup and Configuration

### 1. Environment Configuration

Add the following to your `.env` file:

```env
PUSHBULLET_API_KEY=o.bprB3OR1IITBW3nOKY37IoiPGwL64euR
```

### 2. Service Configuration

The service is configured in `config/services.php`:

```php
'pushbullet' => [
    'key' => env('PUSHBULLET_API_KEY'),
],
```

### 3. Notification Classes

All notification classes extend `Illuminate\Notifications\Notification`:

```php
namespace App\Notifications;

class MyNotification extends Notification
{
    public function via($notifiable)
    {
        return [PushbulletChannel::class];
    }

    public function toPushbullet($notifiable)
    {
        return [
            'type' => 'note',
            'title' => 'Title',
            'body' => 'Message body',
        ];
    }
}
```

---

## Usage Guide

### Basic Notification

Create a notification class:

```bash
php artisan make:notification MyNotification
```

Implement the required methods:

```php
public function via($notifiable)
{
    return [PushbulletChannel::class];
}

public function toPushbullet($notifiable)
{
    return [
        'type' => 'note',
        'title' => 'Notification Title',
        'body' => 'This is the notification body',
    ];
}
```

Send the notification:

```php
use App\Notifications\MyNotification;

$user->notify(new MyNotification());
```

### Multiple Channels

Send via multiple channels:

```php
public function via($notifiable)
{
    return [
        PushbulletChannel::class,
        BrowserNotificationChannel::class,
    ];
}

public function toPushbullet($notifiable)
{
    // Pushbullet format
}

public function toBrowserNotification($notifiable)
{
    // Browser format
}
```

---

## Pushbullet Notifications

### Configuration

1. Get your Pushbullet API token from https://www.pushbullet.com/account
2. Add to `.env`: `PUSHBULLET_API_KEY=your_token_here`
3. Register your devices at https://www.pushbullet.com/

### API Reference

#### Basic Note

```php
public function toPushbullet($notifiable)
{
    return [
        'type' => 'note',
        'title' => 'Title',
        'body' => 'Message',
    ];
}
```

#### Note with URL

```php
public function toPushbullet($notifiable)
{
    return [
        'type' => 'note',
        'title' => 'Check this out',
        'body' => 'Click the link below',
        'url' => 'https://soulyeg.online',
    ];
}
```

#### Link

```php
public function toPushbullet($notifiable)
{
    return [
        'type' => 'link',
        'title' => 'Link Title',
        'body' => 'Link description',
        'url' => 'https://soulyeg.online',
    ];
}
```

#### File

```php
public function toPushbullet($notifiable)
{
    return [
        'type' => 'file',
        'file_name' => 'document.pdf',
        'file_url' => 'https://soulyeg.online/files/document.pdf',
        'file_type' => 'application/pdf',
    ];
}
```

#### Target Specific Device

```php
public function toPushbullet($notifiable)
{
    return [
        'type' => 'note',
        'title' => 'Device-Specific',
        'body' => 'Only to this device',
        'device_iden' => 'your_device_id', // Get from Pushbullet API
    ];
}
```

### Sending to All Devices

By default, omitting the `device_iden` parameter sends to all devices:

```php
return [
    'type' => 'note',
    'title' => 'To All Devices',
    'body' => 'This goes everywhere',
    // No device_iden = all devices
];
```

### Action Buttons (with URLs)

The Pushbullet channel supports action buttons via URL links. Use the `ActionButtonNotification` class:

```php
use App\Notifications\ActionButtonNotification;

$notification = new ActionButtonNotification(
    title: 'Approval Request',
    message: 'Document needs approval',
    actions: [
        ['label' => 'Approve', 'url' => 'https://soulyeg.online/approve'],
        ['label' => 'Reject', 'url' => 'https://soulyeg.online/reject'],
    ],
    actionType: 'approval'
);

$user->notify($notification);
```

---

## Browser Notifications

### Configuration

Browser notifications work out-of-the-box without additional configuration. The frontend handles the Web Notifications API.

### Supported Browsers

- Chrome 50+
- Firefox 48+
- Edge 14+
- Safari 16+ (limited support)

### Implementation

Use the `BrowserTestNotification` class:

```php
use App\Notifications\BrowserTestNotification;

$notification = new BrowserTestNotification(
    title: 'Task Assigned',
    message: 'You have a new task',
    actions: [
        ['action' => 'open', 'title' => 'View Task'],
        ['action' => 'dismiss', 'title' => 'Dismiss'],
    ]
);

$user->notify($notification);
```

### API Reference

```php
public function toBrowserNotification($notifiable)
{
    return [
        'title' => 'Notification Title',
        'body' => 'Notification message',
        'icon' => asset('logo.png'),
        'badge' => asset('badge.png'),
        'tag' => 'notification-id',
        'requireInteraction' => true, // Keep notification visible
        'actions' => [
            ['action' => 'open', 'title' => 'Open'],
            ['action' => 'dismiss', 'title' => 'Dismiss'],
        ],
        'data' => [
            'url' => 'https://soulyeg.online/hub/dashboard',
            'customField' => 'customValue',
        ],
    ];
}
```

### Notification Actions

```php
'actions' => [
    [
        'action' => 'action_name',  // Unique identifier
        'title' => 'Button Label',   // Visible text
        'icon' => asset('icon.png'), // Optional icon
    ],
]
```

### User Permissions

Browser notifications require user permission. The frontend must request permission:

```javascript
// Request permission
if ('Notification' in window) {
    if (Notification.permission === 'granted') {
        // Already granted
    } else if (Notification.permission !== 'denied') {
        Notification.requestPermission();
    }
}
```

---

## Testing

### Test Pushbullet Notifications

```bash
# Basic test with default action
php artisan app:send-pushbullet-test-notification

# With custom action
php artisan app:send-pushbullet-test-notification --action=my_action
```

### Test Action Button Notifications

```bash
# Approval scenario
php artisan app:test-action-notifications --type=approval

# Rejection scenario
php artisan app:test-action-notifications --type=rejection

# Redirect scenario
php artisan app:test-action-notifications --type=redirect

# Alert scenario
php artisan app:test-action-notifications --type=alert
```

### Test Browser Notifications

```bash
# Basic notification
php artisan app:test-browser-notification --type=basic

# Interactive notification with actions
php artisan app:test-browser-notification --type=interactive

# Warning notification
php artisan app:test-browser-notification --type=warning

# Success notification
php artisan app:test-browser-notification --type=success

# Error notification
php artisan app:test-browser-notification --type=error
```

---

## Examples

### Example 1: User Registration Notification

```php
namespace App\Notifications;

use Illuminate\Notifications\Notification;
use App\Notifications\Channels\PushbulletChannel;

class UserRegisteredNotification extends Notification
{
    public function via($notifiable)
    {
        return [PushbulletChannel::class];
    }

    public function toPushbullet($notifiable)
    {
        return [
            'type' => 'note',
            'title' => 'Welcome to Nexus!',
            'body' => "Welcome {$notifiable->name}! Your account has been created.",
            'url' => route('dashboard'),
        ];
    }
}
```

### Example 2: Order Status with Actions

```php
namespace App\Notifications;

use App\Notifications\ActionButtonNotification;
use App\Models\Order;

class OrderStatusNotification extends ActionButtonNotification
{
    public function __construct(Order $order)
    {
        parent::__construct(
            title: "Order {$order->number} Status Update",
            message: "Your order status has been updated.",
            actions: [
                ['label' => 'View Order', 'url' => 'https://soulyeg.online/orders/'.$order->id],
                ['label' => 'Track', 'url' => 'https://soulyeg.online/track/'.$order->id],
            ],
            actionType: 'order_update'
        );
    }
}
```

### Example 3: Task Assignment with Browser Notification

```php
namespace App\Notifications;

use App\Notifications\BrowserTestNotification;
use App\Models\Task;

class TaskAssignedNotification extends BrowserTestNotification
{
    public function __construct(Task $task)
    {
        parent::__construct(
            title: 'New Task Assigned',
            message: "Task: {$task->title}",
            actions: [
                ['action' => 'view', 'title' => 'View Task'],
                ['action' => 'snooze', 'title' => 'Snooze'],
            ]
        );
    }
}
```

### Example 4: Sending from Controller

```php
namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\MyNotification;

class NotificationController extends Controller
{
    public function send()
    {
        $user = User::find(1);
        $user->notify(new MyNotification());

        return response()->json(['message' => 'Notification sent']);
    }
}
```

---

## Troubleshooting

### Pushbullet Notifications Not Arriving

**Check 1: Verify API Key**
```bash
# Check if key is set
grep PUSHBULLET_API_KEY .env
```

**Check 2: Verify Devices**
- Log in to https://www.pushbullet.com/
- Ensure you have active devices registered

**Check 3: Check Logs**
```bash
tail -f storage/logs/laravel.log
```

**Check 4: Test API Directly**
```bash
curl -H "Access-Token: YOUR_TOKEN" \
  -X POST https://api.pushbullet.com/v2/pushes \
  -H "Content-Type: application/json" \
  -d '{
    "type": "note",
    "title": "Test",
    "body": "Test message"
  }'
```

### Browser Notifications Not Showing

**Check 1: Browser Permissions**
- Allow notifications in browser settings
- Check privacy/notification settings

**Check 2: User Permission**
```javascript
console.log(Notification.permission); // Should be "granted"
```

**Check 3: Browser Support**
- Use `'Notification' in window` to check API support
- Some browsers require HTTPS

**Check 4: Service Worker**
- Ensure service worker is registered for push notifications
- Check browser DevTools > Application > Service Workers

### Notifications Appear but No Actions

**For Pushbullet:**
- Pushbullet native app may show links differently
- Use the web interface at pushbullet.com for full URL support
- All action URLs route to: `https://soulyeg.online`

**For Browser:**
- Ensure `requireInteraction: true` is set for persistent notifications
- Some browsers require user interaction for action buttons

### Permission Errors

```
"Pushbullet API key not configured"
```

**Solution:**
1. Add key to `.env`: `PUSHBULLET_API_KEY=your_key`
2. Clear config cache: `php artisan config:clear`
3. Verify key format in Pushbullet account

---

## API Keys and Security

### Pushbullet Token

- Available at: https://www.pushbullet.com/account
- Never commit to version control
- Use `.env` file for sensitive data
- Consider token rotation in production

### Environment Variables

```env
# .env
PUSHBULLET_API_KEY=o.your_token_here

# .env.example (safe version)
PUSHBULLET_API_KEY=
```

### Gitignore

Ensure `.env` is in `.gitignore`:
```
.env
.env.local
.env.*.local
```

---

## Best Practices

1. **Use Queues**: Queue notifications for better performance
   ```php
   class MyNotification extends Notification implements ShouldQueue
   {
       use Queueable;
   }
   ```

2. **Rate Limiting**: Prevent notification spam
   ```php
   // In notification class
   public function shouldSend($notifiable)
   {
       return Cache::has("send_notification_{$notifiable->id}");
   }
   ```

3. **User Preferences**: Allow users to opt-in/out
   ```php
   public function via($notifiable)
   {
       if (!$notifiable->wants_pushbullet_notifications) {
           return [];
       }
       return [PushbulletChannel::class];
   }
   ```

4. **Testing**: Always test before production
   ```bash
   php artisan app:test-action-notifications --type=approval
   php artisan app:test-browser-notification --type=interactive
   ```

5. **Error Handling**: Log failures for debugging
   ```php
   try {
       $user->notify(new MyNotification());
   } catch (Exception $e) {
       Log::error('Notification failed: '.$e->getMessage());
   }
   ```

---

## Additional Resources

- [Laravel Notifications Documentation](https://laravel.com/docs/notifications)
- [Pushbullet API Documentation](https://docs.pushbullet.com/)
- [Web Notifications API](https://developer.mozilla.org/en-US/docs/Web/API/Notifications_API)
- [Nexus Project Repository](https://github.com/your-org/nexus)

---

## Support

For issues or questions about the notification system:

1. Check this documentation
2. Review test commands output
3. Check Laravel logs: `storage/logs/laravel.log`
4. Open an issue on the project repository

---

**Last Updated:** 2026-07-03  
**Version:** 1.0.0  
**Status:** Production Ready
