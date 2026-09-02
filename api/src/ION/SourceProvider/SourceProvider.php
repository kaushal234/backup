<?php

declare(strict_types=1);

namespace App\ION\SourceProvider;

use Alvest\FeatureDoc\Attribute\FeatureDoc;
use App\ION\ResourceSourceProvider\ResourceSourceProviderInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

#[FeatureDoc(path: 'ion-source-provider.md')]
class SourceProvider
{
    public function __construct(
        #[AutowireIterator(tag: 'app.ion.resource_source_provider')]
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
