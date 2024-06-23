<?php

declare(strict_types=1);

namespace OAuth\Server;

use OAuth\Model\AccessTokenInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

interface HandlerInterface
{
    /**
     * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-7
     */
    public function verifyAccessToken(string $tokenParam, ?string $scope = null): AccessTokenInterface;

    /**
     * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-bearer-08#section-2.1
     * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-bearer-08#section-2.2
     * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-bearer-08#section-2.3
     */
    public function getBearerToken(Request $request, bool $removeFromRequest = false): ?string;

    /**
     * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-4
     * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-21#section-10.6
     * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-21#section-4.1.3
     */
    public function grantAccessToken(Request $request): Response;

    /**
     * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-4
     */
    public function finishClientAuthorization(bool $isAuthorized, Request $request, ?UserInterface $user = null, ?string $scope = null): Response;
}
