<?php

declare(strict_types=1);

namespace App;

use App\Sdk\DataTransformer\ResourceTransformer\ResourceTransformerInterface;
use App\Sdk\Http\ResourceSourceProvider\ResourceSourceProviderInterface;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function build(ContainerBuilder $container): void
    {
        $container
            ->registerForAutoconfiguration(ResourceTransformerInterface::class)
            ->addTag(DependencyInjection\Compiler\DataTransformerCompilerPass::RESOURCE_TRANSFORMER_TAG);

        $container
            ->registerForAutoconfiguration(ResourceSourceProviderInterface::class)
            ->addTag(DependencyInjection\Compiler\SourceProviderCompilerPass::RESOURCE_SOURCE_PROVIDER_TAG);

        $container
            ->addCompilerPass(new DependencyInjection\Compiler\DataTransformerCompilerPass())
            ->addCompilerPass(new DependencyInjection\Compiler\SourceProviderCompilerPass())
        ;
    }
}
