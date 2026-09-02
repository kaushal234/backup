<?php

declare(strict_types=1);

namespace App\Report\Handler\Sales\SalesForecast;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Finance\Currency;
use App\Entity\Sales\SalesForecast;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Repository\Finance\ExchangeRateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class SalesForecastValueByMonth implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IsGrantedTrait;

    private readonly EntityManagerInterface $entityManager;
    private readonly ExchangeRateRepository $exchangeRateRepository;
    private readonly IriConverterInterface $iriConverter;
    private readonly TokenStorageInterface $tokenStorage;
    private readonly AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(EntityManagerInterface $entityManager, ExchangeRateRepository $exchangeRateRepository, IriConverterInterface $iriConverter, TokenStorageInterface $tokenStorage, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->entityManager = $entityManager;
        $this->exchangeRateRepository = $exchangeRateRepository;
        $this->iriConverter = $iriConverter;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (SalesForecast::class !== $resourceClass || 'date' !== $x || 'value' !== $y) {
            return null;
        }

        $dateTo = (new \DateTime('midnight first day of this month next year'))->format('Y-m-d');
        $dateFrom = (new \DateTime('midnight first day of this month'))->format('Y-m-d');
        $andWhere = '';
        $currencyRate = 1;

        /** @var People|null $currentUser */
        $currentUser = null !== ($token = $this->tokenStorage->getToken()) ? $token->getUser() : null;

        if (isset($options['asm'])) {
            if (null !== $currentUser && !$this->authorizationChecker->isGranted('FEATURE_SALES_FORECAST_VALORIZATION_READ') && $options['asm'] !== $this->iriConverter->getIriFromResource($currentUser)) {
                throw new AccessDeniedHttpException();
            }

            try {
                $user = $this->iriConverter->getResourceFromIri($options['asm']);
                if (!$user instanceof People) {
                    throw new \InvalidArgumentException('Given ASM IRI is not a People');
                }
            } catch (\InvalidArgumentException $invalidArgumentException) {
                throw new NotFoundHttpException(\sprintf('People %s not found', $options['asm']), $invalidArgumentException);
            }

            if (null === $currency = $user->getBusinessUnit()->getLocation()->getCurrency()) {
                /** @var Currency $currency */
                $currency = $this->entityManager->getRepository(Currency::class)->findOneBy(['name' => 'USD']);
            }

            $andWhere = \sprintf('AND q.asm_id = %s', $user->getId());

            $currencyRate = $this->exchangeRateRepository->getCurrencyRate($currency);
        }

        if (isset($options['sso'])) {
            if (null !== $currentUser && !$this->authorizationChecker->isGranted('FEATURE_SALES_FORECAST_VALORIZATION_READ')) {
                throw new AccessDeniedHttpException();
            }

            try {
                $sso = $this->iriConverter->getResourceFromIri($options['sso']);
                if (!$sso instanceof Location) {
                    throw new \InvalidArgumentException('Given SSO IRI is not a Location');
                }
            } catch (\InvalidArgumentException $invalidArgumentException) {
                throw new NotFoundHttpException(\sprintf('SSO %s not found', $options['sso']), $invalidArgumentException);
            }

            if (null === $currency = $sso->getCurrency()) {
                /** @var Currency $currency */
                $currency = $this->entityManager->getRepository(Currency::class)->findOneBy(['name' => 'USD']);
            }

            $andWhere = \sprintf('AND q.sso_id = %s', $sso->getId());

            $currencyRate = $this->exchangeRateRepository->getCurrencyRate($currency);
        }

        $y = isset($options['byFactory']) && $options['byFactory'] ? 'f.name' : "'amount'";

        $ponderation = isset($options['ponderated']) && $options['ponderated'] ? '(q.customer_success_percentage/100)*(q.success_percentage/100)' : 1;

        $sql = \sprintf('
SELECT
        CEIL(SUM(
            q.price
            * q.quantity
            * (CASE
                    WHEN c.name = \'EUR\'
                    THEN (%s)
                    ELSE 1/lastRate.rate*(%s)
                END
            )*%s
        )) AS value,
        DATE_FORMAT(q.estimated_sale_date, \'%%Y-%%m\') AS x,
        %s AS y
FROM sales_forecasts q
LEFT JOIN directory_location l ON l.id = q.sso_id
LEFT JOIN directory_location f ON f.id = q.factory_id
LEFT JOIN currencies c ON c.id = l.currency_id
LEFT JOIN (
        SELECT MAX(id) max_id, currency_id, rate, type
        FROM exchange_rates
        WHERE type = \'AVG\'
        GROUP BY currency_id
    ) lastRate ON lastRate.currency_id = c.id
WHERE q.estimated_sale_date >= \'%s\' AND q.estimated_sale_date <= \'%s\' AND q.status IN (\'BUDGET\', \'IN_PROGRESS\', \'DELAYED\') %s
GROUP BY x, q.asm_id, y
ORDER BY x ASC
', $currencyRate, $currencyRate, $ponderation, $y, $dateFrom, $dateTo, $andWhere);

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('x', 'x');
        $rsm->addScalarResult('y', 'y');
        $rsm->addScalarResult('value', 'value');

        $query = $this->entityManager->createNativeQuery($sql, $rsm);

        return new ReportDataProvider(
            $query->getScalarResult()
        );
    }
}
