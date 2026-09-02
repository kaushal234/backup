<?php

declare(strict_types=1);

namespace LegacyBundle\Doctrine\Mapping\Mapper;

use LegacyBundle\Doctrine\Mapping\Attributes\Column;
use LegacyBundle\Doctrine\Mapping\Attributes\EmbeddedColumn;
use LegacyBundle\Doctrine\Mapping\Attributes\ExtraColumn;
use LegacyBundle\Doctrine\Mapping\Attributes\ExtraTable;
use LegacyBundle\Doctrine\Mapping\Attributes\ExtraTableColumn;
use LegacyBundle\Doctrine\Mapping\Attributes\Id;
use LegacyBundle\Doctrine\Mapping\Attributes\Synchronize;

class DoubleWriteMapper
{
    /**
     * @var string
     */
    final public const OPERATION_PERSIST = 'persist';
    /**
     * @var string
     */
    final public const OPERATION_UPDATE = 'update';
    /**
     * @var string
     */
    final public const OPERATION_REMOVE = 'remove';

    private array $mappings = [];
    private array $defaultMapping = [
        'table' => null,
        'extra_columns' => [],
        'extra_tables' => [],
        'columns' => [],
        'operations' => [
            self::OPERATION_PERSIST,
            self::OPERATION_UPDATE,
            self::OPERATION_REMOVE,
        ],
    ];

    public function supports(\ReflectionClass $class)
    {
        $mapping = $this->getMapping($class);

        return $this->hasTableMapping($mapping);
    }

    public function supportsOperation(\ReflectionClass $class, $operation)
    {
        $mapping = $this->getMapping($class);

        return $this->hasTableMapping($mapping) && \in_array($operation, $mapping['operations'], true);
    }

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

        return $parentClass ? $this->getMapping($parentClass) : $this->defaultMapping;
    }

    private function loadMapping(\ReflectionClass $class)
    {
        $mapping = $this->getDefaultMapping($class);
        if (!empty($synchronizeAttributes = $class->getAttributes(Synchronize::class))) {
            $synchronizeAttribute = $synchronizeAttributes[0];
            $synchronize = $synchronizeAttribute->newInstance();

            $mapping['table'] = $synchronize->table;

            if ($synchronize->isNestedEntity) {
                $mapping['operations'] = [self::OPERATION_UPDATE];
            }

            $mapping['force_update'] = $synchronize->forceUpdate;
        }

        if (!empty($idAttributes = $class->getAttributes(Id::class))) {
            $idAttribute = $idAttributes[0];
            $id = $idAttribute->newInstance();
            $mapping['id_field'] = $id->path;
        }

        $attributes = $class->getAttributes();

        // Add ExtraColumns
        foreach ($attributes as $attribute) {
            if (ExtraColumn::class !== $attribute->getName()) {
                continue;
            }

            $column = $attribute->newInstance();
            $mapping['extra_columns'][$column->column] = [
                'column' => $column->column,
                'value' => $column->value,
                'transformer' => (array) $column->transformer,
                'options' => \is_array($column->options) ? $column->options : [],
            ];
        }

        foreach ($attributes as $attribute) {
            if (ExtraTable::class !== $attribute->getName()) {
                continue;
            }

            $column = $attribute->newInstance();
            foreach ($column->properties as $property) {
                if (!$property instanceof ExtraTableColumn) {
                    continue;
                }
                $mapping['extra_tables'][$column->table][] = [
                    'column' => $property->column,
                    'property' => $property->property,
                    'key' => $property->key,
                    'value' => $property->value,
                    'transformer' => (array) $property->transformer,
                    'options' => $property->options,
                ];
            }
        }

        $properties = $class->getProperties();

        $annotationInheritanceMap = [];
        foreach ($properties as $property) {
            $annotationInheritanceMap = $this->getAttributeInheritanceMap($annotationInheritanceMap, $class, $property);

            foreach ($property->getAttributes() as $attribute) {
                if (!($column = $attribute->newInstance()) instanceof Column) {
                    continue;
                }

                $propertyName = $property->getName();

                if ($column instanceof EmbeddedColumn) {
                    $propertyName .= '.'.$column->property;
                }

                $mapping['columns'][$propertyName][$column->column] = [
                    'column' => $column->column,
                    'encoding' => $column->encoding,
                    'transformer' => (array) $column->transformer,
                    'options' => (array) $column->options,
                ];
            }

            if (!\array_key_exists('id_field', $mapping) && !empty($property->getAttributes(Id::class))) {
                $mapping['id_field'] = $property->getName();
            }
        }

        foreach ($mapping['columns'] as $propName => $columns) {
            // If Column annotations are not ALL coming from inheritance, remove inherited ones.
            if (isset($annotationInheritanceMap[$propName]) && array_keys($columns) !== array_keys($annotationInheritanceMap[$propName])) {
                $toBeRemoved = array_filter($annotationInheritanceMap[$propName], static fn (array $annotation) => !isset($annotation[0]));
                $mapping['columns'][$propName] = array_diff_key($mapping['columns'][$propName], $toBeRemoved);
            }
        }

        return $mapping;
    }

    private function getAttributeInheritanceMap(array $attributeInheritanceMap, \ReflectionClass $class, \ReflectionProperty $property, int $depth = 0): array
    {
        if ($class->hasProperty($property->getName())) {
            $property = $class->getProperty($property->getName());
            $parentAttributes = $property->getAttributes();

            foreach ($parentAttributes as $parentAttribute) {
                if (Column::class === $parentAttribute->getName()) {
                    $column = $parentAttribute->newInstance();

                    if (!\array_key_exists($property->getName(), $attributeInheritanceMap)) {
                        $attributeInheritanceMap[$property->getName()] = [];
                    }
                    if (!\array_key_exists($column->column, $attributeInheritanceMap[$property->getName()])) {
                        $attributeInheritanceMap[$property->getName()][$column->column] = [];
                    }
                    $attributeInheritanceMap[$property->getName()][$column->column][$depth] = true;
                }
            }
        }

        $parent = $class->getParentClass();
        if ($parent instanceof \ReflectionClass) {
            $attributeInheritanceMap = $this->getAttributeInheritanceMap($attributeInheritanceMap, $parent, $property, $depth + 1) + $attributeInheritanceMap;
        }

        return $attributeInheritanceMap;
    }

    private function hasTableMapping($mapping)
    {
        return isset($mapping['table']);
    }
}
