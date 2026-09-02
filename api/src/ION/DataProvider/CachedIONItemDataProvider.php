<?php

declare(strict_types=1);

namespace App\ION\DataProvider;

use ApiPlatform\Metadata\Operation;
use EasySlugger\Slugger;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class CachedIONItemDataProvider extends AbstractCachedIONDataProvider
{
    public function __construct(
        protected CacheInterface $arrayCache,
        private readonly IONItemDataProvider $itemDataProvider
    ) {
        parent::__construct($arrayCache);
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (isset($context['fetch_data'])) {
            // this key is added manually in the IriToProperty transformer for double write, here we don't want it or the cache will not be hit
            unset($context['fetch_data']);
        }

        return $this->arrayCache->get(
            Slugger::slugify(\sprintf('%s-%s-%s-%s', $operation->getClass(), hash('sha256', serialize((array) $uriVariables)), $operation->getName(), hash('sha256', json_encode($context)))),
            function (ItemInterface $item) use ($operation, $uriVariables, $context) {
                return $this->itemDataProvider->provide($operation, $uriVariables, $context);
            }
        );
    }
}
