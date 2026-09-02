<?php

declare(strict_types=1);

namespace App\DataProvider\Directory;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class RandomPeopleProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.collection_provider')]
        private readonly ProviderInterface $provider,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?object
    {
        $filteredPeople = $this->provider->provide($operation->withForceEager(false)->withPaginationEnabled(false), $uriVariables, $context);

        return 0 !== \count($filteredPeople) ? $filteredPeople[array_rand($filteredPeople)] : null;
    }
}
