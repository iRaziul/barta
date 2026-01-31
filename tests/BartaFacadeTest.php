<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Larament\Barta\BartaManager;
use Larament\Barta\Data\ResponseData;
use Larament\Barta\Facades\Barta;

it('can resolve the Barta facade', function () {
    expect(app(BartaManager::class))->toBeInstanceOf(BartaManager::class);
    expect(Barta::getFacadeRoot())->toBeInstanceOf(BartaManager::class);
});

it('can send sms using the Barta facade with log driver', function () {
    config()->set('barta.default', 'log');

    $response = Barta::to('8801700000000')->message('Facade Test Message')->send();

    expect($response)->toBeInstanceOf(ResponseData::class);
    expect($response->success)->toBeTrue();
});

it('can send sms using the Barta facade with esms driver', function () {
    Http::fake([
        'https://login.esms.com.bd/*' => Http::response(['status' => 'success', 'message' => 'SMS Sent'], 200),
    ]);

    config()->set('barta.default', 'esms');
    config()->set('barta.drivers.esms.api_token', 'test_token');
    config()->set('barta.drivers.esms.sender_id', 'test_sender_id');

    $response = Barta::to('8801700000000')->message('Facade Test Message')->send();

    expect($response)->toBeInstanceOf(ResponseData::class);
    expect($response->success)->toBeTrue();

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'esms.com.bd') &&
               $request->method() === 'POST' &&
               $request->hasHeader('Authorization', 'Bearer test_token') &&
               $request['recipient'] === '8801700000000' &&
               $request['sender_id'] === 'test_sender_id' &&
               $request['message'] === 'Facade Test Message';
    });
});
