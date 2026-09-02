<?php

declare(strict_types=1);

namespace App\Report\Handler\MIS;

use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\MIS\TroubleTicket\TroubleTicket;
use App\Report\DataProvider\Extractor\QueryBuilderExtractor;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IrisExtractorBuilderFactoryAwareTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\Handler\ReportQueriesBuilderFactoryAwareTrait;
use App\Report\ReportQueriesBuilder;

class TroubleTicketByStatusByAssigneeOrByFactoryHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IrisExtractorBuilderFactoryAwareTrait;
    use IsGrantedTrait;
    use ReportQueriesBuilderFactoryAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (TroubleTicket::class !== $resourceClass || !\in_array($x, ['createdBy.businessUnit.location.name', 'misAssignee'], true) || 'status' !== $y) {
            return null;
        }

        $queriesBuilder = $this->factory->getQueriesBuilder($resourceClass, $x, $y);

        $mainQueryBuilder = $queriesBuilder->getMainQueryBuilder();
        $alias = $mainQueryBuilder->getRootAliases()[0];

        $mainQueryBuilder->resetDQLPart('select');
        $queriesBuilder->getMainQueryBuilder()
            ->addSelect(ReportQueriesBuilder::SELECT_PART_VALUE)
            ->addSelect(\sprintf('%s.status as y', $alias))
            ->where(\sprintf('%s.status NOT IN (:closed_status)', $alias))
            ->setParameter('closed_status', TroubleTicket::CLOSED_STATUSES)
        ;

        switch ($x) {
            case 'createdBy.businessUnit.location.name':
                $mainQueryBuilder
                    ->addSelect('x_2.name AS x')
                    ->addSelect('x_2.id AS factory_id')
                ;

                $class = Location::class;
                $field = 'factory_id';
                break;
            case 'misAssignee':
                $mainQueryBuilder
                    ->innerJoin(\sprintf('%s.misAssignee', $alias), 'misAssignee')
                    ->addSelect("CONCAT(misAssignee.firstname, ' ', misAssignee.lastname) AS x")
                    ->addSelect('misAssignee.id AS mis_assignee_id')
                ;

                $class = People::class;
                $field = 'mis_assignee_id';
                break;
            default:
                $class = null;
                $field = null;
        }

        $provider = new ReportDataProvider((new QueryBuilderExtractor($queriesBuilder->getMainQueryBuilder()))());

        return $provider->setMetadataExtractor($this->irisExtractorBuilderFactory->createBuilder()
            ->setX($class, $field)
            ->generate()
        );
    }
}
