# Nexus Notifications - Quick Reference

## Quick Start

### Setup (One-time)

```bash
# 1. Add API key to .env
echo "PUSHBULLET_API_KEY=o.bprB3OR1IITBW3nOKY37IoiPGwL64euR" >> .env

# 2. Done! Ready to use
```

---

## Common Tasks

### Send a Pushbullet Notification

```php
use App\Notifications\TestPushbulletNotification;

$user->notify(new TestPushbulletNotification('my_action'));
```

**Command:**
```bash
php artisan app:send-pushbullet-test-notification --action=hedra
```

---

### Send Action Button Notification

```php
use App\Notifications\ActionButtonNotification;

$notification = new ActionButtonNotification(
    title: 'Approval Request',
    message: 'Review and approve the document',
    actions: [
        ['label' => 'Approve', 'url' => 'https://app.com/approve'],
        ['label' => 'Reject', 'url' => 'https://app.com/reject'],
    ],
    actionType: 'approval'
);

$user->notify($notification);
```

**Command:**
```bash
php artisan app:test-action-notifications --type=approval
php artisan app:test-action-notifications --type=rejection
php artisan app:test-action-notifications --type=redirect
php artisan app:test-action-notifications --type=alert
```

---

### Send Browser Notification

```php
use App\Notifications\BrowserTestNotification;

$notification = new BrowserTestNotification(
    title: 'Task Assigned',
    message: 'You have a new task',
    actions: [
        ['action' => 'open', 'title' => 'View'],
        ['action' => 'dismiss', 'title' => 'Dismiss'],
    ]
);

$user->notify($notification);
```

**Command:**
```bash
php artisan app:test-browser-notification --type=basic
php artisan app:test-browser-notification --type=interactive
php artisan app:test-browser-notification --type=warning
php artisan app:test-browser-notification --type=success
php artisan app:test-browser-notification --type=error
```

---

## Test Commands

### Pushbullet

```bash
# Default test with 'hedra' action
php artisan app:send-pushbullet-test-notification

# Custom action
php artisan app:send-pushbullet-test-notification --action=my_action
```

### Action Buttons

```bash
php artisan app:test-action-notifications --type=approval
php artisan app:test-action-notifications --type=rejection
php artisan app:test-action-notifications --type=redirect
php artisan app:test-action-notifications --type=alert
```

### Browser Notifications

```bash
php artisan app:test-browser-notification --type=basic
php artisan app:test-browser-notification --type=interactive
php artisan app:test-browser-notification --type=warning
php artisan app:test-browser-notification --type=success
php artisan app:test-browser-notification --type=error
```

---

## File Locations

| Component | Path |
|-----------|------|
| Pushbullet Channel | `app/Notifications/Channels/PushbulletChannel.php` |
| Browser Channel | `app/Notifications/Channels/BrowserNotificationChannel.php` |
| Test Notification | `app/Notifications/TestPushbulletNotification.php` |
| Action Notification | `app/Notifications/ActionButtonNotification.php` |
| Browser Notification | `app/Notifications/BrowserTestNotification.php` |
| Pushbullet Test Command | `app/Console/Commands/SendPushbulletTestNotification.php` |
| Action Test Command | `app/Console/Commands/TestActionNotifications.php` |
| Browser Test Command | `app/Console/Commands/TestBrowserNotification.php` |
| Configuration | `config/services.php` |
| Full Docs | `NOTIFICATION_SYSTEM.md` |
| Domain | `https://soulyeg.online` |

---

## Notification Classes

### TestPushbulletNotification

Basic Pushbullet notification with action support.

```php
new TestPushbulletNotification('action_name')
```

### ActionButtonNotification

Pushbullet notification with action buttons.

```php
new ActionButtonNotification(
    title: 'Title',
    message: 'Message',
    actions: [['label' => 'Action', 'url' => 'url']],
    actionType: 'approval'
)
```

### BrowserTestNotification

Browser native notification.

```php
new BrowserTestNotification(
    title: 'Title',
    message: 'Message',
    actions: [['action' => 'id', 'title' => 'Label']]
)
```

---

## Supported Channels

| Channel | Status | Features |
|---------|--------|----------|
| Pushbullet | ✅ Active | Send to all devices, action URLs |
| Browser | ✅ Active | Native notifications, action buttons |
| Database | ✅ Available | Store for later retrieval |

---

## Configuration

### .env

```env
PUSHBULLET_API_KEY=o.bprB3OR1IITBW3nOKY37IoiPGwL64euR
```

### config/services.php

```php
'pushbullet' => [
    'key' => env('PUSHBULLET_API_KEY'),
],
```

---

## Troubleshooting

### Notifications not arriving?

```bash
# 1. Check API key is set
grep PUSHBULLET_API_KEY .env

# 2. Check logs
tail -f storage/logs/laravel.log

# 3. Verify devices registered
# Visit: https://www.pushbullet.com/

# 4. Test API directly
curl -H "Access-Token: YOUR_TOKEN" \
  -X POST https://api.pushbullet.com/v2/pushes \
  -H "Content-Type: application/json" \
  -d '{"type":"note","title":"Test","body":"Test"}'
```

### Browser notifications not showing?

```bash
# 1. Check browser permission
# Settings > Privacy > Notifications > Allow

# 2. Check browser console for errors
# DevTools > Console

# 3. Ensure HTTPS (some browsers require it)

# 4. Check Service Worker
# DevTools > Application > Service Workers
```

---

## Usage Pattern

```php
// 1. Choose a notification class
use App\Notifications\TestPushbulletNotification;

// 2. Create instance with parameters
$notification = new TestPushbulletNotification('hedra');

// 3. Send to user
$user->notify($notification);

// OR send to multiple users
User::all()->each(fn ($user) => $user->notify($notification));
```

---

## Best Practices

✅ **Do:**
- Queue notifications for performance
- Test before production
- Allow user opt-in/out
- Log errors
- Use `.env` for secrets

❌ **Don't:**
- Commit API keys
- Send duplicate notifications
- Ignore permission errors
- Use synchronous sending at scale

---

## Learn More

- Full documentation: [NOTIFICATION_SYSTEM.md](NOTIFICATION_SYSTEM.md)
- [Laravel Notifications](https://laravel.com/docs/notifications)
- [Pushbullet API](https://docs.pushbullet.com/)
- [Web Notifications API](https://developer.mozilla.org/en-US/docs/Web/API/Notifications_API)

---

**Last Updated:** 2026-07-03  
**Status:** ✅ Production Ready
