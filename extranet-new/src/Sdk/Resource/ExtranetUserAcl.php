<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use App\Sdk\Resource\Traits\CompleteTypeStructureTrait;
use Psl\Type;

class ExtranetUserAcl implements ResourceInterface
{
    use CompleteTypeStructureTrait;

    public function __construct(
        public readonly string $iri,
        public readonly Group $group,
        public readonly CustomerRelationshipTeam $customerRelationshipTeam,
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
            'extranetUserGroup' => Group::getTypeStructure(),
            'crt' => CustomerRelationshipTeam::getTypeStructure(),
        ], allowUnknownFields: true);
    }
}
