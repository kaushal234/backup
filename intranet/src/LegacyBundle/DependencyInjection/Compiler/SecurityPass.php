<?php

declare(strict_types=1);

namespace LegacyBundle\DependencyInjection\Compiler;

use LegacyBundle\Security\Http\Firewall\Firewall;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SecurityPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('security.firewall')) {
            return;
        }
        $firewall = $container->findDefinition('security.firewall');
        $firewall->setClass(Firewall::class);
    }
}
