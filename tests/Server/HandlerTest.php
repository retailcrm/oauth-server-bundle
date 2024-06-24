<?php

declare(strict_types=1);

namespace OAuth\Tests\Server;

use OAuth\Event\AfterGrantAccessEvent;
use OAuth\Exception\OAuthAuthenticateException;
use OAuth\Exception\OAuthServerException;
use OAuth\Model\AccessTokenInterface;
use OAuth\Model\ClientInterface;
use OAuth\Server\BearerToken\ChainExtractor;
use OAuth\Server\BearerToken\FormExtractor;
use OAuth\Server\BearerToken\HeaderExtractor;
use OAuth\Server\BearerToken\QueryExtractor;
use OAuth\Server\Config;
use OAuth\Server\GrantExtension\AuthCodeGrantExtension;
use OAuth\Server\GrantExtension\ClientCredentialsGrantExtension;
use OAuth\Server\GrantExtension\CustomGrantExtension;
use OAuth\Server\GrantExtension\Grant;
use OAuth\Server\GrantExtension\GrantExtensionInterface;
use OAuth\Server\GrantExtension\RefreshTokenGrantExtension;
use OAuth\Server\GrantExtension\UserCredentialsGrantExtension;
use OAuth\Server\Handler;
use OAuth\Server\Storage\AccessTokenStorageInterface;
use OAuth\Server\Storage\AuthCodeStorageInterface;
use OAuth\Server\Storage\ClientStorageInterface;
use OAuth\Server\Storage\RefreshTokenStorageInterface;
use OAuth\Server\TokenGenerator\TokenGeneratorInterface;
use OAuth\Tests\Stub\Entity\AccessToken;
use OAuth\Tests\Stub\Entity\AuthCode;
use OAuth\Tests\Stub\Entity\Client;
use OAuth\Tests\Stub\Entity\RefreshToken;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\NativePasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Security\Core\User\InMemoryUserProvider;
use Symfony\Component\Stopwatch\Stopwatch;

class HandlerTest extends TestCase
{
    protected function setUp(): void
    {
        $this->eventDispatcher = new TraceableEventDispatcher(new EventDispatcher(), new Stopwatch());
        $bearerTokenExtractor = new ChainExtractor([
            new HeaderExtractor(),
            new FormExtractor(),
            new QueryExtractor(),
        ]);
        $this->tokenGenerator = $this->createMock(TokenGeneratorInterface::class);

        $this->clientStorage = $this->createMock(ClientStorageInterface::class);
        $this->accessTokenStorage = $this->createMock(AccessTokenStorageInterface::class);
        $this->refreshTokenStorage = $this->createMock(RefreshTokenStorageInterface::class);
        $this->authCodeStorage = $this->createMock(AuthCodeStorageInterface::class);

        $this->userProviderInterface = new InMemoryUserProvider([
            'test_user' => ['password' => '$2y$13$9OD4fb/aaUI1nvhstrDpi.JRLikEc3OeV4TNqu/j6.ICTFclKUws6'],
        ]);
        $this->passwordHasherFactory = $this->createMock(PasswordHasherFactoryInterface::class);

        $this->customGrantExtension = $this->getMockBuilder(CustomGrantExtension::class)
            ->onlyMethods(['getExtensions'])
            ->getMock()
        ;

        $this->config = [];

        $this->manager = new Handler(
            $this->eventDispatcher,
            $bearerTokenExtractor,
            $this->tokenGenerator,
            new Config($this->config),
            $this->clientStorage,
            $this->accessTokenStorage,
            $this->refreshTokenStorage,
            $this->authCodeStorage,
            new AuthCodeGrantExtension($this->authCodeStorage),
            new ClientCredentialsGrantExtension(),
            new RefreshTokenGrantExtension($this->refreshTokenStorage),
            new UserCredentialsGrantExtension($this->userProviderInterface, $this->passwordHasherFactory),
            $this->customGrantExtension,
        );
    }

    public function testVerifyAccessToken(): void
    {
        $accessToken = new AccessToken(new Client('1'));
        $accessToken
            ->setToken('my_token')
            ->setExpiresAt(time() + 10)
            ->setScope('read')
        ;

        $this
            ->accessTokenStorage
            ->method('getAccessToken')
            ->willReturn($accessToken)
        ;

        $token = $this->manager->verifyAccessToken('my_token');
        $this->assertNotNull($token);
        $this->assertEquals('my_token', $token->getToken());
    }

