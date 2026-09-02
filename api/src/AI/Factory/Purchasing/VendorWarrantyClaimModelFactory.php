<?php

declare(strict_types=1);

namespace App\AI\Factory\Purchasing;

use App\AI\Dto\Finance\CurrencyModel;
use App\AI\Dto\Purchasing\VendorWarrantyClaim\NCRVendorWarrantyClaimModel;
use App\AI\Dto\Purchasing\VendorWarrantyClaim\VendorWarrantyClaimModel;
use App\AI\Dto\Purchasing\VendorWarrantyClaim\VendorWarrantyClaimPartModel;
use App\AI\Dto\Purchasing\VendorWarrantyClaim\VendorWarrantyClaimStatusModel;
use App\AI\Dto\Purchasing\VendorWarrantyClaim\VendorWarrantyClaimTypeModel;
use App\AI\Dto\Purchasing\VendorWarrantyClaim\WCVendorWarrantyClaimModel;
use App\AI\Factory\Directory\LocationModelFactory;
use App\AI\Factory\Directory\UserModelFactory;
use App\AI\Factory\ModelFactoryInterface;
use App\AI\Factory\Quality\NonConformityModelFactory;
use App\AI\Factory\Quality\SupplierCorrectiveActionRequestModelFactory;
use App\Entity\Finance\Currency;
use App\Entity\Parts\VendorWarrantyClaimPart;
use App\Entity\Purchasing\NCRVendorWarrantyClaim;
use App\Entity\Purchasing\VendorWarrantyClaim;
use App\Entity\Purchasing\VendorWarrantyClaimStatus;
use App\Entity\Purchasing\VendorWarrantyClaimType;
use App\Entity\Purchasing\WCVendorWarrantyClaim;

