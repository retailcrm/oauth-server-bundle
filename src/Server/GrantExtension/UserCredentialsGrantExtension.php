<?php

declare(strict_types=1);

namespace OAuth\Server\GrantExtension;

use OAuth\Enum\ErrorCode;
use OAuth\Exception\OAuthServerException;
use OAuth\Model\ClientInterface;
use OAuth\Server\Config;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\PasswordHasher\LegacyPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\LegacyPasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserCredentialsGrantExtension implements GrantExtensionInterface
{
    public function __construct(
        private readonly UserProviderInterface $userProvider,
        private readonly PasswordHasherFactoryInterface $passwordHasherFactory,
    ) {
    }

    public function checkGrantExtension(ClientInterface $client, Config $config, string $grantType, array $input): Grant
    {
        if (!$input['username'] || !$input['password']) {
            throw new OAuthServerException(Response::HTTP_BAD_REQUEST, ErrorCode::ERROR_INVALID_REQUEST, 'Missing parameters. "username" and "password" required');
        }

        try {
            $user = $this->userProvider->loadUserByIdentifier($input['username']);
        } catch (AuthenticationException) {
            throw new OAuthServerException(Response::HTTP_BAD_REQUEST, ErrorCode::ERROR_INVALID_GRANT, 'Invalid username and password combination');
        }

        if (!$user instanceof PasswordAuthenticatedUserInterface) {
            throw new \LogicException('User must implement PasswordAuthenticatedUserInterface');
        }

        $encoder = $this->passwordHasherFactory->getPasswordHasher($user);

        if ($user instanceof LegacyPasswordAuthenticatedUserInterface && $encoder instanceof LegacyPasswordHasherInterface) {
            if (!$encoder->verify($user->getPassword(), $input['password'], $user->getSalt())) {
                throw new OAuthServerException(Response::HTTP_BAD_REQUEST, ErrorCode::ERROR_INVALID_GRANT, 'Invalid username and password combination');
            }
        } elseif (!$encoder->verify($user->getPassword(), $input['password'])) {
            throw new OAuthServerException(Response::HTTP_BAD_REQUEST, ErrorCode::ERROR_INVALID_GRANT, 'Invalid username and password combination');
        }

        return new Grant($user);
    }
}
