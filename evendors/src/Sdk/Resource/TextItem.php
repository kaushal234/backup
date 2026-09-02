<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use Psl\Type;

/**
 * @phpstan-type TextItemStructure array{"@id": non-empty-string, "@type": non-empty-string, langCode: non-empty-string, lang: non-empty-string, texts: list<string|int>}
 *
 * @psalm-type TextItemStructure = array{"@id": non-empty-string, "@type": non-empty-string, langCode: non-empty-string, lang: non-empty-string, texts: list<string|int>}
 */
final class TextItem implements ResourceInterface
{
    public function __construct(
        public readonly string $iri,
        public readonly string $type,
        public readonly string $langCode,
        public readonly string $lang,
        public readonly string $texts,
    ) {
    }

    public function getIri(): string
    {
        return $this->iri;
    }

    /**
     * @return Type\TypeInterface<TextItemStructure>
     */
    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            '@type' => Type\non_empty_string(),
            'langCode' => Type\non_empty_string(),
            'lang' => Type\non_empty_string(),
        ], allow_unknown_fields: true);
    }
}
