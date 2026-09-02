<?php

declare(strict_types=1);

namespace App\Notifier\Purchasing\VendorWarrantyClaim;

use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use App\Client\Exception\SoapException;
use App\Entity\Common\Subscription;
use App\Entity\Directory\People;
use App\Entity\Purchasing\VendorWarrantyClaim;
use App\Entity\Purchasing\VendorWarrantyClaimStatus;
use App\ION\DataProvider\CachedIONItemDataProvider;
use App\ION\Manager\MasterData\BusinessPartners\BusinessPartnerManager;
use App\ION\Resources\MasterData\BusinessPartners\BusinessPartner;
use App\Manager\Purchasing\VendorWarrantyClaim\VendorWarrantyClaimManager;
use App\Repository\Common\SubscriptionRepository;
use App\Repository\Directory\PeopleRepository;
use Doctrine\ORM\EntityManagerInterface;

class RecipientsFinder
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly BusinessPartnerManager $businessPartnerManager,
        private readonly VendorWarrantyClaimManager $vendorWarrantyClaimManager,
        private readonly CachedIONItemDataProvider $itemDataProvider,
        private readonly ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory,
    ) {
    }

    public function findCreationTos(VendorWarrantyClaim $vendorWarrantyClaim): array
    {
        /** @var PeopleRepository $peopleRepository */
        $peopleRepository = $this->entityManager->getRepository(People::class);
        $recipients = null !== ($assignee = $vendorWarrantyClaim->assignee) ? [$assignee] : [];

        return [
            ...$recipients,
            ...$peopleRepository->findGroupsMembers(['ROLE_VWCM', 'ROLE_QA', 'ROLE_MLM'], $vendorWarrantyClaim->location),
        ];
    }

    public function findCommentRecipients(VendorWarrantyClaim $vendorWarrantyClaim): array
    {
        /** @var SubscriptionRepository $subscriptionRepository */
        $subscriptionRepository = $this->entityManager->getRepository(Subscription::class);
        $recipients = null !== ($assignee = $vendorWarrantyClaim->assignee) ? [$assignee->getEmail()] : [];

        try {
            $metadata = $this->resourceMetadataCollectionFactory->create(BusinessPartner::class);
            /** @var BusinessPartner|null $businessPartner */
            $businessPartner = $this->itemDataProvider->provide($metadata->getOperation(), ['code' => $vendorWarrantyClaim->getBusinessPartnerCode()]);
        } catch (SoapException $e) {
            $businessPartner = null;
        }

        if (null !== $businessPartner?->buyer?->emailAddress) {
            $recipients[] = $businessPartner->buyer->emailAddress;
        }

        return [
            ...$recipients,
            ...array_reduce($subscriptionRepository->findByResource($vendorWarrantyClaim), static function ($memo, Subscription $subscription) {
                $memo[] = $subscription->getUser()->getEmail();

                return $memo;
            }, []),
        ];
    }

    public function findInternalNoteCcs(VendorWarrantyClaim $vendorWarrantyClaim): array
    {
        /** @var PeopleRepository $peopleRepository */
        $peopleRepository = $this->entityManager->getRepository(People::class);

        return [
            ...array_reduce($peopleRepository->findGroupMembers('ROLE_QAM', $vendorWarrantyClaim->location), static function ($memo, People $people) {
                $memo[] = $people->getEmail();

                return $memo;
            }, []),
            $vendorWarrantyClaim->poster->getEmail(),
        ];
    }

    public function findCommentCcs(VendorWarrantyClaim $vendorWarrantyClaim): array
    {
        /** @var PeopleRepository $peopleRepository */
        $peopleRepository = $this->entityManager->getRepository(People::class);

        return [
            $vendorWarrantyClaim->poster->getEmail(),
            ...array_reduce($peopleRepository->findGroupMembers('ROLE_VWCM', $vendorWarrantyClaim->location), static function ($memo, People $people) {
                $memo[] = $people->getEmail();

                return $memo;
            }, []),
        ];
    }

    public function findStatusCcs(VendorWarrantyClaim $vendorWarrantyClaim): array
    {
        /** @var SubscriptionRepository $subscriptionRepository */
        $subscriptionRepository = $this->entityManager->getRepository(Subscription::class);

        return [
            ...array_reduce($subscriptionRepository->findByResource($vendorWarrantyClaim), static function ($memo, Subscription $subscription) {
                $memo[] = $subscription->getUser()->getEmail();

                return $memo;
            }, []),
        ];
    }

    public function findStatusRecipients(VendorWarrantyClaim $vendorWarrantyClaim): array
    {
        /** @var PeopleRepository $peopleRepository */
        $peopleRepository = $this->entityManager->getRepository(People::class);
        $recipients = [
            ...array_reduce($peopleRepository->findGroupMembers('ROLE_VWCM', $vendorWarrantyClaim->location), static function ($memo, People $people) {
                $memo[] = $people->getEmail();

                return $memo;
            }, []),
            $vendorWarrantyClaim->poster->getEmail(),
        ];

        if (VendorWarrantyClaimStatus::REVIEW_VENDOR_RESPONSE === $vendorWarrantyClaim->status->name) {
            $recipients = [
                ...$recipients,
                ...array_reduce($peopleRepository->findGroupMembers('SEQ_VWC.PUR', $vendorWarrantyClaim->location), static function ($memo, People $people) {
                    $memo[] = $people->getEmail();

                    return $memo;
                }, []),
            ];
        }

        if (null !== $vendorWarrantyClaim->assignee) {
            $recipients = [...$recipients, $vendorWarrantyClaim->assignee->getEmail()];
        }

        if (null !== $vendorWarrantyClaim->getSupplierNumber()) {
            $supplier = $this->businessPartnerManager->findSupplier($vendorWarrantyClaim->getSupplierNumber());
            if (!$supplier instanceof BusinessPartner) {
                return $recipients;
            }

            if (null !== $supplier->buyer) {
                $recipients = [...$recipients, $supplier->buyer->emailAddress];
            }
        }

        return $recipients;
    }

    public function findVendorRecipients(VendorWarrantyClaim $vendorWarrantyClaim): array
    {
        return array_column($this->vendorWarrantyClaimManager->findQualityContacts($vendorWarrantyClaim)->toArray(), 'emailAddress');
    }
}
