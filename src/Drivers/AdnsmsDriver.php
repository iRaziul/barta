<?php

declare(strict_types=1);

namespace Larament\Barta\Drivers;

use Illuminate\Support\Facades\Http;
use Larament\Barta\Data\ResponseData;
use Larament\Barta\Exceptions\BartaException;

final class AdnsmsDriver extends AbstractDriver
{
    private string $baseUrl = 'https://portal.adnsms.com/api/v1/secure';

    protected function sendSms(): ResponseData
    {
        $response = Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->retry($this->retry, $this->retryDelay, throw: false)
            ->acceptJson()
            ->asForm()
            ->post('/send-sms', [
                'api_key' => $this->config['api_key'],
                'api_secret' => $this->config['api_secret'],
                'request_type' => $this->config['request_type'] ?? 'SINGLE_SMS',
                'message_type' => $this->config['message_type'] ?? 'TEXT',
                'senderid' => $this->config['sender_id'] ?? null,
                'mobile' => implode(',', $this->recipients),
                'message_body' => $this->message,
            ]);

        if ($response->failed()) {
            throw new BartaException('ADN SMS API error: '.($response->body() ?: 'HTTP request failed with status '.$response->status()));
        }

        $data = $response->json();
        if (! is_array($data)) {
            throw new BartaException('ADN SMS API error: Invalid response received');
        }

        if (($data['api_response_code'] ?? 0) !== 200) {
            throw new BartaException((string) ($data['api_response_message'] ?? 'ADN SMS API error'));
        }

        return new ResponseData(
            success: true,
            data: $data,
        );
    }

    protected function validateConfig(): void
    {
        if (empty($this->config['api_key'])) {
            throw new BartaException('Please set api_key for ADN in config/barta.php.');
        }

        if (empty($this->config['api_secret'])) {
            throw new BartaException('Please set api_secret for ADN in config/barta.php.');
        }
    }
}
