<?php

declare(strict_types=1);

namespace App\ExternalERP\Mapping\Mapper;

use App\ExternalERP\Mapping\Attribute\ERPField;

class FieldMapper
{
    /**
     * @param class-string $class
     */
    public function getMapping(string $class): array
    {
        $reflectionClass = new \ReflectionClass($class);

        $mapping = [];
        foreach ($reflectionClass->getProperties() as $property) {
            $formattedName = $property->getName();
            foreach ($property->getAttributes() as $attribute) {
                if (!($erpField = $attribute->newInstance()) instanceof ERPField) {
                    continue;
                }

                $formattedName = $erpField->name;
            }
            $mapping[$property->getName()] = $formattedName;
        }

        return $mapping;
    }
}
