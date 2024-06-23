<?php

declare(strict_types=1);

namespace OAuth\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

class GrantExtensionsCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $customGrantDefinition = $container->findDefinition('oauth_server.grant_extension.custom');

        foreach ($container->findTaggedServiceIds('oauth_server.grant_extension') as $id => $tags) {
            foreach ($tags as $tag) {
                if (empty($tag['uri'])) {
                    throw new InvalidArgumentException(sprintf('Service "%s" must define the "uri" attribute on "oauth_server.grant_extension" tags.', $id));
                }

                $customGrantDefinition->addMethodCall('addExtension', [$tag['uri'], new Reference($id)]);
            }
        }
    }
}
