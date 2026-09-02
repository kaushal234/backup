<?php

declare(strict_types=1);

namespace App\Report\Handler\Sales\SalesForecast;

use ApiPlatform\Doctrine\Orm\Util\QueryBuilderHelper;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use App\Entity\Directory\Location;
use App\Entity\Finance\Currency;
use App\Entity\Sales\SalesForecast;
use App\Entity\Sales\SalesForecastSnapshot;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IrisExtractorBuilderFactoryAwareTrait;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SalesForecastValueByPastWeeksHandler extends AbstractSalesForecastValuationHandler
{
    use DefaultPriorityTrait;
    use IrisExtractorBuilderFactoryAwareTrait;

    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (SalesForecast::class !== $resourceClass || 'week' !== $x || 'sso' !== $y) {
            return null;
        }

        $iriConverter = $this->iriConverter;

        $dateTimeNormalizer = static function (Options $options, $value) {
            $value = $value instanceof \DateTime ? $value : new \DateTime($value);
            $value->modify('Monday this week');

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
                'endDate' => new \DateTime('last monday'),
                'currency' => $this->entityManager->getRepository(Currency::class)->findOneBy(['name' => 'EUR']),
                'ponderated' => false,
                'productTypes' => [],
                'products' => [],
                'ssos' => [],
                'factories' => [],
                'saleDateDistance' => null,
            ])
            ->setNormalizer('startDate', $dateTimeNormalizer)
            ->setNormalizer('endDate', $dateTimeNormalizer)
            ->setNormalizer('currency', static function (Options $options, $value) use ($iriConverter) {
                if ($value instanceof Currency) {
                    return $value;
                }

                return $iriConverter->getResourceFromIri($value);
            })
            ->setNormalizer('productTypes', $collectionNormalizer)
            ->setNormalizer('products', $collectionNormalizer)
            ->setNormalizer('ssos', $collectionNormalizer)
            ->setNormalizer('factories', $collectionNormalizer)
        ;

        $options = $resolver->resolve($options);

        $queryNameGenerator = new QueryNameGenerator();

        $qb = $this->getBaseQueryBuilder($queryNameGenerator, $options['currency'], (bool) $options['ponderated']);

        $ssoAlias = QueryBuilderHelper::addJoinOnce($qb, $queryNameGenerator, 'o', 'sso', Join::LEFT_JOIN);
        $productAlias = QueryBuilderHelper::addJoinOnce($qb, $queryNameGenerator, 'o', 'product', Join::LEFT_JOIN);
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
            $productOrStatement->add($qb->expr()->eq('o.product', $parameterName));
            $qb->setParameter($parameterName, $product);
        }
        $qb->andWhere($productOrStatement);

        $ssoOrStatement = $qb->expr()->orX();
        foreach ($options['ssos'] as $index => $sso) {
            $parameterName = \sprintf(':sso_%s', $index);
            $ssoOrStatement->add($qb->expr()->eq('o.sso', $parameterName));
            $qb->setParameter($parameterName, $sso);
        }
        $qb->andWhere($ssoOrStatement);

        $factoryOrStatement = $qb->expr()->orX();
        foreach ($options['factories'] as $index => $factory) {
            $parameterName = \sprintf(':factory_%s', $index);
            $factoryOrStatement->add($qb->expr()->eq('o.factory', $parameterName));
            $qb->setParameter($parameterName, $factory);
        }
        $qb->andWhere($factoryOrStatement);

        $oneWeekInterval = new \DateInterval('P1W');
        $period = new \DatePeriod($options['startDate'], $oneWeekInterval, $options['endDate']);

        $qb
            ->addSelect(\sprintf('%s.id AS sso_id', $ssoAlias))
            ->addSelect(\sprintf('%s.name AS y', $ssoAlias))
            ->orderBy('y', 'ASC')
            ->groupBy('y')
            ->resetDQLPart('from')
            ->from(SalesForecastSnapshot::class, 'o')
        ;

        $results = [];
        /** @var \DateTime $week */
        foreach ($period as $week) {
            $weekQb = clone $qb;
            $weekFormat = $week->format('Y-m-d');
            $weekQb
                ->addSelect(\sprintf("'%s' AS x", $weekFormat))
                ->andWhere('o.snapshotCreatedAt = :week')
                ->setParameter('week', $weekFormat)
            ;

            if ($options['saleDateDistance']) {
                $weekQb
                    ->andWhere('DATE_DIFF(o.estimatedSaleDate, o.snapshotCreatedAt) < :saleDateDistance')
                    ->setParameter('saleDateDistance', (int) $options['saleDateDistance'])
                ;
            }

            $results[] = $weekQb->getQuery()->getScalarResult();
        }

        $provider = new ReportDataProvider(
            array_merge(...$results),
            array_map(static fn (\DateTimeInterface $dateTime) => ['x' => $dateTime->format('Y-m-d')], [...$period]),
            null
        );

        return $provider->setMetadataExtractor($this->irisExtractorBuilderFactory->createBuilder()
            ->setY(Location::class, 'sso_id')
            ->generate()
        );
    }
}
