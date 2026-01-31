<div align="center">
<a href="https://github.com/iRaziul/barta">
<img src="https://raw.githubusercontent.com/iRaziul/barta/main/.github/assets/banner.svg" alt="Barta Banner">
</a>
<br>
<h1>Barta (কথা)</h1>
<p>
    <strong>The unified interface for every Bangladeshi SMS gateway.</strong>
</p>
<p>
Barta is a clean, expressive Laravel package designed to integrate popular Bangladeshi SMS gateways seamlessly. Whether you're sending OTPs, marketing alerts, or notifications, Barta makes the process as simple as a conversation.
</p>

[![Latest Version on Packagist](https://img.shields.io/packagist/v/larament/barta.svg?style=flat-square)](https://packagist.org/packages/larament/barta)
[![Total Downloads](https://img.shields.io/packagist/dt/larament/barta.svg?style=flat-square)](https://packagist.org/packages/larament/barta)
[![Run Tests](https://github.com/iRaziul/barta/actions/workflows/run-tests.yml/badge.svg)](https://github.com/iRaziul/barta/actions/workflows/run-tests.yml)
[![PHPStan](https://github.com/iRaziul/barta/actions/workflows/phpstan.yml/badge.svg)](https://github.com/iRaziul/barta/actions/workflows/phpstan.yml)
[![Pint](https://github.com/iRaziul/barta/actions/workflows/fix-php-code-style-issues.yml/badge.svg)](https://github.com/iRaziul/barta/actions/workflows/fix-php-code-style-issues.yml)
[![License](https://img.shields.io/github/license/iRaziul/barta.svg?style=flat-square)](https://github.com/iRaziul/barta/blob/main/LICENSE.md)

</div>

---

## ✨ Features

- **Multiple Gateways** — Seamlessly switch between SMS providers
- **Bulk SMS** — Send to multiple recipients in a single call
- **Queue Support** — Dispatch SMS to background jobs
- **Laravel Notifications** — Native integration with Laravel's notification system
- **BD Phone Formatting** — Automatic phone number normalization to `8801XXXXXXXXX` format
- **Extensible** — Create custom drivers for any SMS gateway

## 📦 Supported Gateways

| Gateway                                 | Driver         | Status       |
| --------------------------------------- | -------------- | ------------ |
| Log (Development)                       | `log`          | ✅ Built-in  |
| [eSMS](https://esms.com.bd)             | `esms`         | ✅ Supported |
| [MimSMS](https://mimsms.com)            | `mimsms`       | ✅ Supported |
| [SSL Wireless](https://sslwireless.com) | `ssl`          | ✅ Supported |
| Grameenphone                            | `grameenphone` | ✅ Supported |
| Banglalink                              | `banglalink`   | ✅ Supported |
| Robi                                    | `robi`         | ✅ Supported |
| [Infobip](https://infobip.com)          | `infobip`      | ✅ Supported |
| [ADN SMS](https://portal.adnsms.com)    | `adnsms`       | ✅ Supported |
| [Alpha SMS](https://sms.net.bd)         | `alphasms`     | ✅ Supported |
| [GreenWeb](https://greenweb.com.bd)     | `greenweb`     | ✅ Supported |
| [BulkSMS BD](https://bulksmsbd.net)     | `bulksms`      | ✅ Supported |
| [ElitBuzz](https://elitbuzz.com)        | `elitbuzz`     | ✅ Supported |
| [SMS NOC](https://smsnoc.com)           | `smsnoc`       | ✅ Supported |

> **Want more gateways?** [Request a gateway](https://github.com/iRaziul/barta/issues) or [contribute a driver](#-creating-custom-drivers).

---

## 🚀 Installation

Install via Composer:

```bash
composer require larament/barta
```

Run the install command (publishes config + optional setup):

```bash
php artisan barta:install
```

---

## ⚙️ Configuration

Set your default driver and add credentials to `.env`:

```env
BARTA_DRIVER=ssl

# Example: SSL Wireless
BARTA_SSL_TOKEN=your-api-token
BARTA_SSL_SENDER_ID=your-sender-id
```

Each gateway requires different credentials. See [`config/barta.php`](config/barta.php) for all available options and environment variable names.

> 💡 **Tip:** Use `log` driver during development to avoid sending real SMS.

---

## 🛠 Usage

### Basic Usage

```php
use Larament\Barta\Facades\Barta;

// Send SMS using the default driver
Barta::to('01712345678')
    ->message('Your OTP is 1234')
    ->send();
```

### Specifying a Driver

```php
// Use a specific gateway
Barta::driver('esms')
    ->to('01712345678')
    ->message('Hello from eSMS!')
    ->send();
```

### Bulk SMS

Send to multiple recipients in a single API call:

```php
Barta::to(['01712345678', '01812345678', '01912345678'])
    ->message('Hello everyone!')
    ->send();
```

### Getting the Response

```php
$response = Barta::to('01712345678')
    ->message('Hello!')
    ->send();

// Check if successful
if ($response->success) {
    // Access raw API response
    $data = $response->data;
}

// Convert to array
$array = $response->toArray();
// ['success' => true, 'data' => [...], 'errors' => []]
```

---

## ⏱️ Queue Support

Dispatch SMS to your queue for background processing:

```php
// Queue with default settings
Barta::to('01712345678')
    ->message('This will be queued')
    ->queue();

// Specify queue name
Barta::to('01712345678')
    ->message('Priority message')
    ->queue('sms');

// Specify connection and queue
Barta::to('01712345678')
    ->message('Redis queue')
    ->queue('sms', 'redis');

// Bulk queued SMS
Barta::to(['01712345678', '01812345678'])
    ->message('Queued bulk message')
    ->queue();
```

---

## 🔔 Laravel Notifications

Barta integrates seamlessly with Laravel's notification system.

### 1. Create a Notification

```php
use Illuminate\Notifications\Notification;
use Larament\Barta\Notifications\BartaMessage;

class OrderShipped extends Notification
{
    public function via($notifiable): array
    {
        return ['barta'];
    }

    public function toBarta($notifiable): BartaMessage
    {
        return new BartaMessage(
            "Hi {$notifiable->name}, your order has been shipped!"
        );
    }
}
```

### 2. Add Route to Your Model

```php
// app/Models/User.php
public function routeNotificationForBarta($notification): string
{
    return $this->phone;
}
```

### 3. Send the Notification

```php
$user->notify(new OrderShipped());
```

### Using a Specific Driver

Override the default driver per notification:

```php
class OrderShipped extends Notification
{
    public function toBarta($notifiable): BartaMessage
    {
        return new BartaMessage('Your order shipped!');
    }

    // Optional: specify driver for this notification
    public function bartaDriver(): string
    {
        return 'mimsms';
    }
}
```

---

## 🔧 Creating Custom Drivers

Extend Barta to support any SMS gateway.

### 1. Create the Driver Class

```php
namespace App\Sms\Drivers;

use Illuminate\Support\Facades\Http;
use Larament\Barta\Drivers\AbstractDriver;
use Larament\Barta\Data\ResponseData;
use Larament\Barta\Exceptions\BartaException;

class CustomGatewayDriver extends AbstractDriver
{
    public function send(): ResponseData
    {
        $this->validate();

        $response = Http::withToken($this->config['api_token'])
            ->post('https://api.customgateway.com/sms', [
                'to' => implode(',', $this->recipients),
                'message' => $this->message,
                'sender' => $this->config['sender_id'],
            ])
            ->json();

        if ($response['status'] !== 'success') {
            throw new BartaException($response['error']);
        }

        return new ResponseData(
            success: true,
            data: $response,
        );
    }
}
```

### 2. Register the Driver

```php
// app/Providers/AppServiceProvider.php
use App\Sms\Drivers\CustomGatewayDriver;
use Larament\Barta\Facades\Barta;

public function boot(): void
{
    Barta::extend('custom', function ($app) {
        return new CustomGatewayDriver(config('barta.drivers.custom'));
    });
}
```

### 3. Add Configuration

```php
// config/barta.php
'drivers' => [
    // ...existing drivers...

    'custom' => [
        'api_token' => env('CUSTOM_SMS_TOKEN'),
        'sender_id' => env('CUSTOM_SMS_SENDER_ID'),
    ],
],
```

### 4. Use Your Driver

```php
Barta::driver('custom')
    ->to('01712345678')
    ->message('Hello from custom gateway!')
    ->send();
```

---

## 📞 Phone Number Formatting

Barta automatically normalizes Bangladeshi phone numbers to the `8801XXXXXXXXX` format:

| Input            | Output          |
| ---------------- | --------------- |
| `1712345678`     | `8801712345678` |
| `01712345678`    | `8801712345678` |
| `8801712345678`  | `8801712345678` |
| `+8801712345678` | `8801712345678` |
| `017-1234-5678`  | `8801712345678` |

Invalid numbers will throw a `BartaException`.

---

## 🧪 Testing

```bash
# Run tests
composer test

# Run tests with coverage
composer test-coverage

# Run static analysis
composer analyse
```

### Testing in Your Application

Use the `log` driver during testing to avoid sending real SMS:

```php
// phpunit.xml or .env.testing
BARTA_DEFAULT_DRIVER=log
```

---

## Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING.md](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please see our [security policy](.github/SECURITY.md) for details on how to report security vulnerabilities.

## Credits

- [Raziul Islam](https://github.com/iRaziul)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [LICENSE.md](LICENSE.md) for more information.

---

<div align="center">
<p>Made with ❤️ for the Bangladeshi Laravel Community</p>
<p>
<a href="https://github.com/iRaziul/barta/stargazers">⭐ Star us on GitHub</a>
</p>
</div>
