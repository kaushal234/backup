<?php

declare(strict_types=1);

namespace App\Report\Handler\Sales\ForecastClosure;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Sales\Customer;
use App\Entity\Sales\ForecastClosure;
use App\Entity\Sales\Product;
use App\Entity\Sales\ProductFamily;
use App\Entity\Sales\SalesForecast;
use App\Report\DataProvider\Extractor\QueryBuilderExtractor;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IrisExtractorBuilderFactoryAwareTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\Handler\ReportQueriesBuilderFactoryAwareTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;

class ForecastClosureReportHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IrisExtractorBuilderFactoryAwareTrait;
    use IsGrantedTrait;
    use ReportQueriesBuilderFactoryAwareTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly IriConverterInterface $iriConverter,
    ) {
    }

    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (ForecastClosure::class !== $resourceClass || 'reason' !== $x) {
            return null;
        }

        $queryBuilder = $this->entityManager->createQueryBuilder();

        $queryBuilder
            ->select('COUNT(a.reason) as value')
            ->addSelect('a.reason AS x')
            ->addSelect("'Value' as y")
            ->from(ForecastClosure::class, 'a')
            ->leftJoin(SalesForecast::class, 'sf', Join::WITH, 'a.salesForecast = sf')
            ->groupBy('x')
        ;

        if (isset($options['competitor'])) {
            $competitor = $this->iriConverter->getResourceFromIri($options['competitor']);
            $queryBuilder
                ->andWhere('a.competitor = :competitor')
                ->setParameter('competitor', $competitor)
            ;
        }

        if (isset($options['salesForecast.product'])) {
            $product = $this->iriConverter->getResourceFromIri($options['salesForecast.product']);
            $queryBuilder
                ->andWhere('sf.product = :product')
                ->setParameter('product', $product)
            ;
        }

        if (isset($options['salesForecast.product.family.productType'])) {
            $productType = $this->iriConverter->getResourceFromIri($options['salesForecast.product.family.productType']);
            $queryBuilder
                ->leftJoin(Product::class, 'pr', Join::WITH, 'sf.product = pr')
                ->leftJoin(ProductFamily::class, 'fa', Join::WITH, 'pr.family = fa')
                ->andWhere('fa.productType = :productType')
                ->setParameter('productType', $productType)
            ;
        }

        if (isset($options['status'])) {
            $status = $options['status'];
            $queryBuilder
                ->andWhere($queryBuilder->expr()->in('a.status', ':status'))
                ->setParameter('status', $status)
            ;
        }

        if (isset($options['reason'])) {
            $reason = $options['reason'];
            $queryBuilder
                ->andWhere($queryBuilder->expr()->in('a.reason', ':reason'))
                ->setParameter('reason', $reason)
            ;
        }

        if (isset($options['createdAt']['after'])) {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->gte('a.createdAt', ':after'))
                ->setParameter('after', (new \DateTime($options['createdAt']['after']))->format('Y-m-d'))
            ;
        }

        if (isset($options['createdAt']['before'])) {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->lte('a.createdAt', ':before'))
                ->setParameter('before', (new \DateTime($options['createdAt']['before']))->format('Y-m-d'))
            ;
        }

        if (isset($options['salesForecast.estimatedSaleDate']['after'])) {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->gte('sf.estimatedSaleDate', ':after'))
                ->setParameter('after', (new \DateTime($options['salesForecast.estimatedSaleDate']['after']))->format('Y-m-d'))
            ;
        }

        if (isset($options['salesForecast.estimatedSaleDate']['before'])) {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->lte('sf.estimatedSaleDate', ':before'))
                ->setParameter('before', (new \DateTime($options['salesForecast.estimatedSaleDate']['before']))->format('Y-m-d'))
            ;
        }

        if (isset($options['salesForecast.asm'])) {
            $asm = $this->iriConverter->getResourceFromIri($options['salesForecast.asm']);
            $queryBuilder
                ->andWhere('sf.asm = :asm')
                ->setParameter('asm', $asm)
            ;
        }

        if (isset($options['salesForecast.factory'])) {
            $factory = $this->iriConverter->getResourceFromIri($options['salesForecast.factory']);
            $queryBuilder
                ->andWhere('sf.factory = :factory')
                ->setParameter('factory', $factory)
            ;
        }

        if (isset($options['salesForecast.sso'])) {
            $sso = $this->iriConverter->getResourceFromIri($options['salesForecast.sso']);
            $queryBuilder
                ->andWhere('sf.sso = :sso')
                ->setParameter('sso', $sso)
            ;
        }

        if (isset($options['salesForecast.buyer.country'])) {
            $country = $this->iriConverter->getResourceFromIri($options['salesForecast.buyer.country']);
            $queryBuilder
                ->leftJoin(Customer::class, 'bu', Join::WITH, 'sf.buyer = bu')
                ->andWhere('bu.country = :country')
                ->setParameter('country', $country)
            ;
        }

        if (isset($options['salesForecast.buyer'])) {
            $buyer = $this->iriConverter->getResourceFromIri($options['salesForecast.buyer']);
            $queryBuilder
                ->andWhere('sf.buyer = :buyer')
                ->setParameter('buyer', $buyer)
            ;
        }

        if (isset($options['salesForecast.endUser'])) {
            $endUser = $this->iriConverter->getResourceFromIri($options['salesForecast.endUser']);
            $queryBuilder
                ->andWhere('sf.endUser = :endUser')
                ->setParameter('endUser', $endUser)
            ;
        }

        if (isset($options['q'])) {
            $queryBuilder
                ->leftJoin('sf.buyer', 'buyer')
                ->leftJoin('sf.endUser', 'endUser')
                ->leftJoin('sf.factory', 'factory')
                ->leftJoin('sf.sso', 'sso')
                ->leftJoin('sf.product', 'product')
                ->andWhere(
                    $queryBuilder->expr()->orX(
                        $queryBuilder->expr()->like('buyer.name', ':q'),
                        $queryBuilder->expr()->like('endUser.name', ':q'),
                        $queryBuilder->expr()->like('a.comment', ':q'),
                        $queryBuilder->expr()->like('a.reason', ':q'),
                        $queryBuilder->expr()->like('factory.name', ':q'),
                        $queryBuilder->expr()->like('sso.name', ':q'),
                        $queryBuilder->expr()->eq('a.id', ':q'),
                        $queryBuilder->expr()->eq('sf.id', ':q'),
                    )
                )
                ->setParameter('q', \sprintf('%%%s%%', $options['q']))
            ;
        }

        return new ReportDataProvider(
            (new QueryBuilderExtractor($queryBuilder))(),
            [],
            []
        );
    }
}
