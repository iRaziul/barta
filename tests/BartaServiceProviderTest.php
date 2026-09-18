<?php

declare(strict_types=1);

use Illuminate\Notifications\ChannelManager;
use Larament\Barta\BartaManager;
use Larament\Barta\Notifications\BartaChannel;

it('registers BartaManager as a singleton in the container', function () {
    $manager1 = app(BartaManager::class);
    $manager2 = app(BartaManager::class);

    expect($manager1)
        ->toBeInstanceOf(BartaManager::class)
        ->and($manager1)
        ->toBe($manager2);
});

it('registers the barta notification channel in the ChannelManager', function () {
    $channelManager = app(ChannelManager::class);
    $channel = $channelManager->channel('barta');

    expect($channel)->toBeInstanceOf(BartaChannel::class);
});

it('merges default barta configuration', function () {
    expect(config('barta'))->toBeArray()
        ->and(config('barta.default'))->not->toBeNull();
});
