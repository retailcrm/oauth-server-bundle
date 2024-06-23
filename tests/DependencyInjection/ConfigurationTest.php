<?php

declare(strict_types=1);

namespace OAuth\Tests\DependencyInjection;

use OAuth\DependencyInjection\Configuration;
use OAuth\Tests\Stub\Entity\AccessToken;
use OAuth\Tests\Stub\Entity\AuthCode;
use OAuth\Tests\Stub\Entity\Client;
use OAuth\Tests\Stub\Entity\RefreshToken;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    protected function setUp(): void
    {
        $this->configuration = new Configuration();
        $this->processor = new Processor();
    }

    public function testConfiguration(): void
    {
        $config = $this->processor->processConfiguration($this->configuration, [[
            'client_class' => Client::class,
            'access_token_class' => AccessToken::class,
            'refresh_token_class' => RefreshToken::class,
            'auth_code_class' => AuthCode::class,
            'user_provider' => 'in_memory_user_provider',
            'options' => [
                'foo' => 'bar',
            ],
        ]]);

        $this->assertEquals([
            'client_class' => Client::class,
            'access_token_class' => AccessToken::class,
            'refresh_token_class' => RefreshToken::class,
            'auth_code_class' => AuthCode::class,
            'user_provider' => 'in_memory_user_provider',
            'options' => [
                'foo' => 'bar',
            ],
        ], $config);
    }
}
