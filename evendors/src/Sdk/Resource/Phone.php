<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use Psl\Type;

/**
 * @phpstan-type PhoneStructure array{"@id": non-empty-string, "@type": non-empty-string, type: non-empty-string, number: non-empty-string}
 *
 * @psalm-type PhoneStructure = array{"@id": non-empty-string, "@type": non-empty-string, type: non-empty-string, number: non-empty-string}
 *
 * @uses CompleteTypeStructureTrait<PhoneStructure>
 */
final class Phone implements ResourceInterface
{
    use Traits\CompleteTypeStructureTrait;

    public function __construct(
        public readonly string $iri,
        public readonly string $type,
        public readonly string $number,
    ) {
    }

    public function getIri(): string
    {
        return $this->iri;
    }

    /**
     * @return Type\TypeInterface<PhoneStructure>
     */
    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            '@type' => Type\literal_scalar('Phone'),
            'type' => Type\non_empty_string(),
            'number' => Type\non_empty_string(),
        ], allow_unknown_fields: true);
    }
}
