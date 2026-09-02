<?php

declare(strict_types=1);

namespace App\Report\Handler\Sales\SalesForecast;

use ApiPlatform\Doctrine\Orm\Util\QueryBuilderHelper;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use App\Entity\Directory\Location;
use App\Entity\Finance\Currency;
use App\Entity\Sales\Customer;
use App\Entity\Sales\SalesForecast;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IrisExtractorBuilderFactoryAwareTrait;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SalesForecastValueByCustomerHandler extends AbstractSalesForecastValuationHandler
{
    use DefaultPriorityTrait;
    use IrisExtractorBuilderFactoryAwareTrait;

    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (SalesForecast::class !== $resourceClass || 'customer' !== $x || 'sso' !== $y) {
            return null;
        }

        $iriConverter = $this->iriConverter;

        $resolver = (new OptionsResolver())
            ->setDefaults([
                'estimatedSaleDate' => static function (OptionsResolver $estimatedSaleDateResolver) {
                    $dateTimeNormalizer = static function (Options $options, $value) {
                        if ($value instanceof \DateTime || null === $value) {
                            return $value;
                        }

                        return new \DateTime($value);
                    };
                    $estimatedSaleDateResolver
                        ->setDefaults([
                            'after' => null,
                            'before' => null,
                        ])
                        ->setNormalizer('after', $dateTimeNormalizer)
                        ->setNormalizer('before', $dateTimeNormalizer)
                    ;
                },
                'buyer.watchList' => null,
                'currency' => $this->entityManager->getRepository(Currency::class)->findOneBy(['name' => 'EUR']),
                'ponderated' => false,
            ])
            ->setNormalizer('buyer.watchList', static fn (Options $options, $value) => null === $value ? null : (bool) $value)
            ->setNormalizer('currency', static function (Options $options, $value) use ($iriConverter) {
                if ($value instanceof Currency) {
                    return $value;
                }

                return $iriConverter->getResourceFromIri($value);
            })
        ;

        $options = $resolver->resolve($options);

        $queryNameGenerator = new QueryNameGenerator();

        $qb = $this->getBaseQueryBuilder($queryNameGenerator, $options['currency'], (bool) $options['ponderated']);

        $ssoAlias = QueryBuilderHelper::addJoinOnce($qb, $queryNameGenerator, 'o', 'sso', Join::LEFT_JOIN);
        $customerAlias = QueryBuilderHelper::addJoinOnce($qb, $queryNameGenerator, 'o', 'buyer', Join::LEFT_JOIN);

        if (null !== ($after = $options['estimatedSaleDate']['after'])) {
            $qb
                ->andWhere('o.estimatedSaleDate >= :after')
                ->setParameter('after', $after)
            ;
        }

        if (null !== ($before = $options['estimatedSaleDate']['before'])) {
            $qb
                ->andWhere('o.estimatedSaleDate < :before')
                ->setParameter('before', $before)
            ;
        }

        if (true === $options['buyer.watchList']) {
            $qb
                ->andWhere(\sprintf('%s.watchList = :watchList', $customerAlias))
                ->setParameter('watchList', true)
            ;
        }

        $qb
            ->addSelect(\sprintf('%s.id AS sso_id', $ssoAlias))
            ->addSelect(\sprintf('%s.name AS y', $ssoAlias))
            ->addSelect(\sprintf('%s.id AS customer_id', $customerAlias))
            ->addSelect(\sprintf('%s.name AS x', $customerAlias))
            ->andWhere(\sprintf('%s.id IS NOT NULL', $customerAlias))
            ->andWhere($qb->expr()->in('o.status', SalesForecast::OPEN_STATUSES))
            ->orderBy('x, y', 'ASC')
            ->groupBy('x, y')
        ;

        $provider = new ReportDataProvider(
            $qb->getQuery()->getScalarResult()
        );

        return $provider->setMetadataExtractor($this->irisExtractorBuilderFactory->createBuilder()
            ->setX(Customer::class, 'customer_id')
            ->setY(Location::class, 'sso_id')
            ->generate()
        );
    }
}