    public static function provideVerifyAccessTokenException(): iterable
    {
        yield [
            null,
            '',
            null,
        ];

        $accessToken = new AccessToken(new Client('1'));
        $accessToken
            ->setToken('my_token')
            ->setExpiresAt(0)
            ->setScope('read')
        ;

        yield [
            $accessToken,
            'my_token',
            null,
        ];

        yield [
            null,
            'my_token',
            null,
        ];

        $accessToken = new AccessToken(new Client('1'));
        $accessToken
            ->setToken('my_token')
            ->setExpiresAt(time() + 10)
        ;

        yield [
            $accessToken,
            'my_token',
            'read',
        ];

        $accessToken = new AccessToken(new Client('1'));
        $accessToken
            ->setToken('my_token')
            ->setExpiresAt(time() + 10)
            ->setScope('read')
        ;

        yield [
            $accessToken,
            'my_token',
            'write',
        ];
    }

    /**
     * @dataProvider provideVerifyAccessTokenException
     */
    public function testVerifyAccessTokenException(
        ?AccessTokenInterface $token,
        string $tokenParam,
        ?string $scope = null
    ): void {
        $this->accessTokenStorage->method('getAccessToken')->willReturn($token);

        $this->expectException(OAuthAuthenticateException::class);

        $this->manager->verifyAccessToken($tokenParam, $scope);
    }

    public static function provideGetBearerToken(): iterable
    {
        yield [
            new Request(),
            false,
            null,
        ];

        $request = new Request();
        $request->headers->set('AUTHORIZATION', 'Bearer foo');

        yield [
            $request,
            false,
            'foo',
        ];

        $request = new Request();
        $request->headers->set('AUTHORIZATION', 'Bearer foo');
        yield [
            $request,
            true,
            'foo',
        ];

        yield [
            new Request(['access_token' => 'foo']),
            false,
            'foo',
        ];

        yield [
            new Request(['access_token' => 'foo']),
            true,
            'foo',
        ];

        yield [
            Request::create('/', 'GET', [], [], [], ['CONTENT_TYPE' => 'application/x-www-form-urlencoded'], 'access_token=foo'),
            false,
            null,
        ];

        yield [
            Request::create('/', 'POST', [], [], [], ['CONTENT_TYPE' => 'application/x-www-form-urlencoded'], 'access_token=foo'),
            false,
            'foo',
        ];

        $request = new Request();
        $request->setMethod('POST');
        $request->server->set('CONTENT_TYPE', 'multipart/form-data');
        $request->request->set('access_token', 'foo');
        yield [
            $request,
            false,
            null,
        ];

        yield [
            Request::create('/', 'POST', ['access_token' => 'foo'], [], [], ['CONTENT_TYPE' => 'application/x-www-form-urlencoded'], 'access_token=foo'),
            true,
            'foo',
        ];

        $request = new Request();
        $request->setMethod('POST');
        $request->headers->set('AUTHORIZATION', 'Bearer foo');
        $request->server->set('CONTENT_TYPE', 'application/x-www-form-urlencoded');
        yield [
            $request,
            false,
            'foo',
        ];

        $request = new Request(['access_token' => 'foo']);
        $request->headers->set('AUTHORIZATION', 'Basic Zm9vOmJhcg==');
        yield [
            $request,
            false,
            'foo',
        ];

        $createRequest = static function ($method, $contentType) {
            return Request::create('/', $method, [], [], [], ['CONTENT_TYPE' => $contentType], 'access_token=foo');
        };

        foreach ([false, true] as $removeFromRequest) {
            foreach (['POST', 'PUT', 'DELETE', 'FOOBAR'] as $method) {
                yield [
                    $createRequest($method, 'application/x-www-form-urlencoded'),
                    $removeFromRequest,
                    'foo',
                ];

                yield [
                    $createRequest($method, 'application/x-www-form-urlencoded; charset=utf-8'),
                    $removeFromRequest,
                    'foo',
                ];

                yield [
                    $createRequest($method, 'application/x-www-form-urlencoded mode=baz'),
                    $removeFromRequest,
                    'foo',
                ];

                yield [
                    $createRequest($method, 'application/x-www-form-urlencoded-foo'),
                    $removeFromRequest,
                    null,
                ];
            }
        }
    }

