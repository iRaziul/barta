<?php

declare(strict_types=1);

namespace Larament\Barta\Drivers;

use Illuminate\Support\Facades\Http;
use Larament\Barta\Data\ResponseData;
use Larament\Barta\Exceptions\BartaException;

final class EsmsDriver extends AbstractDriver
{
    private string $baseUrl = 'https://login.esms.com.bd/api/v3';

    protected function sendSms(): ResponseData
    {
        $response = Http::baseUrl($this->baseUrl)
            ->withToken($this->config['api_token'])
            ->timeout($this->timeout)
            ->retry($this->retry, $this->retryDelay)
            ->acceptJson()
            ->post('/sms/send', [
                'recipient' => implode(',', $this->recipients),
                'sender_id' => $this->config['sender_id'],
                'type' => 'plain',
                'message' => $this->message,
            ])
            ->json();

        if ($response['status'] === 'error') {
            throw new BartaException($response['message']);
        }

        return new ResponseData(
            success: $response['status'] === 'success',
            data: $response,
        );
    }

    protected function validateConfig(): void
    {
        if (! $this->config['sender_id']) {
            throw new BartaException('Please set sender_id for ESMS in config/barta.php.');
        }

        if (! $this->config['api_token']) {
            throw new BartaException('Please set api_token for ESMS in config/barta.php.');
        }
    }
}
