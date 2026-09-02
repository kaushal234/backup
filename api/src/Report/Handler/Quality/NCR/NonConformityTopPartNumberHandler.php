<?php

declare(strict_types=1);

namespace App\Report\Handler\Quality\NCR;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\Location;
use App\Entity\Quality\NonConformity;
use App\Entity\Sales\Product;
use App\Entity\Sales\ProductType;
use App\Report\DataProvider\Extractor\QueryBuilderExtractor;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;

class NonConformityTopPartNumberHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IsGrantedTrait;

    private readonly EntityManagerInterface $entityManager;
    private readonly IriConverterInterface $iriConverter;

    public function __construct(EntityManagerInterface $entityManager, IriConverterInterface $iriConverter)
    {
        $this->entityManager = $entityManager;
        $this->iriConverter = $iriConverter;
    }

    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        // This report is to calculate the TOP parts represented in NCR
        if (NonConformity::class !== $resourceClass || 'top' !== $x || 'part_number' !== $y) {
            return null;
        }

        $limit = 10;
        if (isset($options['limit']) && \in_array((int) $options['limit'], [10, 20, 30], true)) {
            $limit = (int) $options['limit'];
        }
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder
            ->select('COUNT(ncrPart.id) AS value')
            ->addSelect('ncrPart.partNumber AS x')
            ->addSelect('ncrPart.description AS y')
            ->from(NonConformity::class, 'ncr')
            ->leftJoin('ncr.parts', 'ncrPart')
            ->where($queryBuilder->expr()->isNotNull('ncrPart.partNumber'))
            ->groupBy('ncrPart.partNumber')
            ->orderBy('value', Criteria::DESC)
            ->setMaxResults($limit);

        if (!empty($options['createdAt']['after'])) {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->gte('ncr.createdAt', ':createdAfter'))
                ->setParameter('createdAfter', new \DateTime($options['createdAt']['after']))
            ;
        }

        if (!empty($options['createdAt']['before'])) {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->lte('ncr.createdAt', ':createdBefore'))
                ->setParameter('createdBefore', new \DateTime($options['createdAt']['before']))
            ;
        }

        if (!empty($options['location'])) {
            $locations = [];
            foreach ((array) $options['location'] as $locationIri) {
                $location = $this->iriConverter->getResourceFromIri($locationIri);
                if ($location instanceof Location) {
                    $locations[] = $location;
                }
            }

            if ($locations) {
                $queryBuilder
                    ->andWhere($queryBuilder->expr()->in('ncr.location', ':locations'))
                    ->setParameter('locations', $locations)
                ;
            }
        }

        if (!empty($options['products'])) {
            $models = [];
            foreach ((array) $options['products'] as $modelIri) {
                $model = $this->iriConverter->getResourceFromIri($modelIri);
                if ($model instanceof Product) {
                    $models[] = $model;
                }
            }

            if ($models) {
                $queryBuilder
                    ->join('ncr.products', 'product')
                    ->andWhere($queryBuilder->expr()->in('product', ':models'))
                    ->setParameter('models', $models)
                ;
            }
        }

        if (!empty($options['products.family.productType'])) {
            $types = [];
            foreach ((array) $options['products.family.productType'] as $typeIri) {
                $type = $this->iriConverter->getResourceFromIri($typeIri);
                if ($type instanceof ProductType) {
                    $types[] = $type;
                }
            }

            if ($types) {
                $joins = $queryBuilder->getDQLPart('join');
                $aliases = array_map(static fn ($join) => $join->getAlias(), $joins['ncr'] ?? []);
                if (!\in_array('product', $aliases, true)) {
                    $queryBuilder->join('ncr.products', 'product');
                }

                $queryBuilder
                    ->join('product.family', 'family')
                    ->join('family.productType', 'productType')
                    ->andWhere($queryBuilder->expr()->in('productType', ':types'))
                    ->setParameter('types', $types)
                ;
            }
        }

        return new ReportDataProvider(
            (new QueryBuilderExtractor($queryBuilder))(),
            [],
            []
        );
    }
}
