<?php

declare(strict_types=1);

namespace App\ION\DataProvider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use EasySlugger\Slugger;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class CachedCollectionDataProvider implements ProviderInterface
{
    public function __construct(
        protected CacheInterface $arrayCache,
        private readonly CollectionDataProvider $collectionDataProvider
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        return $this->arrayCache->get(
            Slugger::slugify(\sprintf('%s-%s-%s', $operation->getClass(), $operation->getName(), hash('sha256', json_encode($context)))),
            function (ItemInterface $item) use ($operation, $uriVariables, $context) {
                return $this->collectionDataProvider->provide($operation, $uriVariables, $context);
            }
        );
    }
}
