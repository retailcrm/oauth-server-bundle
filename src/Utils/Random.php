<?php

declare(strict_types=1);

namespace OAuth\Utils;

class Random
{
    public static function generateToken(): string
    {
        $bytes = random_bytes(32);

        return base_convert(bin2hex($bytes), 16, 36);
    }
}
