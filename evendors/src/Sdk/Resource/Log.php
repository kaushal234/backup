<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use Psl\Type;

/**
 * @phpstan-import-type PersonStructure from Person
 *
 * @psalm-import-type PersonStructure from Person
 *
 * @phpstan-type LogStructure array{"@id": non-empty-string, "@type": non-empty-string, message: non-empty-string, resource: non-empty-string, user: ?PersonStructure, createdAt: non-empty-string, updatedAt: non-empty-string, public: bool}
 *
 * @psalm-type LogStructure = array{"@id": non-empty-string, "@type": non-empty-string, message: non-empty-string, resource: non-empty-string, user: ?PersonStructure, createdAt: non-empty-string, updatedAt: non-empty-string, public: bool}
 */
final class Log implements ActivityInterface
{
    /**
     * @param array<string, mixed> $changeSet
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public readonly string $iri,
        public readonly string $resource,
        public readonly ?Person $user,
        public readonly string $createdAt,
        public readonly string $updatedAt,
        public readonly bool $public,
        public readonly array $changeSet = [],
        public readonly array $metadata = [],
    ) {
    }

    public function getIri(): string
    {
        return $this->iri;
    }

    /**
     * @return Type\TypeInterface<LogStructure>
     */
    public static function getLegacyTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            '@type' => Type\non_empty_string(),
            'resource' => Type\non_empty_string(),
            'user' => Type\nullable(Person::getTypeStructure()),
            'createdAt' => Type\non_empty_string(),
            'updatedAt' => Type\non_empty_string(),
            'public' => Type\bool(),
        ], allow_unknown_fields: true);
    }

    public function getType(): string
    {
        return ActivityInterface::LOG_TYPE;
    }
}
