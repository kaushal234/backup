<?php

declare(strict_types=1);

namespace App\Report\Handler\Quality\Crab\Report;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\Location;
use App\Entity\Quality\Crab;
use App\Entity\Sales\Product;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Repository\Quality\Crab\CrabRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class CrabCodeByPeriodByProductHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IsGrantedTrait;

    protected QueryBuilder $queryBuilder;
    private readonly IriConverterInterface $iriConverter;
    private EntityManagerInterface $entityManager;
    private readonly CrabRepository $crabRepository;

    public function __construct(EntityManagerInterface $entityManager, IriConverterInterface $iriConverter, CrabRepository $crabRepository)
    {
        $this->entityManager = $entityManager;
        $this->crabRepository = $crabRepository;
        $this->iriConverter = $iriConverter;
        $this->queryBuilder = $this->entityManager->createQueryBuilder();
    }

    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (Crab::class !== $resourceClass || 'reportByPeriodByCodeByProduct' !== $x || !isset($options['from'], $options['to'], $options['factory'])) {
            return null;
        }

        $factory = $this->iriConverter->getResourceFromIri($options['factory']);
        if (!$factory instanceof Location) {
            return null;
        }

        $from = new \DateTime($options['from']);
        $to = new \DateTime($options['to']);

        $products = [];
        if (isset($options['product'])) {
            foreach ($options['product'] as $productIri) {
                $product = $this->iriConverter->getResourceFromIri($productIri);
                if (!$product instanceof Product) {
                    return null;
                }
                $products[] = $product->getId();
            }
            $productQuery = $this->crabRepository->countCrabByCrabCodeByPeriod($from, $to, $factory)
                ->andWhere('er.product IN (:products)')
                ->setParameter('products', $products)
            ;
        } else {
            $productQuery = $this->crabRepository->countCrabByCrabCodeByPeriod($from, $to, $factory);
        }

        return new ReportDataProvider(
            $productQuery->getQuery()->getScalarResult()
        );
    }
}
