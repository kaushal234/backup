<?php

declare(strict_types=1);

namespace Alvest\TwigHelper\DependencyInjection;

use InvalidArgumentException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class TwigHelperExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @param array<array<mixed>> $configs
     *
     * @throws InvalidArgumentException
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('twig.yaml');
    }

    public function prepend(ContainerBuilder $container): void
    {
        if (!$container->hasExtension('twig')) {
            return;
        }

        $container->prependExtensionConfig('twig', ['paths' => [__DIR__.'/../../templates' => 'AlvestTwigHelper']]);
        $container->prependExtensionConfig('framework', [
            'translator' => [
                'paths' => [__DIR__.'/../../translations'],
            ],
        ]);
    }
}
