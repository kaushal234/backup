<?php

declare(strict_types=1);

namespace App\AI\Dto\Purchasing\VendorWarrantyClaim;

use App\AI\Dto\Directory\LocationModel;
use App\AI\Dto\Directory\PeopleModel;
use App\AI\Dto\Finance\CurrencyModel;
use App\AI\Dto\Quality\SupplierCorrectiveActionRequestModel;

abstract readonly class VendorWarrantyClaimModel
{
    /**
     * @param list<VendorWarrantyClaimPartModel> $parts
     */
    public function __construct(
        public VendorWarrantyClaimTypeModel $type,
        public VendorWarrantyClaimStatusModel $status,
        public LocationModel $location,
        public PeopleModel $poster,
        public ?PeopleModel $assignee,
        public ?CurrencyModel $currency,
        public ?SupplierCorrectiveActionRequestModel $supplierCorrectiveActionRequest,
        public bool $scarRequested,
        public \DateTimeInterface $createdAt,
        public ?\DateTimeInterface $closedAt,
        public ?\DateTimeInterface $statusUpdatedAt,
        public ?\DateTimeInterface $vendorToRespondAt,
        public string $requestedSupplierAction,
        public ?float $requestedCreditAmount,
        public ?string $supplierReturnMerchandiseAuthorization,
        public ?string $supplierCreditNote,
        public ?float $supplierCreditAmount,
        public ?float $actualCreditAmount,
        public ?string $supplierShippingInstruction,
        public ?string $supplierStockVerified,
        public ?string $tldStockVerified,
        public ?string $issueOrigin,
        public ?string $correctiveAction,
        public ?string $supplierShipperName,
        public bool $accepted,
        public ?string $costBreakdown,
        public bool $shipBackDefectivePart,
        public ?string $resolution,
        public ?string $trackingNumber,
        public ?string $supplierName,
        public ?string $supplierNumber,
        public array $parts,
    ) {
    }
}
