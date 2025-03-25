<?php

declare(strict_types=1);

namespace OAuth\Event;

use OAuth\Model\TokenInterface;

class VerifyTokenEvent
{
    public function __construct(
        private readonly TokenInterface $token,
    ) {
    }

    public function getToken(): TokenInterface
    {
        return $this->token;
    }
}
