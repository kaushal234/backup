<?php

declare(strict_types=1);

namespace App\Report\Handler\MIS;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Directory\Premise;
use App\Entity\MIS\SupportTeam;
use App\Entity\MIS\TroubleTicket\TroubleTicket;
use App\Report\DataProvider\Extractor\LabelExtractor;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Query\Expr\Join;

class TroubleTicketSatisfactionByRegionHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IsGrantedTrait;

    public function __construct(
        private readonly IriConverterInterface $iriConverter,
        private readonly Connection $connection,
    ) {
    }

    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (TroubleTicket::class !== $resourceClass || 'satisfaction' !== $x || !\in_array($y, ['week', 'month'], true)) {
            return null;
        }

        $queryBuilder = $this->connection->createQueryBuilder();
        $satisfactionCase = \sprintf(
            "(CASE t.satisfaction WHEN '%s' THEN 1 WHEN '%s' THEN 2 WHEN '%s' THEN 3 WHEN '%s' THEN 4 ELSE 0 END)", TroubleTicket::NOT_SATISFIED_AT_ALL, TroubleTicket::NOT_MUCH_SATISFIED, TroubleTicket::SATISFIED, TroubleTicket::VERY_SATISFIED
        );

        $queryBuilder
            ->select(\sprintf('ROUND(AVG(%s), 1) AS value', $satisfactionCase))
            ->addSelect('ty.type AS y')
            ->from('trouble_ticket', 't')
            ->innerJoin('t', 'base_task', 'bt', 'bt.id = t.id')
            ->innerJoin('t', 'user', 'u', 'u.id = bt.created_by')
            ->innerJoin('t', 'type', 'ty', 't.type_id = ty.id')
            ->where($queryBuilder->expr()->isNotNull('t.satisfaction'))
            ->groupBy('x')
            ->addGroupBy('y')
            ->orderBy('bt.created_at')
        ;

        if ('week' === $y) {
            $mondayThisWeek = new \DateTime('monday this week');
            $monday12WeeksAgo = clone $mondayThisWeek;
            $monday12WeeksAgo->modify('-12 weeks');
            $queryBuilder
                ->addSelect("CONCAT('W-', TIMESTAMPDIFF(WEEK, bt.closed_at, :mondayThisWeek)) as x")
                ->andWhere($queryBuilder->expr()->gt('bt.closed_at', ':12weeksAgo'))
                ->andWhere($queryBuilder->expr()->lt('bt.closed_at', ':mondayThisWeek'))
                ->setParameter('mondayThisWeek', $mondayThisWeek->format('y-m-d'))
                ->setParameter('12weeksAgo', $monday12WeeksAgo->format('y-m-d'))
            ;
        }

        if ('month' === $y) {
            $lastDayOfPreviousMonth = new \DateTime('last day of previous month');
            $oneYearAgo = clone $lastDayOfPreviousMonth;
            $oneYearAgo->modify('first day of previous month -1 year');
            $queryBuilder
                ->addSelect("DATE_FORMAT(bt.closed_at,'%Y-%m') AS x")
                ->andWhere($queryBuilder->expr()->gt('bt.closed_at', ':oneYearAgo'))
                ->andWhere($queryBuilder->expr()->lt('bt.closed_at', ':lastDayOfPreviousMonth'))
                ->setParameter('lastDayOfPreviousMonth', $lastDayOfPreviousMonth->format('y-m-d'))
                ->setParameter('oneYearAgo', $oneYearAgo->format('y-m-d'))
            ;
        }

        if (null !== ($options['region'] ?? null)) {
            $regions = [];
            foreach ($options['region'] as $region) {
                $id = $this->iriConverter->getResourceFromIri($region)->getId();
                $regions[$id] = $id;
            }
            $queryBuilder
                ->innerJoin('u', 'directory_businessunit', 'b', 'u.business_unit_id = b.id')
                ->innerJoin('b', 'directory_region', 'r', 'b.region_id = r.id')
                ->andWhere($queryBuilder->expr()->in('r.id', $regions))
            ;
        }

        if (null !== ($options['misAssignee'] ?? null)) {
            $misAssignees = [];
            foreach ($options['misAssignee'] as $misAssignee) {
                $id = $this->iriConverter->getResourceFromIri($misAssignee)->getId();
                $misAssignees[$id] = $id;
            }

            $queryBuilder->andWhere($queryBuilder->expr()->in('mis_assignee_id', $misAssignees));
        }

        if (null !== ($options['application'] ?? null)) {
            $applications = [];
            foreach ($options['application'] as $application) {
                $id = $this->iriConverter->getResourceFromIri($application)->getId();
                $applications[$id] = $id;
            }
            $queryBuilder
                ->innerJoin('t', 'modules', 'm', 'bt.module_id = m.id')
                ->innerJoin('m', 'application', 'a', 'm.application_id = a.id')
                ->andWhere($queryBuilder->expr()->in('a.id', $applications));
        }

        if (\array_key_exists('supportTeam', $options) && !empty($options['supportTeam'])) {
            $supportTeams = [];

            foreach ($options['supportTeam'] as $supportTeamIri) {
                $supportTeam = $this->iriConverter->getResourceFromIri($supportTeamIri);
                if ($supportTeam instanceof Location) {
                    $supportTeams[] = $supportTeam;
                }
            }

            if (!empty($supportTeams)) {
                $queryBuilder
                    ->leftJoin(People::class, 'p', Join::WITH, 't.createdBy = p')
                    ->leftJoin(Premise::class, 'premise', Join::WITH, 'p.premise = premise')
                    ->leftJoin(SupportTeam::class, 'st', Join::WITH, 'premise.supportTeam = st')
                    ->andWhere($queryBuilder->expr()->in('st', ':supportTeams'))
                    ->setParameter('supportTeams', $supportTeams)
                ;
            }
        }

        if (!empty($options['type.type'])) {
            if (false === \is_array($options['type.type'])) {
                $options['type.type'] = [$options['type.type']];
            }
            $queryBuilder
                ->innerJoin('t', 'type', 'type', 't.type_id = type.id')
                ->andWhere($queryBuilder->expr()->in('type.type', ':types'))
                ->setParameter('types', $options['type.type'], ArrayParameterType::STRING)
            ;
        }

        if (null !== ($options['after'] ?? null)) {
            $queryBuilder->andWhere($queryBuilder->expr()->gte('bt.closed_at', ':after'))
                ->setParameter('after', (new \DateTime($options['after']))->format('Y-m-d'));
        }

        if (null !== ($options['before'] ?? null)) {
            $queryBuilder->andWhere($queryBuilder->expr()->lte('bt.closed_at', ':before'))
                ->setParameter('before', (new \DateTime($options['before']))->format('Y-m-d'));
        }

        $results = $queryBuilder->executeQuery()->fetchAllAssociative();

        return new ReportDataProvider(
            $results,
            (new LabelExtractor($results, 'x'))(),
            (new LabelExtractor($results, 'y'))()
        );
    }
}
