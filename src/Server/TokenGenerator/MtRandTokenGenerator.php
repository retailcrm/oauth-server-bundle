<?php

declare(strict_types=1);

namespace OAuth\Server\TokenGenerator;

class MtRandTokenGenerator implements TokenGeneratorInterface
{
    public function generate(): string
    {
        $randomData = ''
            . mt_rand()
            . mt_rand()
            . mt_rand()
            . uniqid((string) mt_rand(), true)
            . microtime(true)
            . uniqid((string) mt_rand(), true);

        return rtrim(strtr(base64_encode(hash('sha256', $randomData)), '+/', '-_'), '=');
    }
}
