<?php

declare(strict_types=1);

namespace LegacyBundle\Command\Helper;

use Doctrine\Persistence\ObjectRepository;

class EntityCacheHelper
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

    public function fetch(?string $descr, array $extraCriteria = [])
    {
        if (!isset($this->cache[$descr])) {
            $this->cache[$descr] = $this->repository->findOneBy([$this->descProperty => $descr] + $extraCriteria);
        }

        return $this->cache[$descr];
    }

    public function getRepository(): ObjectRepository
    {
        return $this->repository;
    }

    public function getDescProperty(): string
    {
        return $this->descProperty;
    }
}