    /**
     * @dataProvider provideGetBearerToken
     */
    public function testGetBearerToken(Request $request, bool $removeFromRequest, ?string $expectedToken): void
    {
        $token = $this->manager->getBearerToken($request, $removeFromRequest);

        $this->assertEquals($expectedToken, $token);
    }

    public static function provideGrantAccessTokenAuthCode(): iterable
    {
        yield [
            new Request(
                [
                    'grant_type' => 'authorization_code',
                    'client_id' => 'foo',
                    'client_secret' => 'bar',
                    'code' => 'foo',
                    'redirect_uri' => 'http://google.ru',
                ]
            ),
            [
                'access_token' => 'access_token',
                'expires_in' => 3600,
                'token_type' => 'bearer',
                'scope' => 'one two three',
                'refresh_token' => 'refresh_token',
            ],
        ];

        yield [
            new Request(
                [
                    'grant_type' => 'authorization_code',
                    'client_id' => 'foo',
                    'client_secret' => 'bar',
                    'code' => 'foo',
                    'redirect_uri' => 'http://google.ru',
                    'scope' => 'two three',
                ]
            ),
            [
                'access_token' => 'access_token',
                'expires_in' => 3600,
                'token_type' => 'bearer',
                'scope' => 'two three',
                'refresh_token' => 'refresh_token',
            ],
        ];
    }

