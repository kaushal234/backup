<?php

declare(strict_types=1);

namespace LegacyBundle\Doctrine\Transformer;

use Doctrine\DBAL\Connection;
use LegacyBundle\Doctrine\Mapping\Mapper\DoubleWriteMapper;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class ObjectToLegacyProperty
{
    private readonly Connection $legacyConnection;

    private readonly PropertyAccessorInterface $propertyAccessor;

    private readonly DoubleWriteMapper $mapper;

    public function __construct(Connection $legacyConnection, PropertyAccessorInterface $propertyAccessor, DoubleWriteMapper $mapper)
    {
        $this->legacyConnection = $legacyConnection;
        $this->propertyAccessor = $propertyAccessor;
        $this->mapper = $mapper;
    }

    public function __invoke($object, array $options)
    {
        $mapping = $this->getMapping($object);

        $id = $this->propertyAccessor->getValue($object, $mapping['id_field']);

        $sql = \sprintf(
            'SELECT %s AS value FROM %s WHERE id = :id',
            $options['property'],
            $mapping['table']
        );
        $stmt = $this->legacyConnection->prepare($sql);
        $stmt->bindValue('id', $id);
        $value = $stmt->executeQuery()->fetchAssociative();

        if (false === $value) {
            throw new \Exception('The identifier "%s" does not exists', $id);
        }

        return $value['value'];
    }

    protected function getMapping($object)
    {
        $class = new \ReflectionClass($object);

        if (!$this->mapper->supports($class)) {
            throw new \Exception('Not supported object');
        }

        return $this->mapper->getMapping($class);
    }
}
