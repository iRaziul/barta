<?php

declare(strict_types=1);

namespace Larament\Barta\Notifications;

final readonly class BartaMessage
{
    /**
     * Create a new message instance.
     */
    public function __construct(public string $content = '') {}
}
