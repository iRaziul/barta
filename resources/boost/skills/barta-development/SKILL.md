---
name: barta-development
description: Integrate Barta package for sending SMS via Bangladeshi gateways.
---

# Barta Development

## When to use this skill

Use this skill when you need to send SMS, integrate SMS-based notifications, or configure SMS gateways in a Laravel application using the Barta package.

## Core Usage

### Sending SMS immediately

```php
use Larament\Barta\Facades\Barta;

$response = Barta::to('01700000000')
    ->message('Hello from Barta!')
    ->send();

if ($response->success) {
    // SMS sent
}
```

### Queuing SMS

```php
Barta::to(['01700000000', '01800000000'])
    ->message('Bulk update')
    ->queue();
```

### Using a specific driver

```php
Barta::driver('ssl')
    ->to('01900000000')
    ->message('Direct SSL message')
    ->send();
```

## Notification Integration

Define the `toBarta` method in your notification class:

```php
use Larament\Barta\Notifications\BartaMessage;

public function toBarta($notifiable)
{
    return new BartaMessage("Your security code: 987654");
}

// Optional: specify driver per notification
public function bartaDriver()
{
    return 'mimsms';
}
```

## Standardized Phone Numbers

Barta formats numbers automatically. All these inputs result in `8801700000000`:
- `01700000000`
- `8801700000000`
- `+8801700000000`
- `017-00000000`

## Configuration

Publish config: `php artisan barta:install`

Key settings in `config/barta.php`:
- `default`: The driver used when `driver()` is not called.
- `drivers`: Gateway-specific credentials (API keys, tokens, sender IDs).
- `request`: Global timeout and retry settings.
