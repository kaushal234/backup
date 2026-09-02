<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use App\Sdk\Resource\Traits\CompleteTypeStructureTrait;
use Psl\Type;

/**
 * @phpstan-import-type PersonStructure from Person
 *
 * @psalm-import-type PersonStructure from Person
 *
 * @phpstan-type DocumentStructure array{"@id": non-empty-string, "@type": non-empty-string, id: positive-int, legacyId: positive-int, title: non-empty-string, subject: non-empty-string, description: non-empty-string, type: null|non-empty-string, language: non-empty-string, portal: string, owner: PersonStructure}
 *
 * @psalm-type DocumentStructure = array{"@id": non-empty-string, "@type": non-empty-string, id: positive-int, legacyId: positive-int, title: non-empty-string, subject: non-empty-string, description: non-empty-string, type: null|non-empty-string, language: non-empty-string, portal: string, owner: PersonStructure}
 */
final class Document implements ResourceInterface
{
    use CompleteTypeStructureTrait;

    /**
     * @param non-empty-string      $iri
     * @param positive-int          $id
     * @param positive-int          $legacyId
     * @param non-empty-string      $title
     * @param non-empty-string      $subject
     * @param non-empty-string|null $type
     * @param non-empty-string      $description
     * @param non-empty-string      $language
     */
    public function __construct(
        public readonly string $iri,
        public readonly int $id,
        public readonly int $legacyId,
        public readonly string $title,
        public readonly string $subject,
        public readonly string $description,
        public readonly ?string $type,
        public readonly string $language,
        public readonly string $portal,
        public readonly Person $owner,
    ) {
    }

    public function getIri(): string
    {
        return $this->iri;
    }

    /**
     * @return Type\TypeInterface<DocumentStructure>
     */
    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            '@type' => Type\literal_scalar('dms'),
            'id' => Type\positive_int(),
            'legacyId' => Type\positive_int(),
            'title' => Type\non_empty_string(),
            'subject' => Type\non_empty_string(),
            'description' => Type\non_empty_string(),
            'type' => Type\nullable(Type\non_empty_string()),
            'language' => Type\non_empty_string(),
            'portal' => Type\string(),
            'owner' => Person::getTypeStructure(),
        ], allow_unknown_fields: true);
    }
}
