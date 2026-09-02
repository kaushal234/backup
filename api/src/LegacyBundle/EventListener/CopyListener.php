<?php

declare(strict_types=1);

namespace LegacyBundle\EventListener;

use Doctrine\DBAL\Connection;
use LegacyBundle\Doctrine\Mapping\Mapper\CopyMapper;
use LegacyBundle\Event\UpdateEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class CopyListener implements EventSubscriberInterface
{
    private readonly CopyMapper $mapper;
    private readonly PropertyAccessorInterface $propertyAccessor;
    private readonly Connection $legacyConnection;
    private readonly LoggerInterface $logger;

    /**
     * CopyListener constructor.
     */
    public function __construct(CopyMapper $mapper, PropertyAccessorInterface $propertyAccessor, Connection $legacyConnection, LoggerInterface $logger)
    {
        $this->mapper = $mapper;
        $this->propertyAccessor = $propertyAccessor;
        $this->legacyConnection = $legacyConnection;
        $this->logger = $logger;
    }

    /**
     * @throws \Exception
     */
    public function onUpdate(UpdateEvent $event)
    {
        $object = $event->getObject();
        $class = new \ReflectionClass($object);

        if (!$this->mapper->supports($class)) {
            return;
        }

        $mapping = $this->mapper->getMapping($class);
        $changeSet = $event->getChangeSet();

        $updatedProperties = array_filter(array_keys($mapping), static fn ($property) => isset($changeSet[$property]));

        $updatedColumns = $this->getUpdatedColumns($object, $mapping, $updatedProperties);

        if ([] === $updatedColumns) {
            return;
        }

        $this->legacyConnection->beginTransaction();
        try {
            foreach ($mapping as $property => $tables) {
                foreach ($tables as $table => $config) {
                    foreach ($config['columns'] as $column) {
                        $sql = \sprintf(
                            'UPDATE %s SET %s WHERE %s',
                            $table,
                            $column.' = :newValue',
                            $column.' = :oldValue'
                        );

                        $this->logger->debug('DoubleWrite: prepare sql '.$sql);

                        $stmt = $this->legacyConnection->prepare($sql);
                        $stmt->bindValue('newValue', $this->propertyAccessor->getValue($object, $property));
                        $stmt->bindValue('oldValue', current($changeSet[$property]));
                        $stmt->executeStatement();
                    }
                }
            }
            $this->legacyConnection->commit();
        } catch (\Exception $exception) {
            $this->legacyConnection->rollBack();
            throw $exception;
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            UpdateEvent::class => 'onUpdate',
        ];
    }

    /**
     * Retrieve an array composed of columnName => newValue pairs.
     *
     * @param object $object
     *
     * @return array
     */
    private function getUpdatedColumns($object, array $mapping, array $properties)
    {
        $updatedColumns = [];
        foreach ($properties as $property) {
            $value = $this->propertyAccessor->getValue($object, $property) ?: '';
            foreach ($mapping[$property] as $legacyColumn => $mappingConfig) {
                foreach ($mappingConfig['columns'] as $column) {
                    $updatedColumns[$column] = $value;
                }
            }
        }

        return $updatedColumns;
    }
}
