<?php

declare(strict_types=1);

namespace App\Manager\Finance;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\UrlGeneratorInterface;
use App\Dto\Finance\AccountReceivableOverview;
use App\Entity\Finance\Currency;
use App\Entity\Finance\ExchangeRate;
use App\Entity\Sales\CustomerErpReference;
use App\Repository\Finance\ExchangeRateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class AccountReceivableOverviewManager
{
    private readonly EntityManagerInterface $entityManager;
    private readonly DenormalizerInterface $denormalizer;
    private readonly IriConverterInterface $iriConverter;

    public function __construct(EntityManagerInterface $entityManager, DenormalizerInterface $denormalizer, IriConverterInterface $iriConverter)
    {
        $this->entityManager = $entityManager;
        $this->denormalizer = $denormalizer;
        $this->iriConverter = $iriConverter;
    }

    /**
     * @return array|AccountReceivableOverview[]
     */
    public function getOverview(Currency $currency, float $currencyRate, array $parameters): array
    {
        /** @var ExchangeRateRepository $exchangeRateRepository */
        $exchangeRateRepository = $this->entityManager->getRepository(ExchangeRate::class);
        $rates = $exchangeRateRepository->getAllCurrenciesRates(ExchangeRate::TYPE_END_OF_MONTH_RATE);
        $condition = 'IF(%s, %s, %s)';
        foreach ($rates as $i => $rate) {
            if (null === $rate['rate']) {
                continue;
            }
            $valueIfFalse = $i === (\count($rates) - 1) ? '1' : 'IF(%s, %s, %s)';
            $condition = \sprintf($condition, \sprintf("%s.name = '%s'", 'cu', $rate['currency']), $rate['rate'], $valueIfFalse);
        }

        $where = 'WHERE';
        foreach (array_keys($parameters['ssos'] ?? []) as $key) {
            $where .= \sprintf(' cus_erp.sso_id = :sso_%s %s', $key, \count($parameters['ssos']) === $key + 1 ? 'AND' : 'OR');
        }

        foreach (array_keys($parameters['transactionTypes'] ?? []) as $key) {
            $where .= \sprintf(' t_ref.transaction_type_id = :transactionType_%s %s', $key, \count($parameters['transactionTypes']) === $key + 1 ? 'AND' : 'OR');
        }

        foreach (array_keys($parameters['asms'] ?? []) as $key) {
            $where .= \sprintf(' asm.id = :asm_%s %s', $key, \count($parameters['asms']) === $key + 1 ? 'AND' : 'OR');
        }

        foreach (array_keys($parameters['ssds'] ?? []) as $key) {
            $where .= \sprintf(' asm.supervisor_id = :ssd_%s %s', $key, \count($parameters['ssds']) === $key + 1 ? 'AND' : 'OR');
        }

        $where = mb_substr($where, 0, -3);

        $sql = "
SELECT
        ar.id,
        cus_erp.id AS customerErpReferenceId,
        :currency AS currency,
        SUM(ar.balance_amount / ($condition) * :currencyRate) / 1000 AS totalValue,
        SUM(CASE WHEN ar.due_date > :now THEN (ar.balance_amount / ($condition) * :currencyRate) / 1000 ELSE 0 END) AS notPastDue,
        SUM(CASE WHEN ar.due_date > :oneMonthAgo AND ar.due_date <= :now THEN (ar.balance_amount / ($condition) * :currencyRate) / 1000 ELSE 0 END) AS pastDueOneMonth,
        SUM(CASE WHEN ar.due_date > :twoMonthsAgo AND ar.due_date <= :oneMonthAgo THEN (ar.balance_amount / ($condition) * :currencyRate) / 1000 ELSE 0 END) AS pastDueTwoMonths,
        SUM(CASE WHEN ar.due_date > :threeMonthsAgo AND ar.due_date <= :twoMonthsAgo THEN (ar.balance_amount / ($condition) * :currencyRate) / 1000 ELSE 0 END) AS pastDueThreeMonths,
        SUM(CASE WHEN ar.due_date > :sixMonthsAgo AND ar.due_date <= :threeMonthsAgo THEN (ar.balance_amount / ($condition) * :currencyRate) / 1000 ELSE 0 END) AS pastDueSixMonths,
        SUM(CASE WHEN ar.due_date <= :sixMonthsAgo THEN (ar.balance_amount / ($condition) * :currencyRate) / 1000 ELSE 0 END) AS pastDueMoreThanSixMonths,
        (SUM(CASE WHEN ar.due_date < :twoMonthsAgo THEN (ar.balance_amount / ($condition) * :currencyRate) ELSE 0 END) * 100 / (SUM(ar.balance_amount / ($condition) * :currencyRate))) AS pastDueTwoMonthsPercentage,
        (SUM(CASE WHEN ar.due_date <= :now THEN (ar.balance_amount / ($condition) * :currencyRate) ELSE 0 END) * 100 / (SUM(ar.balance_amount / ($condition) * :currencyRate))) AS pastDuePercentage
FROM account_receivables ar
LEFT JOIN customer_erp_references cus_erp ON cus_erp.id = ar.customer_erp_reference_id
LEFT JOIN currencies cu ON cu.id = ar.currency_id
LEFT JOIN customers c ON c.id = cus_erp.customer_id
LEFT JOIN customer_sales_representatives rep ON c.main_sales_representative_id = rep.id
LEFT JOIN user asm ON rep.asm_id = asm.id
LEFT JOIN transaction_type_references t_ref ON ar.transaction_type_reference_id = t_ref.id LEFT JOIN transaction_types tt ON t_ref.transaction_type_id = tt.id
$where
GROUP BY c.id
ORDER BY totalValue DESC
";

        $queryParameters = [
            'now' => date('Y-m-d'),
            'oneMonthAgo' => (new \DateTime('30 days ago'))->format('Y-m-d'),
            'twoMonthsAgo' => (new \DateTime('60 days ago'))->format('Y-m-d'),
            'threeMonthsAgo' => (new \DateTime('90 days ago'))->format('Y-m-d'),
            'sixMonthsAgo' => (new \DateTime('180 days ago'))->format('Y-m-d'),
            'currencyRate' => $currencyRate,
            'currency' => $currency->getName(),
        ];

        foreach ($parameters['ssos'] ?? [] as $key => $sso) {
            $queryParameters[\sprintf('sso_%s', $key)] = $sso->getId();
        }

        foreach ($parameters['transactionTypes'] ?? [] as $key => $transactionType) {
            $queryParameters[\sprintf('transactionType_%s', $key)] = $transactionType->getId();
        }

        foreach ($parameters['asms'] ?? [] as $key => $asm) {
            $queryParameters[\sprintf('asm_%s', $key)] = $asm->getId();
        }

        foreach ($parameters['ssds'] ?? [] as $key => $ssd) {
            $queryParameters[\sprintf('ssd_%s', $key)] = $ssd->getId();
        }

        $statement = $this->entityManager->getConnection()->executeQuery($sql, $queryParameters);

        $results = [];
        foreach ($statement->fetchAllAssociative() as $row) {
            if (null !== $row['customerErpReferenceId']) {
                $row['customerErpReference'] = $this->iriConverter->getIriFromResource(CustomerErpReference::class, UrlGeneratorInterface::ABS_PATH, new Get(), ['uri_variables' => ['id' => $row['customerErpReferenceId']]]);
            }
            $results[] = $this->denormalizer->denormalize($row, AccountReceivableOverview::class, null, [ObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true]);
        }

        return $results;
    }
}
