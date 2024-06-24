<?php

declare(strict_types=1);

namespace OAuth\Server;

use OAuth\Enum\ErrorCode;
use OAuth\Enum\TransportMethod;
use OAuth\Event\AfterGrantAccessEvent;
use OAuth\Exception\OAuthAuthenticateException;
use OAuth\Exception\OAuthRedirectException;
use OAuth\Exception\OAuthServerException;
use OAuth\Model\AccessTokenInterface;
use OAuth\Model\ClientInterface;
use OAuth\Server\BearerToken\ExtractorInterface;
use OAuth\Server\GrantExtension\AuthCodeGrantExtension;
use OAuth\Server\GrantExtension\ClientCredentialsGrantExtension;
use OAuth\Server\GrantExtension\CustomGrantExtension;
use OAuth\Server\GrantExtension\RefreshTokenGrantExtension;
use OAuth\Server\GrantExtension\UserCredentialsGrantExtension;
use OAuth\Server\Storage\AccessTokenStorageInterface;
use OAuth\Server\Storage\AuthCodeStorageInterface;
use OAuth\Server\Storage\ClientStorageInterface;
use OAuth\Server\Storage\RefreshTokenStorageInterface;
use OAuth\Server\TokenGenerator\TokenGeneratorInterface;
use OAuth\Utils\RedirectUri;
use OAuth\Utils\Uri;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class Handler implements HandlerInterface
{
    public const GRANT_TYPE_AUTH_CODE = 'authorization_code';
    public const GRANT_TYPE_USER_CREDENTIALS = 'password';
    public const GRANT_TYPE_CLIENT_CREDENTIALS = 'client_credentials';
    public const GRANT_TYPE_REFRESH_TOKEN = 'refresh_token';

    public const REGEXP_GRANT_TYPE = '#^(authorization_code|token|password|client_credentials|refresh_token|https?://.+|urn:.+)$#';
    public const REGEXP_CLIENT_ID = '/.+/';

    /**
     * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-4.1.1
     * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-4.2.1
     */
    public const RESPONSE_TYPE_AUTH_CODE = 'code';
    public const RESPONSE_TYPE_ACCESS_TOKEN = 'token';

    /**
     * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-7.1
     */
    public const TOKEN_TYPE_BEARER = 'bearer';
    public const TOKEN_TYPE_MAC = 'mac';

    protected ClientStorageInterface $clientStorage;
    protected AccessTokenStorageInterface $accessTokenStorage;
    protected RefreshTokenStorageInterface $refreshTokenStorage;
    protected AuthCodeStorageInterface $authCodeStorage;

    protected AuthCodeGrantExtension $authCodeGrantExtension;
    protected ClientCredentialsGrantExtension $clientCredentialsGrantExtension;
    protected RefreshTokenGrantExtension $refreshTokenGrantExtension;
    protected UserCredentialsGrantExtension $userCredentialsGrantExtension;
    protected CustomGrantExtension $customGrantExtension;

    public function __construct(
        protected EventDispatcherInterface $eventDispatcher,
        protected ExtractorInterface $bearerTokenExtractor,
        protected TokenGeneratorInterface $tokenGenerator,
        protected Config $config,

        ClientStorageInterface $clientStorage,
        AccessTokenStorageInterface $accessTokenStorage,
        RefreshTokenStorageInterface $refreshTokenStorage,
        AuthCodeStorageInterface $authCodeStorage,

        AuthCodeGrantExtension $authCodeGrantExtension,
        ClientCredentialsGrantExtension $clientCredentialsGrantExtension,
        RefreshTokenGrantExtension $refreshTokenGrantExtension,
        UserCredentialsGrantExtension $userCredentialsGrantExtension,
        CustomGrantExtension $customGrantExtension,
    ) {
        $this->clientStorage = $clientStorage;
        $this->accessTokenStorage = $accessTokenStorage;
        $this->refreshTokenStorage = $refreshTokenStorage;
        $this->authCodeStorage = $authCodeStorage;

        $this->authCodeGrantExtension = $authCodeGrantExtension;
        $this->clientCredentialsGrantExtension = $clientCredentialsGrantExtension;
        $this->refreshTokenGrantExtension = $refreshTokenGrantExtension;
        $this->userCredentialsGrantExtension = $userCredentialsGrantExtension;
        $this->customGrantExtension = $customGrantExtension;
    }

    public function verifyAccessToken(string $tokenParam, ?string $scope = null): AccessTokenInterface
    {
        $tokenType = $this->config->getVariable(Config::CONFIG_TOKEN_TYPE);
        $realm = $this->config->getVariable(Config::CONFIG_WWW_REALM);

        if (!$tokenParam) {
            throw new OAuthAuthenticateException(Response::HTTP_BAD_REQUEST, $tokenType, $realm, ErrorCode::ERROR_INVALID_REQUEST, 'The request is missing a required parameter, includes an unsupported parameter or parameter value, repeats the same parameter, uses more than one method for including an access token, or is otherwise malformed.', $scope);
        }

        $token = $this->accessTokenStorage->getAccessToken($tokenParam);
        if (!$token) {
            throw new OAuthAuthenticateException(Response::HTTP_UNAUTHORIZED, $tokenType, $realm, ErrorCode::ERROR_INVALID_GRANT, 'The access token provided is invalid.', $scope);
        }

        if ($token->hasExpired()) {
            throw new OAuthAuthenticateException(Response::HTTP_UNAUTHORIZED, $tokenType, $realm, ErrorCode::ERROR_INVALID_GRANT, 'The access token provided has expired.', $scope);
        }

        if ($scope && (!$token->getScope() || !$this->checkScope($scope, $token->getScope()))) {
            throw new OAuthAuthenticateException(Response::HTTP_FORBIDDEN, $tokenType, $realm, ErrorCode::ERROR_INSUFFICIENT_SCOPE, 'The request requires higher privileges than provided by the access token.', $scope);
        }

        return $token;
    }

    public function getBearerToken(Request $request, bool $removeFromRequest = false): ?string
    {
        return $this->bearerTokenExtractor->extract($request, $removeFromRequest);
    }

    public function grantAccessToken(Request $request): Response
    {
        $filters = [
            'grant_type' => [
                'filter' => FILTER_VALIDATE_REGEXP,
                'options' => ['regexp' => self::REGEXP_GRANT_TYPE],
                'flags' => FILTER_REQUIRE_SCALAR,
            ],
            'scope' => ['flags' => FILTER_REQUIRE_SCALAR],
            'client_id' => ['flags' => FILTER_REQUIRE_SCALAR],
            'client_secret' => ['flags' => FILTER_REQUIRE_SCALAR],
            'code' => ['flags' => FILTER_REQUIRE_SCALAR],
            'redirect_uri' => ['filter' => FILTER_SANITIZE_URL],
            'username' => ['flags' => FILTER_REQUIRE_SCALAR],
            'password' => ['flags' => FILTER_REQUIRE_SCALAR],
            'refresh_token' => ['flags' => FILTER_REQUIRE_SCALAR],
        ];

        if (Request::METHOD_POST === $request->getMethod()) {
            $inputData = $request->request->all();
        } else {
            $inputData = $request->query->all();
        }

        $input = filter_var_array($inputData, $filters);
        $input += $inputData;

        if (!$input['grant_type']) {
            throw new OAuthServerException(Response::HTTP_BAD_REQUEST, ErrorCode::ERROR_INVALID_REQUEST, 'Invalid grant_type parameter or parameter missing');
        }

        if (empty($input['client_id'])) {
            throw new OAuthServerException(Response::HTTP_BAD_REQUEST, ErrorCode::ERROR_INVALID_CLIENT, 'Client id was not found in the body');
        }

        $client = $this->clientStorage->getClient($input['client_id']);
        if (!$client) {
            throw new OAuthServerException(Response::HTTP_BAD_REQUEST, ErrorCode::ERROR_INVALID_CLIENT, 'The client credentials are invalid');
        }

        if ($client->getSecret() && $client->getSecret() !== ($input['client_secret'] ?? null)) {
            throw new OAuthServerException(Response::HTTP_BAD_REQUEST, ErrorCode::ERROR_INVALID_CLIENT, 'The client credentials are invalid');
        }

        if (!$client->checkGrantType($input['grant_type'])) {
            throw new OAuthServerException(Response::HTTP_BAD_REQUEST, ErrorCode::ERROR_UNAUTHORIZED_CLIENT, 'The grant type is unauthorized for this client_id');
        }

        $grant = match ($input['grant_type']) {
            self::GRANT_TYPE_AUTH_CODE => $this->authCodeGrantExtension->checkGrantExtension(
                $client,
                $this->config,
                self::GRANT_TYPE_AUTH_CODE,
                $input,
            ),
            self::GRANT_TYPE_USER_CREDENTIALS => $this->userCredentialsGrantExtension->checkGrantExtension(
                $client,
                $this->config,
                self::GRANT_TYPE_USER_CREDENTIALS,
                $input,
            ),
            self::GRANT_TYPE_CLIENT_CREDENTIALS => $this->clientCredentialsGrantExtension->checkGrantExtension(
                $client,
                $this->config,
                self::GRANT_TYPE_CLIENT_CREDENTIALS,
                $input,
            ),
            self::GRANT_TYPE_REFRESH_TOKEN => $this->refreshTokenGrantExtension->checkGrantExtension(
                $client,
                $this->config,
                self::GRANT_TYPE_REFRESH_TOKEN,
                $input,
            ),
            default => $this->customGrantExtension->checkGrantExtension(
                $client,
                $this->config,
                $input['grant_type'],
                $input,
            ),
        };

        $this->eventDispatcher->dispatch(new AfterGrantAccessEvent($input['grant_type'], $grant));

        $scope = $grant->getScope();
        if ($input['scope']) {
            if (!$grant->getScope() || !$this->checkScope($input['scope'], $scope)) {
                throw new OAuthServerException(Response::HTTP_BAD_REQUEST, ErrorCode::ERROR_INVALID_SCOPE, 'An unsupported scope was requested.');
            }

            $scope = $input['scope'];
        }

        $token = $this->createAccessToken(
            $client,
            $grant->getUser(),
            $scope,
            $this->config->getVariable(Config::CONFIG_ACCESS_LIFETIME),
            $grant->issueRefreshToken(),
            $this->config->getVariable(Config::CONFIG_REFRESH_LIFETIME),
        );

        $headers = $this->config->getVariable(Config::CONFIG_RESPONSE_EXTRA_HEADERS);
        if (!$headers) {
            $headers = [];
        }

        $headers += [
            'Content-Type' => 'application/json',
            'Cache-Control' => 'no-store',
            'Pragma' => 'no-cache',
        ];

        return new Response(json_encode($token, JSON_THROW_ON_ERROR), 200, $headers);
    }

    public function finishClientAuthorization(bool $isAuthorized, Request $request, ?UserInterface $user = null, ?string $scope = null): Response
    {
        $filters = [
            'client_id' => [
                'filter' => FILTER_VALIDATE_REGEXP,
                'options' => ['regexp' => self::REGEXP_CLIENT_ID],
                'flags' => FILTER_REQUIRE_SCALAR,
            ],
            'response_type' => ['flags' => FILTER_REQUIRE_SCALAR],
            'redirect_uri' => ['filter' => FILTER_SANITIZE_URL],
            'state' => ['flags' => FILTER_REQUIRE_SCALAR],
            'scope' => ['flags' => FILTER_REQUIRE_SCALAR],
        ];

        $inputData = $request->query->all();
        $input = filter_var_array($inputData, $filters);

        if (!$input['client_id']) {
            throw new OAuthServerException(Response::HTTP_BAD_REQUEST, ErrorCode::ERROR_INVALID_REQUEST, 'No client id supplied');
        }

        $client = $this->clientStorage->getClient($input['client_id']);
        if (!$client) {
            throw new OAuthServerException(Response::HTTP_BAD_REQUEST, ErrorCode::ERROR_INVALID_CLIENT, 'Unknown client');
        }

        $params = [
            'client' => $client,
        ];

        if (empty($input['redirect_uri'])) {
            if (!$client->getRedirectUris()) {
                throw new OAuthServerException(Response::HTTP_BAD_REQUEST, ErrorCode::ERROR_REDIRECT_URI_MISMATCH, 'No redirect URL was supplied or registered.');
            }
            if (count($client->getRedirectUris()) > 1) {
                throw new OAuthServerException(Response::HTTP_BAD_REQUEST, ErrorCode::ERROR_REDIRECT_URI_MISMATCH, 'No redirect URL was supplied and more than one is registered.');
            }
            if ($this->config->getVariable(Config::CONFIG_ENFORCE_INPUT_REDIRECT)) {
                throw new OAuthServerException(Response::HTTP_BAD_REQUEST, ErrorCode::ERROR_REDIRECT_URI_MISMATCH, 'The redirect URI is mandatory and was not supplied.');
            }

            $input['redirect_uri'] = $client->getRedirectUris()[0];
        }

        if (!RedirectUri::validate($input['redirect_uri'], $client->getRedirectUris())) {
            throw new OAuthServerException(Response::HTTP_BAD_REQUEST, ErrorCode::ERROR_REDIRECT_URI_MISMATCH, 'The redirect URI provided does not match registered URI(s).');
        }

        if (!$input['response_type']) {
            throw new OAuthRedirectException($input['redirect_uri'], ErrorCode::ERROR_INVALID_REQUEST, 'Invalid response type.', $input['state']);
        }

        if ($input['scope'] && !$this->checkScope($input['scope'], $this->config->getVariable(Config::CONFIG_SUPPORTED_SCOPES))) {
            throw new OAuthRedirectException($input['redirect_uri'], ErrorCode::ERROR_INVALID_SCOPE, 'An unsupported scope was requested.', $input['state']);
        }

        if (!$input['state'] && $this->config->getVariable(Config::CONFIG_ENFORCE_STATE)) {
            throw new OAuthRedirectException($input['redirect_uri'], ErrorCode::ERROR_INVALID_REQUEST, 'The state parameter is required.');
        }

        $params += $input;
        $params += ['state' => null];

        $result = [];

        if (false === $isAuthorized) {
            $method = self::RESPONSE_TYPE_AUTH_CODE === $params['response_type'] ? TransportMethod::Query : TransportMethod::Fragment;

            throw new OAuthRedirectException(
                $params['redirect_uri'],
                ErrorCode::ERROR_USER_DENIED,
                'The user denied access to your application',
                $params['state'],
                $method
            );
        }

        if (self::RESPONSE_TYPE_AUTH_CODE === $params['response_type']) {
            $result[TransportMethod::Query->value]['state'] = $params['state'];
            $result[TransportMethod::Query->value] += $this->createAuthCode(
                $params['client'],
                $user,
                $params['redirect_uri'],
                $scope
            );
        } elseif (self::RESPONSE_TYPE_ACCESS_TOKEN === $params['response_type']) {
            $result[TransportMethod::Fragment->value]['state'] = $params['state'];
            $result[TransportMethod::Fragment->value] += $this->createAccessToken(
                $params['client'],
                $user,
                $scope,
                null,
                false
            );
        } else {
            throw new OAuthServerException(
                Response::HTTP_BAD_REQUEST,
                ErrorCode::ERROR_UNSUPPORTED_RESPONSE_TYPE,
                'The response type is not supported by the authorization server.'
            );
        }

        return new Response('', Response::HTTP_FOUND, [
            'Location' => Uri::build($params['redirect_uri'], $result),
        ]);
    }

    /**
     * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-5
     */
    private function createAccessToken(
        ClientInterface $client,
        ?UserInterface $user,
        ?string $scope = null,
        ?int $accessTokenLifetime = null,
        bool $issueRefreshToken = true,
        ?int $refreshTokenLifetime = null
    ): array {
        if (null === $accessTokenLifetime) {
            $accessTokenLifetime = (int) $this->config->getVariable(Config::CONFIG_ACCESS_LIFETIME);
        }

        $token = [
            'access_token' => $this->tokenGenerator->generate(),
            'expires_in' => $accessTokenLifetime,
            'token_type' => $this->config->getVariable(Config::CONFIG_TOKEN_TYPE),
            'scope' => $scope,
        ];

        $this->accessTokenStorage->createAccessToken(
            $token['access_token'],
            $client,
            $user,
            time() + $accessTokenLifetime,
            $scope
        );

        if ($issueRefreshToken) {
            $token['refresh_token'] = $this->tokenGenerator->generate();

            if (null === $refreshTokenLifetime) {
                $refreshTokenLifetime = (int) $this->config->getVariable(Config::CONFIG_REFRESH_LIFETIME);
            }

            $this->refreshTokenStorage->createRefreshToken(
                $token['refresh_token'],
                $client,
                $user,
                time() + $refreshTokenLifetime,
                $scope
            );
        }

        return $token;
    }

    private function createAuthCode(ClientInterface $client, ?UserInterface $user, string $redirectUri, ?string $scope = null): array
    {
        $token = [
            'code' => $this->tokenGenerator->generate(),
        ];

        $this->authCodeStorage->createAuthCode(
            $token['code'],
            $client,
            $user,
            $redirectUri,
            time() + (int) $this->config->getVariable(Config::CONFIG_AUTH_LIFETIME),
            $scope
        );

        return $token;
    }

    private function checkScope(?string $requiredScope, ?string $availableScope): bool
    {
        $requiredData = [];
        if (null !== $requiredScope) {
            $requiredData = explode(' ', trim($requiredScope));
        }

        $availableData = [];
        if (null !== $availableScope) {
            $availableData = explode(' ', trim($availableScope));
        }

        return 0 === count(array_diff($requiredData, $availableData));
    }
}
