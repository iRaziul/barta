<?php

declare(strict_types=1);

namespace Larament\Barta\Drivers;

use Illuminate\Support\Facades\Http;
use Larament\Barta\Data\ResponseData;
use Larament\Barta\Exceptions\BartaException;

final class GrameenphoneDriver extends AbstractDriver
{
    private string $baseUrl = 'https://gpcmp.grameenphone.com/ecmapigw/webresources/ecmapigw.v2';

    public function send(): ResponseData
    {
        $this->validate();

        $response = Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->retry($this->retry, $this->retryDelay)
            ->acceptJson()
            ->asJson()
            ->post('', [
                'username' => $this->config['username'],
                'password' => $this->config['password'],
                'apicode' => 1, // 1 for sending
                'msisdn' => implode(',', $this->recipients),
                'countrycode' => '880',
                'cli' => $this->config['cli'] ?? '2222',
                'messagetype' => $this->config['message_type'] ?? 1, // 1=Text, 2=Flash, 3=Unicode
                'messageid' => 0,
                'message' => $this->message,
            ])
            ->json();

        $statusCode = $response['statusCode'] ?? null;

        if ($statusCode !== 200 && $statusCode !== '200') {
            throw new BartaException($response['statusDescription'] ?? 'Grameenphone API error');
        }

        return new ResponseData(
            success: true,
            data: $response,
        );
    }

    protected function validate(): void
    {
        parent::validate();

        if (empty($this->config['username'])) {
            throw new BartaException('Please set username for Grameenphone in config/barta.php.');
        }

        if (empty($this->config['password'])) {
            throw new BartaException('Please set password for Grameenphone in config/barta.php.');
        }
    }
}
