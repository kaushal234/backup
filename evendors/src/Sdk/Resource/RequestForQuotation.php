<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use App\Sdk\Resource\Traits\CompleteTypeStructureTrait;
use Psl\Type;

/**
 * @phpstan-import-type RequestForQuotationLineStructure from RequestForQuotationLine
 *
 * @psalm-import-type RequestForQuotationLineStructure from RequestForQuotationLine
 *
 * @phpstan-import-type SimplifiedSiteStructure from Site
 *
 * @psalm-import-type SimplifiedSiteStructure from Site
 *
 * @phpstan-type RequestForQuotationStructure array{"@id": non-empty-string, "@type": non-empty-string, requestForQuotationCode: non-empty-string, site: SimplifiedSiteStructure, buyerEmail: null|non-empty-string, businessPartnerCodes: array<string, mixed>, responseDate: non-empty-string, status: non-empty-string, lines?: list<RequestForQuotationLineStructure>}
 *
 * @psalm-type RequestForQuotationStructure = array{"@id": non-empty-string, "@type": non-empty-string, requestForQuotationCode: non-empty-string, site: SimplifiedSiteStructure, buyerEmail: null|non-empty-string, businessPartnerCodes: array<string, mixed>, responseDate: non-empty-string, status: non-empty-string, ?lines: list<RequestForQuotationLineStructure>}
 *
 * @uses CompleteTypeStructureTrait<RequestForQuotationStructure>
 */
final class RequestForQuotation implements ResourceInterface
{
    use CompleteTypeStructureTrait;

    /**
     * @param non-empty-string              $iri
     * @param positive-int|non-empty-string $id
     * @param positive-int|null             $erp
     * @param non-empty-string|null         $buyerEmail
     * @param non-empty-string              $returnDate
     * @param list<RequestForQuotationLine> $lines
     */
    public function __construct(
        public readonly string $iri,
        public readonly int|string $id,
        public readonly ?int $erp,
        public readonly ?string $buyerEmail,
        public readonly string $supplierNumber,
        public readonly string $returnDate,
        public readonly RequestForQuotationStatus $status,
        public readonly array $lines = [],
    ) {
    }

    public function getIri(): string
    {
        return $this->iri;
    }

    /**
     * @return Type\TypeInterface<RequestForQuotationStructure>
     */
    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            '@type' => Type\literal_scalar('RequestForQuotation'),
            'requestForQuotationCode' => Type\non_empty_string(),
            'buyerEmail' => Type\nullable(Type\string()),
            'responseDate' => Type\string(),
            'status' => RequestForQuotationStatus::getTypeStructure(),
            'lines' => Type\optional(Type\vec(RequestForQuotationLine::getTypeStructure())),
            'businessPartnerCodes' => Type\vec(Type\string()),
            'site' => Site::getSimplifiedTypeStructure(),
        ], allow_unknown_fields: true);
    }
}
