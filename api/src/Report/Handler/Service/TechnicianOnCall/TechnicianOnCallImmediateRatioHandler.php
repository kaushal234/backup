<?php

declare(strict_types=1);

namespace App\Report\Handler\Service\TechnicianOnCall;

use App\Entity\Service\TechnicianOnCall;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\Options\TechnicianOnCallKpiOptions;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\ORM\EntityManagerInterface;

class TechnicianOnCallImmediateRatioHandler implements ReportHandlerInterface
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
        if (TechnicianOnCall::class !== $resourceClass || 'month' !== $x || 'tir' !== $y) {
            return null;
        }

        $options = $this->technicianOnCallKpiOptions->configureOptions($options);

        $queryBuilder = $this->entityManager->createQueryBuilder();

        $queryBuilder
            ->select("DATE_FORMAT(t.solvedAt, '%Y-%m') AS x")
            ->addSelect('s.name AS y')
            ->addSelect('ROUND(
                    SUM(CASE
                            WHEN TIMESTAMP_DIFF(HOUR, t.createdAt, t.solvedAt) < z.delay THEN 1
                            ELSE 0
                        END) * 100.0
                        / COUNT(t.id)
                ) AS value'
            )
            ->from(TechnicianOnCall::class, 't')
            ->join('t.salesOrganisationService', 's')
            ->join('t.airport', 'a')
            ->join('a.country', 'c')
            ->join(TechnicianOnCall\TechnicianOnCallZone::class, 'z', 'WITH', 'c MEMBER OF z.countries')
            ->where('t.solvedAt >= :from')
            ->andWhere('t.solvedAt < :to')
            ->groupBy('s.name', 'x')
            ->orderBy('x')
            ->setParameter('from', $options['from'])
            ->setParameter('to', $options['to'])
        ;

        if ($options['salesServiceOrganisation']) {
            $queryBuilder
                ->andWhere('t.salesOrganisationService = :sso_id')
                ->setParameter('sso_id', $options['salesServiceOrganisation']);
        }

        if ($options['customers']) {
            $queryBuilder
                ->andWhere('t.customer in (:customers)')
                ->setParameter('customers', $options['customers'], ArrayParameterType::INTEGER)
            ;
        }

        return new ReportDataProvider(
            $queryBuilder->getQuery()->getResult()
        );
    }
}
