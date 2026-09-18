<?php

declare(strict_types=1);

namespace Larament\Barta\Facades;

use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Facades\Facade;
use Larament\Barta\BartaManager;
use Larament\Barta\Data\ResponseData;
use Larament\Barta\Drivers\AbstractDriver;

/**
 * @method static AbstractDriver to(string|array<string> $numbers)
 * @method static AbstractDriver message(string $message)
 * @method static ResponseData send()
 * @method static float balance()
 * @method static PendingDispatch queue(?string $queue = null, ?string $connection = null)
 * @method static AbstractDriver reset()
 * @method static string getName()
 * @method static AbstractDriver driver(?string $driver = null)
 * @method static string getDefaultDriver()
 *
 * @see BartaManager
 * @see AbstractDriver
 */
final class Barta extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return BartaManager::class;
    }
}
