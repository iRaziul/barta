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
            ->retry($this->retry, $this->retryDelay)
            ->acceptJson()
            ->asJson()
            ->post($endpoint, [
                'api_token' => $this->config['api_token'],
                'sid' => $this->config['sender_id'],
                'msisdn' => implode(',', $this->recipients),
                'sms' => $this->message,
                'csms_id' => $this->config['csms_id'] ?? uniqid('barta_'),
            ])
            ->json();

        if (($response['status'] ?? '') === 'FAILED' || isset($response['error'])) {
            throw new BartaException($response['error'] ?? $response['status_message'] ?? 'SSL Wireless API error');
        }

        return new ResponseData(
            success: ($response['status'] ?? '') === 'SUCCESS',
            data: $response,
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
