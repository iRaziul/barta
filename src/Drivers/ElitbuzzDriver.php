<?php

declare(strict_types=1);

namespace Larament\Barta\Drivers;

use Illuminate\Support\Facades\Http;
use Larament\Barta\Data\ResponseData;
use Larament\Barta\Exceptions\BartaException;

final class ElitbuzzDriver extends AbstractDriver
{
    public function send(): ResponseData
    {
        $this->validate();

        $response = Http::timeout($this->timeout)
            ->retry($this->retry, $this->retryDelay)
            ->asForm()
            ->post($this->config['url'].'/smsapi', [
                'api_key' => $this->config['api_key'],
                'type' => $this->config['type'] ?? 'text',
                'senderid' => $this->config['sender_id'],
                'contacts' => implode(',', $this->recipients),
                'msg' => $this->message,
            ]);

        $body = $response->body();

        if (str_contains(mb_strtolower($body), 'error') || str_contains(mb_strtolower($body), 'fail')) {
            throw new BartaException($body ?: 'ElitBuzz API error');
        }

        return new ResponseData(
            success: true,
            data: ['response' => $body],
        );
    }

    protected function validate(): void
    {
        parent::validate();

        if (empty($this->config['url'])) {
            throw new BartaException('Please set url for ElitBuzz in config/barta.php.');
        }

        if (empty($this->config['api_key'])) {
            throw new BartaException('Please set api_key for ElitBuzz in config/barta.php.');
        }

        if (empty($this->config['sender_id'])) {
            throw new BartaException('Please set sender_id for ElitBuzz in config/barta.php.');
        }
    }
}
