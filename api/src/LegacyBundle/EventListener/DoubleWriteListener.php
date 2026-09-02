<?php

declare(strict_types=1);

namespace LegacyBundle\EventListener;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Mapping\Annotation\SoftDeleteable;
use LegacyBundle\Doctrine\Mapping\Mapper\DoubleWriteMapper;
use LegacyBundle\Entity\LegacyIdInterface;
use LegacyBundle\Event\PersistEvent;
use LegacyBundle\Event\RemoveEvent;
use LegacyBundle\Event\UpdateEvent;
use LegacyBundle\Exception\NotTransformablePropertyException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class DoubleWriteListener implements EventSubscriberInterface
{
    private readonly DoubleWriteMapper $mapper;
    private readonly PropertyAccessorInterface $propertyAccessor;
    private readonly Connection $legacyConnection;
    private array $transformers = [];
    private readonly LoggerInterface $logger;
    private readonly EntityManagerInterface $entityManager;

    public function __construct(
        DoubleWriteMapper $mapper,
        PropertyAccessorInterface $propertyAccessor,
        Connection $legacyConnection,
        LoggerInterface $logger,
        EntityManagerInterface $entityManager,
        iterable $transformers
    ) {
        $this->mapper = $mapper;
        $this->propertyAccessor = $propertyAccessor;
        $this->legacyConnection = $legacyConnection;
        $this->logger = $logger;
        $this->entityManager = $entityManager;

        foreach ($transformers as $transformer) {
            $this->transformers[$transformer::class] = $transformer;
        }
    }

    public function onPersist(PersistEvent $event)
    {
        $object = $event->getObject();
        $class = new \ReflectionClass($object);

        if (!$this->mapper->supportsOperation($class, DoubleWriteMapper::OPERATION_PERSIST)) {
            return;
        }

        $mapping = $this->mapper->getMapping($class);
        $updatedColumns = $this->getUpdatedColumns($object, $mapping, array_keys($mapping['columns']));
        if ([] === $updatedColumns) {
            return;
        }

        if ($mapping['force_update']) {
            $changeset = [];
            $colToProp = [];
            foreach ($mapping['columns'] as $prop => $infos) {
                foreach (array_keys($infos) as $legacy_column) {
                    $colToProp[$legacy_column] = $prop;
                }
            }
            foreach ($updatedColumns as $column => $value) {
                $changeset[$colToProp[$column]] = [null, $value];
            }

            $this->onUpdate(new UpdateEvent($object, $changeset));

            return;
        }

        $sql = \sprintf(
            'INSERT INTO %s SET %s',
            $mapping['table'],
            implode(', ', array_map(static fn ($column) => $column.' = :'.$column, array_keys($updatedColumns)))
        );

        $this->logger->debug('DoubleWrite: prepare sql '.$sql);

        $this->legacyConnection->beginTransaction();
        try {
            $stmt = $this->legacyConnection->prepare($sql);
            foreach ($updatedColumns as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->executeStatement();

            $this->propertyAccessor->setValue($object, $mapping['id_field'], $this->legacyConnection->lastInsertId());

            $queries = [];
            foreach ($mapping['extra_tables'] as $legacyTable => $columns) {
                $updatedExtraColumns = $this->getUpdatedExtraColums($object, $columns);
                $extraTableSql = \sprintf(
                    'INSERT INTO %s SET %s',
                    $legacyTable,
                    implode(', ', array_map(static fn ($column) => $column.' = :'.$column, array_keys($updatedExtraColumns)))
                );
                $queries[] = [
                    'sql' => $extraTableSql,
                    'parameters' => $updatedExtraColumns,
                ];
                $this->logger->debug('DoubleWrite: prepare sql '.$extraTableSql);
            }
            foreach ($queries as $query) {
                $stmt = $this->legacyConnection->prepare($query['sql']);
                foreach ($query['parameters'] as $key => $value) {
                    $stmt->bindValue($key, $value);
                }
                $stmt->executeStatement();
            }

            $this->legacyConnection->commit();
        } catch (\Exception $exception) {
            $this->legacyConnection->rollBack();
            throw $exception;
        }
    }

    public function onUpdate(UpdateEvent $event)
    {
        $object = $event->getObject();
        $class = new \ReflectionClass($object);
        if (!$this->mapper->supportsOperation($class, DoubleWriteMapper::OPERATION_UPDATE)) {
            return;
        }

        $mapping = $this->mapper->getMapping($class);
        $changeSet = $event->getChangeSet();

        foreach ($changeSet as $item) {
            if ($item[1] instanceof LegacyIdInterface) {
                $this->entityManager->refresh($item[1]);
            }
        }

        $updatedProperties = array_filter(array_keys($mapping['columns']), static function ($property) use ($changeSet, $mapping) {
            $matchEmbeddables = false;
            foreach ($mapping['columns'][$property] as $column) {
                if (!isset($column['options']['embeddedFields'])) {
                    continue;
                }
                foreach ($column['options']['embeddedFields'] as $mappedField) {
                    if (!isset($changeSet[$mappedField])) {
                        continue;
                    }
                    $matchEmbeddables = true;
                }
            }

            return isset($changeSet[$property]) || $matchEmbeddables;
        });
        $updatedColumns = $this->getUpdatedColumns($object, $mapping, $updatedProperties);

        $queries = [];

        if ([] !== $updatedColumns) {
            $extraTableSql = \sprintf(
                'UPDATE %s SET %s WHERE id = :id',
                $mapping['table'],
                implode(', ', array_map(static fn ($column) => $column.' = :'.$column, array_keys($updatedColumns))));
            $queries[] = [
                'sql' => $extraTableSql,
                'parameters' => array_merge($updatedColumns, ['id' => $this->propertyAccessor->getValue($object, $mapping['id_field'])]),
            ];
            $this->logger->debug('DoubleWrite: prepare sql '.$extraTableSql);
        }

        foreach ($mapping['extra_tables'] as $legacyTable => $columns) {
            $updatedExtraColumns = $this->getUpdatedExtraColums($object, $columns);
            $whereParameters = $this->getUpdatedExtraColums($object, array_filter($columns, static fn ($column) => true === ($column['key'] ?? null)), $changeSet);

            if ([] === $whereParameters) {
                throw new \InvalidArgumentException('You should configure a key to define how to write in the extra table');
            }

            $queries[] = ['sql' => \sprintf(
                'UPDATE %s SET %s WHERE %s',
                $legacyTable,
                implode(', ', array_map(static fn ($column) => $column.' = :'.$column, array_keys($updatedExtraColumns))),
                implode(' AND ', array_map(static fn ($column) => $column.' = :'.$column, array_keys($whereParameters)))
            ),
                'parameters' => array_merge($updatedExtraColumns, $whereParameters),
            ];
        }

        $this->insertLegacyQueries($queries);
    }

    public function onRemove(RemoveEvent $event)
    {
        $object = $event->getObject();
        $class = new \ReflectionClass($object);

        if (!$this->mapper->supportsOperation($class, DoubleWriteMapper::OPERATION_REMOVE)) {
            return;
        }

        $reflection = new \ReflectionClass(ClassUtils::getRealClass($class->getName()));
        $softDelete = !empty($reflection->getAttributes(SoftDeleteable::class));
        $mapping = $this->mapper->getMapping($class);

        $queries = [];
        $queries[] = [
            'sql' => $softDelete ? \sprintf('UPDATE %s SET deleted_at = "%s" WHERE id = :id', $mapping['table'], (new \DateTime())->format('Y-m-d')) : \sprintf('DELETE FROM %s WHERE id = :id', $mapping['table']),
            'parameters' => ['id' => $this->propertyAccessor->getValue($object, $mapping['id_field'])],
        ];

        foreach ($mapping['extra_tables'] as $legacyTable => $columns) {
            $whereParameters = $this->getUpdatedExtraColums($object, array_filter($columns, static fn ($column) => true === ($column['key'] ?? null)));
            if ([] === $whereParameters) {
                throw new \InvalidArgumentException('You should configure a key to define how to write in the extra table');
            }
            $whereCondition = implode(' AND ', array_map(static fn ($column) => $column.' = :'.$column, array_keys($whereParameters)));
            $queries[] = [
                'sql' => $softDelete ? \sprintf('UPDATE %s SET deleted_at = "%s" WHERE %s', $legacyTable, (new \DateTime())->format('Y-m-d'), $whereCondition) : \sprintf('DELETE FROM %s WHERE %s', $legacyTable, $whereCondition),
                'parameters' => $whereParameters,
            ];
        }

        $this->insertLegacyQueries($queries);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            PersistEvent::class => 'onPersist',
            UpdateEvent::class => 'onUpdate',
            RemoveEvent::class => 'onRemove',
        ];
    }

    private function getUpdatedColumns($object, array $mapping, array $properties): array
    {
        $updatedColumns = [];
        foreach ($properties as $property) {
            $value = $this->propertyAccessor->getValue($object, $property) ?: '';
            foreach ($mapping['columns'][$property] as $legacyColumn => $mappingConfig) {
                try {
                    $updatedColumns[$mappingConfig['column']] = $this->transformValue(
                        $value, $mappingConfig, $value, $object
                    );
                } catch (NotTransformablePropertyException $notTransformablePropertyException) {
                }
            }
        }

        foreach ($mapping['extra_columns'] as $legacyColumn => $mappingConfig) {
            try {
                $updatedColumns[$mappingConfig['column']] = $this->transformValue(
                    $object, $mappingConfig, $mappingConfig['value'], $object
                );
            } catch (\Exception $notTransformablePropertyException) {
            }
        }

        return $updatedColumns;
    }

    private function getUpdatedExtraColums(object $object, array $mapping, array $changeSet = []): array
    {
        $updatedColumns = [];
        foreach ($mapping as $mappingConfig) {
            if ((null === $mappingConfig['value'] && null === $mappingConfig['property']) || (null !== $mappingConfig['value'] && null !== $mappingConfig['property'])) {
                throw new \InvalidArgumentException('You should configure either the value or the property');
            }
            if (null !== $mappingConfig['value']) {
                $updatedColumns[$mappingConfig['column']] = $mappingConfig['value'];
                continue;
            }

            $value = $changeSet[$mappingConfig['property']][0] ?? $this->propertyAccessor->getValue($object, $mappingConfig['property']) ?: '';

            try {
                $updatedColumns[$mappingConfig['column']] = $this->transformValue(
                    $value, $mappingConfig, $value, $object
                );
            } catch (\Exception $notTransformablePropertyException) {
            }
        }

        return $updatedColumns;
    }

    private function insertLegacyQueries(array $queries): void
    {
        $this->legacyConnection->beginTransaction();
        try {
            foreach ($queries as $query) {
                $stmt = $this->legacyConnection->prepare($query['sql']);
                foreach ($query['parameters'] as $key => $value) {
                    $stmt->bindValue($key, $value);
                }
                $stmt->executeStatement();
            }
            $this->legacyConnection->commit();
        } catch (\Exception $exception) {
            $this->legacyConnection->rollBack();
            throw $exception;
        }
    }

    private function transformValue($value, array $mappingConfig, $defaultValue, $object)
    {
        $transformers = $mappingConfig['transformer'];
        if (empty($transformers)) {
            return $defaultValue;
        }

        foreach ($transformers as $transformer) {
            if (!\array_key_exists($transformer, $this->transformers)) {
                continue;
            }
            $value = $this->transformers[$transformer]($value, $mappingConfig['options'], $object);
        }

        return $value;
    }
}
