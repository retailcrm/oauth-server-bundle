<?php

declare(strict_types=1);

namespace OAuth\Model;

abstract class Token implements TokenInterface
{
    public function getExpiresIn(): ?int
    {
        if (!$this->getExpiresAt()) {
            return null;
        }

        if ($this->getExpiresAt() - time() < 0) {
            return 0;
        }

        return $this->getExpiresAt() - time();
    }

    public function hasExpired(): bool
    {
        return time() > $this->getExpiresAt();
    }
}
