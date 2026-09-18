<?php

declare(strict_types=1);

namespace Larament\Barta\Drivers;

use Illuminate\Support\Facades\Http;
use Larament\Barta\Data\ResponseData;
use Larament\Barta\Exceptions\BartaException;

final class BulksmsDriver extends AbstractDriver
{
    private string $baseUrl = 'https://bulksmsbd.net/api';

    protected function sendSms(): ResponseData
    {
        $response = Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->retry($this->retry, $this->retryDelay, throw: false)
            ->get('/smsapi', [
                'api_key' => $this->config['api_key'],
                'senderid' => $this->config['sender_id'],
                'type' => 'text',
                'number' => implode(',', $this->recipients),
                'message' => $this->message,
            ]);

        if ($response->failed()) {
            throw new BartaException('BulkSMS BD API error: '.($response->body() ?: 'HTTP request failed with status '.$response->status()));
        }

        $data = $response->json();
        if (! is_array($data)) {
            throw new BartaException('BulkSMS BD API error: Invalid response received');
        }

        if (($data['response_code'] ?? 0) !== 202) {
            throw new BartaException((string) ($data['error_message'] ?? 'BulkSMS BD API error'));
        }

        return new ResponseData(success: true, data: $data);
    }

    protected function fetchBalance(): float
    {
        $response = Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->retry($this->retry, $this->retryDelay, throw: false)
            ->acceptJson()
            ->get('/getBalanceApi', [
                'api_key' => $this->config['api_key'],
            ]);

        if ($response->failed()) {
            throw new BartaException('BulkSMS BD API error: '.($response->body() ?: 'HTTP request failed with status '.$response->status()));
        }

        $data = $response->json();
        if (! is_array($data)) {
            throw new BartaException('BulkSMS BD API error: Invalid response received');
        }

        if (isset($data['error_message'])) {
            throw new BartaException((string) $data['error_message']);
        }

        return (float) ($data['balance'] ?? $data['Balance'] ?? 0.0);
    }

    protected function validateConfig(): void
    {
        if (empty($this->config['api_key'])) {
            throw new BartaException('Please set api_key for BulkSMS in config/barta.php.');
        }
        if (empty($this->config['sender_id'])) {
            throw new BartaException('Please set sender_id for BulkSMS in config/barta.php.');
        }
    }
}
