<?php

declare(strict_types=1);

namespace App\Report\Handler\MIS;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\People;
use App\Entity\MIS\TroubleTicket\TroubleTicket;
use App\Report\DataProvider\Extractor\LabelExtractor;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;

class TroubleTicketByTypeAndModuleHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IsGrantedTrait;

    final public const X = 'module.name';
    final public const Y = 'type.type';

    public function __construct(
        private readonly IriConverterInterface $iriConverter,
        private readonly Connection $connection,
    ) {
    }

    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (TroubleTicket::class !== $resourceClass || self::X !== $x || self::Y !== $y) {
            return null;
        }

        $queryBuilder = $this->connection->createQueryBuilder();

        $queryBuilder
            ->select('COUNT(t.id) AS value')
            ->addSelect('m.name AS x')
            ->addSelect('ty.type AS y')
            ->from('trouble_ticket', 't')
            ->innerJoin('t', 'base_task', 'bt', 'bt.id = t.id')
            ->innerJoin('bt', 'modules', 'm', 'bt.module_id = m.id')
            ->innerJoin('t', 'type', 'ty', 't.type_id = ty.id')
            ->groupBy('x')
            ->addGroupBy('y')
            ->orderBy('x', 'ASC')
            ->addOrderBy('y', 'ASC')
        ;

        if (!empty($options['createdAt'])) {
            if (!empty($options['createdAt']['after'])) {
                $queryBuilder
                    ->andWhere('bt.created_at >= :after')
                    ->setParameter('after', (new \DateTime($options['createdAt']['after']))->format('Y-m-d'));
            }
            if (!empty($options['createdAt']['before'])) {
                $queryBuilder
                    ->andWhere('bt.created_at <= :before')
                    ->setParameter('before', (new \DateTime($options['createdAt']['before']))->format('Y-m-d'));
            }
        }

        if (!empty($options['application'])) {
            $applicationIds = [];
            foreach ($options['application'] as $applicationIri) {
                $applicationIds[] = $this->iriConverter->getResourceFromIri($applicationIri)->getId();
            }
            $queryBuilder
                ->innerJoin('m', 'application', 'a', 'm.application_id = a.id')
                ->andWhere('a.id IN (:applicationIds)')
                ->setParameter('applicationIds', $applicationIds, ArrayParameterType::INTEGER);
        }

        if (!empty($options['module'])) {
            $moduleIds = [];
            foreach ($options['module'] as $moduleIri) {
                $moduleIds[] = $this->iriConverter->getResourceFromIri($moduleIri)->getId();
            }
            $queryBuilder
                ->andWhere('m.id IN (:moduleIds)')
                ->setParameter('moduleIds', $moduleIds, ArrayParameterType::INTEGER);
        }

        if (!empty($options['type.type'])) {
            $types = (array) $options['type.type'];
            $queryBuilder
                ->andWhere('ty.type IN (:types)')
                ->setParameter('types', $types, ArrayParameterType::STRING);
        }

        if (!empty($options['misAssignee'])) {
            $misAssigneeIds = [];
            foreach ($options['misAssignee'] as $misAssigneeIri) {
                $misAssigneeIds[] = $this->iriConverter->getResourceFromIri($misAssigneeIri)->getId();
            }
            $queryBuilder
                ->andWhere('t.mis_assignee_id IN (:misAssigneeIds)')
                ->setParameter('misAssigneeIds', $misAssigneeIds, ArrayParameterType::INTEGER);
        }

        $results = $queryBuilder->executeQuery()->fetchAllAssociative();

        return new ReportDataProvider(
            $results,
            (new LabelExtractor($results, 'x'))(),
            (new LabelExtractor($results, 'y'))()
        );
    }

    public function isGranted(?object $user = null): bool
    {
        return $user instanceof People || null === $user;
    }
}
