<?php

declare(strict_types=1);

namespace Larament\Barta\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Larament\Barta\BartaManager;

class SendSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param  array<string>  $recipients
     */
    public function __construct(
        public string $driver,
        public array $recipients,
        public string $message,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(BartaManager $manager): void
    {
        $manager
            ->driver($this->driver)
            ->to($this->recipients)
            ->message($this->message)
            ->send();
    }
}
