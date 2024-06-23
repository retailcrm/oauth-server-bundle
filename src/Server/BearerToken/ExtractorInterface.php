<?php

declare(strict_types=1);

namespace OAuth\Server\BearerToken;

use Symfony\Component\HttpFoundation\Request;

interface ExtractorInterface
{
    public function extract(Request $request, bool $removeFromRequest = false): ?string;
}
