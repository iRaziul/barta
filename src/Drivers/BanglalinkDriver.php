<?php

declare(strict_types=1);

namespace Larament\Barta\Drivers;

use Illuminate\Support\Facades\Http;
use Larament\Barta\Data\ResponseData;
use Larament\Barta\Exceptions\BartaException;

final class BanglalinkDriver extends AbstractDriver
{
    private string $baseUrl = 'https://vas.banglalink.net/sendSMS';

    public function send(): ResponseData
    {
        $this->validate();

        $response = Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->retry($this->retry, $this->retryDelay)
            ->asForm()
            ->post('/sendSMS', [
                'userID' => $this->config['user_id'],
                'passwd' => $this->config['password'],
                'sender' => $this->config['sender_id'],
                'msisdn' => implode(',', $this->recipients),
                'message' => $this->message,
            ]);

        $body = $response->body();

        // Banglalink returns XML/text response
        if (str_contains(mb_strtolower($body), 'error') || str_contains(mb_strtolower($body), 'fail')) {
            throw new BartaException($body ?: 'Banglalink API error');
        }

        return new ResponseData(
            success: true,
            data: ['response' => $body],
        );
    }

    protected function validate(): void
    {
        parent::validate();

        if (empty($this->config['user_id'])) {
            throw new BartaException('Please set user_id for Banglalink in config/barta.php.');
        }

        if (empty($this->config['password'])) {
            throw new BartaException('Please set password for Banglalink in config/barta.php.');
        }

        if (empty($this->config['sender_id'])) {
            throw new BartaException('Please set sender_id for Banglalink in config/barta.php.');
        }
    }
}