final readonly class VendorWarrantyClaimModelFactory implements ModelFactoryInterface
{
    public function __construct(
        private UserModelFactory $peopleModelFactory,
        private LocationModelFactory $locationModelFactory,
        private SupplierCorrectiveActionRequestModelFactory $supplierCorrectiveActionRequestModelFactory,
        private NonConformityModelFactory $nonConformityModelFactory,
    ) {
    }

    public function supports(string $class): bool
    {
        return VendorWarrantyClaim::class === $class;
    }

    /**
     * @param VendorWarrantyClaim $entity
     */
    public function create(object $entity): VendorWarrantyClaimModel
    {
        $type = $this->createType($entity->type);
        $status = $this->createStatus($entity->status);
        $location = $this->locationModelFactory->create($entity->location);
        $poster = $this->peopleModelFactory->create($entity->poster);
        $assignee = null === $entity->assignee ? null : $this->peopleModelFactory->create($entity->assignee);
        $currency = null === $entity->currency ? null : $this->createCurrency($entity->currency);
        $supplierCorrectiveActionRequest = null === $entity->supplierCorrectiveActionRequest
            ? null
            : $this->supplierCorrectiveActionRequestModelFactory->create($entity->supplierCorrectiveActionRequest);
        $parts = array_values(array_map(
            static fn (VendorWarrantyClaimPart $part) => new VendorWarrantyClaimPartModel(
                partNumber: $part->partNumber,
                description: $part->description,
                quantity: $part->quantity,
                unitOfMeasure: $part->unitOfMeasure,
                standardCost: $part->standardCost,
                serialNumber: $part->serialNumber,
                vendorPartNumber: $part->vendorPartNumber,
                vendorSerialNumber: $part->vendorSerialNumber,
                failureType: $part->failureType,
                failureSystem: $part->failureSystem,
                ship: $part->ship,
                receivedQuantity: $part->receivedQuantity,
            ),
            $entity->getParts()->toArray(),
        ));

        if ($entity instanceof NCRVendorWarrantyClaim) {
            return new NCRVendorWarrantyClaimModel(
                type: $type,
                status: $status,
                location: $location,
                poster: $poster,
                assignee: $assignee,
                currency: $currency,
                supplierCorrectiveActionRequest: $supplierCorrectiveActionRequest,
                scarRequested: $entity->scarRequested,
                createdAt: $entity->createdAt,
                closedAt: $entity->closedAt,
                statusUpdatedAt: $entity->statusUpdatedAt,
                vendorToRespondAt: $entity->vendorToRespondAt,
                requestedSupplierAction: $entity->requestedSupplierAction,
                requestedCreditAmount: $entity->requestedCreditAmount,
                supplierReturnMerchandiseAuthorization: $entity->supplierReturnMerchandiseAuthorization,
                supplierCreditNote: $entity->supplierCreditNote,
                supplierCreditAmount: $entity->supplierCreditAmount,
                actualCreditAmount: $entity->actualCreditAmount,
                supplierShippingInstruction: $entity->supplierShippingInstruction,
                supplierStockVerified: $entity->supplierStockVerified,
                tldStockVerified: $entity->tldStockVerified,
                issueOrigin: $entity->issueOrigin,
                correctiveAction: $entity->correctiveAction,
                supplierShipperName: $entity->supplierShipperName,
                accepted: $entity->accepted,
                costBreakdown: $entity->costBreakdown,
                shipBackDefectivePart: $entity->shipBackDefectivePart,
                resolution: $entity->resolution,
                trackingNumber: $entity->trackingNumber,
                supplierName: $entity->getSupplierName(),
                supplierNumber: $entity->getSupplierNumber(),
                parts: $parts,
                nonConformity: $this->nonConformityModelFactory->create($entity->nonConformity),
            );
        }

        if ($entity instanceof WCVendorWarrantyClaim) {
            return new WCVendorWarrantyClaimModel(
                type: $type,
                status: $status,
                location: $location,
                poster: $poster,
                assignee: $assignee,
                currency: $currency,
                supplierCorrectiveActionRequest: $supplierCorrectiveActionRequest,
                scarRequested: $entity->scarRequested,
                createdAt: $entity->createdAt,
                closedAt: $entity->closedAt,
                statusUpdatedAt: $entity->statusUpdatedAt,
                vendorToRespondAt: $entity->vendorToRespondAt,
                requestedSupplierAction: $entity->requestedSupplierAction,
                requestedCreditAmount: $entity->requestedCreditAmount,
                supplierReturnMerchandiseAuthorization: $entity->supplierReturnMerchandiseAuthorization,
                supplierCreditNote: $entity->supplierCreditNote,
                supplierCreditAmount: $entity->supplierCreditAmount,
                actualCreditAmount: $entity->actualCreditAmount,
                supplierShippingInstruction: $entity->supplierShippingInstruction,
                supplierStockVerified: $entity->supplierStockVerified,
                tldStockVerified: $entity->tldStockVerified,
                issueOrigin: $entity->issueOrigin,
                correctiveAction: $entity->correctiveAction,
                supplierShipperName: $entity->supplierShipperName,
                accepted: $entity->accepted,
                costBreakdown: $entity->costBreakdown,
                shipBackDefectivePart: $entity->shipBackDefectivePart,
                resolution: $entity->resolution,
                trackingNumber: $entity->trackingNumber,
                supplierName: $entity->getSupplierName(),
                supplierNumber: $entity->getSupplierNumber(),
                parts: $parts,
                warrantyClaimId: $entity->warrantyClaimId,
            );
        }

        throw new \LogicException(\sprintf('Unsupported VendorWarrantyClaim subclass "%s".', $entity::class));
    }

    private function createType(VendorWarrantyClaimType $type): VendorWarrantyClaimTypeModel
    {
        return new VendorWarrantyClaimTypeModel(
            name: $type->name,
            description: $type->description,
        );
    }

    private function createStatus(VendorWarrantyClaimStatus $status): VendorWarrantyClaimStatusModel
    {
        return new VendorWarrantyClaimStatusModel(
            name: $status->name,
            description: $status->description,
            position: $status->position,
        );
    }

    private function createCurrency(Currency $currency): CurrencyModel
    {
        return new CurrencyModel(
            name: $currency->getName(),
        );
    }
}
