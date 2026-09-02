<?php

declare(strict_types=1);

namespace App\Report\Handler\Quality\Crab\Report;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\Location;
use App\Entity\Quality\Crab;
use App\Entity\Sales\Product;
use App\Entity\Sales\ProductFamily;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Repository\Quality\Crab\CrabRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

class CrabCodeByPeriodByFamilyHandler
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
        if (Crab::class !== $resourceClass || 'reportByPeriodByCodeByFamily' !== $x || !isset($options['from'], $options['to'], $options['factory'])) {
            return null;
        }

        $factory = $this->iriConverter->getResourceFromIri($options['factory']);
        if (!$factory instanceof Location) {
            return null;
        }

        $from = new \DateTime($options['from']);
        $to = new \DateTime($options['to']);

        $families = [];
        if (isset($options['family'])) {
            foreach ($options['family'] as $familyIri) {
                $family = $this->iriConverter->getResourceFromIri($familyIri);
                if (!$family instanceof ProductFamily) {
                    return null;
                }
                $families[] = $family->getId();
            }
            $familyQuery = $this->crabRepository->countCrabByCrabCodeByPeriod($from, $to, $factory)
                ->leftJoin(Product::class, 'p', Join::WITH, 'er.product = p')
                ->leftJoin(ProductFamily::class, 'f', Join::WITH, 'p.family = f')
                ->andWhere('f IN (:families)')
                ->setParameter('families', $families)
            ;
        } else {
            $familyQuery = $this->crabRepository->countCrabByCrabCodeByPeriod($from, $to, $factory);
        }

        return new ReportDataProvider(
            $familyQuery->getQuery()->getScalarResult()
        );
    }
}
