<?php

declare(strict_types=1);

namespace App\Report\Handler\HumanResources;

use App\Entity\Report\ReportSnapshot;
use App\Report\DataProvider\Extractor\QueryBuilderExtractor;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\Report;
use Doctrine\ORM\EntityManagerInterface;

class EmployeeStaffingSnapshotHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IsGrantedTrait;

    private readonly EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (Report::class !== $resourceClass || 'businessUnit' !== $x || 'createdAt' !== $y) {
            return null;
        }

        $queryBuilder = $this->entityManager->createQueryBuilder();

        $queryBuilder
            ->addSelect('COUNT(r) AS value')
            ->addSelect("CONCAT('resource=', r.resource, ';x=', r.x, ';y=', r.y) AS x")
            ->addSelect("DATE_FORMAT(r.createdAt, '%Y-%m-%d') AS y")
            ->from(ReportSnapshot::class, 'r')
            ->groupBy('x, y')
        ;

        foreach (['resource', 'x', 'y', 'options'] as $property) {
            if (null !== ($value = $options[$property] ?? null)) {
                if ('options' === $property) {
                    $value = json_encode($value, \JSON_THROW_ON_ERROR);
                }
                $queryBuilder
                    ->andWhere(\sprintf('r.%1$s = :%1$s', $property))
                    ->setParameter($property, $value)
                ;
            }
        }

        return new ReportDataProvider(
            (new QueryBuilderExtractor($queryBuilder))(),
            [],
            []
        );
    }
}
