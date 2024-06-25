<?php

declare(strict_types=1);

namespace OAuth\Server\BearerToken;

use Symfony\Component\HttpFoundation\Request;

class QueryExtractor implements ExtractorInterface
{
    public const QUERY_NAME = 'access_token';

    public function extract(Request $request, bool $removeFromRequest = false): ?string
    {
        if (!$token = $request->query->get(self::QUERY_NAME)) {
            return null;
        }

        if (!is_string($token)) {
            return null;
        }

        if ($removeFromRequest) {
            $request->query->remove(self::QUERY_NAME);
        }

        return $token;
    }
}
