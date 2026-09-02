<?php

declare(strict_types=1);

namespace App\Doctrine\Mapping\Mapper;

use App\Doctrine\Mapping\Attributes\Exclude;
use App\Doctrine\Mapping\Attributes\Loggable;
use App\Doctrine\Mapping\Attributes\LoggedDate;
use App\Doctrine\Mapping\Attributes\LoggedName;

class LoggingMapper
{
    private array $mappings = [];

    private array $defaultMapping = [
        'on' => [],
        'excluded_properties' => [],
        'renamed_properties' => [],
        'datetime_properties_formats' => [],
        'showIri' => false,
    ];

    public function getMapping(\ReflectionClass $class)
    {
        if (!isset($this->mappings[$class->getName()])) {
            $this->mappings[$class->getName()] = $this->loadMapping($class);
        }

        return $this->mappings[$class->getName()];
    }

    private function getDefaultMapping(\ReflectionClass $class)
    {
        $parentClass = $class->getParentClass();
        if (!$parentClass) {
            return $this->defaultMapping;
        }

        return $this->getMapping($parentClass);
    }

    private function loadMapping(\ReflectionClass $class)
    {
        $mapping = $this->getDefaultMapping($class);

        if (!empty($loggableAttributes = $class->getAttributes(Loggable::class))) {
            $loggableAttribute = $loggableAttributes[0];
            $loggable = $loggableAttribute->newInstance();

            $mapping['on'] = $loggable->on;
            $mapping['owner'] = $loggable->owner;
            $mapping['ownerRelation'] = $loggable->ownerRelation;
            $mapping['showIri'] = $loggable->showIri;
        }

        $properties = $class->getProperties();
        foreach ($properties as $property) {
            foreach ($property->getAttributes() as $attribute) {
                if (Exclude::class === $attribute->getName()) {
                    $mapping['excluded_properties'][] = $property->getName();
                }

                if (LoggedName::class === $attribute->getName()) {
                    $loggedName = $attribute->newInstance();
                    $mapping['renamed_properties'][$property->getName()] = $loggedName->name;
                }

                if (LoggedDate::class === $attribute->getName()) {
                    $loggedDate = $attribute->newInstance();
                    $mapping['datetime_properties_formats'][$property->getName()] = $loggedDate->format;
                }
            }
        }

        return $mapping;
    }
}
