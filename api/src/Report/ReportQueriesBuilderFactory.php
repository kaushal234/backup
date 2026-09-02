<?php

declare(strict_types=1);

namespace App\Report;

use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReportQueriesBuilderFactory
{
    private readonly ManagerRegistry $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function getQueriesBuilder(string $entityClass, string $x, string $y): ReportQueriesBuilder
    {
        /** @var EntityRepository $entityRepository */
        $entityRepository = $this->registry->getManager()->getRepository($entityClass);

        return new ReportQueriesBuilder($this->registry, $entityRepository, $x, $y);
    }
}
