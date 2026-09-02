<?php

declare(strict_types=1);

namespace App\ION\Filter;

use ApiPlatform\Serializer\Filter\FilterInterface;
use App\ION\SourceProvider\SourceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\TypeInfo\TypeIdentifier;

class DataAreaFilter implements FilterInterface
{
    /** @var string */
    final public const CONTEXT_DATA_AREA_KEY = '_ion_data_area';

    public function __construct(
        private readonly SourceProvider $sourceProvider,
        private readonly array $properties = [])
    {
    }

    public function apply(Request $request, bool $normalization, array $attributes, array &$context): void
    {
        if (!$request->attributes->has('_api_resource_class')) {
            return;
        }

        $resourceClass = $request->attributes->get('_api_resource_class');

        $resourceSourceProvider = $this->sourceProvider->getResourceSourceProvider($resourceClass);

        if (null === $resourceSourceProvider->getResource()) {
            return;
        }

        if ([] === array_intersect_key($this->properties, $request->query->all())) {
            return;
        }

        foreach (array_keys($this->properties) as $property) {
            if ($request->query->has($property)) {
                $propertyValues = $request->query->get($property);
                $context[self::CONTEXT_DATA_AREA_KEY][$property] = $propertyValues;
            }
        }
    }

    public function getDescription(string $resourceClass): array
    {
        $description = [];
        foreach (array_keys($this->properties) as $property) {
            $description[$property] = [
                'property' => $property,
                'type' => TypeIdentifier::STRING->value,
                'required' => false,
            ];
        }

        return $description;
    }
}
