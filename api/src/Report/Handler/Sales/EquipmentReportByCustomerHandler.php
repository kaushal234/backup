<?php

declare(strict_types=1);

namespace App\Report\Handler\Sales;

use App\Entity\EquipmentRecord;
use App\Report\DataProvider\Extractor\LabelExtractor;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\Handler\ReportQueriesBuilderFactoryAwareTrait;
use App\Util\IriToId;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class EquipmentReportByCustomerHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IsGrantedTrait;
    use ReportQueriesBuilderFactoryAwareTrait;

    private readonly Connection $connection;
    private readonly IriToId $iriToId;
    private readonly Security $security;

    public function __construct(Connection $connection, IriToId $iriToId, Security $security)
    {
        $this->connection = $connection;
        $this->iriToId = $iriToId;
        $this->security = $security;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (EquipmentRecord::class !== $resourceClass || !\in_array($y, ['product.name', 'units'], true) || !\in_array($x, ['buyer.name', 'endUser.name'], true)) {
            return null;
        }

        if (!$this->security->isGranted('FEATURE_CATALOG_DOWNLOAD')) {
            throw new AccessDeniedException();
        }

        $productType = null;
        if ('product.name' === $y && null === ($productType = $this->iriToId->getId((string) ($options['product.family.productType'] ?? '')))) {
            throw new BadRequestHttpException("There's not enough filters to generate this report.");
        }

        $joinColumnName = 'buyer.name' === $x ? 'buyer_id' : 'end_user_id';
        $queryBuilder = $this->connection->createQueryBuilder();

        $queryBuilder
            ->select('COUNT(DISTINCT e.id) AS value')
            ->addSelect('c.name AS x')
            ->from('equipment_records', 'e')
            ->innerJoin('e', 'customers', 'c', \sprintf('e.%s = c.id', $joinColumnName))
            ->groupBy('x')
            ->orderBy('c.name')
        ;

        if (!$productType) {
            $queryBuilder
                ->addSelect('"units" AS y');
        } else {
            $queryBuilder
                ->addSelect('p.name AS y')
                ->innerJoin('e', 'products', 'p', 'e.product_id = p.id')
                ->innerJoin('p', 'product_families', 'pf', 'p.family_id = pf.id')
                ->andWhere('pf.product_type_id = :productType')
                ->setParameter('productType', $productType)
                ->addGroupBy('p.name');
            if (isset($options['product.hidden'])) {
                $queryBuilder
                    ->andWhere('p.hidden = :hidden')
                    ->setParameter('hidden', (bool) $options['product.hidden']);
            }
        }

        $results = $queryBuilder->executeQuery()->fetchAllAssociative();

        return new ReportDataProvider(
            $results,
            (new LabelExtractor($results, 'x'))(),
            (new LabelExtractor($results, 'y'))()
        );
    }
}
