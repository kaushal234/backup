<?php

declare(strict_types=1);

namespace App\AI\Dto\Quality\NonConformity;

use App\AI\Dto\Directory\LocationModel;
use App\AI\Dto\Directory\PeopleModel;
use App\AI\Dto\Finance\CurrencyModel;
use App\AI\Dto\Parts\NonConformityPartModel;
use App\AI\Dto\Sales\ProductModel;
use App\AI\Dto\Support\EquipmentRecordModel;

final readonly class NonConformityModel
{
    /**
     * @param list<int>                    $crabs
     * @param list<ProcessModel>           $processes
     * @param list<ResponsibleModel>       $responsibles
     * @param list<NonConformityPartModel> $parts
     * @param list<ProductModel>           $products
     * @param list<EquipmentRecordModel>   $equipmentRecords
     */
    public function __construct(
        public string $status,
        public \DateTimeInterface $createdAt,
        public ?int $hours,
        public string $problem,
        public string $shortDescription,
        public ?string $solution,
        public ?string $purchaseOrderNumber,
        public bool $rush,
        public bool $chargeVendor,
        public ?string $failureType,
        public string $iFactor,
        public ?string $investigation,
        public bool $scrap,
        public bool $rework,
        public bool $firstArticleInspection,
        public bool $useAsIs,
        public bool $derogation,
        public bool $returnVendor,
        public bool $chargeVendorForRepair,
        public bool $supplierCorrectiveActionRequest,
        public bool $internalCorrectiveActionRequest,
        public bool $other,
        public bool $containment,
        public ?string $actionComment,
        public ?\DateTimeInterface $repairApprovalDate,
        public ?string $costBreakdown,
        public ?float $cost,
        public ?string $workOrderReference,
        public ?float $nonQualityCost,
        public ?string $invoiceNumber,
        public bool $environmentalIssue,
        public bool $safety,
        public ?string $supplierName,
        public ?string $supplierNumber,
        public LocationModel $location,
        public ?PeopleModel $reportedBy,
        public ?PeopleModel $repairApprover,
        public ?CurrencyModel $currency,
        public array $crabs,
        public array $processes,
        public array $responsibles,
        public array $parts,
        public array $products,
        public array $equipmentRecords,
    ) {
    }
}
