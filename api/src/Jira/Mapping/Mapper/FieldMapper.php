<?php

declare(strict_types=1);

namespace App\Jira\Mapping\Mapper;

use App\Jira\Mapping\Attributes\JiraField;

class FieldMapper
{
    private array $mappings = [];

    public function supports(\ReflectionClass $class): bool
    {
        $mapping = $this->getMapping($class);

        return !empty($mapping);
    }

    public function getMapping(\ReflectionClass $class): array
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
                if (!($jiraField = $attribute->newInstance()) instanceof JiraField) {
                    continue;
                }
                $propertyName = $property->getName();
                $mapping[$propertyName][] = [
                    'transformer' => (array) $jiraField->transformer,
                    'options' => (array) $jiraField->options,
                ];
            }
        }

        return $mapping;
    }
}
