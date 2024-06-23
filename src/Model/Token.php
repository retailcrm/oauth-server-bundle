<?php

declare(strict_types=1);

namespace OAuth\Model;

abstract class Token implements TokenInterface
{
    public function hasExpired(): bool
    {
        return time() > $this->getExpiresAt();
    }
}
