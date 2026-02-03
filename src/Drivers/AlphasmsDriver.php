<?php

declare(strict_types=1);

namespace Larament\Barta\Drivers;

use Illuminate\Support\Facades\Http;
use Larament\Barta\Data\ResponseData;
use Larament\Barta\Exceptions\BartaException;

final class AlphasmsDriver extends AbstractDriver
{
    private string $baseUrl = 'https://api.sms.net.bd';

    protected function sendSms(): ResponseData
    {
        $params = [
            'api_key' => $this->config['api_key'],
            'msg' => $this->message,
            'to' => implode(',', $this->recipients),
        ];

        if (! empty($this->config['sender_id'])) {
            $params['sender_id'] = $this->config['sender_id'];
        }

        if (! empty($this->config['schedule'])) {
            $params['schedule'] = $this->config['schedule'];
        }

        $response = Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->retry($this->retry, $this->retryDelay)
            ->acceptJson()
            ->post('/sendsms', $params)
            ->json();

        if (($response['error'] ?? 0) !== 0) {
            throw new BartaException($response['msg'] ?? 'Alpha SMS API error');
        }

        return new ResponseData(
            success: ($response['error'] ?? 1) === 0,
            data: $response,
        );
    }

    protected function validateConfig(): void
    {
        if (empty($this->config['api_key'])) {
            throw new BartaException('Please set api_key for Alpha in config/barta.php.');
        }
    }
}
