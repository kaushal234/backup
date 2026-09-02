<?php

declare(strict_types=1);

namespace App\Notifier\Sales\SalesForecast;

use App\Entity\Common\Subscription;
use App\Entity\Directory\Location;
use App\Entity\Directory\Network;
use App\Entity\Directory\People;
use App\Entity\Sales\Customer;
use App\Entity\Sales\CustomerType;
use App\Entity\Sales\ProductType;
use App\Entity\Sales\SalesArea;
use App\Entity\Sales\SalesForecast;
use App\Repository\Common\SubscriptionRepository;
use App\Repository\Directory\NetworkRepository;
use App\Repository\Directory\PeopleRepository;
use App\Repository\Sales\SalesAreaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Bundle\SecurityBundle\Security;

class RecipientsFinder
{
    private readonly EntityManagerInterface $entityManager;
    private readonly Security $security;

    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    public function findRecipients(SalesForecast $salesForecast, $subject = null)
    {
        /** @var PeopleRepository $peopleRepository */
        $peopleRepository = $this->entityManager->getRepository(People::class);

        $recipients = [$salesForecast->getAsm()];
        if (null !== ($user = $this->security->getUser())) {
            $recipients[] = $user;
        }

        $locations = [['factory' => $salesForecast->getFactory(), 'sso' => $salesForecast->getSso()]];
        if (null !== $salesForecast->getMasterSalesForecast() && $salesForecast->isNotifyPackage()) {
            $locations = array_map(static fn (SalesForecast $salesForecast) => ['factory' => $salesForecast->getFactory(), 'sso' => $salesForecast->getSso()], $salesForecast->getMasterSalesForecast()->getSalesForecasts()->toArray());
        }

        $extraRecipients = [];
        foreach ($locations as $location) {
            if ($salesForecast->isNotificationRestricted()) {
                $extraRecipients[] = [
                    ...$peopleRepository->findGroupsMembers(['ROLE_EVP'], $location['sso']),
                    ...$peopleRepository->findGroupsMembers(['ROLE_RCEO', 'ROLE_COO', 'ROLE_PSM'], $location['factory']),
                ];
                continue;
            }

            $extraRecipients[] = [
                ...$peopleRepository->findGroupsMembers(['ROLE_GCOO', 'ROLE_GCEO', 'ROLE_CHAIRMAN', 'ROLE_GPID', 'ROLE_CSD', 'ROLE_GCFO', 'ROLE_GCTO']),
                ...$peopleRepository->findGroupsMembers(['ROLE_EVP', 'ROLE_CEO', 'ROLE_SAM'], $location['sso']),
                ...$peopleRepository->findGroupsMembers(['ROLE_RCEO', 'ROLE_CEO', 'ROLE_RCOO', 'ROLE_COO', 'ROLE_PSM', 'ROLE_PSE'], $location['factory']),
            ];
            if (\in_array($subject, ['sfr.subject.closure', 'sfr.subject.cancellation'], true)) {
                $extraRecipients[] = [
                    ...$peopleRepository->findGroupsMembers(['ROLE_GCH']),
                ];
            }
        }

        return array_merge($recipients, array_merge_recursive(...$extraRecipients));
    }

    public function findCcs(SalesForecast $salesForecast, ?string $subject = null)
    {
        /** @var PeopleRepository $peopleRepository */
        $peopleRepository = $this->entityManager->getRepository(People::class);

        $ccs = [];
        if (null !== $supervisor = $salesForecast->getAsm()->getSupervisor()) {
            $ccs[] = $supervisor;
        }

        if ('sfr.subject.comment' === $subject) {
            foreach ($peopleRepository->findGroupMembers('ROLE_RCEO', $salesForecast->getSso()) as $people) {
                $ccs[] = $people;
            }
        }

        if (\in_array($salesForecast->getStatus(), [SalesForecast::LOST, SalesForecast::ORDERED, SalesForecast::PARTIAL, SalesForecast::ORDER_CANCELLED], true)) {
            foreach ($peopleRepository->findGroupMembers('GG_GFD') as $people) {
                $ccs[] = $people;
            }
        }

        return [
            ...$ccs,
            ...$this->getCcWinning($salesForecast),
            ...$this->getCcSubscriptions($salesForecast),
            ...$this->getCcSAS($salesForecast),
            ...$this->getCcCustomer($salesForecast),
        ];
    }

    public function findCcsDelinquent(Location $sso, People $asm): array
    {
        /** @var PeopleRepository $peopleRepository */
        $peopleRepository = $this->entityManager->getRepository(People::class);
        $ccs = $peopleRepository->findGroupsMembers(['ROLE_EVP', 'ROLE_CEO'], $sso);

        if (null !== $supervisor = $asm->getSupervisor()) {
            $ccs[] = $supervisor;
        }

        return $ccs;
    }

