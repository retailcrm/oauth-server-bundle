<?php

declare(strict_types=1);

namespace OAuth\Server\GrantExtension;

use OAuth\Enum\ErrorCode;
use OAuth\Exception\OAuthServerException;
use OAuth\Model\ClientInterface;
use OAuth\Server\Config;
use OAuth\Utils\ClientCredentials;
use Symfony\Component\HttpFoundation\Response;

class ClientCredentialsGrantExtension implements GrantExtensionInterface
{
    public function checkGrantExtension(ClientInterface $client, Config $config, string $grantType, array $input, array $headers): Grant
    {
        $clientCredentials = ClientCredentials::get($input, $headers);

        if (empty($clientCredentials[1])) {
            throw new OAuthServerException(Response::HTTP_BAD_REQUEST, ErrorCode::ERROR_INVALID_CLIENT, 'The client_secret is mandatory for the "client_credentials" grant type');
        }

        if (!$client->checkSecret($clientCredentials[1])) {
            throw new OAuthServerException(Response::HTTP_BAD_REQUEST, ErrorCode::ERROR_INVALID_GRANT);
        }

        return new Grant(null, null, false);
    }
}
