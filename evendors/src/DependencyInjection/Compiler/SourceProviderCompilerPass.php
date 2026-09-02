<?php

declare(strict_types=1);

namespace App\DependencyInjection\Compiler;

use App\Sdk\Http\SourceProvider;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class SourceProviderCompilerPass implements CompilerPassInterface
{
    public const RESOURCE_SOURCE_PROVIDER_TAG = 'app.sdk.resource_source_provider';

    public function process(ContainerBuilder $container): void
    {
        $resourceSourceProviders = $container->findTaggedServiceIds(self::RESOURCE_SOURCE_PROVIDER_TAG);
        $sourceProvider = $container->findDefinition(SourceProvider::class);
        foreach ($resourceSourceProviders as $service => $_) {
            $sourceProvider->addMethodCall('addResourceSourceProvider', [new Reference($service)]);
        }
    }
}
