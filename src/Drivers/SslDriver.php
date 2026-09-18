<?php

declare(strict_types=1);

namespace Larament\Barta\Drivers;

use Illuminate\Support\Facades\Http;
use Larament\Barta\Data\ResponseData;
use Larament\Barta\Exceptions\BartaException;

final class SslDriver extends AbstractDriver
{
    private string $baseUrl = 'https://smsplus.sslwireless.com/api/v3';

    protected function sendSms(): ResponseData
    {
        $endpoint = count($this->recipients) > 1 ? '/send-sms/bulk' : '/send-sms';

        $response = Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->retry($this->retry, $this->retryDelay, throw: false)
            ->acceptJson()
            ->asJson()
            ->post($endpoint, [
                'api_token' => $this->config['api_token'],
                'sid' => $this->config['sender_id'],
                'msisdn' => implode(',', $this->recipients),
                'sms' => $this->message,
                'csms_id' => $this->config['csms_id'] ?? uniqid('barta_'),
            ]);

        if ($response->failed()) {
            throw new BartaException('SSL Wireless API error: '.($response->body() ?: 'HTTP request failed with status '.$response->status()));
        }

        $data = $response->json();
        if (! is_array($data)) {
            throw new BartaException('SSL Wireless API error: Invalid response received');
        }

        if (($data['status'] ?? '') === 'FAILED' || isset($data['error'])) {
            throw new BartaException((string) ($data['error'] ?? $data['status_message'] ?? 'SSL Wireless API error'));
        }

        return new ResponseData(
            success: ($data['status'] ?? '') === 'SUCCESS',
            data: $data,
        );
    }

    protected function validateConfig(): void
    {
        if (empty($this->config['api_token'])) {
            throw new BartaException('Please set api_token for SSL Wireless in config/barta.php.');
        }

        if (empty($this->config['sender_id'])) {
            throw new BartaException('Please set sender_id for SSL Wireless in config/barta.php.');
        }
    }
}
