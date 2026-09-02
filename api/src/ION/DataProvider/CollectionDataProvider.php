<?php

declare(strict_types=1);

namespace App\ION\DataProvider;

use ApiPlatform\Metadata\Operation;
use App\ExternalERP\Filter\ContainsFilter;
use App\ExternalERP\Filter\EqualsFilter;
use App\ExternalERP\Mapping\Mapper\FieldMapper;
use App\ExternalERP\Resolver\OperationResolverInterface;
use App\Http\LnClient;
use App\ION\Event\IONPreRequestEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

readonly class CollectionDataProvider extends AbstractDataProvider
{
    public function __construct(
        LnClient $client,
        OperationResolverInterface $operationResolver,
        private DenormalizerInterface $denormalizer,
        private FieldMapper $fieldMapper,
        private EventDispatcherInterface $eventDispatcher,
    ) {
        parent::__construct($client, $operationResolver);
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        /** @var Request $request */
        $request = $context['request'];

        $mapping = $this->fieldMapper->getMapping($operation->getClass());
        $filters = $this->buildFilters($request, $mapping);

        $this->eventDispatcher->dispatch($event = new IONPreRequestEvent($operation->getClass()));

        $response = $this->client->doRequest($this->getOperation($operation, $event->getParameters()), $filters);

        return $this->denormalizer->denormalize(json_decode($response->getContent(), true)['value'], \sprintf('%s[]', $operation->getClass()), 'jsonld', $context);
    }

    public function buildFilters(Request $request, array $mapping): array
    {
        $filters = [];

        foreach ($request->query as $filter => $properties) {
            foreach ($properties as $property => $value) {
                $formattedProperty = $mapping[$property] ?? $property;

                match ($filter) {
                    ContainsFilter::FILTER_PROPERTY => $filters[] = \sprintf("%s(%s,'%s')", $filter, $formattedProperty, $value),
                    EqualsFilter::FILTER_PROPERTY => $filters[] = \sprintf('%s %s %s', $formattedProperty, $filter, $value),
                    default => null,
                };
            }
        }

        return $filters;
    }
}
