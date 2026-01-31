<?php

declare(strict_types=1);

use Larament\Barta\Data\ResponseData;

it('can create a response data object', function () {
    $response = new ResponseData(
        success: true,
        data: ['message' => 'SMS Sent'],
        errors: [],
    );

    expect($response->success)->toBeTrue();
    expect($response->data)->toEqual(['message' => 'SMS Sent']);
    expect($response->errors)->toEqual([]);
});

it('can create a failed response data object', function () {
    $response = new ResponseData(
        success: false,
        data: [],
        errors: ['Invalid API key'],
    );

    expect($response->success)->toBeFalse();
    expect($response->data)->toEqual([]);
    expect($response->errors)->toEqual(['Invalid API key']);
});

it('can convert response data to array', function () {
    $response = new ResponseData(
        success: true,
        data: ['id' => 123],
        errors: [],
    );

    $array = $response->toArray();

    expect($array)->toBeArray();
    expect($array)->toEqual([
        'success' => true,
        'data' => ['id' => 123],
        'errors' => [],
    ]);
});
