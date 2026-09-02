<?php

declare(strict_types=1);

namespace App\Report\Handler\MIS;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\MIS\TroubleTicket\TroubleTicket;
use App\Report\DataProvider\Extractor\QueryBuilderExtractor;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IrisExtractorBuilderFactoryAwareTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\Handler\ReportQueriesBuilderFactoryAwareTrait;
use Doctrine\ORM\EntityManagerInterface;

class TroubleTicketClosedByMonthHandler extends AbstractTroubleTicketReport implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IrisExtractorBuilderFactoryAwareTrait;
    use IsGrantedTrait;
    use ReportQueriesBuilderFactoryAwareTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly IriConverterInterface $iriConverter,
    ) {
        parent::__construct($this->iriConverter);
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (TroubleTicket::class !== $resourceClass || 'closedStatus' !== $x || 'closedMonth' !== $y) {
            return null;
        }

        $closedQueryBuilder = $this->entityManager->createQueryBuilder();
        $closedQueryBuilder
            ->select("DATE_FORMAT(t.closedAt, '%Y-%m') as x")
            ->addSelect("'Closed' as y")
            ->addSelect('COUNT(t.id) as value')
            ->from(TroubleTicket::class, 't')
            ->where($closedQueryBuilder->expr()->in('t.status', ':closedStatuses'))
            ->andWhere($closedQueryBuilder->expr()->isNotNull('t.closedAt'))
            ->groupBy('x')
            ->setParameter('closedStatuses', TroubleTicket::CLOSED_STATUSES);

        $openQueryBuilder = $this->entityManager->createQueryBuilder();
        $openQueryBuilder
            ->select("DATE_FORMAT(t.createdAt, '%Y-%m') as x")
            ->addSelect("'Opened' as y")
            ->addSelect('COUNT(t.id) as value')
            ->from(TroubleTicket::class, 't')
            ->groupBy('x');

        $results = [];
        foreach ([$closedQueryBuilder, $openQueryBuilder] as $queryBuilder) {
            $this->applyFilters($queryBuilder, $options);

            $results = [...$results, ...(new QueryBuilderExtractor($queryBuilder))()];
        }

        usort($results, static function ($a, $b) {
            return [$a['x'], $a['y']] <=> [$b['x'], $b['y']];
        });

        return new ReportDataProvider(
            $results,
            [],
            []
        );
    }
}
