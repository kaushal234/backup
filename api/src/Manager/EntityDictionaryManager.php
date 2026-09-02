<?php

declare(strict_types=1);

namespace App\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class EntityDictionaryManager
{
    private readonly EntityManagerInterface $entityManager;
    private readonly PropertyAccessorInterface $propertyAccessor;
    private array $cache = [];

    public function __construct(EntityManagerInterface $entityManager, PropertyAccessorInterface $propertyAccessor)
    {
        $this->entityManager = $entityManager;
        $this->propertyAccessor = $propertyAccessor;
    }

    public function getIndexedTable(string $class, array $keys): array
    {
        $cacheKey = md5(serialize([$class, implode(', ', $keys)]));

        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $repository = $this->entityManager->getRepository($class);

        $results = [];
        foreach ($repository->findAll() as $object) {
            $singleKey = '';
            foreach ($keys as $key) {
                $singleKey = \sprintf('%s%s', $singleKey, $this->propertyAccessor->getValue($object, $key));
            }
            $results[$singleKey] = $object;
        }

        $this->cache[$cacheKey] = $results;

        return $results;
    }
}
