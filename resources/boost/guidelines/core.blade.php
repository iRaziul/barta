## Barta

Laravel package for sending SMS via various Bangladeshi SMS gateways with a unified API.

### Facade API

- `Barta::to($numbers)` - Set recipient(s). Supports string or array of numbers.
- `Barta::message($text)` - Set the SMS content.
- `Barta::send()` - Send the SMS immediately. Returns `ResponseData`.
- `Barta::queue($queue = null, $connection = null)` - Queue the SMS for background sending.
- `Barta::driver($name)` - Switch to a specific driver for the current request.

### Drivers

Barta supports many BD gateways:
- `esms`, `mimsms`, `ssl`, `grameenphone`, `banglalink`, `robi`, `adnsms`, `alphasms`, `greenweb`, `bulksms`, `elitbuzz`, `smsnoc`, `infobip`.
- `log` - Logs SMS to application logs (useful for local development).
- `fallback` - Tries a list of drivers until one succeeds.
- `roundrobin` - Cycles through drivers to distribute load.

### Notifications

Use the `barta` channel in your Laravel Notifications:

```php
public function via($notifiable)
{
    return ['barta'];
}

public function toBarta($notifiable)
{
    return (new BartaMessage)
        ->content("Your OTP is: 123456");
}
```

### Formatting

Barta automatically standardizes Bangladeshi numbers to `8801XXXXXXXXX` format. Invalid numbers throw a `BartaException`.

### Commands

@verbatim
<code-snippet name="Install Barta" lang="shell">
php artisan barta:install
</code-snippet>
@endverbatim

### Best Practices

- Use the `log` driver for local development to avoid wasting credits.
- Always use `8801...` format or let Barta handle it; don't rely on users entering the correct prefix.
- Use `queue()` for bulk SMS or non-critical messages to avoid blocking the request cycle.
- Configure `fallback` driver for critical alerts (e.g., OTPs).
