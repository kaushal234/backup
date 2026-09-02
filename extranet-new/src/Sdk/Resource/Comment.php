<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use App\Sdk\Resource\Traits\CompleteTypeStructureTrait;
use Psl\Type;

final class Comment implements ResourceInterface
{
    use CompleteTypeStructureTrait;

    public function __construct(
        public readonly string $iri,
        public readonly int $id,
        public readonly string $message,
        public readonly \DateTime $createdAt,
        public readonly ?People $user = null,
        public readonly ?File $file = null
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
            'message' => Type\non_empty_string(),
            'user' => Type\nullable(People::getTypeStructure()),
            'createdAt' => Type\non_empty_string(),
            'files' => Type\optional(Type\vec(File::getTypeStructure())),
        ], allowUnknownFields: true);
    }
}
