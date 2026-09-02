<?php

declare(strict_types=1);

namespace App\Report\Handler\Service\TechnicianOnCall;

use App\Entity\AuditLog;
use App\Entity\Service\TechnicianOnCall;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IrisExtractorBuilderFactoryAwareTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\Handler\ReportQueriesBuilderFactoryAwareTrait;
use App\Report\Options\TechnicianOnCallKpiOptions;
use Doctrine\ORM\EntityManagerInterface;

class TechnicianOnCallQuantityFactoryFlagRaisedHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IrisExtractorBuilderFactoryAwareTrait;
    use IsGrantedTrait;
    use ReportQueriesBuilderFactoryAwareTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TechnicianOnCallKpiOptions $technicianOnCallKpiOptions,
    ) {
    }

    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (TechnicianOnCall::class !== $resourceClass || 'equipmentRecord.model' !== $x || 'factoryFlag' !== $y) {
            return null;
        }

        $options['from'] = !empty($options['from']) ? $options['from'] : new \DateTime('first day of this month 00:00:00');
        $options['to'] = !empty($options['to']) ? $options['to'] : new \DateTime('now');

        $options = $this->technicianOnCallKpiOptions->configureOptions($options);
        $repository = $this->entityManager->getRepository($resourceClass);

        $queryBuilder = $repository->createQueryBuilder('t');

        $queryBuilder
            ->select('e.model AS x')
            ->addSelect('l.name AS y')
            ->addSelect('COUNT(t.id) AS value')
            ->join(AuditLog::class, 'a', 'WITH', 'a.referenceId = t.id AND a.auditType = :type AND a.property = :property AND a.value = :value')
            ->join('t.equipmentRecord', 'e')
            ->join('t.salesOrganisationService', 'l')
            ->where('t.createdAt >= :from')
            ->andWhere('t.createdAt < :to')
            ->groupBy('l.id', 'e.model')
            ->setParameter('from', $options['from'])
            ->setParameter('to', $options['to'])
            ->setParameter('type', 'technician_on_call')
            ->setParameter('property', 'factoryFlag')
            ->setParameter('value', '1')
            ->distinct()
        ;

        if ($options['salesServiceOrganisation']) {
            $queryBuilder
                ->andWhere('t.salesOrganisationService = :sso_id')
                ->setParameter('sso_id', $options['salesServiceOrganisation'])
            ;
        }

        return new ReportDataProvider(
            $queryBuilder->getQuery()->getResult()
        );
    }
}
