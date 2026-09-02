<?php

declare(strict_types=1);

namespace App\Report\Handler\Quality\NCR;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Quality\NonConformity;
use App\Report\DataProvider\Extractor\LabelExtractor;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IrisExtractorBuilderFactoryAwareTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\Handler\ReportQueriesBuilderFactoryAwareTrait;
use Doctrine\ORM\EntityManagerInterface;

class NonConformityByProcessLastYearByMonthHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IrisExtractorBuilderFactoryAwareTrait;
    use IsGrantedTrait;
    use ReportQueriesBuilderFactoryAwareTrait;

    private readonly EntityManagerInterface $entityManager;
    private readonly IriConverterInterface $iriConverter;

    public function __construct(EntityManagerInterface $entityManager, IriConverterInterface $iriConverter)
    {
        $this->entityManager = $entityManager;
        $this->iriConverter = $iriConverter;
    }

    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (NonConformity::class !== $resourceClass || 'ncr_by_process' !== $x) {
            return null;
        }

        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder
            ->select('COUNT(ncr) AS value')
            ->addSelect("DATE_FORMAT(ncr.createdAt,'%Y%m') AS x")
            ->addSelect('pro.category AS y')
            ->from(NonConformity::class, 'ncr')
            ->leftJoin('ncr.processes', 'pro')
            ->groupBy('x, y')
            ->orderBy('x')
            ->setParameter('oneYearAgo', new \DateTime('midnight first day of this month last year'))
            ->where('ncr.createdAt >= :oneYearAgo');

        if ('ALL' !== $y) {
            $location = $this->iriConverter->getResourceFromIri($y);
            $queryBuilder
                ->andWhere('ncr.location = :location')
                ->setParameter('location', $location);
        }

        $results = $queryBuilder->getQuery()->getScalarResult();
        foreach ($results as &$result) {
            $result['y'] = null === $result['y'] ? 'UNKNOWN' : $result['y'];
        }

        return new ReportDataProvider(
            $results,
            (new LabelExtractor($results, 'x'))(),
            (new LabelExtractor($results, 'y'))()
        );
    }
}
