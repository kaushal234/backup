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

class TechnicianOnCallSparePartRequestHandler implements ReportHandlerInterface
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
        if (TechnicianOnCall::class !== $resourceClass || 'date' !== $x || 'sparePartRequest' !== $y) {
            return null;
        }

        $options['from'] = !empty($options['from']) ? $options['from'] : new \DateTime('first day of this month 00:00:00');
        $options['to'] = !empty($options['to']) ? $options['to'] : new \DateTime('now');

        $options = $this->technicianOnCallKpiOptions->configureOptions($options);
        $repository = $this->entityManager->getRepository($resourceClass);

        $queryBuilder = $repository->createQueryBuilder('t');

        $queryBuilder
            ->select('t.id as id')
            ->addSelect('COUNT(DISTINCT s.id) as spr_count')
            ->leftJoin('t.sparePartsRequests', 's')
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
            'WITH_SPR' => [
                'value' => 0,
                'x' => 'WITH_SPR',
                'y' => 'Value',
            ],
            'WITHOUT_SPR' => [
                'value' => 0,
                'x' => 'WITHOUT_SPR',
                'y' => 'Value',
            ],
        ];

        foreach ($results as $row) {
            0 === (int) $row['spr_count'] ? ++$counters['WITHOUT_SPR']['value'] : ++$counters['WITH_SPR']['value'];
        }

        return new ReportDataProvider(
            $counters
        );
    }
}
