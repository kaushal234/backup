<?php

declare(strict_types=1);

namespace App\Link\Mapping\Mapper;

use App\Link\Mapping\Attributes\LinkField;

class FieldMapper
{
    private array $mappings = [];

    public function supports(\ReflectionClass $class): bool
    {
        $mapping = $this->getMapping($class);

        return !empty($mapping);
    }

    public function getMapping(\ReflectionClass $class)
    {
        if (!isset($this->mappings[$class->getName()])) {
            $this->mappings[$class->getName()] = $this->loadMapping($class);
        }

        return $this->mappings[$class->getName()];
    }

    private function loadMapping(\ReflectionClass $class): array
    {
        $mapping = [];
        $properties = $class->getProperties();

        foreach ($properties as $property) {
            foreach ($property->getAttributes() as $attribute) {
                if (!($linkField = $attribute->newInstance()) instanceof LinkField) {
                    continue;
                }
                $propertyName = $property->getName();
                $mapping[$propertyName] = [
                    'fields' => (array) $linkField->fields,
                    'transformer' => (array) $linkField->transformer,
                    'options' => (array) $linkField->options,
                ];
            }
        }

        return $mapping;
    }
}
