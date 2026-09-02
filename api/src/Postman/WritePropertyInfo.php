<?php

declare(strict_types=1);

namespace App\Postman;

use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\TypeInfo\Type;

class WritePropertyInfo
{
    public function __construct(
        private readonly ResourceMetadataCollectionFactoryInterface $resourceMetadataFactory,
        private readonly PropertyInfoExtractorInterface $propertyInfoExtractor,
    ) {
    }

    public function getFields(Route $route): array
    {
        $resourceClass = $route->getDefault('_api_resource_class');

        $metadata = $this->resourceMetadataFactory->create($resourceClass);
        $operation = $metadata->getOperation($route->getDefault('_api_operation_name'));
        $denormalizeContext = $operation->getDenormalizationContext();

        return array_flip($this->propertyInfoExtractor->getProperties(
            $resourceClass,
            ['serializer_groups' => $denormalizeContext['groups'] ?? []]
        ));
    }

    public function getStructuredFields(Route $route): array
    {
        if (null === ($resourceClass = $route->getDefault('_api_resource_class'))) {
            return [];
        }

        $fields = $this->getFields($route);

        foreach ($fields as $property => $typeValue) {
            /** @var Type $value */
            $value = current($this->propertyInfoExtractor->getType($resourceClass, $property));
            $fields[$property] = $value;
        }

        return $fields;
    }
}
