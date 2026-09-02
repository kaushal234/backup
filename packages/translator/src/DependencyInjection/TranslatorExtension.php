<?php

declare(strict_types=1);

namespace Alvest\Translator\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

class TranslatorExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        // No configuration to load
    }

    public function prepend(ContainerBuilder $container): void
    {
        if (!$container->hasExtension('translator')) {
            return;
        }

        $container->prependExtensionConfig('framework', [
            'translator' => [
                'paths' => [__DIR__.'/../../translations'],
            ],
        ]);
    }
}
