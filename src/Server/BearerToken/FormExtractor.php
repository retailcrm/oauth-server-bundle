<?php

declare(strict_types=1);

namespace OAuth\Server\BearerToken;

use Symfony\Component\HttpFoundation\Request;

class FormExtractor implements ExtractorInterface
{
    public const FORM_NAME = 'access_token';

    public function extract(Request $request, bool $removeFromRequest = false): ?string
    {
        if (false === $request->server->has('CONTENT_TYPE')) {
            return null;
        }

        $contentType = $request->server->get('CONTENT_TYPE');

        if (!preg_match('/^application\/x-www-form-urlencoded([\s|;].*)?$/', $contentType)) {
            return null;
        }

        if (Request::METHOD_GET === $request->getMethod()) {
            return null;
        }

        $body = $request->getContent();
        parse_str($body, $parameters);

        if (false === array_key_exists(self::FORM_NAME, $parameters)) {
            return null;
        }

        $token = $parameters[self::FORM_NAME];

        if ($removeFromRequest && true === $request->request->has(self::FORM_NAME)) {
            $request->request->remove(self::FORM_NAME);
        }

        return $token;
    }
}
