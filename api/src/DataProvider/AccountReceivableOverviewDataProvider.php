<?php

declare(strict_types=1);

namespace App\DataProvider;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Directory\Location;
use App\Entity\Finance\Currency;
use App\Entity\Finance\ExchangeRate;
use App\Entity\Sales\CustomerErpReference;
use App\Manager\Finance\AccountReceivableOverviewManager;
use App\Repository\Finance\ExchangeRateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class AccountReceivableOverviewDataProvider implements ProviderInterface, ServiceSubscriberInterface
{
    public function __construct(
        private readonly ContainerInterface $container
    ) {
    }

    public static function getSubscribedServices(): array
    {
        return [
            IriConverterInterface::class,
            Security::class,
            EntityManagerInterface::class,
            AccountReceivableOverviewManager::class,
        ];
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $currencyIri = $context['filters']['currency'] ?? null;
        $transactionTypeIris = $context['filters']['transactionTypeReference.transactionType'] ?? null;
        $asmIris = $context['filters']['customerErpReference.customer.mainSalesRepresentative.asm'] ?? null;
        $ssdIris = $context['filters']['customerErpReference.customer.mainSalesRepresentative.asm.supervisor'] ?? null;
        $ssoIris = $context['filters']['customerErpReference.sso'] ?? null;

        /** @var IriConverterInterface $iriConverter */
        $iriConverter = $this->container->get(IriConverterInterface::class);

        $parameters = [];
        foreach (['transactionTypes' => $transactionTypeIris, 'asms' => $asmIris, 'ssds' => $ssdIris, 'ssos' => $ssoIris] as $key => $iris) {
            foreach ($iris ?? [] as $iri) {
                $parameters[$key][] = $iriConverter->getResourceFromIri($iri);
            }
        }

        $currency = null !== $currencyIri ? $iriConverter->getResourceFromIri($currencyIri) : null;
        if (!$currency instanceof Currency || empty($parameters['ssos'])) {
            throw new BadRequestHttpException('Currency and SSO are mandatory filters for this route.');
        }

        /** @var Security $security */
        $security = $this->container->get(Security::class);

        if (!$security->isGranted('FEATURE_ACCOUNT_RECEIVABLES_VIEW_FULL') && !$security->isGranted('MOO_AR')) {
            /** @var Location $sso */
            foreach ($parameters['ssos'] as $key => $sso) {
                if (!$security->isGranted('FEATURE_ACCOUNT_RECEIVABLES_VIEW_SSO_'.$sso->getId())) {
                    unset($parameters['ssos'][$key]);
                }
            }

            if ([] === $parameters['ssos']) {
                return [];
            }
        }

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->container->get(EntityManagerInterface::class);

        /** @var ExchangeRateRepository $exchangeRateRepository */
        $exchangeRateRepository = $entityManager->getRepository(ExchangeRate::class);

        try {
            $currencyRate = $exchangeRateRepository->getCurrencyRate($currency, ExchangeRate::TYPE_END_OF_MONTH_RATE);
        } catch (NoResultException $e) {
            throw new BadRequestHttpException('No exchange rate found for this currency.', $e);
        }

        /** @var AccountReceivableOverviewManager $accountReceivableOverviewManager */
        $accountReceivableOverviewManager = $this->container->get(AccountReceivableOverviewManager::class);

        foreach ($accountReceivableOverviews = $accountReceivableOverviewManager->getOverview($currency, (float) $currencyRate, $parameters) as $accountReceivableOverview) {
            $customerErpReferenceRepository = $entityManager->getRepository(CustomerErpReference::class);
            $accountReceivableOverview->customerErpReference = $customerErpReferenceRepository->findOneBy(['id' => $accountReceivableOverview->customerErpReference]);
        }

        return $accountReceivableOverviews;
    }
}
