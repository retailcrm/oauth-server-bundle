<?php

declare(strict_types=1);

namespace OAuth\Server\BearerToken;

use Symfony\Component\HttpFoundation\Request;

class HeaderExtractor implements ExtractorInterface
{
    public const HEADER_NAME = 'Bearer';

    public function extract(Request $request, bool $removeFromRequest = false): ?string
    {
        $header = $request->headers->get('AUTHORIZATION');
        if (!$header) {
            return null;
        }

        if (!preg_match('/' . preg_quote(self::HEADER_NAME, '/') . '\s(\S+)/', $header, $matches)) {
            return null;
        }

        $token = $matches[1];

        if ($removeFromRequest) {
            $request->headers->remove('AUTHORIZATION');
        }

        return $token;
    }
}
