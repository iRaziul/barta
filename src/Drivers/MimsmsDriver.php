<?php

declare(strict_types=1);

namespace Larament\Barta\Drivers;

use Illuminate\Support\Facades\Http;
use Larament\Barta\Data\ResponseData;
use Larament\Barta\Exceptions\BartaException;

final class MimsmsDriver extends AbstractDriver
{
    private string $baseUrl = 'https://api.mimsms.com/api/SmsSending';

    protected function sendSms(): ResponseData
    {
        $response = Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->retry($this->retry, $this->retryDelay)
            ->asJson()
            ->post('/Send', [
                'UserName' => $this->config['username'],
                'ApiKey' => $this->config['api_key'],
                'SenderName' => $this->config['sender_id'],
                'TransactionType' => 'T',
                'CampaignId' => 'null',
                'MobileNumber' => implode(',', $this->recipients),
                'Message' => $this->message,
            ])
            ->json();

        if ((int) $response['statusCode'] !== 200) {
            throw new BartaException($response['responseResult']);
        }

        return new ResponseData(
            success: true,
            data: $response,
        );
    }

    protected function validateConfig(): void
    {
        if (! $this->config['username']) {
            throw new BartaException('Please set username for Mimsms in config/barta.php.');
        }

        if (! $this->config['api_key']) {
            throw new BartaException('Please set api_key for Mimsms in config/barta.php.');
        }

        if (! $this->config['sender_id']) {
            throw new BartaException('Please set sender_id for Mimsms in config/barta.php.');
        }
    }
}
