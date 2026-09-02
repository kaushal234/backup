<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use App\Sdk\Resource\Traits\CompleteTypeStructureTrait;
use Psl\Type;

/**
 * @phpstan-import-type SimplifiedSiteStructure from Site
 *
 * @psalm-import-type SimplifiedSiteStructure from Site
 *
 * @phpstan-type RequestForQuotationLineStructure array{"@type": non-empty-string, "@id": non-empty-string, position: int, sequence: int, item: string, itemDescription: string, date: string, quantity: string|float|int, unitOfMeasure: string, receiptDate: string, itemRevision: string, site: null|SimplifiedSiteStructure}
 *
 * @psalm-type RequestForQuotationLineStructure = array{"@type": non-empty-string, "@id": non-empty-string, position: int, sequence: int, item: string, itemDescription: string, date: string, quantity: string|float|int, unitOfMeasure: string, receiptDate: string, itemRevision: string,site: null|SimplifiedSiteStructure}
 *
 * @uses CompleteTypeStructureTrait<RequestForQuotationLineStructure>
 * @uses LegacyTypeStructureTrait<LegacyRequestForQuotationLineStructure>
 */
final class RequestForQuotationLine implements ResourceInterface
{
    use CompleteTypeStructureTrait;

    /**
     * @param non-empty-string             $iri
     * @param SimplifiedSiteStructure|null $site
     */
    public function __construct(
        public readonly string $iri,
        public readonly int $position,
        public readonly string $partNumber,
        public readonly string $description,
        public readonly string $date,
        public readonly string|float|int $quantity,
        public readonly ?string $unitOfMeasure,
        public readonly string $requestedDeliveryDate,
        public readonly ?string $revision,
        public readonly ?array $site = null,
    ) {
    }

    public function getIri(): string
    {
        return $this->iri;
    }

    /**
     * @return Type\TypeInterface<RequestForQuotationLineStructure>
     */
    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@type' => Type\literal_scalar('RequestForQuotationLine'),
            '@id' => Type\non_empty_string(),
            'position' => Type\int(),
            'sequence' => Type\int(),
            'item' => Type\string(),
            'itemDescription' => Type\string(),
            'date' => Type\string(),
            'quantity' => Type\union(Type\string(), Type\float(), Type\int()),
            'unitOfMeasure' => Type\string(),
            'receiptDate' => Type\string(),
            'itemRevision' => Type\string(),
            'site' => Type\nullable(Site::getSimplifiedTypeStructure()),
        ], allow_unknown_fields: true);
    }
}
