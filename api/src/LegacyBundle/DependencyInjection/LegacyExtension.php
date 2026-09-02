<?php

declare(strict_types=1);

namespace LegacyBundle\DependencyInjection;

use App\Tests\LegacyBundle\Manager\DMSManagerStub;
use LegacyBundle\Manager\DMSManager;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class LegacyExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('legacy.module_mapping', $config['module_mapping']);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        if ('test' === $container->getParameter('kernel.environment')) {
            $container
                ->getDefinition(DMSManager::class)
                ->setClass(DMSManagerStub::class)
                ->setArgument('$projectDir', $container->getParameter('kernel.project_dir'))
            ;
        }
    }
}
