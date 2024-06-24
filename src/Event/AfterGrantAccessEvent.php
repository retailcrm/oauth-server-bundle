<?php

declare(strict_types=1);

namespace OAuth\Event;

use OAuth\Server\GrantExtension\Grant;

class AfterGrantAccessEvent
{
    public function __construct(
        private readonly string $grantType,
        private readonly Grant $grant,
    ) {
    }

    public function getGrantType(): string
    {
        return $this->grantType;
    }

    public function getGrant(): Grant
    {
        return $this->grant;
    }
}
