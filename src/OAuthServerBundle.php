<?php

declare(strict_types=1);

namespace OAuth;

use OAuth\DependencyInjection\Compiler\GrantExtensionsCompilerPass;
use OAuth\DependencyInjection\OAuthServerExtension;
use OAuth\DependencyInjection\Security\Factory\OAuthFactory;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OAuthServerBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        /** @var SecurityExtension $extension */
        $extension = $container->getExtension('security');
        $extension->addAuthenticatorFactory(new OAuthFactory());
        $container->addCompilerPass(new GrantExtensionsCompilerPass());
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        return new OAuthServerExtension();
    }
}
