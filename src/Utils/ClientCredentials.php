<?php

declare(strict_types=1);

namespace OAuth\Utils;

use OAuth\Enum\ErrorCode;
use OAuth\Exception\OAuthServerException;
use Symfony\Component\HttpFoundation\Response;

class ClientCredentials
{
    /**
     * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-2.4.1
     */
    public static function get(array $input, array $headers): array
    {
        if (!empty($headers['PHP_AUTH_USER'])) {
            return [$headers['PHP_AUTH_USER'], $headers['PHP_AUTH_PW']];
        }

        if (empty($input['client_id'])) {
            throw new OAuthServerException(Response::HTTP_BAD_REQUEST, ErrorCode::ERROR_INVALID_CLIENT, 'Client id was not found in the headers or body');
        }

        $clientId = $input['client_id'];
        $clientSecret = $input['client_secret'] ?? null;

        return [$clientId, $clientSecret];
    }
}