    private function getCcWinning(SalesForecast $salesForecast): array
    {
        /** @var PeopleRepository $peopleRepository */
        $peopleRepository = $this->entityManager->getRepository(People::class);

        $ccs = [];
        if (\in_array($salesForecast->getStatus(), [SalesForecast::ORDERED, SalesForecast::PARTIAL], true)) {
            $ccs = $peopleRepository->findGroupsMembers(['GG_SFR_ORDERED_NOTIFICATION']);
            $ccs = array_merge($ccs, $peopleRepository->findGroupsMembers(['GG_SFR_SSO_ORDERED_NOTIFICATION'], $salesForecast->getSso()));
        }

        return $ccs;
    }

    private function getCcSubscriptions(SalesForecast $salesForecast): array
    {
        $ccs = [];
        /** @var SubscriptionRepository $subscriptionRepository */
        $subscriptionRepository = $this->entityManager->getRepository(Subscription::class);
        foreach ($subscriptionRepository->findByResource($salesForecast) as $subscription) {
            if (!$subscription->getUser() instanceof People) {
                continue;
            }
            $ccs[] = $subscription->getUser();
        }

        return $ccs;
    }

    private function getCcSAS(SalesForecast $salesForecast)
    {
        if (
            null === ($product = $salesForecast->getProduct())
            || null === ($country = $salesForecast->getCountry())
            || null === ($network = $salesForecast->getSso()->getNetwork())
            || !\in_array($product->getFamily()->getProductType()->getEnglishName(), [ProductType::PRODUCT_TYPE_ACU, ProductType::PRODUCT_TYPE_FIXED_ACU], true)
        ) {
            return [];
        }

        $ccs = [];

        /** @var NetworkRepository $networkRepository */
        $networkRepository = $this->entityManager->getRepository(Network::class);
        $sasNetwork = $networkRepository->findNetworkByName(Network::NETWORK_SAS);

        $locationRepository = $this->entityManager->getRepository(Location::class);
        $sasLocations = $locationRepository->findBy(['network' => $sasNetwork]);

        if (Network::NETWORK_SAS !== $network->getName()) {
            /** @var SalesAreaRepository $salesAreaRepository */
            $salesAreaRepository = $this->entityManager->getRepository(SalesArea::class);
            $salesAreas = $salesAreaRepository->findByCountryAndNetwork($country, $sasNetwork);

            foreach ($sasLocations as $location) {
                /** @var PeopleRepository $peopleRepository */
                $peopleRepository = $this->entityManager->getRepository(People::class);
                $ceos = $peopleRepository->findGroupsMembers(['ROLE_CEO'], $location);
                foreach ($ceos as $ceo) {
                    $ccs[] = $ceo;
                }
            }

            foreach ($salesAreas as $salesArea) {
                $ccs[] = $salesArea->getAsm();
            }
        }

        return $ccs;
    }

    private function getCcCustomer(SalesForecast $salesForecast): array
    {
        if ($salesForecast->isNotificationRestricted()) {
            return [];
        }

        $ccs = $this->extractContactsFromCustomer($salesForecast->getBuyer());

        if (null !== $salesForecast->getEndUser()) {
            $ccs = [...$ccs, ...$this->extractContactsFromCustomer($salesForecast->getEndUser())];
        }

        return $ccs;
    }

    private function extractContactsFromCustomer(Customer $customer): array
    {
        $i = 1;
        $contacts = [];
        while ($customer && $i <= 10) {
            if (null !== $mainSalesRepresentative = $customer->getMainSalesRepresentative()) {
                $asm = $mainSalesRepresentative->asm;
                $contacts[] = $asm;

                if (null !== $asm->getSupervisor()) {
                    $contacts[] = $asm->getSupervisor();
                }

                /** @var PeopleRepository $peopleRepository */
                $peopleRepository = $this->entityManager->getRepository(People::class);
                foreach ($asm->getAcls() as $acl) {
                    if ('ROLE_ASM' === $acl->getGroup()->getName() && null !== $acl->getLocation()) {
                        $contacts = [...$contacts, ...$peopleRepository->findGroupMembers('ROLE_EVP', $acl->getLocation())];
                    }
                }

                if ($customer->getCustomerTypes()->filter(static fn (CustomerType $customerType) => CustomerType::MILITARY_TYPE_NAME === $customerType->getName())->count() > 0) {
                    $contacts = [...$contacts, ...$peopleRepository->findGroupMembers('ROLE_VPM')];
                }
            }

            foreach ($customer->getSecondarySalesRepresentatives() as $secondarySalesRepresentative) {
                $contacts[] = $secondaryAsm = $secondarySalesRepresentative->asm;
                if (null !== $secondaryAsm->getSupervisor()) {
                    $contacts[] = $secondaryAsm->getSupervisor();
                }
            }

            $customer = $customer->getParentCustomer();
            if (null === $customer) {
                ++$i;
                continue;
            }

            try {
                // this is here to prevent a soft deleted customer to be loaded here
                // and throw an EntityNotFoundException in the next iteration of the while loop
                $this->entityManager->initializeObject($customer);
            } catch (EntityNotFoundException $exception) {
                $customer = null;
            }

            ++$i;
        }

        return $contacts;
    }
}
