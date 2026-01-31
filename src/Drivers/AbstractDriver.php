<?php

declare(strict_types=1);

namespace Larament\Barta\Drivers;

use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Str;
use Larament\Barta\Data\ResponseData;
use Larament\Barta\Exceptions\BartaException;
use Larament\Barta\Jobs\SendSmsJob;

abstract class AbstractDriver
{
    protected array $recipients = [];

    protected string $message = '';

    protected int $timeout;

    protected int $retry;

    protected int $retryDelay;

    /**
     * Create a new driver instance.
     */
    public function __construct(
        protected array $config = [],
    ) {
        $this->timeout = config()->integer('barta.request.timeout');
        $this->retry = config()->integer('barta.request.retry');
        $this->retryDelay = config()->integer('barta.request.retry_delay');
    }

    /**
     * Send the message
     */
    abstract public function send(): ResponseData;

    /**
     * Queue the message for later sending.
     */
    final public function queue(?string $queue = null, ?string $connection = null): PendingDispatch
    {
        $this->validate();

        $job = new SendSmsJob(
            driver: $this->getDriverName(),
            recipients: $this->recipients,
            message: $this->message,
        );

        if ($queue) {
            $job->onQueue($queue);
        }

        if ($connection) {
            $job->onConnection($connection);
        }

        return dispatch($job);
    }

    /**
     * Set the recipient number(s)
     *
     * @param  string|array<string>  $numbers
     */
    final public function to(string|array $numbers): self
    {
        $this->recipients = array_map(
            fn (string $number) => $this->formatPhoneNumber($number),
            is_array($numbers) ? $numbers : [$numbers]
        );

        return $this;
    }

    /**
     * Set the message content
     */
    final public function message(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get the driver name for queue purposes.
     */
    protected function getDriverName(): string
    {
        $className = class_basename(static::class);

        return Str::of($className)
            ->before('Driver')
            ->lower()
            ->toString();
    }

    /**
     * Standardizes BD phone numbers to 8801XXXXXXXXX format.
     */
    protected function formatPhoneNumber(string $number): string
    {
        $phone = Str::of($number)
            ->replaceMatches('/\D/', '')
            ->ltrim('88')
            ->ltrim('0')
            ->prepend('880')
            ->toString();

        if (! preg_match('/^8801[3-9][0-9]{8}$/', $phone)) {
            throw BartaException::invalidNumber($number);
        }

        return $phone;
    }

    /**
     * Validate the recipient and message
     */
    protected function validate(): void
    {
        if (empty($this->recipients)) {
            throw BartaException::missingRecipient();
        }

        if (empty($this->message)) {
            throw BartaException::missingMessage();
        }
    }
}
