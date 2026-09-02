<?php

declare(strict_types=1);

namespace App\ExternalERP\Filter;

use ApiPlatform\Metadata\FilterInterface;

class ContainsFilter implements FilterInterface
{
    final public const FILTER_PROPERTY = 'contains';

    public function __construct(
        private readonly array $properties = []
    ) {
    }

    public function getDescription(string $resourceClass): array
    {
        $description = [];

        $properties = $this->getProperties();

        foreach ($properties as $property => $unused) {
            $description[\sprintf('%s[%s]', self::FILTER_PROPERTY, $property)] = [
                'property' => $property,
                'type' => 'string',
                'is_collection' => true,
                'description' => 'Contains filter',
                'required' => false,
            ];
        }

        return $description;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }
}
