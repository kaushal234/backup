<?php

declare(strict_types=1);

namespace LegacyBundle;

use LegacyBundle\DependencyInjection\Compiler\SecurityPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class LegacyBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new SecurityPass());
    }
}
