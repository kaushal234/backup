<?php

declare(strict_types=1);

namespace App\Report\Handler\Service\TechnicianOnCall;

use App\Entity\AuditLog;
use App\Entity\Directory\Location;
use App\Entity\Service\TechnicianOnCall;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\Options\TechnicianOnCallKpiOptions;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\ORM\EntityManagerInterface;

class TechnicianOnCallAverageTimeHandler implements ReportHandlerInterface
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
        if (TechnicianOnCall::class !== $resourceClass || 'month' !== $x || 'tat' !== $y) {
            return null;
        }

        $options = $this->technicianOnCallKpiOptions->configureOptions($options);

        $conn = $this->entityManager->getConnection();

        $auditLogTable = $this->entityManager->getClassMetadata(AuditLog::class)->getTableName();
        $technicianTable = $this->entityManager->getClassMetadata(TechnicianOnCall::class)->getTableName();
        $serviceTable = $this->entityManager->getClassMetadata(Location::class)->getTableName();

        $suspendedQb = $conn->createQueryBuilder()
            ->select('a.reference_id', 'SUM(TIMESTAMPDIFF(DAY, a.created_at, an.created_at)) AS suspended_days')
            ->from($auditLogTable, 'a')
            ->join('a', $auditLogTable, 'an', 'an.id = a.next_id')
            ->where('a.audit_type = :auditType')
            ->andWhere('a.property = :auditProperty')
            ->andWhere('a.value = :auditValue')
            ->groupBy('a.reference_id')
        ;

        $queryBuilder = $conn->createQueryBuilder()
            ->select("DATE_FORMAT(t.solved_at, '%Y-%m') AS x")
            ->addSelect('s.name AS y')
            ->addSelect('ROUND(SUM(TIMESTAMPDIFF(DAY, t.created_at, t.solved_at) - COALESCE(susp.suspended_days, 0)) / COUNT(t.id)) AS value')
            ->from($technicianTable, 't')
            ->join('t', $serviceTable, 's', 's.id = t.sales_organisation_service_id')
            ->leftJoin('t', '('.$suspendedQb->getSQL().')', 'susp', 'susp.reference_id = t.id')
            ->where('t.solved_at >= :from')
            ->andWhere('t.solved_at < :to')
            ->groupBy('s.name', 'x')
            ->orderBy('x')
            ->setParameter('from', $options['from']->format('Y-m-d'))
            ->setParameter('to', $options['to']->format('Y-m-d'))
            ->setParameter('auditType', 'technician_on_call')
            ->setParameter('auditProperty', 'status')
            ->setParameter('auditValue', 'SUSPENDED')
        ;

        if ($options['salesServiceOrganisation']) {
            $queryBuilder
                ->andWhere('t.sales_organisation_service_id = :sso_id')
                ->setParameter('sso_id', $options['salesServiceOrganisation']);
        }

        if ($options['customers']) {
            $queryBuilder
                ->andWhere('t.customer_id in (:customers)')
                ->setParameter('customers', $options['customers'], ArrayParameterType::INTEGER)
            ;
        }

        return new ReportDataProvider(
            $queryBuilder->executeQuery()->fetchAllAssociative()
        );
    }
}
