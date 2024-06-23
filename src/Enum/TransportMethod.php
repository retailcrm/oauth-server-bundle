<?php

declare(strict_types=1);

namespace OAuth\Enum;

enum TransportMethod: string
{
    case Query = 'query';
    case Fragment = 'fragment';
}
