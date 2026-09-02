<?php

declare(strict_types=1);

namespace App\Report\Handler\Sales\SalesForecast;

use ApiPlatform\Doctrine\Orm\Util\QueryBuilderHelper;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\IriConverterInterface;
use App\Doctrine\ORM\Extension\SalesForecastExtension;
use App\Entity\Finance\Currency;
use App\Entity\Finance\ExchangeRate;
use App\Entity\Sales\SalesForecast;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Repository\Finance\ExchangeRateRepository;
use App\Repository\Sales\SalesForecastRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

abstract class AbstractSalesForecastValuationHandler implements ReportHandlerInterface
{
    use IsGrantedTrait;
    protected EntityManagerInterface $entityManager;
    protected IriConverterInterface $iriConverter;
    private readonly SalesForecastExtension $extension;

    public function __construct(EntityManagerInterface $entityManager, IriConverterInterface $iriConverter, SalesForecastExtension $extension)
    {
        $this->entityManager = $entityManager;
        $this->iriConverter = $iriConverter;
        $this->extension = $extension;
    }

    protected function getBaseQueryBuilder(QueryNameGenerator $queryNameGenerator, ?Currency $currency = null, bool $ponderated = false): QueryBuilder
    {
        /** @var SalesForecastRepository $salesForecastRepository */
        $salesForecastRepository = $this->entityManager->getRepository(SalesForecast::class);
        /** @var ExchangeRateRepository $exchangeRateRepository */
        $exchangeRateRepository = $this->entityManager->getRepository(ExchangeRate::class);
        $currencyRepository = $this->entityManager->getRepository(Currency::class);

        $qb = $salesForecastRepository->createQueryBuilder('o');

        $this->extension->applyToCollection($qb, $queryNameGenerator, SalesForecast::class, new GetCollection());

        $ssoAlias = QueryBuilderHelper::addJoinOnce($qb, $queryNameGenerator, 'o', 'sso', Join::LEFT_JOIN);
        $currencyAlias = QueryBuilderHelper::addJoinOnce($qb, $queryNameGenerator, $ssoAlias, 'currency', Join::LEFT_JOIN);

        if (null === $currency) {
            /** @var Currency $currency */
            $currency = $currencyRepository->findOneBy(['name' => 'EUR']);
        }

        $ponderation = '1';
        if ($ponderated) {
            $ponderation = '(o.customerSuccessPercentage/100)*(o.successPercentage/100)';
        }

        $rates = $exchangeRateRepository->getAllCurrenciesRates();

        $ratesDictionary = [];
        foreach ($rates as $rate) {
            $ratesDictionary[$rate['currency']] = $rate['rate'];
        }

        $conversion = (float) ($ratesDictionary[$currency->getName()] ?? 1);

        $condition = 'IFELSE(%s, %s, %s)';
        $ratesQuantity = \count($rates);
        foreach ($rates as $i => $rate) {
            if (null === $rate['rate']) {
                continue;
            }
            $valueIfFalse = $i === ($ratesQuantity - 1) ? '1' : 'IFELSE(%s, %s, %s)';
            $condition = \sprintf($condition, \sprintf("%s.name = '%s'", $currencyAlias, $rate['currency']), $rate['rate'], $valueIfFalse);
        }

        $qb
            ->select(\sprintf('CEIL(SUM(o.price * o.quantity / %s * %s * %s)) AS value', $condition, $ponderation, $conversion))
        ;

        return $qb;
    }
}
