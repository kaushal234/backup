<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use Psl\Type;

/**
 * @phpstan-type NonConformityStructure array{"@id": non-empty-string, "@type": non-empty-string, problem: string, id: int }
 *
 * @psalm-type NonConformityStructure = array{"@id": non-empty-string, "@type": non-empty-string, problem: string, id: int }
 */
final class NonConformity implements ResourceInterface
{
    /**
     * @param list<NonConformityFile> $files
     * @param list<Process>           $processes
     * @param list<Responsible>       $responsibles
     * @param list<NonConformityPart> $parts
     */
    public function __construct(
        public readonly string $iri,
        public readonly int $id,
        public readonly ?string $problem = null,
        public readonly ?Location $location = null,
        public readonly array $processes = [],
        public readonly ?string $createdAt = null,
        public readonly ?string $status = null,
        public readonly ?string $shortDescription = null,
        public readonly ?Person $reportedBy = null,
        public readonly ?int $hours = null,
        public readonly ?string $supplier = null,
        public readonly ?string $solution = null,
        public readonly array $responsibles = [],
        public readonly ?string $purchaseOrderNumber = null,
        public readonly ?bool $rush = null,
        public readonly ?bool $chargeVendor = null,
        public readonly ?string $failureType = null,
        public readonly ?string $iFactor = null,
        public readonly ?string $investigation = null,
        public readonly ?bool $scrap = null,
        public readonly ?bool $rework = null,
        public readonly ?bool $firstArticleInspection = null,
        public readonly ?bool $useAsIs = null,
        public readonly ?bool $derogation = null,
        public readonly ?bool $returnVendor = null,
        public readonly ?bool $chargeVendorForRepair = null,
        public readonly ?bool $supplierCorrectiveActionRequest = null,
        public readonly ?bool $internalCorrectiveActionRequest = null,
        public readonly ?bool $other = null,
        public readonly ?bool $containment = null,
        public readonly ?bool $environmentalIssue = null,
        public readonly ?string $actionComment = null,
        public readonly string|Currency|null $currency = null,
        public readonly int|float|null $cost = null,
        public readonly ?string $costBreakdown = null,
        public readonly int|float|null $nonQualityCost = null,
        public readonly ?string $supplierName = null,
        public readonly ?string $supplierNumber = null,
        public readonly ?int $supplierErp = null,
        public readonly ?string $invoiceNumber = null,
        public readonly ?NonConformityMainFile $mainFile = null,
        public readonly array $files = [],
        public readonly array $parts = [],
    ) {
    }

    public function getIri(): string
    {
        return $this->iri;
    }

    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            '@type' => Type\literal_scalar('NonConformity'),
            'id' => Type\int(),
            'location' => Type\optional(Location::getTypeStructure()),
            'processes' => Type\optional(Type\vec(Process::getTypeStructure())),
            'createdAt' => Type\optional(Type\string()),
            'status' => Type\optional(Type\string()),
            'reportedBy' => Type\optional(Type\nullable(Person::getTypeStructure())),
            'hours' => Type\optional(Type\nullable(Type\int())),
            'supplier' => Type\optional(Type\string()),
            'problem' => Type\optional(Type\nullable(Type\string())),
            'shortDescription' => Type\optional(Type\string()),
            'solution' => Type\optional(Type\nullable(Type\string())),
            'responsibles' => Type\optional(Type\vec(Responsible::getTypeStructure())),
            'purchaseOrderNumber' => Type\optional(Type\nullable(Type\string())),
            'rush' => Type\optional(Type\bool()),
            'chargeVendor' => Type\optional(Type\bool()),
            'failureType' => Type\optional(Type\nullable(Type\string())),
            'iFactor' => Type\optional(Type\string()),
            'investigation' => Type\optional(Type\nullable(Type\string())),
            'scrap' => Type\optional(Type\bool()),
            'rework' => Type\optional(Type\bool()),
            'firstArticleInspection' => Type\optional(Type\bool()),
            'useAsIs' => Type\optional(Type\bool()),
            'derogation' => Type\optional(Type\bool()),
            'returnVendor' => Type\optional(Type\bool()),
            'chargeVendorForRepair' => Type\optional(Type\bool()),
            'supplierCorrectiveActionRequest' => Type\optional(Type\bool()),
            'internalCorrectiveActionRequest' => Type\optional(Type\bool()),
            'other' => Type\optional(Type\bool()),
            'containment' => Type\optional(Type\bool()),
            'environmentalIssue' => Type\optional(Type\bool()),
            'actionComment' => Type\optional(Type\nullable(Type\string())),
            'currency' => Type\optional(Type\nullable(Type\union(Type\string(), Currency::getTypeStructure()))),
            'cost' => Type\optional(Type\nullable(Type\union(Type\int(), Type\float()))),
            'costBreakdown' => Type\optional(Type\nullable(Type\string())),
            'nonQualityCost' => Type\optional(Type\nullable(Type\union(Type\int(), Type\float()))),
            'supplierName' => Type\optional(Type\nullable(Type\string())),
            'supplierNumber' => Type\optional(Type\nullable(Type\string())),
            'supplierErp' => Type\optional(Type\nullable(Type\int())),
            'invoiceNumber' => Type\optional(Type\nullable(Type\string())),
            'mainFile' => Type\optional(Type\nullable(NonConformityMainFile::getTypeStructure())),
            'files' => Type\optional(Type\vec(NonConformityFile::getTypeStructure())),
            'parts' => Type\optional(Type\vec(NonConformityPart::getTypeStructure())),
        ], allow_unknown_fields: true);
    }
}
