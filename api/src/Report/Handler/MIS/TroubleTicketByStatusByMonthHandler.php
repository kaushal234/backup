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

class TroubleTicketByStatusByMonthHandler extends AbstractTroubleTicketReport implements ReportHandlerInterface
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
        if (TroubleTicket::class !== $resourceClass || 'status' !== $x || 'createdMonth' !== $y) {
            return null;
        }

        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder
            ->select("
                CASE
                    WHEN t.status IN (:closedStatuses) THEN 'Closed'
                    ELSE 'Still opened'
                END AS y
            ")
            ->addSelect("DATE_FORMAT(t.createdAt, '%Y-%m') as x")
            ->addSelect('COUNT(t.id) as value')
            ->from(TroubleTicket::class, 't')
            ->groupBy('x')
            ->addGroupBy('y')
            ->setParameter('closedStatuses', TroubleTicket::CLOSED_STATUSES)
        ;

        $this->applyFilters($queryBuilder, $options);

        return new ReportDataProvider(
            (new QueryBuilderExtractor($queryBuilder))(),
            [],
            []
        );
    }
}
