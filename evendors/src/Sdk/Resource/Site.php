<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use Psl\Type;

/**
 * @phpstan-type SiteStructure array{"@id": non-empty-string, "@type": non-empty-string, siteID: string, siteDescription: string, siteAddressCode: string, siteAddressName: string}
 *
 * @psalm-type SiteStructure = array{"@id": non-empty-string, "@type": non-empty-string, siteID: string, siteDescription: string, siteAddressCode: string, siteAddressName: string}
 *
 * @phpstan-type SimplifiedSiteStructure array{"@id": non-empty-string, "@type": non-empty-string, siteID: string, siteDescription: string}
 *
 * @psalm-type SimplifiedSiteStructure = array{"@id": non-empty-string, "@type": non-empty-string, siteID: string, siteDescription: string}
 */
final class Site implements ResourceInterface
{
    use Traits\CompleteTypeStructureTrait;

    public function __construct(
        public readonly string $iri,
        public readonly string $siteID,
        public readonly string $siteDescription,
        public readonly ?string $siteAddressCode,
        public readonly ?string $siteAddressName,
    ) {
    }

    public function getIri(): string
    {
        return $this->iri;
    }

    /**
     * @return Type\TypeInterface<SiteStructure>
     */
    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            '@type' => Type\literal_scalar('Site'),
            'siteID' => Type\string(),
            'siteDescription' => Type\string(),
            'siteAddressCode' => Type\string(),
            'siteAddressName' => Type\string(),
        ], allow_unknown_fields: true);
    }

    /**
     * @return Type\TypeInterface<SimplifiedSiteStructure>
     */
    public static function getSimplifiedTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            '@type' => Type\literal_scalar('Site'),
            'siteID' => Type\string(),
            'siteDescription' => Type\string(),
        ], allow_unknown_fields: true);
    }
}
