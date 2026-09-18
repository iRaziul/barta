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
            ->retry($this->retry, $this->retryDelay, throw: false)
            ->acceptJson()
            ->post('/sms/send', [
                'recipient' => implode(',', $this->recipients),
                'sender_id' => $this->config['sender_id'],
                'type' => 'plain',
                'message' => $this->message,
            ]);

        if ($response->failed()) {
            throw new BartaException('ESMS API error: '.($response->body() ?: 'HTTP request failed with status '.$response->status()));
        }

        $data = $response->json();
        if (! is_array($data)) {
            throw new BartaException('ESMS API error: Invalid response received');
        }

        if (($data['status'] ?? '') === 'error') {
            throw new BartaException((string) ($data['message'] ?? 'ESMS API error'));
        }

        return new ResponseData(
            success: ($data['status'] ?? '') === 'success',
            data: $data,
        );
    }

    protected function validateConfig(): void
    {
        if (empty($this->config['sender_id'])) {
            throw new BartaException('Please set sender_id for ESMS in config/barta.php.');
        }

        if (empty($this->config['api_token'])) {
            throw new BartaException('Please set api_token for ESMS in config/barta.php.');
        }
    }
}
