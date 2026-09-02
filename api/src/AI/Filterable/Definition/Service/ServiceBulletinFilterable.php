<?php

declare(strict_types=1);

namespace App\AI\Filterable\Definition\Service;

use App\AI\Filterable\Definition\AbstractFilterableDefinition;
use App\AI\Filterable\Definition\Filter;
use App\AI\Filterable\Definition\PathResolver;
use App\AI\Filterable\FilterableField;
use Doctrine\ORM\QueryBuilder;
use LegacyBundle\Entity\ServiceBulletin;

final readonly class ServiceBulletinFilterable extends AbstractFilterableDefinition
{
    /** @var string[] Statuses considered "closed" (terminal) for the isClosed filter. */
    private const array CLOSED_STATUSES = ['CLOSED', 'CANCELLED'];

    public function name(): string
    {
        return 'service_bulletin';
    }

    public function entityClass(): string
    {
        return ServiceBulletin::class;
    }

    public function defaultAlias(): string
    {
        return 'sb';
    }

    public function defaultOrder(): array
    {
        return ['createdAt' => 'DESC'];
    }

    public function description(): string
    {
        return 'Service bulletins (SB) — technical bulletins issued against a GSE product to describe a service/modification to apply, with their status, category, type, importance factor (ifactor), labor hours, parts requirements, factory, poster and the approval/implementation/closure dates.';
    }

    public function fields(): array
    {
        $statuses = ['PENDING', 'CSM_APPROVAL', 'SSD_DECISION', 'IMPLEMENTATION', 'PARTIAL_IMPLEMENTATION', 'CLOSED', 'CANCELLED'];
        $categories = ['COMPULSORY', 'RECOMMENDED', 'INFORMATION'];
        $types = ['IMPROVEMENT', 'MAINTENANCE', 'OPERATION'];

        return [
            Filter::in('statuses', 'status', enum: $statuses, desc: 'Service bulletin statuses. Open/in-progress = PENDING, CSM_APPROVAL, SSD_DECISION, IMPLEMENTATION, PARTIAL_IMPLEMENTATION; terminal = CLOSED, CANCELLED. Multiple = OR.'),
            Filter::in('categories', 'category', enum: $categories, desc: 'Bulletin categories. Multiple = OR.'),
            Filter::in('types', 'type', enum: $types, desc: 'Bulletin types (may be empty for some bulletins). Multiple = OR.'),
            Filter::like('titleLike', 'title', desc: 'Partial title (LIKE %value%).'),
            Filter::like('descriptionLike', 'description', desc: 'Partial description (LIKE %value%).'),
            Filter::inInt('importanceFactors', 'importanceFactor', desc: 'Importance factors (ifactor, severity weight), e.g. 1, 10, 100, 1000. Multiple = OR.'),
            Filter::in('factoryNames', 'factory.name', desc: 'Exact factory / location names. Multiple = OR.'),
            Filter::inInt('posterIds', 'poster', desc: 'IDs of the people who posted the service bulletin.'),
            Filter::bool('confidential', 'confidential', trueValue: 'Y', falseValue: 'N', desc: 'True = only confidential bulletins, false = only non-confidential, omit for both.'),
            Filter::custom(
                fields: [new FilterableField('isClosed', FilterableField::TYPE_BOOL, description: 'True = only terminal bulletins (CLOSED/CANCELLED), false = only open/in-progress ones, omit for both.')],
                apply: static function (QueryBuilder $qb, PathResolver $paths, array $filters): void {
                    $value = $filters['isClosed'] ?? null;
                    if (null === $value) {
                        return;
                    }
                    $op = (bool) $value ? 'IN' : 'NOT IN';
                    $qb->andWhere(\sprintf('%s %s (:p_sb_isClosed)', $paths->resolve($qb, 'status'), $op))
                        ->setParameter('p_sb_isClosed', self::CLOSED_STATUSES);
                },
            ),
            Filter::dateRange('createdAt', afterName: 'createdAfter', beforeName: 'createdBefore', afterDesc: 'ISO-8601 date — bulletins created on/after this date.', beforeDesc: 'ISO-8601 date — bulletins created on/before this date.'),
            Filter::dateRange('implementedAt', afterName: 'implementedAfter', beforeName: 'implementedBefore', afterDesc: 'ISO-8601 date — bulletins implemented on/after this date.', beforeDesc: 'ISO-8601 date — bulletins implemented on/before this date.'),
            Filter::dateRange('closedAt', afterName: 'closedAfter', beforeName: 'closedBefore', afterDesc: 'ISO-8601 date — bulletins closed on/after this date.', beforeDesc: 'ISO-8601 date — bulletins closed on/before this date.'),
        ];
    }

    public function summarize(object $entity): array
    {
        $entity = $this->ensureInstance($entity, ServiceBulletin::class);

        return [
            'id' => $entity->getId(),
            'status' => $entity->status,
            'category' => $entity->category,
            'type' => $entity->type,
            'title' => $entity->title,
            'importanceFactor' => $entity->importanceFactor,
            'laborHours' => $entity->laborHours,
            'numberOfTechniciansNeeded' => $entity->numberOfTechniciansNeeded,
            'partsNeeded' => $entity->isPartsNeeded(),
            'factory' => $entity->factory?->getName(),
            'poster' => $this->legacyPersonName($entity->poster),
            'confidential' => $entity->confidential,
            'createdAt' => $entity->createdAt->format(\DATE_ATOM),
            'implementedAt' => $entity->implementedAt?->format(\DATE_ATOM),
            'closedAt' => $entity->closedAt?->format(\DATE_ATOM),
        ];
    }
}
