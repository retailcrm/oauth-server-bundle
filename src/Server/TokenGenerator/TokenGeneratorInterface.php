<?php

declare(strict_types=1);

namespace OAuth\Server\TokenGenerator;

interface TokenGeneratorInterface
{
    public function generate(): string;
}
