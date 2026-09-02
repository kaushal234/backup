<?php

declare(strict_types=1);

namespace LegacyBundle\Command\Helper;

use Doctrine\Persistence\ObjectRepository;

class FirstOrNullEntityCacheHelper
{
    private readonly ObjectRepository $repository;

    private readonly string $descProperty;

    private array $cache = [];

    public function __construct(
        ObjectRepository $repository,
        string $descProperty
    ) {
        $this->repository = $repository;
        $this->descProperty = $descProperty;
    }

    public function fetch(string $descr)
    {
        if (!isset($this->cache[$descr])) {
            $entity = $this->repository->findBy([$this->descProperty => $descr], [$this->descProperty => 'asc']);

            $this->cache[$descr] = [] === $entity ? null : $entity[0];
        }

        return $this->cache[$descr];
    }
}
