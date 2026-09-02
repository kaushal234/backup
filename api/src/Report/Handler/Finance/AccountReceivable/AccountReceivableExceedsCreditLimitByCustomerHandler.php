<?php

declare(strict_types=1);

namespace App\Report\Handler\Finance\AccountReceivable;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Finance\AccountReceivable;
use App\Entity\Finance\CreditLimit;
use App\Entity\Finance\Currency;
use App\Entity\Finance\ExchangeRate;
use App\Entity\Finance\TransactionType;
use App\Entity\Sales\Customer;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IrisExtractorBuilderFactoryAwareTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Repository\Finance\ExchangeRateRepository;
use App\Util\IriToId;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;

class AccountReceivableExceedsCreditLimitByCustomerHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IrisExtractorBuilderFactoryAwareTrait;
    use IsGrantedTrait;

    private readonly EntityManagerInterface $entityManager;
    private readonly IriConverterInterface $iriConverter;
    private readonly IriToId $iriToId;

    public function __construct(EntityManagerInterface $entityManager, IriConverterInterface $iriConverter, IriToId $iriToId)
    {
        $this->entityManager = $entityManager;
        $this->iriConverter = $iriConverter;
        $this->iriToId = $iriToId;
    }

    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (AccountReceivable::class !== $resourceClass || 'credit_limit' !== $x || 'value' !== $y || !isset($options['currency'])) {
            return null;
        }

        $andWhere = '';
        foreach (['asms' => 'id', 'ssds' => 'supervisor_id'] as $key => $queryValue) {
            if (isset($options[$key]) && \is_array($options[$key])) {
                $ids = [];
                foreach ($options[$key] as $iri) {
                    $ids[] = $this->iriToId->getId((string) $iri);
                }
                $andWhere = \sprintf(' AND asm.%s IN (%s)', $queryValue, implode(', ', $ids));
            }
        }

        /** @var Currency $currency */
        $currency = $this->iriConverter->getResourceFromIri($options['currency']);
        /** @var ExchangeRateRepository $exchangeRateRepository */
        $exchangeRateRepository = $this->entityManager->getRepository(ExchangeRate::class);

        $rates = $exchangeRateRepository->getAllCurrenciesRates(ExchangeRate::TYPE_END_OF_MONTH_RATE);
        $ratesDictionary = [];
        foreach ($rates as $rate) {
            $ratesDictionary[$rate['currency']] = $rate['rate'];
        }

        $conversion = (float) ($ratesDictionary[$currency->getName()] ?? 1);

        $conditionAR = $conditionCreditLimit = 'IF(%s, %s, %s)';
        foreach ($rates as $i => $rate) {
            if (null === $rate['rate']) {
                continue;
            }
            $valueIfFalse = $i === (\count($rates) - 1) ? '1' : 'IF(%s, %s, %s)';
            $conditionAR = \sprintf($conditionAR, \sprintf("%s.name = '%s'", 'cu', $rate['currency']), $rate['rate'], $valueIfFalse);
            $conditionCreditLimit = \sprintf($conditionCreditLimit, \sprintf("%s.name = '%s'", 'cu2', $rate['currency']), $rate['rate'], $valueIfFalse);
        }

        $sql = \sprintf('
SELECT
    c.id as customer_id,
    ROUND((SUM(q.balance_amount / %s * %s )) -  (cl.amount / %s * %s)) as value,
    c.name as x,
    cl.type as y
FROM account_receivables q
         LEFT JOIN transaction_type_references tt_ref ON q.transaction_type_reference_id = tt_ref.id
         LEFT JOIN transaction_types tt ON tt_ref.transaction_type_id = tt.id
         LEFT JOIN customer_erp_references cus_erp ON q.customer_erp_reference_id = cus_erp.id
         LEFT JOIN customers c ON cus_erp.customer_id = c.id
         LEFT JOIN customer_sales_representatives rep ON c.main_sales_representative_id = rep.asm_id
         LEFT JOIN user asm ON rep.asm_id = asm.id
         LEFT JOIN credit_limit cl ON c.id = cl.customer_id
                AND (CASE cl.type
                        WHEN "%s" THEN tt.name = "%s"
                        WHEN "%s" THEN tt.name = "%s"
                        WHEN "%s" THEN tt.name IN ("%s", "%s", "%s")
                        WHEN "%s" THEN tt.name IN ("%s", "%s", "%s", "%s", "%s")
                    END
                    )
         LEFT JOIN currencies cu ON q.currency_id = cu.id
         LEFT JOIN currencies cu2 ON cl.currency_id = cu2.id
WHERE (SELECT SUM(q.balance_amount / %s * %s )) >  (cl.amount / %s * %s) %s
GROUP BY x, y
ORDER BY c.name', $conditionAR, $conversion, $conditionCreditLimit, $conversion, CreditLimit::INTERNAL_SPH, TransactionType::SPARE_PARTS, CreditLimit::INTERNAL_UNITS, TransactionType::UNITS, CreditLimit::INTERNAL_OTHERS, TransactionType::SERVICE, TransactionType::WARRANTY, TransactionType::MISC, CreditLimit::FACTOR, TransactionType::MISC, TransactionType::WARRANTY, TransactionType::SERVICE, TransactionType::UNITS, TransactionType::SPARE_PARTS, $conversion, $conditionAR, $conversion, $conditionCreditLimit, $andWhere);

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('customer_id', 'customer_id');
        $rsm->addScalarResult('value', 'value');
        $rsm->addScalarResult('x', 'x');
        $rsm->addScalarResult('y', 'y');

        $query = $this->entityManager->createNativeQuery($sql, $rsm);
        $provider = new ReportDataProvider(
            $query->getScalarResult()
        );

        return $provider->setMetadataExtractor($this->irisExtractorBuilderFactory->createBuilder()
            ->setX(Customer::class, 'customer_id')
            ->generate()
        );
    }
}
