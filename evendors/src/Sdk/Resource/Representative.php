<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use App\Sdk\Resource\Traits\CompleteTypeStructureTrait;
use App\Security\User\Address;
use Psl\Type;

/**
 * @phpstan-import-type BusinessUnitStructure from BusinessUnit
 *
 * @psalm-import-type BusinessUnitStructure from BusinessUnit
 *
 * @phpstan-import-type DepartmentStructure from Department
 *
 * @psalm-import-type DepartmentStructure from Department
 *
 * @phpstan-import-type PhoneStructure from Phone
 *
 * @psalm-import-type PhoneStructure from Phone
 *
 * @phpstan-import-type PhotoStructure from Photo
 *
 * @psalm-import-type PhotoStructure from Photo
 *
 * @phpstan-type RepresentativeStructure array{"@id": non-empty-string, "id": positive-int, email: non-empty-string, firstname: non-empty-string, lastname: non-empty-string, "businessUnit": BusinessUnitStructure, "department": DepartmentStructure, "phones": null|list<PhoneStructure>, "photo": null|list<PhotoStructure>}
 *
 * @psalm-type RepresentativeStructure = array{"@id": non-empty-string, "id": positive-int, email: non-empty-string, firstname: non-empty-string, lastname: non-empty-string, "businessUnit": BusinessUnitStructure, "department": DepartmentStructure, "phones": null|list<PhoneStructure>, "photo": null|list<PhotoStructure>}
 */
final class Representative implements ResourceInterface
{
    use CompleteTypeStructureTrait;

    /**
     * @param list<Phone> $phones
     */
    public function __construct(
        public readonly string $iri,
        public readonly int $id,
        public readonly string $firstname,
        public readonly string $lastname,
        public readonly string $email,
        public readonly BusinessUnit $businessUnit,
        public readonly ?Department $department,
        public readonly ?string $jobTitle,
        public readonly array $phones,
        public readonly Address $address,
        public readonly ?Photo $photo,
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
            'id' => Type\positive_int(),
            'email' => Type\non_empty_string(),
            'firstname' => Type\non_empty_string(),
            'lastname' => Type\non_empty_string(),
            'jobTitle' => Type\nullable(Type\non_empty_string()),
            'businessUnit' => BusinessUnit::getTypeStructure(),
            'department' => Type\nullable(Department::getTypeStructure()),
            'phones' => Type\optional(Type\vec(Phone::getTypeStructure())),
            'photo' => Type\nullable(Type\optional(Photo::getTypeStructure())),
        ], allow_unknown_fields: true);
    }
}
