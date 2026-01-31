<?php

declare(strict_types=1);

namespace Larament\Barta\Data;

final readonly class ResponseData
{
    public function __construct(
        public bool $success,
        public array $data = [],
        public array $errors = [],
    ) {}

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'data' => $this->data,
            'errors' => $this->errors,
        ];
    }
}
