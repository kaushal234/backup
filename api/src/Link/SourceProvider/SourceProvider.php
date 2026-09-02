<?php

declare(strict_types=1);

namespace App\Link\SourceProvider;

use App\Link\ResourceSourceProvider\ResourceSourceProviderInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class SourceProvider
{
    public function __construct(
        #[AutowireIterator(tag: 'app.link.resource_source_provider')]
        private readonly iterable $resourceSourceProviders
    ) {
    }

    public function getResourceSourceProvider(string $class): ResourceSourceProviderInterface
    {
        foreach ($this->resourceSourceProviders as $resourceSourceProvider) {
            if ($resourceSourceProvider->supports($class)) {
                return $resourceSourceProvider;
            }
        }

        throw new UnprocessableEntityHttpException(\sprintf('No supportive Resource Source Provider found for class %s', $class));
    }
}
