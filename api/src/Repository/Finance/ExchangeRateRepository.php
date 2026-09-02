<?php

declare(strict_types=1);

namespace App\Repository\Finance;

use App\Entity\Finance\Currency;
use App\Entity\Finance\ExchangeRate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;

class ExchangeRateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExchangeRate::class);
    }

    public function getCurrencyRate(Currency $currency, $type = ExchangeRate::TYPE_MONTH_AVERAGE_RATE)
    {
        if ('EUR' === $currency->getName()) {
            return 1;
        }

        $qb = $this->createQueryBuilder('e');
        $qb
            ->select('e.rate')
            ->join(Currency::class, 'c', Join::WITH, 'e.currency = c.id')
            ->where('c.name = :money')
            ->andWhere('e.type = :type')
            ->orderBy('e.applicatedOn', 'DESC')
            ->setMaxResults(1)
            ->setParameter('money', $currency->getName())
            ->setParameter('type', $type)
        ;

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function convertAmount(float $amount, string $currencyName, string $type = ExchangeRate::TYPE_MONTH_AVERAGE_RATE)
    {
        $currency = $this->getEntityManager()->getRepository(Currency::class)->findOneBy(['name' => 'RMB' === $currencyName ? 'CNY' : $currencyName]);
        if (!$currency instanceof Currency) {
            throw new \InvalidArgumentException(\sprintf('Currency %s not found', $currencyName));
        }
        $rate = $this->getCurrencyRate($currency, $type);

        return $amount / $rate;
    }

    public function getAllCurrenciesRates(string $type = ExchangeRate::TYPE_MONTH_AVERAGE_RATE): array
    {
        $sql = \sprintf(
            'SELECT c.name AS currency,
(SELECT rate FROM exchange_rates WHERE type="%s" AND currency_id=c.id ORDER BY applicated_on DESC LIMIT 1) as rate
FROM currencies c
WHERE (SELECT rate FROM exchange_rates WHERE type="%s" AND currency_id=c.id ORDER BY applicated_on DESC LIMIT 1) IS NOT NULL
GROUP BY c.id', $type, $type);

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('currency', 'currency');
        $rsm->addScalarResult('rate', 'rate', Types::FLOAT);

        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->setParameter('type', $type);

        return $query->getScalarResult();
    }

    public function getIfConditionForExchangeRates(string $currencyAlias)
    {
        $rates = $this->getAllCurrenciesRates(ExchangeRate::TYPE_END_OF_MONTH_RATE);
        $condition = 'IFELSE(%s, %s, %s)';
        foreach ($rates as $i => $rate) {
            if (null === $rate['rate']) {
                continue;
            }
            $valueIfFalse = $i === (\count($rates) - 1) ? '1' : 'IFELSE(%s, %s, %s)';
            $condition = \sprintf($condition, \sprintf("%s.name = '%s'", $currencyAlias, $rate['currency']), $rate['rate'], $valueIfFalse);
        }

        return $condition;
    }
}
