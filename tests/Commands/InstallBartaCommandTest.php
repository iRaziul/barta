<?php

declare(strict_types=1);

use function Pest\Laravel\artisan;

it('can run the install command', function () {
    artisan('barta:install')
        ->expectsConfirmation('Would you like to star this repo on GitHub?', 'no')
        ->assertSuccessful();
});

it('publishes the config file', function () {
    $configPath = config_path('barta.php');

    // Remove config if it exists from previous test run
    if (file_exists($configPath)) {
        unlink($configPath);
    }

    artisan('barta:install')
        ->expectsConfirmation('Would you like to star this repo on GitHub?', 'no')
        ->assertSuccessful();

    expect(file_exists($configPath))->toBeTrue();

    // Cleanup
    if (file_exists($configPath)) {
        unlink($configPath);
    }
});

it('shows thank you message after install', function () {
    artisan('barta:install')
        ->expectsConfirmation('Would you like to star this repo on GitHub?', 'no')
        ->expectsOutputToContain('Thank you')
        ->assertSuccessful();
});
