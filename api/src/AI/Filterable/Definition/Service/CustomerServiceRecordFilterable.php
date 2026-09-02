<?php

declare(strict_types=1);

namespace App\AI\Filterable\Definition\Service;

use App\AI\Filterable\Definition\AbstractFilterableDefinition;
use App\AI\Filterable\Definition\Filter;
use App\AI\Filterable\Definition\PathResolver;
use App\AI\Filterable\FilterableField;
use App\Entity\Service\CustomerServiceRecord\AbstractCustomerServiceRecord;
use App\Entity\Service\CustomerServiceRecord\CommissioningCustomerServiceRecord;
use App\Entity\Service\CustomerServiceRecord\CustomerServiceRecord;
use App\Entity\Service\CustomerServiceRecord\Intervention;
use App\Entity\Service\CustomerServiceRecord\ServiceBulletinCustomerServiceRecord;
use App\Entity\Service\CustomerServiceRecord\TechnicianOnCallCustomerServiceRecord;
use Doctrine\ORM\QueryBuilder;

final readonly class CustomerServiceRecordFilterable extends AbstractFilterableDefinition
{
    /** Maps LLM-facing type ids to concrete STI sub-classes. */
    private const array TYPE_CLASSES = [
        'default' => CustomerServiceRecord::class,
        'toc' => TechnicianOnCallCustomerServiceRecord::class,
        'service_bulletin' => ServiceBulletinCustomerServiceRecord::class,
        'commissioning' => CommissioningCustomerServiceRecord::class,
    ];

    public function name(): string
    {
        return 'customer_service_record';
    }

    public function entityClass(): string
    {
        return AbstractCustomerServiceRecord::class;
    }

    public function defaultAlias(): string
    {
        return 'csr';
    }

    public function defaultOrder(): array
    {
        return ['createdAt' => 'DESC'];
    }

    public function description(): string
    {
        return 'Customer service records (CSR — also called service tickets, service requests or interventions on GSE equipment), including their type (default, TOC/TLD-on-call, service bulletin, commissioning), status, title, the airport, the serviced equipment record (serial number, model), its sales office (SSO) and end-user customer, the assigned intervention leaders, and the creation/planned dates.';
    }

    public function fields(): array
    {
        return [
            Filter::custom(
                fields: [new FilterableField('types', FilterableField::TYPE_STRING, multi: true, enum: array_keys(self::TYPE_CLASSES), description: 'CSR type. "toc" = TLD-on-call, "service_bulletin" = SB-driven, "commissioning" = commissioning. Multiple = OR.')],
                apply: static function (QueryBuilder $qb, PathResolver $paths, array $filters): void {
                    $values = $filters['types'] ?? null;
                    if (!\is_array($values) || [] === $values) {
                        return;
                    }
                    $rootAlias = $qb->getRootAliases()[0];
                    $orX = $qb->expr()->orX();
                    foreach (array_values($values) as $i => $type) {
                        $class = self::TYPE_CLASSES[(string) $type] ?? null;
                        if (null === $class) {
                            continue;
                        }
                        $param = \sprintf('p_csr_type_%d', $i);
                        $orX->add(\sprintf('%s INSTANCE OF :%s', $rootAlias, $param));
                        $qb->setParameter($param, $class);
                    }
                    if (0 < $orX->count()) {
                        $qb->andWhere($orX);
                    }
                },
            ),
            Filter::in('statuses', 'status', enum: [
                AbstractCustomerServiceRecord::PENDING, AbstractCustomerServiceRecord::PLANNED,
                AbstractCustomerServiceRecord::ASSIGNED, AbstractCustomerServiceRecord::IN_PROGRESS,
                AbstractCustomerServiceRecord::COMPLETED, AbstractCustomerServiceRecord::CLOSED,
            ], desc: 'Exact CSR statuses. Open = PENDING, PLANNED, ASSIGNED; closed = COMPLETED, CLOSED. Multiple = OR.'),
            Filter::like('titleLike', 'title', desc: 'Partial, case-insensitive match on the CSR title (LIKE %value%).'),
            Filter::in('airportCodes', 'airport.code', desc: 'Exact airport codes where the service takes place. Multiple = OR.'),
            Filter::in('serialNumbers', 'equipmentRecord.serialNumber', desc: 'Exact serial numbers of the serviced equipment record. Multiple = OR.'),
            Filter::in('ssoNames', 'equipmentRecord.salesOrganisationService.name', desc: 'Exact SSO (sales office service) location names of the serviced equipment. Multiple = OR.'),
            Filter::in('endUserNames', 'equipmentRecord.endUser.name', desc: 'Exact end-user customer names of the serviced equipment. Multiple = OR.'),
            Filter::concatLike('leaderNameLike', ['interventions.leader.firstname', 'interventions.leader.lastname'], desc: 'Partial, case-insensitive match on an intervention leader full name "firstname lastname" (LIKE %value%).'),
            Filter::dateRange('createdAt', afterName: 'createdAfter', beforeName: 'createdBefore', afterDesc: 'ISO-8601 date — CSRs created on/after this date.', beforeDesc: 'ISO-8601 date — CSRs created on/before this date.'),
            Filter::dateRange('plannedAt', afterName: 'plannedAfter', beforeName: 'plannedBefore', afterDesc: 'ISO-8601 date — CSRs planned on/after this date.', beforeDesc: 'ISO-8601 date — CSRs planned on/before this date.'),
        ];
    }

    public function summarize(object $entity): array
    {
        $entity = $this->ensureInstance($entity, AbstractCustomerServiceRecord::class);

        $equipmentRecord = $entity->equipmentRecord;
        $leaders = array_values(array_unique(array_filter(array_map(
            fn (Intervention $intervention): ?string => $this->personName($intervention->leader),
            $entity->getInterventions()->toArray(),
        ))));

        return [
            'id' => $entity->getId(),
            'type' => $this->resolveType($entity),
            'status' => $entity->getStatus(),
            'title' => $entity->title,
            'airport' => $entity->getAirport()?->getCode(),
            'serialNumber' => $equipmentRecord->getSerialNumber(),
            'model' => $equipmentRecord->getModel(),
            'sso' => $equipmentRecord->getSalesOrganisationService()?->getName(),
            'endUser' => $equipmentRecord->getEndUser()?->getName(),
            'leaders' => $leaders,
            'createdBy' => $this->personName($entity->createdBy),
            'createdAt' => $entity->createdAt->format(\DATE_ATOM),
            'plannedAt' => $entity->plannedAt?->format(\DATE_ATOM),
            'completedAt' => $entity->completedAt?->format(\DATE_ATOM),
            'closedAt' => $entity->closedAt?->format(\DATE_ATOM),
        ];
    }

    private function resolveType(AbstractCustomerServiceRecord $entity): string
    {
        return match (true) {
            $entity instanceof TechnicianOnCallCustomerServiceRecord => 'toc',
            $entity instanceof ServiceBulletinCustomerServiceRecord => 'service_bulletin',
            $entity instanceof CommissioningCustomerServiceRecord => 'commissioning',
            default => 'default',
        };
    }
}
