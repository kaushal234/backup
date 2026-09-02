<?php

declare(strict_types=1);

namespace App\Report\Handler\Sales\SalesForecast;

use ApiPlatform\Doctrine\Orm\Util\QueryBuilderHelper;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\Location;
use App\Entity\Sales\SalesForecast;
use App\Entity\Sales\SalesForecastSnapshot;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IrisExtractorBuilderFactoryAwareTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Repository\Sales\SalesForecastRepository;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class SalesForecastQuantityByPastPeriodsHandler
{
    use DefaultPriorityTrait;
    use IrisExtractorBuilderFactoryAwareTrait;
    use IsGrantedTrait;

    public const WEEK = 'week';
    public const MONTH = 'month';

    protected readonly SalesForecastRepository $salesForecastRepository;
    protected readonly IriConverterInterface $iriConverter;
    protected string $dateModifier;
    protected \DateTime $defaultEndDate;
    protected \DateInterval $periodInterval;
    protected string $timeSlotFormat;
    protected string $whereClause;

    public function __construct(IriConverterInterface $iriConverter, SalesForecastRepository $salesForecastRepository)
    {
        $this->salesForecastRepository = $salesForecastRepository;
        $this->iriConverter = $iriConverter;
    }

    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (SalesForecast::class !== $resourceClass || !$this->supports($x) || 'quantity_per_sso' !== $y) {
            return null;
        }

        $iriConverter = $this->iriConverter;

        $modifier = $this->dateModifier;
        $dateTimeNormalizer = static function (Options $options, $value) use ($modifier) {
            $value = $value instanceof \DateTime ? $value : new \DateTime($value);
            $value->modify($modifier);

            return $value;
        };

        $collectionNormalizer = static function (Options $options, $values) use ($iriConverter) {
            $collection = [];
            foreach ($values as $value) {
                $collection[] = $iriConverter->getResourceFromIri($value);
            }

            return $collection;
        };

        $resolver = (new OptionsResolver())
            ->setDefaults([
                'startDate' => new \DateTime('midnight first day of this month last year'),
                'endDate' => $this->defaultEndDate,
                'productTypes' => [],
                'products' => [],
                'ssos' => [],
                'factories' => [],
                'saleDateDistance' => null,
                'quantity_per_sso' => null,
            ])
            ->setNormalizer('startDate', $dateTimeNormalizer)
            ->setNormalizer('endDate', $dateTimeNormalizer)
            ->setNormalizer('productTypes', $collectionNormalizer)
            ->setNormalizer('products', $collectionNormalizer)
            ->setNormalizer('ssos', $collectionNormalizer)
            ->setNormalizer('factories', $collectionNormalizer)
        ;

        $options = $resolver->resolve($options);

        $queryNameGenerator = new QueryNameGenerator();

        $qb = $this->salesForecastRepository->createQueryBuilder('snap')->select('COUNT(DISTINCT snap.originalSalesForecast) AS value');

        $ssoAlias = QueryBuilderHelper::addJoinOnce($qb, $queryNameGenerator, 'snap', 'sso', Join::LEFT_JOIN);
        $productAlias = QueryBuilderHelper::addJoinOnce($qb, $queryNameGenerator, 'snap', 'product', Join::LEFT_JOIN);
        $familyAlias = QueryBuilderHelper::addJoinOnce($qb, $queryNameGenerator, $productAlias, 'family', Join::LEFT_JOIN);

        $typeOrStatement = $qb->expr()->orX();
        foreach ($options['productTypes'] as $index => $productType) {
            $parameterName = \sprintf(':productType_%s', $index);
            $typeOrStatement->add($qb->expr()->eq(\sprintf('%s.productType', $familyAlias), $parameterName));
            $qb->setParameter($parameterName, $productType);
        }
        $qb->andWhere($typeOrStatement);

        $productOrStatement = $qb->expr()->orX();
        foreach ($options['products'] as $index => $product) {
            $parameterName = \sprintf(':product_%s', $index);
            $productOrStatement->add($qb->expr()->eq('snap.product', $parameterName));
            $qb->setParameter($parameterName, $product);
        }
        $qb->andWhere($productOrStatement);

        $ssoOrStatement = $qb->expr()->orX();
        foreach ($options['ssos'] as $index => $sso) {
            $parameterName = \sprintf(':sso_%s', $index);
            $ssoOrStatement->add($qb->expr()->eq('snap.sso', $parameterName));
            $qb->setParameter($parameterName, $sso);
        }
        $qb->andWhere($ssoOrStatement);

        $factoryOrStatement = $qb->expr()->orX();
        foreach ($options['factories'] as $index => $factory) {
            $parameterName = \sprintf(':factory_%s', $index);
            $factoryOrStatement->add($qb->expr()->eq('snap.factory', $parameterName));
            $qb->setParameter($parameterName, $factory);
        }
        $qb->andWhere($factoryOrStatement);

        $period = new \DatePeriod($options['startDate'], $this->periodInterval, $options['endDate']);

        $qb
            ->addSelect(\sprintf('%s.id AS sso_id', $ssoAlias))
            ->addSelect(\sprintf('%s.name AS y', $ssoAlias))
            ->orderBy('y', 'ASC')
            ->groupBy('y')
            ->resetDQLPart('from')
            ->from(SalesForecastSnapshot::class, 'snap')
        ;

        $results = [];

        /** @var \DateTime $timeSlot */
        foreach ($period as $timeSlot) {
            $timeSlotQb = clone $qb;
            $formattedTimeSlot = $timeSlot->format($this->timeSlotFormat);
            $timeSlotQb
                ->addSelect(\sprintf("'%s' AS x", $formattedTimeSlot))

                ->andWhere($this->whereClause)
                ->setParameter('timeSlot', $formattedTimeSlot)
            ;

            if ($options['saleDateDistance']) {
                $timeSlotQb
                    ->andWhere('DATE_DIFF(snap.estimatedSaleDate, snap.snapshotCreatedAt) < :saleDateDistance')
                    ->setParameter('saleDateDistance', (int) $options['saleDateDistance'])
                ;
            }

            $results[] = $timeSlotQb->getQuery()->getScalarResult();
        }

        $format = $this->timeSlotFormat;
        $provider = new ReportDataProvider(
            array_merge(...$results),
            array_map(static fn (\DateTimeInterface $dateTime) => ['x' => $dateTime->format($format)], [...$period]),
            null
        );

        return $provider->setMetadataExtractor($this->irisExtractorBuilderFactory->createBuilder()
            ->setY(Location::class, 'sso_id')
            ->generate()
        );
    }

    abstract protected function supports(string $x): bool;
}
