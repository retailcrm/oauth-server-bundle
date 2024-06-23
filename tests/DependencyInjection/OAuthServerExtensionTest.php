<?php

declare(strict_types=1);

namespace OAuth\Tests\DependencyInjection;

use OAuth\Command\CleanCommand;
use OAuth\Command\CreateClientCommand;
use OAuth\Doctrine\Storage\AccessTokenStorage;
use OAuth\Doctrine\Storage\AuthCodeStorage;
use OAuth\Doctrine\Storage\ClientStorage;
use OAuth\Doctrine\Storage\RefreshTokenStorage;
use OAuth\Security\Authenticator\OAuthAuthenticator;
use OAuth\Server\BearerToken\ChainExtractor;
use OAuth\Server\BearerToken\FormExtractor;
use OAuth\Server\BearerToken\HeaderExtractor;
use OAuth\Server\BearerToken\QueryExtractor;
use OAuth\Server\Config;
use OAuth\Server\GrantExtension\AuthCodeGrantExtension;
use OAuth\Server\GrantExtension\ClientCredentialsGrantExtension;
use OAuth\Server\GrantExtension\CustomGrantExtension;
use OAuth\Server\GrantExtension\RefreshTokenGrantExtension;
use OAuth\Server\GrantExtension\UserCredentialsGrantExtension;
use OAuth\Server\Handler;
use OAuth\Server\TokenGenerator\MtRandTokenGenerator;
use OAuth\Tests\Stub\ContainerTrait;
use OAuth\Tests\Stub\Entity\AccessToken;
use OAuth\Tests\Stub\Entity\AuthCode;
use OAuth\Tests\Stub\Entity\Client;
use OAuth\Tests\Stub\Entity\RefreshToken;
use PHPUnit\Framework\TestCase;

class OAuthServerExtensionTest extends TestCase
{
    use ContainerTrait;

    public function testLoad(): void
    {
        $container = $this->mockContainer();

        $this->assertEquals(['foo' => 'bar'], $container->getParameter('oauth_server.config.options'));
        $this->assertEquals(Client::class, $container->getParameter('oauth_server.config.client_class'));
        $this->assertEquals(AccessToken::class, $container->getParameter('oauth_server.config.access_token_class'));
        $this->assertEquals(RefreshToken::class, $container->getParameter('oauth_server.config.refresh_token_class'));
        $this->assertEquals(AuthCode::class, $container->getParameter('oauth_server.config.auth_code_class'));

        $this->assertInstanceOf(Config::class, $container->get(Config::class));

        $this->assertInstanceOf(FormExtractor::class, $container->get(FormExtractor::class));
        $this->assertInstanceOf(QueryExtractor::class, $container->get(QueryExtractor::class));
        $this->assertInstanceOf(HeaderExtractor::class, $container->get(HeaderExtractor::class));
        $this->assertInstanceOf(ChainExtractor::class, $container->get(ChainExtractor::class));
        $this->assertInstanceOf(MtRandTokenGenerator::class, $container->get(MtRandTokenGenerator::class));

        $this->assertInstanceOf(AccessTokenStorage::class, $container->get(AccessTokenStorage::class));
        $this->assertInstanceOf(RefreshTokenStorage::class, $container->get(RefreshTokenStorage::class));
        $this->assertInstanceOf(AuthCodeStorage::class, $container->get(AuthCodeStorage::class));
        $this->assertInstanceOf(ClientStorage::class, $container->get(ClientStorage::class));

        $this->assertInstanceOf(AuthCodeGrantExtension::class, $container->get(AuthCodeGrantExtension::class));
        $this->assertInstanceOf(ClientCredentialsGrantExtension::class, $container->get(ClientCredentialsGrantExtension::class));
        $this->assertInstanceOf(RefreshTokenGrantExtension::class, $container->get(RefreshTokenGrantExtension::class));
        $this->assertInstanceOf(UserCredentialsGrantExtension::class, $container->get(UserCredentialsGrantExtension::class));
        $this->assertInstanceOf(CustomGrantExtension::class, $container->get(CustomGrantExtension::class));

        $this->assertInstanceOf(Handler::class, $container->get(Handler::class));

        $this->assertInstanceOf(CleanCommand::class, $container->get(CleanCommand::class));
        $this->assertInstanceOf(CreateClientCommand::class, $container->get(CreateClientCommand::class));

        $this->assertInstanceOf(OAuthAuthenticator::class, $container->get(OAuthAuthenticator::class));
    }
}
