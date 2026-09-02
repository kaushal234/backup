<?php

declare(strict_types=1);

namespace App\AI\Factory\Quality;

use App\AI\Dto\Finance\CurrencyModel;
use App\AI\Dto\Parts\NonConformityPartModel;
use App\AI\Dto\Quality\NonConformity\NonConformityModel;
use App\AI\Dto\Quality\NonConformity\ProcessModel;
use App\AI\Dto\Quality\NonConformity\ResponsibleModel;
use App\AI\Dto\Sales\ProductModel;
use App\AI\Factory\Directory\LocationModelFactory;
use App\AI\Factory\Directory\UserModelFactory;
use App\AI\Factory\ModelFactoryInterface;
use App\AI\Factory\Support\EquipmentRecordModelFactory;
use App\Entity\EquipmentRecord;
use App\Entity\Finance\Currency;
use App\Entity\Parts\NonConformityPart;
use App\Entity\Quality\Crab;
use App\Entity\Quality\NonConformity;
use App\Entity\Quality\Process;
use App\Entity\Quality\Responsible;
use App\Entity\Sales\Product;

final readonly class NonConformityModelFactory implements ModelFactoryInterface
{
    public function __construct(
        private UserModelFactory $peopleModelFactory,
        private LocationModelFactory $locationModelFactory,
        private EquipmentRecordModelFactory $equipmentRecordModelFactory,
    ) {
    }

    public function supports(string $class): bool
    {
        return NonConformity::class === $class;
    }

    /**
     * @param NonConformity $entity
     */
    public function create(object $entity): NonConformityModel
    {
        return new NonConformityModel(
            status: $entity->status,
            createdAt: $entity->createdAt,
            hours: $entity->hours,
            problem: $entity->problem,
            shortDescription: $entity->shortDescription,
            solution: $entity->solution,
            purchaseOrderNumber: $entity->purchaseOrderNumber,
            rush: $entity->rush,
            chargeVendor: $entity->chargeVendor,
            failureType: $entity->failureType,
            iFactor: $entity->iFactor,
            investigation: $entity->investigation,
            scrap: $entity->scrap,
            rework: $entity->rework,
            firstArticleInspection: $entity->firstArticleInspection,
            useAsIs: $entity->useAsIs,
            derogation: $entity->derogation,
            returnVendor: $entity->returnVendor,
            chargeVendorForRepair: $entity->chargeVendorForRepair,
            supplierCorrectiveActionRequest: $entity->supplierCorrectiveActionRequest,
            internalCorrectiveActionRequest: $entity->internalCorrectiveActionRequest,
            other: $entity->other,
            containment: $entity->containment,
            actionComment: $entity->actionComment,
            repairApprovalDate: $entity->repairApprovalDate,
            costBreakdown: $entity->costBreakdown,
            cost: $entity->cost,
            workOrderReference: $entity->workOrderReference,
            nonQualityCost: $entity->nonQualityCost,
            invoiceNumber: $entity->invoiceNumber,
            environmentalIssue: $entity->environmentalIssue,
            safety: $entity->safety,
            supplierName: $entity->getSupplierName(),
            supplierNumber: $entity->getSupplierNumber(),
            location: $this->locationModelFactory->create($entity->location),
            reportedBy: null === $entity->reportedBy ? null : $this->peopleModelFactory->create($entity->reportedBy),
            repairApprover: null === $entity->repairApprover ? null : $this->peopleModelFactory->create($entity->repairApprover),
            currency: null === $entity->currency ? null : $this->createCurrency($entity->currency),
            crabs: array_values(array_map(
                static fn (Crab $crab) => $crab->getId(),
                $entity->getCrabs()->toArray(),
            )),
            processes: array_values(array_map(
                static fn (Process $process) => new ProcessModel(
                    category: $process->category,
                    description: $process->description,
                ),
                $entity->getProcesses()->toArray(),
            )),
            responsibles: array_values(array_map(
                static fn (Responsible $responsible) => new ResponsibleModel(name: $responsible->name),
                $entity->getResponsibles()->toArray(),
            )),
            parts: array_values(array_map(
                static fn (NonConformityPart $part) => new NonConformityPartModel(
                    reference: $part->reference,
                    referenceNumber: $part->referenceNumber,
                    serialNumber: $part->serialNumber,
                    standardCost: $part->standardCost,
                ),
                $entity->getParts()->toArray(),
            )),
            products: array_values(array_map(
                fn (Product $product) => $this->createProduct($product),
                $entity->getProducts()->toArray(),
            )),
            equipmentRecords: array_values(array_map(
                fn (EquipmentRecord $equipmentRecord) => $this->equipmentRecordModelFactory->create($equipmentRecord),
                $entity->getEquipmentRecords()->toArray(),
            )),
        );
    }

    private function createCurrency(Currency $currency): CurrencyModel
    {
        return new CurrencyModel(
            name: $currency->getName(),
        );
    }

    private function createProduct(Product $product): ProductModel
    {
        return new ProductModel(
            name: $product->getName(),
            family: $product->getFamily()->getName(),
        );
    }
}
