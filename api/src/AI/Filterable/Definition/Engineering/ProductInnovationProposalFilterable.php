<?php

declare(strict_types=1);

namespace App\AI\Filterable\Definition\Engineering;

use App\AI\Filterable\Definition\AbstractFilterableDefinition;
use App\AI\Filterable\Definition\Filter;
use App\AI\Filterable\Definition\PathResolver;
use App\AI\Filterable\FilterableField;
use Doctrine\ORM\QueryBuilder;
use LegacyBundle\Entity\Engineering\ProductInnovationProposal;

final readonly class ProductInnovationProposalFilterable extends AbstractFilterableDefinition
{
    /** @var string[] Statuses considered "closed" (terminal) for the isClosed filter. */
    private const array CLOSED_STATUSES = ['CLOSED', 'REJECTED'];

    public function name(): string
    {
        return 'product_innovation_proposal';
    }

    public function entityClass(): string
    {
        return ProductInnovationProposal::class;
    }

    public function defaultOrder(): array
    {
        return ['submittedAt' => 'DESC'];
    }

    public function description(): string
    {
        return 'Product innovation proposals (PIP) — innovation ideas/proposals raised against a GSE product type/model, with their status, importance factor (ifactor), factory, poster/initiator, submission/closing dates, resolution and rejection reason.';
    }

    public function fields(): array
    {
        $statuses = ['PENDING', 'IN PROGRESS', 'SUSPENDED', 'REJECTED', 'CLOSED'];

        return [
            Filter::in('statuses', 'status', enum: $statuses, desc: 'PIP statuses. Open = PENDING, IN PROGRESS, SUSPENDED; closed = REJECTED, CLOSED. Multiple = OR.'),
            Filter::in('productTypes', 'productType', desc: 'Exact product types (product families), e.g. "Belt Loaders", "Ground Power Units". Multiple = OR.'),
            Filter::like('modelLike', 'model', desc: 'Partial product model (LIKE %value%).'),
            Filter::like('shortDescriptionLike', 'shortDescription', desc: 'Partial short description / title (LIKE %value%).'),
            Filter::like('descriptionLike', 'description', desc: 'Partial full description (LIKE %value%).'),
            Filter::inInt('importanceFactors', 'importanceFactor', desc: 'Importance factors (ifactor, severity weight). Multiple = OR.'),
            Filter::in('factoryNames', 'factory.name', desc: 'Exact factory / location names. Multiple = OR.'),
            Filter::inInt('posterIds', 'poster', desc: 'IDs of the people who posted the PIP.'),
            Filter::inInt('initiatorIds', 'initiator', desc: 'IDs of the initiators (People).'),
            Filter::custom(
                fields: [new FilterableField('isClosed', FilterableField::TYPE_BOOL, description: 'True = only closed (CLOSED/REJECTED), false = only open, omit for both.')],
                apply: static function (QueryBuilder $qb, PathResolver $paths, array $filters): void {
                    $value = $filters['isClosed'] ?? null;
                    if (null === $value) {
                        return;
                    }
                    $op = (bool) $value ? 'IN' : 'NOT IN';
                    $qb->andWhere(\sprintf('%s %s (:p_isClosed)', $paths->resolve($qb, 'status'), $op))
                        ->setParameter('p_isClosed', self::CLOSED_STATUSES);
                },
            ),
            Filter::dateRange('submittedAt', afterName: 'submittedAfter', beforeName: 'submittedBefore', afterDesc: 'ISO-8601 date — PIPs submitted on/after this date.', beforeDesc: 'ISO-8601 date — PIPs submitted on/before this date.'),
            Filter::dateRange('closedAt', afterName: 'closedAfter', beforeName: 'closedBefore', afterDesc: 'ISO-8601 date — PIPs closed on/after this date.', beforeDesc: 'ISO-8601 date — PIPs closed on/before this date.'),
        ];
    }

    public function summarize(object $entity): array
    {
        $entity = $this->ensureInstance($entity, ProductInnovationProposal::class);

        return [
            'id' => $entity->getId(),
            'status' => $entity->status,
            'productType' => $entity->productType,
            'model' => $entity->model,
            'shortDescription' => $entity->shortDescription,
            'importanceFactor' => $entity->importanceFactor,
            'finalWeight' => $entity->finalWeight,
            'factory' => $entity->factory?->getName(),
            'poster' => $this->legacyPersonName($entity->poster),
            'initiator' => $this->legacyPersonName($entity->initiator),
            'submittedAt' => $entity->submittedAt->format(\DATE_ATOM),
            'closedAt' => $entity->closedAt?->format(\DATE_ATOM),
            'suspendedAt' => $entity->suspendedAt?->format(\DATE_ATOM),
            'resolution' => '' !== $entity->resolution ? $entity->resolution : null,
            'rejectionReason' => '' !== $entity->rejectionReason ? $entity->rejectionReason : null,
        ];
    }
}
