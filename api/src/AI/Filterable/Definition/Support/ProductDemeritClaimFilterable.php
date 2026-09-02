<?php

declare(strict_types=1);

namespace App\AI\Filterable\Definition\Support;

use App\AI\Filterable\Definition\AbstractFilterableDefinition;
use App\AI\Filterable\Definition\Filter;
use App\AI\Filterable\Definition\PathResolver;
use App\AI\Filterable\FilterableField;
use Doctrine\ORM\QueryBuilder;
use LegacyBundle\Entity\Support\ProductDemeritClaim;

final readonly class ProductDemeritClaimFilterable extends AbstractFilterableDefinition
{
    /** @var string[] Statuses considered "closed" (terminal) for the isClosed filter. */
    private const array CLOSED_STATUSES = ['REJECTED', 'CLOSED'];

    public function name(): string
    {
        return 'product_demerit_claim';
    }

    public function entityClass(): string
    {
        return ProductDemeritClaim::class;
    }

    public function defaultAlias(): string
    {
        return 'd';
    }

    public function defaultOrder(): array
    {
        return ['openingDate' => 'DESC'];
    }

    public function description(): string
    {
        return 'Product demerit claims (PDC), also known as demerit reports — quality claims raised against a GSE product type/model, with their status, importance factor (ifactor), factory, poster/initiator/assignee, IBS/IHS/LINK involvement and opening/closing dates.';
    }

    public function fields(): array
    {
        $statuses = ['PENDING', 'INVESTIGATION', 'ACTION', 'SUSPENDED', 'REJECTED', 'CLOSED'];

        return [
            Filter::in('statuses', 'status', enum: $statuses, desc: 'PDC statuses. Open = PENDING, INVESTIGATION, ACTION, SUSPENDED; closed = REJECTED, CLOSED. Multiple = OR.'),
            Filter::in('productTypes', 'productType', desc: 'Exact product types (product families). Multiple = OR.'),
            Filter::like('productModelLike', 'productModel', desc: 'Partial product model (LIKE %value%).'),
            Filter::like('shortDescriptionLike', 'shortDescription', desc: 'Partial short description / title (LIKE %value%).'),
            Filter::like('descriptionLike', 'description', desc: 'Partial full description (LIKE %value%).'),
            Filter::inInt('importanceFactors', 'importanceFactor', desc: 'Importance factors (ifactor, severity weight), e.g. 1, 10, 100, 1000. Multiple = OR.'),
            Filter::in('factoryNames', 'factory.name', desc: 'Exact factory / location names. Multiple = OR.'),
            Filter::inInt('posterIds', 'poster', desc: 'IDs of the people who posted the PDC.'),
            Filter::inInt('initiatorIds', 'initiator', desc: 'IDs of the initiators (People).'),
            Filter::inInt('assigneeIds', 'assignee', desc: 'IDs of the assignees (People).'),
            Filter::bool('involvesIbs', 'involvesIbs', desc: 'True = only PDCs involving IBS, false = without, omit for both.'),
            Filter::bool('involvesIhs', 'involvesIhs', desc: 'True = only PDCs involving IHS, false = without, omit for both.'),
            Filter::bool('involvesLink', 'involvesLink', desc: 'True = only PDCs involving LINK, false = without, omit for both.'),
            Filter::bool('readyToClose', 'readyToClose', desc: 'True = only PDCs ready to close, false = not ready, omit for both.'),
            Filter::custom(
                fields: [new FilterableField('isClosed', FilterableField::TYPE_BOOL, description: 'True = only closed (REJECTED/CLOSED), false = only open, omit for both.')],
                apply: static function (QueryBuilder $qb, PathResolver $paths, array $filters): void {
                    $value = $filters['isClosed'] ?? null;
                    if (null === $value) {
                        return;
                    }
                    $op = (bool) $value ? 'IN' : 'NOT IN';
                    $qb->andWhere(\sprintf('%s %s (:p_pdc_isClosed)', $paths->resolve($qb, 'status'), $op))
                        ->setParameter('p_pdc_isClosed', self::CLOSED_STATUSES);
                },
            ),
            Filter::dateRange('openingDate', afterName: 'openedAfter', beforeName: 'openedBefore', afterDesc: 'ISO-8601 date — PDCs opened on/after this date.', beforeDesc: 'ISO-8601 date — PDCs opened on/before this date.'),
            Filter::dateRange('closingDate', afterName: 'closedAfter', beforeName: 'closedBefore', afterDesc: 'ISO-8601 date — PDCs closed on/after this date.', beforeDesc: 'ISO-8601 date — PDCs closed on/before this date.'),
        ];
    }

    public function summarize(object $entity): array
    {
        $entity = $this->ensureInstance($entity, ProductDemeritClaim::class);

        return [
            'id' => $entity->getId(),
            'status' => $entity->status,
            'productType' => $entity->productType,
            'productModel' => $entity->productModel,
            'shortDescription' => $entity->shortDescription,
            'importanceFactor' => $entity->importanceFactor,
            'factory' => $entity->factory?->getName(),
            'poster' => $this->legacyPersonName($entity->poster),
            'initiator' => $this->legacyPersonName($entity->initiator),
            'assignee' => $this->legacyPersonName($entity->assignee),
            'involvesIbs' => (bool) $entity->involvesIbs,
            'involvesIhs' => (bool) $entity->involvesIhs,
            'involvesLink' => (bool) $entity->involvesLink,
            'readyToClose' => (bool) $entity->readyToClose,
            'openingDate' => $entity->openingDate?->format(\DATE_ATOM),
            'closingDate' => $entity->closingDate?->format(\DATE_ATOM),
        ];
    }
}
