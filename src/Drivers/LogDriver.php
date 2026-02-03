<?php

declare(strict_types=1);

namespace Larament\Barta\Drivers;

use Illuminate\Support\Facades\Log;
use Larament\Barta\Data\ResponseData;

final class LogDriver extends AbstractDriver
{
    protected function validateConfig(): void
    {
        // no config needed
    }

    protected function sendSms(): ResponseData
    {
        Log::info('[BARTA] Message sent', [
            'recipients' => $this->recipients,
            'message' => $this->message,
        ]);

        return new ResponseData(
            success: true,
            data: [
                'message' => 'Message sent successfully',
            ],
        );
    }
}
