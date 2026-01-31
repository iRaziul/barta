<?php

declare(strict_types=1);

namespace Larament\Barta\Facades;

use Illuminate\Support\Facades\Facade;
use Larament\Barta\BartaManager;

/**
 * @method static self to(string $number)
 * @method static self message(string $message)
 * @method static \Larament\Barta\Data\ResponseData send()
 * @method static self driver(?string $driver)
 *
 * @see BartaManager
 */
class Barta extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return BartaManager::class;
    }
}
