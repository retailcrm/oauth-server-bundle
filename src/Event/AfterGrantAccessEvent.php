<?php

declare(strict_types=1);

namespace OAuth\Event;

class AfterGrantAccessEvent
{
    public function __construct(
        private readonly string $grantType,
        private readonly array $stored
    ) {
    }

    public function getGrantType(): string
    {
        return $this->grantType;
    }

    public function getStored(): array
    {
        return $this->stored;
    }
}
