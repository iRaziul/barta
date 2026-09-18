<?php

declare(strict_types=1);

namespace Larament\Barta\Drivers;

use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Str;
use Larament\Barta\Data\ResponseData;
use Larament\Barta\Exceptions\BartaException;
use Larament\Barta\Helpers\Util;
use Larament\Barta\Jobs\SendSmsJob;
use Throwable;

abstract class AbstractDriver
{
    protected array $recipients = [];

    protected string $message = '';

    protected int $timeout;

    protected int $retry;

    protected int $retryDelay;

    /**
     * Create a new driver instance.
     *
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        protected array $config = [],
    ) {
        $requestConfig = (array) config('barta.request', []);

        $this->timeout = (int) ($requestConfig['timeout'] ?? 10);
        $this->retry = (int) ($requestConfig['retry'] ?? 3);
        $this->retryDelay = (int) ($requestConfig['retry_delay'] ?? 300);
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

        try {
            return $this->sendSms();
        } catch (BartaException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new BartaException($e->getMessage(), (int) $e->getCode(), $e);
        } finally {
            $this->reset();
        }
    }

    /**
     * Queue the message for later sending.
     */
    final public function queue(?string $queue = null, ?string $connection = null): PendingDispatch
    {
        $this->validate();

        try {
            $job = new SendSmsJob(
                driver: $this->getName(),
                recipients: $this->recipients,
                message: $this->message,
            );

            $job->onQueue($queue)
                ->onConnection($connection);

            return dispatch($job);
        } finally {
            $this->reset();
        }
    }

    /**
     * Reset the driver state after sending or queueing.
     */
    final public function reset(): self
    {
        $this->recipients = [];
        $this->message = '';

        return $this;
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
     * Get the driver name.
     */
    final public function getName(): string
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
