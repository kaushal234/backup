<?php

declare(strict_types=1);

namespace App\Report\Handler\MIS;

use App\Entity\Directory\People;
use App\Entity\MIS\TroubleTicket\TroubleTicket;
use App\Report\DataProvider\Extractor\QueryBuilderExtractor;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IrisExtractorBuilderFactoryAwareTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\Handler\ReportQueriesBuilderFactoryAwareTrait;

class TroubleTicketByStatusHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IrisExtractorBuilderFactoryAwareTrait;
    use ReportQueriesBuilderFactoryAwareTrait;

    final public const X = 'status';
    final public const Y = 'module.application.name';

    /**
     * {@inheritdoc}
     */
    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (TroubleTicket::class !== $resourceClass || self::X !== $x || self::Y !== $y) {
            return null;
        }

        $queriesBuilder = $this->factory->getQueriesBuilder($resourceClass, $x, $y);
        $mainQueryBuilder = $queriesBuilder->getMainQueryBuilder();
        $alias = $mainQueryBuilder->getRootAliases()[0];

        $queriesBuilder->getMainQueryBuilder()
            ->where(\sprintf('%s.status NOT IN (:closed_status)', $alias))
            ->setParameter('closed_status', TroubleTicket::CLOSED_STATUSES)
        ;

        return new ReportDataProvider((new QueryBuilderExtractor($queriesBuilder->getMainQueryBuilder()))());
    }

    public function isGranted(?object $user = null): bool
    {
        // We need to authorize a null user because this report is accessible from a command
        return $user instanceof People || null === $user;
    }
}
