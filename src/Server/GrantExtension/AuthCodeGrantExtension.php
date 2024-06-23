<?php

declare(strict_types=1);

namespace OAuth\Server\GrantExtension;

use OAuth\Enum\ErrorCode;
use OAuth\Exception\OAuthServerException;
use OAuth\Model\ClientInterface;
use OAuth\Server\Config;
use OAuth\Server\Storage\AuthCodeStorageInterface;
use OAuth\Utils\RedirectUri;
use Symfony\Component\HttpFoundation\Response;

class AuthCodeGrantExtension implements GrantExtensionInterface
{
    public function __construct(private readonly AuthCodeStorageInterface $storage)
    {
    }

    public function checkGrantExtension(ClientInterface $client, Config $config, string $grantType, array $input, array $headers): Grant
    {
        if (!$input['code']) {
            throw new OAuthServerException(Response::HTTP_BAD_REQUEST, ErrorCode::ERROR_INVALID_REQUEST, 'Missing parameter. "code" is required');
        }

        if (!$input['redirect_uri'] && $config->getVariable(Config::CONFIG_ENFORCE_INPUT_REDIRECT)) {
            throw new OAuthServerException(Response::HTTP_BAD_REQUEST, ErrorCode::ERROR_INVALID_REQUEST, 'The redirect URI parameter is required.');
        }

        $authCode = $this->storage->getAuthCode($input['code']);

        if (null === $authCode || $client->getPublicId() !== $authCode->getClient()->getPublicId()) {
            throw new OAuthServerException(Response::HTTP_BAD_REQUEST, ErrorCode::ERROR_INVALID_GRANT, "Code doesn't exist or is invalid for the client");
        }

        if ($input['redirect_uri'] && RedirectUri::validate($input['redirect_uri'], [$authCode->getRedirectUri()])) {
            throw new OAuthServerException(Response::HTTP_BAD_REQUEST, ErrorCode::ERROR_REDIRECT_URI_MISMATCH, 'The redirect URI is missing or do not match');
        }

        if ($authCode->hasExpired()) {
            throw new OAuthServerException(Response::HTTP_BAD_REQUEST, ErrorCode::ERROR_INVALID_GRANT, 'The authorization code has expired');
        }

        $this->storage->markAuthCodeAsUsed($authCode->getToken());

        return new Grant($authCode->getUser(), $authCode->getScope());
    }
}
