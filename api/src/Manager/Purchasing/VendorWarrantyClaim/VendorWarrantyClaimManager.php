<?php

declare(strict_types=1);

namespace App\Manager\Purchasing\VendorWarrantyClaim;

use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use App\Client\Exception\SoapException;
use App\Entity\Directory\People;
use App\Entity\Purchasing\VendorWarrantyClaim;
use App\Entity\Purchasing\VendorWarrantyClaimStatus;
use App\ION\DataProvider\CachedIONItemDataProvider;
use App\ION\Manager\MasterData\BusinessPartners\BusinessPartnerManager;
use App\ION\Resources\MasterData\BusinessPartners\BusinessPartner;
use App\ION\Resources\MasterData\BusinessPartners\BusinessPartnerContact;
use App\ION\Resources\MasterData\BusinessPartners\BusinessPartnerContactCategory;
use App\ION\Resources\MasterData\Items\Item;
use App\Repository\Directory\PeopleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;

class VendorWarrantyClaimManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CachedIONItemDataProvider $itemProvider,
        private readonly BusinessPartnerManager $businessPartnerManager,
        private readonly ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory
    ) {
    }

    public function getAssigneeFromParts(VendorWarrantyClaim $vendorWarrantyClaim): ?People
    {
        $assignee = null;
        /** @var PeopleRepository $peopleRepository */
        $peopleRepository = $this->entityManager->getRepository(People::class);
        foreach ($vendorWarrantyClaim->getParts() as $part) {
            if (null !== $assignee) {
                break;
            }
            try {
                $operation = $this->resourceMetadataCollectionFactory->create(Item::class)->getOperation();
                /** @var Item $item */
                $item = $this->itemProvider->provide($operation, ['item' => $part->partNumber, 'site' => $vendorWarrantyClaim->location->getErp()]);
                if (null !== ($buyerEmail = $item->buyer->emailAddress)) {
                    $assignee = $peopleRepository->findOneBy(['email' => $buyerEmail]);
                }
            } catch (SoapException $exception) {
                // do nothing
            }
        }

        return $assignee;
    }

    public function getAssigneeOnStatusChange(VendorWarrantyClaim $vendorWarrantyClaim): ?People
    {
        $assignee = null;
        /** @var PeopleRepository $peopleRepository */
        $peopleRepository = $this->entityManager->getRepository(People::class);
        if (\in_array($vendorWarrantyClaim->status->name, [VendorWarrantyClaimStatus::VENDOR_TO_RESPOND, VendorWarrantyClaimStatus::REVIEW_VENDOR_RESPONSE], true) && null !== $vendorWarrantyClaim->getSupplierNumber()) {
            $assignee = $this->getAssigneeFromParts($vendorWarrantyClaim);
            if (!$assignee instanceof People) {
                /** @var BusinessPartner|null $supplier */
                $supplier = $this->businessPartnerManager->findSupplier($vendorWarrantyClaim->getSupplierNumber());

                if (null !== $supplier && null !== $supplier->buyer) {
                    $assignee = $peopleRepository->findOneBy(['email' => $supplier->buyer->emailAddress]);
                }
            }
        }

        if (null === $assignee) {
            $assignee = $peopleRepository->findGroupMembers($vendorWarrantyClaim->status->group->getName(), $vendorWarrantyClaim->location)[0] ?? null;
        }

        return $assignee;
    }

    public function findQualityContacts(VendorWarrantyClaim $vendorWarrantyClaim): Collection
    {
        if (null === $vendorWarrantyClaim->getSupplierNumber()) {
            return new ArrayCollection();
        }

        $supplier = $this->businessPartnerManager->findSupplier($vendorWarrantyClaim->getSupplierNumber());

        if (!$supplier instanceof BusinessPartner) {
            return new ArrayCollection();
        }

        return $supplier->getContacts()->filter(static fn (BusinessPartnerContact $businessPartnerContact) => $businessPartnerContact->isGrantedCategory(BusinessPartnerContactCategory::QUALITY_CATEGORY_NAME));
    }
}
