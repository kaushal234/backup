<?php

declare(strict_types=1);

namespace App\AI\Filterable\Definition\Sales;

use App\AI\Filterable\Definition\AbstractFilterableDefinition;
use App\AI\Filterable\Definition\Filter;
use App\AI\Filterable\Definition\PathResolver;
use App\AI\Filterable\FilterableField;
use App\Entity\Directory\Division;
use App\Entity\Directory\PositionLevel;
use App\Entity\Sales\Competitor;
use App\Entity\Sales\Customer;
use App\Entity\Sales\MarketIntelligence\MarketIntelligence;
use App\Entity\Sales\ProductType;
use Doctrine\ORM\QueryBuilder;

final readonly class MarketIntelligenceFilterable extends AbstractFilterableDefinition
{
    public function name(): string
    {
        return 'market_intelligence';
    }

    public function entityClass(): string
    {
        return MarketIntelligence::class;
    }

    public function defaultAlias(): string
    {
        return 'mi';
    }

    public function defaultOrder(): array
    {
        return ['createdAt' => 'DESC'];
    }

    public function description(): string
    {
        return 'Market intelligence records (MIM — competitive/market information shared by sales), including type, related customers, competitors, product types, divisions, suppliers, poster and confidentiality. ';
    }

    public function fields(): array
    {
        return [
            Filter::like('shortDescriptionLike', 'shortDescription', desc: 'Partial, case-insensitive match on the short description / title (LIKE %value%).'),
            Filter::like('descriptionLike', 'description', desc: 'Partial, case-insensitive match on the full description (LIKE %value%).'),
            Filter::in('typeNames', 'type.name', desc: 'Exact market intelligence type names. Multiple = OR.'),
            Filter::inInt('customerIds', 'customers.id', desc: 'IDs of related customers. Multiple = OR.'),
            Filter::inInt('competitorIds', 'competitors.id', desc: 'IDs of related competitors. Multiple = OR.'),
            Filter::inInt('productTypeIds', 'productTypes.id', desc: 'IDs of related product types. Multiple = OR.'),
            Filter::in('divisionNames', 'divisions.name', desc: 'Exact division names. Multiple = OR.'),
            Filter::custom(
                fields: [new FilterableField('suppliers', FilterableField::TYPE_STRING, multi: true, description: 'Supplier names (partial match per value). Multiple = OR.')],
                apply: static function (QueryBuilder $qb, PathResolver $paths, array $filters): void {
                    $values = $filters['suppliers'] ?? null;
                    if (!\is_array($values) || [] === $values) {
                        return;
                    }
                    $rootAlias = $qb->getRootAliases()[0];
                    $orX = $qb->expr()->orX();
                    foreach (array_values($values) as $i => $supplier) {
                        $param = \sprintf('p_supplier%d', $i);
                        $orX->add(\sprintf('%s.suppliers LIKE :%s', $rootAlias, $param));
                        $qb->setParameter($param, \sprintf('%%%s%%', (string) $supplier));
                    }
                    $qb->andWhere($orX);
                },
            ),
            Filter::inInt('posterPeopleIds', 'poster', desc: 'IDs of the people who posted the record (People).'),
            Filter::hasMany('confidential', 'positionLevels', desc: 'True = only confidential records (restricted to position levels), false = only non-confidential, omit for both.'),
            Filter::dateRange('createdAt', afterName: 'createdAfter', beforeName: 'createdBefore', afterDesc: 'ISO-8601 date — records created on/after this date.', beforeDesc: 'ISO-8601 date — records created on/before this date.'),
        ];
    }

    public function summarize(object $entity): array
    {
        $entity = $this->ensureInstance($entity, MarketIntelligence::class);

        return [
            'id' => $entity->getId(),
            'shortDescription' => $entity->getShortDescription(),
            'type' => $entity->getType()?->name,
            'customers' => array_values(array_map(
                static fn (Customer $customer) => $customer->getName(),
                $entity->getCustomers()->toArray(),
            )),
            'competitors' => array_values(array_map(
                static fn (Competitor $competitor) => $competitor->getName(),
                $entity->getCompetitors()->toArray(),
            )),
            'productTypes' => array_values(array_map(
                static fn (ProductType $productType) => $productType->getEnglishName(),
                $entity->getProductTypes()->toArray(),
            )),
            'divisions' => array_values(array_map(
                static fn (Division $division) => $division->name,
                $entity->getDivisions()->toArray(),
            )),
            'positionLevels' => array_values(array_map(
                static fn (PositionLevel $positionLevel) => $positionLevel->getLabel(),
                $entity->getPositionLevels()->toArray(),
            )),
            'suppliers' => $entity->getSuppliers(),
            'poster' => $this->personName($entity->getPoster()),
            'confidential' => $entity->isConfidential(),
            'url' => $entity->getUrl(),
            'createdAt' => $entity->getCreatedAt()->format(\DATE_ATOM),
        ];
    }
}
