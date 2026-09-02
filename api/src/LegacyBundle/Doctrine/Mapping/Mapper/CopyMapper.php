<?php

declare(strict_types=1);

namespace LegacyBundle\Doctrine\Mapping\Mapper;

use LegacyBundle\Doctrine\Mapping\Attributes\Copy;

class CopyMapper
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
        $annotationInheritanceMap = [];
        $properties = $class->getProperties();

        foreach ($properties as $property) {
            $annotationInheritanceMap = $this->getAttributeInheritanceMap($annotationInheritanceMap, $class, $property);
            foreach ($property->getAttributes() as $attribute) {
                if (!($copy = $attribute->newInstance()) instanceof Copy) {
                    continue;
                }
                $propertyName = $property->getName();
                $mapping[$propertyName][$copy->table] = [
                    'columns' => $copy->columns,
                    'options' => (array) $copy->options,
                ];
            }
        }

        foreach (array_keys($mapping) as $property) {
            // If Copy annotations are not ALL coming from inheritance, remove inherited ones.
            if (isset($annotationInheritanceMap[$property]) && array_keys($mapping) !== array_keys($annotationInheritanceMap[$property])) {
                $toBeRemoved = array_filter($annotationInheritanceMap[$property], static fn (array $annotation) => !isset($annotation[0]));
                $mapping[$property] = array_diff_key($mapping[$property], $toBeRemoved);
            }
        }

        return $mapping;
    }

    private function getAttributeInheritanceMap(array $annotationInheritanceMap, \ReflectionClass $class, \ReflectionProperty $property, int $depth = 0): array
    {
        if ($class->hasProperty($property->getName())) {
            foreach ($property->getAttributes() as $parentAnnotation) {
                if (($copy = $parentAnnotation->newInstance()) instanceof Copy) {
                    if (!\array_key_exists($property->getName(), $annotationInheritanceMap)) {
                        $annotationInheritanceMap[$property->getName()] = [];
                    }
                    if (!\array_key_exists($copy->table, $annotationInheritanceMap[$property->getName()])) {
                        $annotationInheritanceMap[$property->getName()][$copy->table] = [];
                    }
                    $annotationInheritanceMap[$property->getName()][$copy->table][$depth] = true;
                }
            }
        }
        $parent = $class->getParentClass();
        if ($parent instanceof \ReflectionClass) {
            $annotationInheritanceMap = $this->getAttributeInheritanceMap($annotationInheritanceMap, $parent, $property, $depth + 1) + $annotationInheritanceMap;
        }

        return $annotationInheritanceMap;
    }
}
