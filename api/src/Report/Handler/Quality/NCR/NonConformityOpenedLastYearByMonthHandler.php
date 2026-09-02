<?php

declare(strict_types=1);

namespace App\Report\Handler\Quality\NCR;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Quality\NonConformity;
use App\Report\DataProvider\Extractor\LabelExtractor;
use App\Report\DataProvider\Extractor\QueryBuilderExtractor;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IrisExtractorBuilderFactoryAwareTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\Handler\ReportQueriesBuilderFactoryAwareTrait;
use Doctrine\ORM\EntityManagerInterface;

class NonConformityOpenedLastYearByMonthHandler implements ReportHandlerInterface
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
        if (NonConformity::class !== $resourceClass || 'ncr_opened' !== $x) {
            return null;
        }

        $openedNonConformityQueryBuilder = $this->entityManager->createQueryBuilder();
        $openedNonConformityQueryBuilder
            ->select('COUNT(ncr) AS value')
            ->from(NonConformity::class, 'ncr')
            ->groupBy('x')
            ->orderBy('x')
            ->setParameter('oneYearAgo', new \DateTime('midnight first day of this month last year'))
        ;

        $closedNonConformityQueryBuilder = clone $openedNonConformityQueryBuilder;

        $openedNonConformityQueryBuilder
            ->addSelect("DATE_FORMAT(ncr.createdAt,'%Y%m') AS x")
            ->addSelect("'OPENED' AS y")
            ->where('ncr.createdAt >= :oneYearAgo')
        ;

        $closedNonConformityQueryBuilder
            ->addSelect("DATE_FORMAT(ncr.closedAt,'%Y%m') AS x")
            ->addSelect("'CLOSED' AS y")
            ->where('ncr.closedAt >= :oneYearAgo')
        ;

        if ('ALL' !== $y) {
            $location = $this->iriConverter->getResourceFromIri($y);
            $openedNonConformityQueryBuilder
                ->andWhere('ncr.location = :location')
                ->setParameter('location', $location)
            ;
            $closedNonConformityQueryBuilder
                ->andWhere('ncr.location = :location')
                ->setParameter('location', $location)
            ;
        }

        return new ReportDataProvider(
            $results = array_merge((new QueryBuilderExtractor($openedNonConformityQueryBuilder))(), (new QueryBuilderExtractor($closedNonConformityQueryBuilder))()),
            (new LabelExtractor($results, 'x'))(),
            (new LabelExtractor($results, 'y'))()
        );
    }
}
