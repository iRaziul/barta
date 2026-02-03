<?php

declare(strict_types=1);

namespace Larament\Barta\Drivers;

use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Str;
use Larament\Barta\Data\ResponseData;
use Larament\Barta\Exceptions\BartaException;
use Larament\Barta\Helpers\Util;
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
        [
            'timeout' => $this->timeout,
            'retry' => $this->retry,
            'retry_delay' => $this->retryDelay,
        ] = config('barta.request');
    }

    /**
     * Driver-specific send implementation.
     */
    abstract protected function sendSms(): ResponseData;

    /**
     * Driver-specific config validation.
     */
    abstract protected function validateConfig(): void;

    /**
     * Send the message.
     */
    final public function send(): ResponseData
    {
        $this->validate();

        return $this->sendSms();
    }

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

        $job->onQueue($queue)
            ->onConnection($connection);

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
            fn (string $number) => Util::formatPhoneNumber($number),
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
    private function getDriverName(): string
    {
        $className = class_basename(static::class);

        return Str::of($className)
            ->before('Driver')
            ->lower()
            ->toString();
    }

    /**
     * Validate the recipient, message & config.
     */
    private function validate(): void
    {
        if (empty($this->recipients)) {
            throw BartaException::missingRecipient();
        }

        if (empty($this->message)) {
            throw BartaException::missingMessage();
        }

        $this->validateConfig();
    }
}
