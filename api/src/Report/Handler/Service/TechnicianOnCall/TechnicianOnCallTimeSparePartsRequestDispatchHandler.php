<?php

declare(strict_types=1);

namespace App\Report\Handler\Service\TechnicianOnCall;

use App\Entity\Parts\SparePartsRequest;
use App\Entity\Parts\TOCSparePartsRequest;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IrisExtractorBuilderFactoryAwareTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\Handler\ReportQueriesBuilderFactoryAwareTrait;
use App\Report\Options\TechnicianOnCallKpiOptions;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TechnicianOnCallTimeSparePartsRequestDispatchHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IrisExtractorBuilderFactoryAwareTrait;
    use IsGrantedTrait;
    use ReportQueriesBuilderFactoryAwareTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TechnicianOnCallKpiOptions $technicianOnCallKpiOptions,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (SparePartsRequest::class !== $resourceClass || 'delay' !== $x || 'salesOrganisationService.name' !== $y) {
            return null;
        }

        $options['from'] = !empty($options['from']) ? $options['from'] : new \DateTime('first day of this month 00:00:00');
        $options['to'] = !empty($options['to']) ? $options['to'] : new \DateTime('now');

        $options = $this->technicianOnCallKpiOptions->configureOptions($options);

        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder
            ->select("
                CASE 
                    WHEN DATE_DIFF(s.shippingDate, s.createdAt) = 0 THEN 'same_day'
                    WHEN DATE_DIFF(s.shippingDate, s.createdAt) = 1 THEN 'one_day'
                    WHEN DATE_DIFF(s.shippingDate, s.createdAt) = 2 THEN 'two_days'
                    WHEN DATE_DIFF(s.shippingDate, s.createdAt) = 3 THEN 'three_days'
                    WHEN DATE_DIFF(s.shippingDate, s.createdAt) = 4 THEN 'four_days'
                    WHEN DATE_DIFF(s.shippingDate, s.createdAt) = 5 THEN 'five_days'
                    ELSE 'over'
                END AS x
            ")
            ->addSelect('l.name AS y')
            ->addSelect('COUNT(s.technicianOnCall) AS value')
            ->from(TOCSparePartsRequest::class, ' s')
            ->join('s.sph', 'l')
            ->where($queryBuilder->expr()->in('s.status', ':spr_statuses'))
            ->andWhere('s.createdAt >= :from')
            ->andWhere('s.createdAt < :to')
            ->groupBy('l.name', 'x')
            ->orderBy('MIN(DATE_DIFF(s.shippingDate, s.createdAt))', 'ASC')
            ->setParameter('spr_statuses', [SparePartsRequest::STATUS_SHIPPED, SparePartsRequest::STATUS_CLOSED])
            ->setParameter('from', $options['from'])
            ->setParameter('to', $options['to'])
        ;

        if ($options['salesServiceOrganisation']) {
            $queryBuilder
                ->andWhere('s.sph = :sso_id')
                ->setParameter('sso_id', $options['salesServiceOrganisation'])
            ;
        }

        $results = $queryBuilder->getQuery()->getResult();

        foreach ($results as $key => $result) {
            $results[$key]['x'] = match ($result['x']) {
                'same_day' => $this->translator->trans('spare_parts_request.shipped_delay.same_day', [], 'spare_parts_request'),
                'one_day' => $this->translator->trans('spare_parts_request.shipped_delay.one_day', [], 'spare_parts_request'),
                'two_days' => $this->translator->trans('spare_parts_request.shipped_delay.two_days', [], 'spare_parts_request'),
                'three_days' => $this->translator->trans('spare_parts_request.shipped_delay.three_days', [], 'spare_parts_request'),
                'four_days' => $this->translator->trans('spare_parts_request.shipped_delay.four_days', [], 'spare_parts_request'),
                'five_days' => $this->translator->trans('spare_parts_request.shipped_delay.five_days', [], 'spare_parts_request'),
                default => $this->translator->trans('spare_parts_request.shipped_delay.over', [], 'spare_parts_request'),
            };
        }

        return new ReportDataProvider($results);
    }
}
