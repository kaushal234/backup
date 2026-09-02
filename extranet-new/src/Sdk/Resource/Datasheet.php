<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use App\Sdk\Resource\Traits\CompleteTypeStructureTrait;
use Psl\Type;

final class Datasheet implements ResourceInterface
{
    use CompleteTypeStructureTrait;

    public function __construct(
        public readonly string $iri,
        public readonly int $id,
        public readonly Document $document,
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
            'id' => Type\int(),
            'dms' => Document::getTypeStructure(),
        ], allowUnknownFields: true);
    }
}
