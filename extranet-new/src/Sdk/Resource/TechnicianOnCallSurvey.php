<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use Psl\Type;

class TechnicianOnCallSurvey implements ResourceInterface
{
    public function __construct(
        public readonly string $iri,
        public readonly int $id,
        public readonly int $execution,
        public readonly int $responsiveness,
        public readonly int $communication,
        public readonly int $attitude,
        public readonly string $comment,
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
            'execution' => Type\int(),
            'responsiveness' => Type\int(),
            'communication' => Type\int(),
            'attitude' => Type\int(),
            'comment' => Type\non_empty_string(),
        ], allowUnknownFields: true);
    }
}
