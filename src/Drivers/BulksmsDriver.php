<?php

declare(strict_types=1);

namespace Larament\Barta\Drivers;

use Illuminate\Support\Facades\Http;
use Larament\Barta\Data\ResponseData;
use Larament\Barta\Exceptions\BartaException;

final class BulksmsDriver extends AbstractDriver
{
    private string $baseUrl = 'https://bulksmsbd.net/api';

    public function send(): ResponseData
    {
        $this->validate();

        $response = Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->retry($this->retry, $this->retryDelay)
            ->get('/smsapi', [
                'api_key' => $this->config['api_key'],
                'senderid' => $this->config['sender_id'],
                'type' => 'text',
                'number' => implode(',', $this->recipients),
                'message' => $this->message,
            ])
            ->json();

        if (($response['response_code'] ?? 0) !== 202) {
            throw new BartaException($response['error_message'] ?? 'BulkSMS BD API error');
        }

        return new ResponseData(success: true, data: $response);
    }

    protected function validate(): void
    {
        parent::validate();

        if (empty($this->config['api_key'])) {
            throw new BartaException('Please set api_key for BulkSMS in config/barta.php.');
        }
        if (empty($this->config['sender_id'])) {
            throw new BartaException('Please set sender_id for BulkSMS in config/barta.php.');
        }
    }
}
