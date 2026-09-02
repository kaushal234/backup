<?php

declare(strict_types=1);

namespace App\AI\Dto\Purchasing\VendorWarrantyClaim;

use App\AI\Dto\Directory\LocationModel;
use App\AI\Dto\Directory\PeopleModel;
use App\AI\Dto\Finance\CurrencyModel;
use App\AI\Dto\Quality\SupplierCorrectiveActionRequestModel;

final readonly class WCVendorWarrantyClaimModel extends VendorWarrantyClaimModel
{
    /**
     * @param list<VendorWarrantyClaimPartModel> $parts
     */
    public function __construct(
        VendorWarrantyClaimTypeModel $type,
        VendorWarrantyClaimStatusModel $status,
        LocationModel $location,
        PeopleModel $poster,
        ?PeopleModel $assignee,
        ?CurrencyModel $currency,
        ?SupplierCorrectiveActionRequestModel $supplierCorrectiveActionRequest,
        bool $scarRequested,
        \DateTimeInterface $createdAt,
        ?\DateTimeInterface $closedAt,
        ?\DateTimeInterface $statusUpdatedAt,
        ?\DateTimeInterface $vendorToRespondAt,
        string $requestedSupplierAction,
        ?float $requestedCreditAmount,
        ?string $supplierReturnMerchandiseAuthorization,
        ?string $supplierCreditNote,
        ?float $supplierCreditAmount,
        ?float $actualCreditAmount,
        ?string $supplierShippingInstruction,
        ?string $supplierStockVerified,
        ?string $tldStockVerified,
        ?string $issueOrigin,
        ?string $correctiveAction,
        ?string $supplierShipperName,
        bool $accepted,
        ?string $costBreakdown,
        bool $shipBackDefectivePart,
        ?string $resolution,
        ?string $trackingNumber,
        ?string $supplierName,
        ?string $supplierNumber,
        array $parts,
        public int $warrantyClaimId,
    ) {
        parent::__construct(
            type: $type,
            status: $status,
            location: $location,
            poster: $poster,
            assignee: $assignee,
            currency: $currency,
            supplierCorrectiveActionRequest: $supplierCorrectiveActionRequest,
            scarRequested: $scarRequested,
            createdAt: $createdAt,
            closedAt: $closedAt,
            statusUpdatedAt: $statusUpdatedAt,
            vendorToRespondAt: $vendorToRespondAt,
            requestedSupplierAction: $requestedSupplierAction,
            requestedCreditAmount: $requestedCreditAmount,
            supplierReturnMerchandiseAuthorization: $supplierReturnMerchandiseAuthorization,
            supplierCreditNote: $supplierCreditNote,
            supplierCreditAmount: $supplierCreditAmount,
            actualCreditAmount: $actualCreditAmount,
            supplierShippingInstruction: $supplierShippingInstruction,
            supplierStockVerified: $supplierStockVerified,
            tldStockVerified: $tldStockVerified,
            issueOrigin: $issueOrigin,
            correctiveAction: $correctiveAction,
            supplierShipperName: $supplierShipperName,
            accepted: $accepted,
            costBreakdown: $costBreakdown,
            shipBackDefectivePart: $shipBackDefectivePart,
            resolution: $resolution,
            trackingNumber: $trackingNumber,
            supplierName: $supplierName,
            supplierNumber: $supplierNumber,
            parts: $parts,
        );
    }
}
