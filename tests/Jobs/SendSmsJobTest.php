<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Larament\Barta\BartaManager;
use Larament\Barta\Drivers\LogDriver;
use Larament\Barta\Facades\Barta;
use Larament\Barta\Jobs\SendSmsJob;

it('can dispatch sms job to queue', function () {
    Bus::fake();

    Barta::to('8801700000000')->message('Queued message')->queue();

    Bus::assertDispatched(SendSmsJob::class, function ($job) {
        return $job->driver === 'log' &&
               $job->recipients === ['8801700000000'] &&
               $job->message === 'Queued message';
    });
});

it('can dispatch bulk sms job to queue', function () {
    Bus::fake();

    Barta::to(['8801700000000', '8801800000000'])->message('Bulk queued')->queue();

    Bus::assertDispatched(SendSmsJob::class, function ($job) {
        return $job->recipients === ['8801700000000', '8801800000000'] &&
               $job->message === 'Bulk queued';
    });
});

it('can specify custom queue name', function () {
    Bus::fake();

    Barta::to('8801700000000')->message('Custom queue test')->queue('sms');

    Bus::assertDispatched(SendSmsJob::class, function ($job) {
        return $job->queue === 'sms';
    });
});

it('can specify custom connection', function () {
    Bus::fake();

    Barta::to('8801700000000')->message('Custom connection test')->queue(null, 'redis');

    Bus::assertDispatched(SendSmsJob::class, function ($job) {
        return $job->connection === 'redis';
    });
});

it('can specify both queue and connection', function () {
    Bus::fake();

    Barta::to('8801700000000')->message('Both options')->queue('sms', 'redis');

    Bus::assertDispatched(SendSmsJob::class, function ($job) {
        return $job->queue === 'sms' && $job->connection === 'redis';
    });
});

it('job sends sms when handled', function () {
    Http::fake([
        'https://login.esms.com.bd/*' => Http::response(['status' => 'success', 'message' => 'SMS Sent'], 200),
    ]);

    config()->set('barta.drivers.esms.api_token', 'test_token');
    config()->set('barta.drivers.esms.sender_id', 'test_sender_id');

    $job = new SendSmsJob(
        driver: 'esms',
        recipients: ['8801700000000'],
        message: 'Job test message',
    );

    $job->handle(app(BartaManager::class));

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'esms.com.bd') &&
               $request['recipient'] === '8801700000000' &&
               $request['message'] === 'Job test message';
    });
});

it('job sends sms with log driver when handled', function () {
    $job = new SendSmsJob(
        driver: 'log',
        recipients: ['8801700000000'],
        message: 'Log driver job test',
    );

    $manager = app(BartaManager::class);

    // Should not throw exception
    $job->handle($manager);

    expect(true)->toBeTrue();
});

it('gets correct driver name from driver class', function () {
    $driver = new LogDriver;

    $reflection = new ReflectionClass($driver);
    $method = $reflection->getMethod('getDriverName');

    expect($method->invoke($driver))->toBe('log');
});
