<?php

declare(strict_types=1);

namespace App\Report\Handler\Service\TechnicianOnCall;

use App\Entity\Service\TechnicianOnCall;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\Options\TechnicianOnCallKpiOptions;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TechnicianOnCallOperateHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IsGrantedTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TechnicianOnCallKpiOptions $technicianOnCallKpiOptions,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (TechnicianOnCall::class !== $resourceClass || 'date' !== $x || 'operate' !== $y) {
            return null;
        }

        $options['from'] = !empty($options['from']) ? $options['from'] : new \DateTime('first day of this month 00:00:00');
        $options['to'] = !empty($options['to']) ? $options['to'] : new \DateTime('now');

        $options = $this->technicianOnCallKpiOptions->configureOptions($options);
        $repository = $this->entityManager->getRepository($resourceClass);

        $queryBuilder = $repository->createQueryBuilder('t');

        $queryBuilder
            ->select('t.id as id')
            ->leftJoin('t.customerServiceRecords', 'c')
            ->leftJoin('c.interventions', 'i')
            ->addSelect('COUNT(DISTINCT c.id) as csr_count')
            ->addSelect('COUNT(i.id) as intervention_count')
            ->groupBy('t.id')
            ->where('t.solvedAt >= :from')
            ->andWhere('t.solvedAt < :to')
            ->andWhere($queryBuilder->expr()->in('t.status', ':statuses'))
            ->setParameter('from', $options['from'])
            ->setParameter('to', $options['to'])
            ->setParameter('statuses', TechnicianOnCall::CLOSED_STATUSES)
        ;

        if ($options['salesServiceOrganisation']) {
            $queryBuilder
                ->andWhere('t.salesOrganisationService = :sso_id')
                ->setParameter('sso_id', $options['salesServiceOrganisation'])
            ;
        }

        $results = $queryBuilder->getQuery()->getResult();

        $counters = [
            'WITHOUT_CSR' => [
                'value' => 0,
                'x' => 'WITHOUT_CSR',
                'y' => 'Value',
            ],
            'ONE_INTERVENTION' => [
                'value' => 0,
                'x' => 'ONE_INTERVENTION',
                'y' => 'Value',
            ],
            'TWO_INTERVENTIONS' => [
                'value' => 0,
                'x' => 'TWO_INTERVENTIONS',
                'y' => 'Value',
            ],
            'MORE_THAN_TWO_INTERVENTIONS' => [
                'value' => 0,
                'x' => 'MORE_THAN_TWO_INTERVENTIONS',
                'y' => 'Value',
            ],
        ];

        foreach ($results as $row) {
            $csrCount = (int) $row['csr_count'];
            $interventionCount = (int) $row['intervention_count'];

            if (0 === $csrCount) {
                ++$counters['WITHOUT_CSR']['value'];
            } elseif (0 === $interventionCount) {
                ++$counters['ONE_INTERVENTION']['value'];
            } elseif (1 === $interventionCount) {
                ++$counters['ONE_INTERVENTION']['value'];
            } elseif (2 === $interventionCount) {
                ++$counters['TWO_INTERVENTIONS']['value'];
            } else {
                ++$counters['MORE_THAN_TWO_INTERVENTIONS']['value'];
            }
        }

        return new ReportDataProvider(
            $counters
        );
    }
}
