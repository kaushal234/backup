<?php

declare(strict_types=1);

namespace App\SageParts\P21\DataProvider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ExternalERP\Filter\ContainsFilter;
use App\ExternalERP\Filter\EqualsFilter;
use App\Http\SagePartsClient;
use App\SageParts\P21\SourceProvider\SourceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class CollectionDataProvider implements ProviderInterface
{
    public function __construct(
        private readonly SourceProvider $sourceProvider,
        private readonly SagePartsClient $client,
        private readonly DenormalizerInterface $denormalizer,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $resourceSourceProvider = $this->sourceProvider->getResourceSourceProvider($operation->getClass());

        /** @var Request $request */
        $request = $context['request'];

        $filters = [];

        foreach ($request->query as $filter => $properties) {
            foreach ($properties as $property => $value) {
                $sageField = $resourceSourceProvider->getFieldMapping()[$property];
                switch ($filter) {
                    case ContainsFilter::FILTER_PROPERTY:
                        $filters[] = \sprintf("%s(%s,'%s')", $filter, $sageField, $value);
                        break;
                    case EqualsFilter::FILTER_PROPERTY:
                        $filters[] = \sprintf('%s %s %s', $sageField, $filter, $value);
                        break;
                }
            }
        }

        if (0 === \count($filters)) {
            throw new BadRequestHttpException('At least one filter is needed.');
        }

        return $this->denormalizer->denormalize(json_decode($this->client->doRequest($resourceSourceProvider, [...$context, 'filters' => $filters])->getContent(), true)['value'], \sprintf('%s[]', $operation->getClass()), 'jsonld', $context);
    }
}
