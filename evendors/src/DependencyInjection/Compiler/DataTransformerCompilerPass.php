<?php

declare(strict_types=1);

namespace App\DependencyInjection\Compiler;

use App\Sdk\DataTransformer\DataTransformer;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class DataTransformerCompilerPass implements CompilerPassInterface
{
    public const RESOURCE_TRANSFORMER_TAG = 'app.sdk.resource_transformer';

    public function process(ContainerBuilder $container): void
    {
        $resourceTransformers = $container->findTaggedServiceIds(self::RESOURCE_TRANSFORMER_TAG);
        $dataTransformer = $container->findDefinition(DataTransformer::class);
        foreach ($resourceTransformers as $service => $_) {
            $dataTransformer->addMethodCall('addResourceTransformer', [new Reference($service)]);
        }
    }
}
