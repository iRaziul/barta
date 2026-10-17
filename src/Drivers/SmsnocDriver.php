<?php

declare(strict_types=1);

namespace Larament\Barta\Drivers;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Larament\Barta\Data\ResponseData;
use Larament\Barta\Exceptions\BartaException;

final class SmsnocDriver extends AbstractDriver
{
    private string $baseUrl = 'https://smsnoc.com/api/v1';

    protected function sendSms(): ResponseData
    {
        $response = Http::baseUrl($this->baseUrl)
            ->withToken($this->config['api_token'])
            ->timeout($this->timeout)
            ->retry($this->retry, $this->retryDelay, throw: false)
            ->acceptJson()
            ->asJson()
            ->post('/send-sms', [
                'to' => $this->recipients,
                'sender_id' => $this->config['sender_id'],
                'message' => $this->message,
            ]);

        $data = $this->decode($response);

        if (($data['status'] ?? '') !== 'success') {
            throw new BartaException($this->errorMessage($data, $response));
        }

        return new ResponseData(
            success: true,
            data: $data,
        );
    }

    protected function fetchBalance(): float
    {
        $response = Http::baseUrl($this->baseUrl)
            ->withToken($this->config['api_token'])
            ->timeout($this->timeout)
            ->retry($this->retry, $this->retryDelay, throw: false)
            ->acceptJson()
            ->get('/balance');

        $data = $this->decode($response);

        if (($data['success'] ?? false) !== true) {
            throw new BartaException($this->errorMessage($data, $response));
        }

        return (float) ($data['balance'] ?? 0.0);
    }

    protected function validateConfig(): void
    {
        if (empty($this->config['api_token'])) {
            throw new BartaException('Please set api_token for smsnoc in config/barta.php.');
        }

        if (empty($this->config['sender_id'])) {
            throw new BartaException('Please set sender_id for smsnoc in config/barta.php.');
        }
    }

    /**
     * SMSNOC answers errors with a JSON body too (e.g. 401 Authentication failed), so read it whatever the status.
     *
     * @return array<string, mixed>
     */
    private function decode(Response $response): array
    {
        $data = $response->json();

        if (! is_array($data)) {
            throw new BartaException('smsnoc API error: '.($response->body() ?: 'HTTP request failed with status '.$response->status()));
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function errorMessage(array $data, Response $response): string
    {
        $message = $data['message'] ?? $data['error'] ?? null;

        return is_string($message) && $message !== ''
            ? $message
            : 'smsnoc API error: HTTP request failed with status '.$response->status();
    }
}
