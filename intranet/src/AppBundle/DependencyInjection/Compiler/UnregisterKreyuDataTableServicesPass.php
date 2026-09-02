<?php

declare(strict_types=1);

namespace AppBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class UnregisterKreyuDataTableServicesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $container->removeDefinition('kreyu_data_table.filter.type.doctrine_orm_entity');
    }
}
