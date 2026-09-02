<?php

declare(strict_types=1);

namespace App\Report\Handler\Sales\SalesForecast;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\People;
use App\Entity\Finance\Currency;
use App\Entity\Sales\Customer;
use App\Entity\Sales\SalesForecast;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IrisExtractorBuilderFactoryAwareTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Repository\Finance\ExchangeRateRepository;
use App\Util\IriToId;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SalesForecastsTopCustomersHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IrisExtractorBuilderFactoryAwareTrait;
    use IsGrantedTrait;

    private readonly EntityManagerInterface $entityManager;

    private readonly IriConverterInterface $iriConverter;

    private readonly IriToId $iriToId;

    private readonly ExchangeRateRepository $exchangeRateRepository;

    public function __construct(EntityManagerInterface $entityManager, IriConverterInterface $iriConverter, IriToId $iriToId, ExchangeRateRepository $exchangeRateRepository)
    {
        $this->entityManager = $entityManager;
        $this->iriConverter = $iriConverter;
        $this->iriToId = $iriToId;
        $this->exchangeRateRepository = $exchangeRateRepository;
    }

    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (SalesForecast::class !== $resourceClass || 'top_ten' !== $x || 'value' !== $y) {
            return null;
        }

        $dateFrom = (new \DateTime('midnight first day of this month'))->format('Y-m-d');
        $dateTo = (new \DateTime('first day of this month + 3 months'))->format('Y-m-d');
        $andWhere = '';
        $currencyRate = '';
        if (isset($options['asm'])) {
            $asmId = $this->iriToId->getId((string) $options['asm']);
            $andWhere = \sprintf('AND q.asm_id = %s', $asmId);

            try {
                $user = $this->iriConverter->getResourceFromIri($options['asm']);

                if (!$user instanceof People) {
                    throw new \InvalidArgumentException();
                }
            } catch (\InvalidArgumentException $invalidArgumentException) {
                throw new NotFoundHttpException(\sprintf('People %s not found', $options['asm']), $invalidArgumentException);
            }

            if (null === $currency = $user->getBusinessUnit()->getLocation()->getCurrency()) {
                /** @var Currency $currency */
                $currency = $this->entityManager->getRepository(Currency::class)->findOneBy(['name' => 'USD']);
            }

            if (null !== $user->getBusinessUnit()) {
                $currencyRate = $this->exchangeRateRepository->getCurrencyRate($currency);
            }
        }

        $sql = \sprintf("SELECT \n    cust.id as customer_id,\n    cust.name as x,\n    CEIL(SUM(\n        q.price \n        * q.quantity \n        * (CASE\n                WHEN c.name = 'EUR'\n                THEN (%s)\n                ELSE 1/lastRate.rate*(%s)\n            END\n        )\n    )) value,\n    'amount' as y\nFROM sales_forecasts q\nLEFT JOIN customers cust ON cust.id = q.buyer_id\nLEFT JOIN directory_location l ON l.id = q.sso_id\nLEFT JOIN currencies c ON c.id = l.currency_id \nLEFT JOIN (\n        SELECT MAX(id) max_id, currency_id, rate, type\n        FROM exchange_rates\n        WHERE type = 'AVG'\n        GROUP BY currency_id\n    ) lastRate ON lastRate.currency_id = c.id\nWHERE q.estimated_sale_date >= '%s' AND q.estimated_sale_date <= '%s' AND q.status IN ('BUDGET', 'IN_PROGRESS', 'DELAYED') %s\nGROUP BY x, q.asm_id, y\nORDER BY value DESC LIMIT 10", $currencyRate, $currencyRate, $dateFrom, $dateTo, $andWhere);
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('customer_id', 'customer_id');
        $rsm->addScalarResult('x', 'x');
        $rsm->addScalarResult('y', 'y');
        $rsm->addScalarResult('value', 'value');

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
