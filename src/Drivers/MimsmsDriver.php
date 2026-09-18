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
            ->retry($this->retry, $this->retryDelay, throw: false)
            ->asJson()
            ->post('/SMS', [
                'UserName' => $this->config['username'],
                'ApiKey' => $this->config['api_key'],
                'SenderName' => $this->config['sender_id'],
                'TransactionType' => 'T',
                'CampaignId' => 'null',
                'MobileNumber' => implode(',', $this->recipients),
                'Message' => $this->message,
            ]);

        if ($response->failed()) {
            throw new BartaException('Mimsms API error: '.($response->body() ?: 'HTTP request failed with status '.$response->status()));
        }

        $data = $response->json();
        if (! is_array($data)) {
            throw new BartaException('Mimsms API error: Invalid response received');
        }

        if ((int) ($data['statusCode'] ?? 0) !== 200) {
            throw new BartaException((string) ($data['responseResult'] ?? 'Mimsms API error'));
        }

        return new ResponseData(
            success: true,
            data: $data,
        );
    }

    protected function validateConfig(): void
    {
        if (empty($this->config['username'])) {
            throw new BartaException('Please set username for Mimsms in config/barta.php.');
        }

        if (empty($this->config['api_key'])) {
            throw new BartaException('Please set api_key for Mimsms in config/barta.php.');
        }

        if (empty($this->config['sender_id'])) {
            throw new BartaException('Please set sender_id for Mimsms in config/barta.php.');
        }
    }
}
