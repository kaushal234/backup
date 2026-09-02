<?php

declare(strict_types=1);

namespace App\ION\Filter;

use ApiPlatform\Serializer\Filter\FilterInterface;
use App\ION\SourceProvider\SourceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\TypeInfo\TypeIdentifier;

class SelectionFilter implements FilterInterface
{
    /** @var string */
    final public const CONTEXT_SELECTION_AREA_KEY = '_ion_selection_area';
    final public const FILTER_PROPERTY = 'selection';

    public function __construct(
        public SourceProvider $sourceProvider,
    ) {
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

        if ([] === $selections = $request->query->all(self::FILTER_PROPERTY)) {
            return;
        }

        foreach ($selections as $property) {
            $context[self::CONTEXT_SELECTION_AREA_KEY][] = $property;
        }
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            static::FILTER_PROPERTY => [
                'property' => static::FILTER_PROPERTY,
                'type' => TypeIdentifier::ARRAY->value,
                'required' => false,
            ],
        ];
    }
}