    /**
     * @dataProvider provideGrantAccessTokenAuthCode
     */
    public function testGrantAccessTokenAuthCode(Request $request, array $expectedResponse): void
    {
        $client = new Client('public_id');
        $client
            ->setSecret('bar')
            ->setRedirectUris([])
            ->setGrantTypes(['authorization_code'])
        ;

        $this->clientStorage
            ->method('getClient')
            ->willReturn($client)
        ;

        $authCode = new AuthCode($client);
        $authCode
            ->setToken('auth_token')
            ->setExpiresAt(time() + 20)
            ->setScope('one two three')
        ;

        $this->authCodeStorage
            ->method('getAuthCode')
            ->willReturn($authCode)
        ;

        $this->tokenGenerator
            ->method('generate')
            ->willReturn('access_token', 'refresh_token')
        ;

        $response = $this->manager->grantAccessToken($request);

        $this->assertEquals($expectedResponse, json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR));
        $this->assertEquals([AfterGrantAccessEvent::class], $this->eventDispatcher->getOrphanedEvents());
    }

    public static function provideGrantAccessTokenUserCredentials(): iterable
    {
        yield [
            new Request(
                [
                    'grant_type' => 'password',
                    'client_id' => 'foo',
                    'client_secret' => 'bar',
                    'username' => 'test_user',
                    'password' => 'test_password',
                ]
            ),
        ];
    }

    /**
     * @dataProvider provideGrantAccessTokenUserCredentials
     */
    public function testGrantAccessTokenUserCredentials(Request $request): void
    {
        $client = new Client('public_id');
        $client
            ->setSecret('bar')
            ->setGrantTypes(['authorization_code', 'password'])
        ;

        $this->clientStorage
            ->method('getClient')
            ->willReturn($client)
        ;

        $this->passwordHasherFactory
            ->method('getPasswordHasher')
            ->willReturn(new NativePasswordHasher())
        ;

        $this->tokenGenerator
            ->method('generate')
            ->willReturn('access_token', 'refresh_token')
        ;

        $response = $this->manager->grantAccessToken($request);

        $this->assertEquals([
            'access_token' => 'access_token',
            'expires_in' => 3600,
            'token_type' => 'bearer',
            'scope' => null,
            'refresh_token' => 'refresh_token',
        ], json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR));
        $this->assertEquals([AfterGrantAccessEvent::class], $this->eventDispatcher->getOrphanedEvents());
    }

    public static function provideGrantAccessTokenClientCredentials(): iterable
    {
        yield [
            new Request(
                [
                    'grant_type' => 'client_credentials',
                    'client_id' => 'foo',
                    'client_secret' => 'bar',
                ]
            ),
        ];
    }

    /**
     * @dataProvider provideGrantAccessTokenClientCredentials
     */
    public function testGrantAccessTokenClientCredentials(Request $request): void
    {
        $client = new Client('public_id');
        $client
            ->setSecret('bar')
            ->setGrantTypes(['client_credentials'])
        ;

        $this->clientStorage
            ->method('getClient')
            ->willReturn($client)
        ;

        $this->tokenGenerator
            ->method('generate')
            ->willReturn('access_token', 'refresh_token')
        ;

        $response = $this->manager->grantAccessToken($request);

        $this->assertEquals([
            'access_token' => 'access_token',
            'expires_in' => 3600,
            'token_type' => 'bearer',
            'scope' => null,
        ], json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR));
        $this->assertEquals([AfterGrantAccessEvent::class], $this->eventDispatcher->getOrphanedEvents());
    }

    public static function provideGrantAccessTokenRefreshToken(): iterable
    {
        yield [
            new Request(
                [
                    'grant_type' => 'refresh_token',
                    'client_id' => 'foo',
                    'client_secret' => 'bar',
                    'refresh_token' => 'test_refresh_token',
                ]
            ),
        ];
    }

    /**
     * @dataProvider provideGrantAccessTokenRefreshToken
     */
    public function testGrantAccessTokenRefreshToken(Request $request): void
    {
        $client = new Client('public_id');
        $client
            ->setSecret('bar')
            ->setGrantTypes(['refresh_token'])
        ;

        $this->clientStorage
            ->method('getClient')
            ->willReturn($client)
        ;

        $refreshToken = new RefreshToken($client);
        $refreshToken
            ->setToken('test_refresh_token')
            ->setExpiresAt(time() + 20)
            ->setScope('read')
        ;

        $this->refreshTokenStorage
            ->method('getRefreshToken')
            ->willReturn($refreshToken)
        ;

        $this->tokenGenerator
            ->method('generate')
            ->willReturn('access_token', 'refresh_token')
        ;

        $response = $this->manager->grantAccessToken($request);

        $this->assertEquals([
            'access_token' => 'access_token',
            'expires_in' => 3600,
            'token_type' => 'bearer',
            'scope' => 'read',
            'refresh_token' => 'refresh_token',
        ], json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR));
        $this->assertEquals([AfterGrantAccessEvent::class], $this->eventDispatcher->getOrphanedEvents());
    }

    public static function provideGrantAccessTokenCustom(): iterable
    {
        yield [
            new Request(
                [
                    'grant_type' => 'urn:custom',
                    'client_id' => 'foo',
                    'client_secret' => 'bar',
                ]
            ),
        ];
    }

    /**
     * @dataProvider provideGrantAccessTokenCustom
     */
    public function testGrantAccessTokenCustom(Request $request): void
    {
        $client = new Client('public_id');
        $client
            ->setSecret('bar')
            ->setGrantTypes(['urn:custom'])
        ;

        $this->clientStorage
            ->method('getClient')
            ->willReturn($client)
        ;

        $custom = new class() implements GrantExtensionInterface {
            public function checkGrantExtension(ClientInterface $client, Config $config, string $grantType, array $input): Grant
            {
                return new Grant(null, null);
            }
        };

        $this->customGrantExtension
            ->method('getExtensions')
            ->willReturn(['urn:custom' => $custom])
        ;

        $this->tokenGenerator
            ->method('generate')
            ->willReturn('access_token', 'refresh_token')
        ;

        $response = $this->manager->grantAccessToken($request);

        $this->assertEquals([
            'access_token' => 'access_token',
            'expires_in' => 3600,
            'token_type' => 'bearer',
            'scope' => null,
            'refresh_token' => 'refresh_token',
        ], json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR));
        $this->assertEquals([AfterGrantAccessEvent::class], $this->eventDispatcher->getOrphanedEvents());
    }

    public static function provideGrantAccessTokenException(): iterable
    {
        yield [
            new Request(),
            'invalid_request',
        ];

        yield [
            new Request(server: ['grant_type' => 'authorization_code']),
            'invalid_request',
        ];

        yield [
            new Request(['grant_type' => 'authorization_code']),
            'invalid_client',
        ];

        yield [
            new Request([
                'grant_type' => 'authorization_code',
                'client_id' => 'foo',
            ]),
            'invalid_client',
        ];

        yield [
            new Request([
                'grant_type' => 'authorization_code',
                'client_id' => 'public_id',
            ]),
            'invalid_client',
        ];

        yield [
            new Request([
                'grant_type' => 'authorization_code',
                'client_id' => 'public_id',
                'client_secret' => 'foo',
            ]),
            'invalid_client',
        ];

        yield [
            new Request([
                'grant_type' => 'authorization_code',
                'client_id' => 'public_id',
                'client_secret' => 'bar',
            ]),
            'unauthorized_client',
        ];
    }

    /**
     * @dataProvider provideGrantAccessTokenException
     */
    public function testGrantAccessTokenException(Request $request, string $expectedMessage): void
    {
        $this->expectException(OAuthServerException::class);
        $this->expectExceptionMessage($expectedMessage);

        if ('public_id' === $request->get('client_id')) {
            $client = new Client('public_id');
            $client->setSecret('bar');

            $this->clientStorage
                ->method('getClient')
                ->willReturn($client)
            ;
        }

        $this->manager->grantAccessToken($request);
    }

    public static function provideFinishClientAuthorization(): iterable
    {
        yield [
            true,
            new Request([
                'grant_type' => 'authorization_code',
                'client_id' => 'public_id',
                'redirect_uri' => 'https://google.com',
                'response_type' => 'code',
            ]),
            null,
            null,
            'https://google.com?code=foo_token',
        ];

        yield [
            true,
            new Request([
                'grant_type' => 'authorization_code',
                'client_id' => 'public_id',
                'redirect_uri' => 'https://google.com',
                'response_type' => 'token',
            ]),
            null,
            null,
            'https://google.com#access_token=foo_token&expires_in=3600&token_type=bearer',
        ];
    }

    /**
     * @dataProvider provideFinishClientAuthorization
     */
    public function testFinishClientAuthorization(
        bool $isAuthorized,
        Request $request,
        ?array $data,
        ?string $scope,
        string $expectedLocation,
    ): void {
        $client = new Client('public_id');
        $client
            ->setSecret('bar')
            ->setRedirectUris(['https://google.com'])
        ;

        $this->clientStorage
            ->method('getClient')
            ->willReturn($client)
        ;

        $this->tokenGenerator
            ->method('generate')
            ->willReturn('foo_token')
        ;

        $response = $this->manager->finishClientAuthorization($isAuthorized, $request, $data, $scope);

        $this->assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
        $this->assertEquals($expectedLocation, $response->headers->get('Location'));
    }

    public static function provideFinishClientAuthorizationException(): iterable
    {
        yield [
            new Request([
                'grant_type' => 'authorization_code',
            ]),
            'invalid_request',
        ];

        yield [
            new Request([
                'grant_type' => 'authorization_code',
                'client_id' => 'foo',
            ]),
            'invalid_client',
        ];

        yield [
            new Request([
                'grant_type' => 'authorization_code',
                'client_id' => 'public_id',
            ]),
            'redirect_uri_mismatch',
        ];

        yield [
            new Request([
                'grant_type' => 'authorization_code',
                'client_id' => 'public_id',
                'redirect_uri' => 'http://foo.com',
            ]),
            'redirect_uri_mismatch',
        ];

        yield [
            new Request([
                'grant_type' => 'authorization_code',
                'client_id' => 'public_id',
                'redirect_uri' => 'http://bar.com',
            ]),
            'invalid_request',
        ];

        yield [
            new Request([
                'grant_type' => 'authorization_code',
                'client_id' => 'public_id',
                'redirect_uri' => 'http://bar.com',
                'response_type' => 'foo',
            ]),
            'unsupported_response_type',
        ];
    }

    /**
     * @dataProvider provideFinishClientAuthorizationException
     */
    public function testFinishClientAuthorizationException(Request $request, string $expectedMessage): void
    {
        $this->expectException(OAuthServerException::class);
        $this->expectExceptionMessage($expectedMessage);

        if ('public_id' === $request->get('client_id')) {
            $client = new Client('public_id');
            $client
                ->setSecret('bar')
                ->setRedirectUris(['http://bar.com'])
            ;

            $this->clientStorage
                ->method('getClient')
                ->willReturn($client)
            ;
        }

        $this->manager->finishClientAuthorization(true, $request);
    }
}
