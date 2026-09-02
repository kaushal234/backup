<?php

declare(strict_types=1);

namespace App\Report\Handler\Service\TechnicianOnCall;

use App\Entity\Service\TechnicianOnCall\BacklogReport;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\Options\TechnicianOnCallKpiOptions;
use Doctrine\ORM\EntityManagerInterface;

class TechnicianOnCallBacklogHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IsGrantedTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TechnicianOnCallKpiOptions $technicianOnCallKpiOptions,
    ) {
    }

    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (BacklogReport::class !== $resourceClass || 'month' !== $x || 'backlog' !== $y) {
            return null;
        }

        $options = $this->technicianOnCallKpiOptions->configureOptions($options);
        $repository = $this->entityManager->getRepository($resourceClass);
        $queryBuilder = $repository->createQueryBuilder('b');

        $queryBuilder
            ->select("DATE_FORMAT(b.date, '%Y-%m') AS x")
            ->addSelect('l.name AS y')
            ->addSelect('COUNT(t) AS value')
            ->join('b.salesOrganisation', 'l')
            ->leftJoin('b.technicianOnCalls', 't')
            ->where('b.date >= :from')
            ->andWhere('b.date < :to')
            ->groupBy('b.salesOrganisation', 'x')
            ->orderBy('x')
            ->setParameter('from', $options['from'])
            ->setParameter('to', $options['to'])
        ;

        if ($options['salesServiceOrganisation']) {
            $queryBuilder
                ->andWhere('b.salesOrganisation = :sso_id')
                ->setParameter('sso_id', $options['salesServiceOrganisation'])
            ;
        }

        return new ReportDataProvider(
            $queryBuilder->getQuery()->getResult()
        );
    }
}
