<?php

declare(strict_types=1);

namespace OAuth\Tests\Stub;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\EntityManagerProvider;
use OAuth\DependencyInjection\OAuthServerExtension;
use OAuth\Tests\Stub\Entity\AccessToken;
use OAuth\Tests\Stub\Entity\AuthCode;
use OAuth\Tests\Stub\Entity\Client;
use OAuth\Tests\Stub\Entity\RefreshToken;
use OAuth\Tests\Stub\Repository\AccessTokenRepositoryStub;
use OAuth\Tests\Stub\Repository\AuthCodeRepositoryStub;
use OAuth\Tests\Stub\Repository\ClientRepositoryStub;
use OAuth\Tests\Stub\Repository\RefreshTokenRepositoryStub;
use PHPUnit\Framework\Constraint\IsAnything;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\Security\Core\User\InMemoryUserChecker;
use Symfony\Component\Security\Core\User\InMemoryUserProvider;

trait ContainerTrait
{
    abstract protected function createMock(string $originalClassName): MockObject;

    abstract public static function anything(): IsAnything;

    public function mockContainer(): ContainerBuilder
    {
        $container = new ContainerBuilder(new ParameterBag());
        $extension = new OAuthServerExtension();

        $extension->load([[
            'client_class' => Client::class,
            'access_token_class' => AccessToken::class,
            'refresh_token_class' => RefreshToken::class,
            'auth_code_class' => AuthCode::class,
            'user_provider' => 'in_memory_user_provider',
            'options' => [
                'foo' => 'bar',
            ],
        ]], $container);

        $entityProviderMock = $this
            ->createMock(EntityManagerProvider::class)
        ;

        $entityManagerMock = $this
            ->createMock(EntityManagerInterface::class)
        ;

        $entityProviderMock
            ->method('getManager')
            ->with(self::anything())
            ->willReturn($entityManagerMock)
        ;

        $entityManagerMock
            ->method('getRepository')
            ->willReturnCallback(function ($className) {
                return match ($className) {
                    AccessToken::class => new AccessTokenRepositoryStub(),
                    RefreshToken::class => new RefreshTokenRepositoryStub(),
                    AuthCode::class => new AuthCodeRepositoryStub(),
                    Client::class => new ClientRepositoryStub(),
                    default => throw new \InvalidArgumentException('Unknown repository class'),
                };
            })
        ;

        $container->set('doctrine', $entityProviderMock);
        $container->set('event_dispatcher', new EventDispatcher());
        $container->set('security.user_checker', new InMemoryUserChecker());
        $container->set('in_memory_user_provider', new InMemoryUserProvider());
        $container->set('security.password_hasher_factory', new PasswordHasherFactory([]));

        return $container;
    }
}
