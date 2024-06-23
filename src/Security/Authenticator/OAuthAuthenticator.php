<?php

declare(strict_types=1);

namespace OAuth\Security\Authenticator;

use OAuth\Exception\OAuthAuthenticateException;
use OAuth\Exception\OAuthServerException;
use OAuth\Security\Authenticator\Passport\Badge\AccessTokenBadge;
use OAuth\Security\Authenticator\Token\OAuthToken;
use OAuth\Server\Config;
use OAuth\Server\Handler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class OAuthAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly UserCheckerInterface $userChecker,
        private readonly Handler $handler,
        private readonly Config $config
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return null !== $this->handler->getBearerToken($request);
    }

    public function authenticate(Request $request): Passport
    {
        try {
            $tokenString = $this->handler->getBearerToken($request);
            if (null === $tokenString) {
                throw new AuthenticationException('OAuth2 authentication failed: missing access token.');
            }

            $accessToken = $this->handler->verifyAccessToken($tokenString);

            $user = $accessToken->getUser();

            if (null !== $user) {
                try {
                    $this->userChecker->checkPreAuth($user);
                } catch (AccountStatusException $e) {
                    throw new OAuthAuthenticateException(Response::HTTP_UNAUTHORIZED, Handler::TOKEN_TYPE_BEARER, $this->config->getVariable(Config::CONFIG_WWW_REALM), 'access_denied', $e->getMessage());
                }
            }

            $roles = (null !== $user) ? $user->getRoles() : [];

            $accessTokenBadge = new AccessTokenBadge($accessToken, $roles);

            return new SelfValidatingPassport(new UserBadge($user->getUserIdentifier()), [$accessTokenBadge]);
        } catch (OAuthServerException $e) {
            throw new AuthenticationException('OAuth2 authentication failed', 0, $e);
        }
    }

    public function createToken(Passport $passport, string $firewallName): TokenInterface
    {
        $badge = $passport->getBadge(AccessTokenBadge::class);
        if (!$badge instanceof AccessTokenBadge) {
            throw new \LogicException('');
        }

        $accessToken = $badge->getAccessToken();
        $token = new OAuthToken($badge->getRoles());
        $token->setToken($accessToken->getToken());
        if ($accessToken->getUser()) {
            $token->setUser($accessToken->getUser());
        }
        if (method_exists($token, 'setAuthenticated')) {
            $token->setAuthenticated(true);
        }

        return $token;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $previousException = $exception->getPrevious();
        if ($previousException instanceof OAuthServerException) {
            return $previousException->getHttpResponse();
        }

        return new JsonResponse([
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData()),
        ], Response::HTTP_UNAUTHORIZED);
    }
}
