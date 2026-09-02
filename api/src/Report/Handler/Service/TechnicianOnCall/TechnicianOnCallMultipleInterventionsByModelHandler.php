<?php

declare(strict_types=1);

namespace App\Report\Handler\Service\TechnicianOnCall;

use App\Entity\Service\CustomerServiceRecord\AbstractCustomerServiceRecord;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IrisExtractorBuilderFactoryAwareTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\Handler\ReportQueriesBuilderFactoryAwareTrait;
use App\Report\Options\TechnicianOnCallKpiOptions;
use Doctrine\ORM\EntityManagerInterface;

class TechnicianOnCallMultipleInterventionsByModelHandler implements ReportHandlerInterface
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
        if (AbstractCustomerServiceRecord::class !== $resourceClass || 'equipmentRecord.model' !== $x || 'salesOrganisationService.name' !== $y) {
            return null;
        }

        $options['from'] = !empty($options['from']) ? $options['from'] : new \DateTime('first day of this month 00:00:00');
        $options['to'] = !empty($options['to']) ? $options['to'] : new \DateTime('now');

        $options = $this->technicianOnCallKpiOptions->configureOptions($options);
        $repository = $this->entityManager->getRepository($resourceClass);

        $queryBuilder = $repository->createQueryBuilder('c');

        $queryBuilder
            ->select('e.model as model')
            ->addSelect('l.name as sso')
            ->addSelect('count(i.id) as nbInterventions')
            ->join('c.equipmentRecord', 'e')
            ->join('e.salesOrganisationService', 'l')
            ->join('c.interventions', 'i')
            ->groupBy('c.id', 'l.id', 'e.model')
            ->where('c.createdAt >= :from')
            ->andWhere('c.createdAt < :to')
            ->having('nbInterventions >= 2')
            ->setParameter('from', $options['from'])
            ->setParameter('to', $options['to'])
        ;

        if ($options['salesServiceOrganisation']) {
            $queryBuilder
                ->andWhere('e.salesOrganisationService = :sso_id')
                ->setParameter('sso_id', $options['salesServiceOrganisation'])
            ;
        }

        $results = $queryBuilder->getQuery()->getResult();

        $counts = [];
        foreach ($results as $row) {
            $key = \sprintf('%s_%s', $row['model'], $row['sso']);
            if (!isset($counts[$key])) {
                $counts[$key] = [
                    'x' => $row['model'],
                    'y' => $row['sso'],
                    'value' => 0,
                ];
            }
            ++$counts[$key]['value'];
        }

        return new ReportDataProvider($counts);
    }
}
