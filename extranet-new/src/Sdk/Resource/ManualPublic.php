<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use Psl\Type;

final class ManualPublic
{
    /**
     * @param ManualDocumentPublic[] $documents
     */
    public function __construct(
        public readonly int $id,
        public readonly string $createdAt,
        public readonly array $documents = [],
    ) {
    }

    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@type' => Type\non_empty_string(),
            '@id' => Type\non_empty_string(),
            'id' => Type\int(),
            'createdAt' => Type\non_empty_string(),
            'documents' => Type\optional(
                Type\vec(ManualDocumentPublic::getTypeStructure())
            ),
        ]);
    }
}
