![Barta is a Laravel package for integrating Bangladeshi SMS gateways.](.github/assets/cover.svg)

# Barta - Laravel package for integrating Bangladeshi SMS gateways

[![Latest Version on Packagist](https://img.shields.io/packagist/v/larament/barta.svg?style=flat-square)](https://packagist.org/packages/larament/barta)
[![Total Downloads](https://img.shields.io/packagist/dt/larament/barta.svg?style=flat-square)](https://packagist.org/packages/larament/barta)
[![Run Tests](https://github.com/iRaziul/barta/actions/workflows/run-tests.yml/badge.svg)](https://github.com/iRaziul/barta/actions/workflows/run-tests.yml)
[![PHPStan](https://github.com/iRaziul/barta/actions/workflows/phpstan.yml/badge.svg)](https://github.com/iRaziul/barta/actions/workflows/phpstan.yml)
[![Pint](https://github.com/iRaziul/barta/actions/workflows/fix-php-code-style-issues.yml/badge.svg)](https://github.com/iRaziul/barta/actions/workflows/fix-php-code-style-issues.yml)
[![License](https://img.shields.io/github/license/iRaziul/barta.svg?style=flat-square)](https://github.com/iRaziul/barta/blob/main/LICENSE.md)

Barta provides a clean, expressive way to send SMS from Laravel applications using Bangladeshi gateways. It gives you a single API for transactional messages, OTP delivery, alerts, queued sends, and notification-based messaging.

The package is designed to feel native in Laravel projects while keeping gateway-specific concerns isolated to drivers and configuration.

## Key Features

- **Multiple Gateways** — Seamlessly switch between SMS providers
- **Bulk SMS** — Send to multiple recipients in a single call
- **Queue Support** — Dispatch SMS to background jobs
- **Laravel Notifications** — Native integration with Laravel's notification system
- **BD Phone Formatting** — Automatic phone number normalization to `8801XXXXXXXXX` format
- **Extensible** — Create custom drivers for any SMS gateway

## Supported Gateways

- `log` driver for local development and testing
- Most Bangladeshi SMS gateways are supported

Full gateway list and setup instructions are available at [barta.larament.com/gateways](https://barta.larament.com/gateways/).

## Requirements

- PHP 8.2 or higher
- Laravel 11 to 13

## Installation

Install via Composer:

```bash
composer require larament/barta
```

Optionally, you can run the install command:

```bash
php artisan barta:install
```

## Quick Start

Set your default driver in `.env`:

```env
BARTA_DRIVER=log
```

Then send your first message:

```php
use Larament\Barta\Facades\Barta;

Barta::to('01712345678')
    ->message('Your OTP is 1234')
    ->send();
```

Send through a specific gateway:

```php
Barta::driver('DRIVER_NAME')
    ->to('01712345678')
    ->message('Hello from Larament Barta')
    ->send();
```

Queue a message for background delivery:

```php
Barta::to('01712345678')
    ->message('Queued message')
    ->queue();
```

Send to multiple recipients:

```php
Barta::to(['01712345678', '01812345678'])
    ->message('Hello everyone!')
    ->send();
```

> [!TIP]
> Use the `log` driver during local development and automated tests to avoid sending real SMS.

## Laravel Notifications

Barta integrates with Laravel's notification system through the `barta` channel:

```php
use Illuminate\Notifications\Notification;
use Larament\Barta\Notifications\BartaMessage;

class OrderShipped extends Notification
{
    public function via(object $notifiable): array
    {
        return ['barta'];
    }

    public function toBarta(object $notifiable): BartaMessage
    {
        return new BartaMessage('Your order has been shipped!');
    }
}
```

Route notifications to a phone number on your notifiable model:

```php
public function routeNotificationForBarta($notification): string
{
    return $this->phone;
}
```

Learn more at [barta.larament.com/advanced/notifications](https://barta.larament.com/advanced/notifications/).

## Phone Number Formatting

Barta automatically normalizes Bangladeshi mobile numbers into `8801XXXXXXXXX` format.

| Input            | Normalized      |
| ---------------- | --------------- |
| `01712345678`    | `8801712345678` |
| `+8801712345678` | `8801712345678` |

## Documentation

The full documentation lives at [barta.larament.com](https://barta.larament.com).

- Getting started: [barta.larament.com/usage/basic-usage](https://barta.larament.com/usage/basic-usage/)
- Gateway configuration: [barta.larament.com/gateways](https://barta.larament.com/gateways/)
- Notifications: [barta.larament.com/advanced/notifications](https://barta.larament.com/advanced/notifications/)

## Testing

```bash
composer test          # Run tests
composer test-coverage # With coverage
composer analyse       # Static analysis
```

Use the `log` driver during testing to avoid sending real SMS.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for recent changes.

## Contributing

See [CONTRIBUTING.md](.github/CONTRIBUTING.md) for details.

## Security

Report vulnerabilities via our [security policy](.github/SECURITY.md).

## Credits

- [Raziul Islam](https://github.com/iRaziul)
- [All Contributors](../../contributors)

## License

MIT License. See [LICENSE.md](LICENSE.md).

---

<div align="center">
<p>Made with ❤️ for the Bangladeshi Laravel Community</p>
<p>
<a href="https://github.com/iRaziul/barta/stargazers">⭐ Star us on GitHub</a>
</p>
</div>
