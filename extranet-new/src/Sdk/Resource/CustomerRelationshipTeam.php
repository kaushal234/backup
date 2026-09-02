<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use Psl\Type;

class CustomerRelationshipTeam implements ResourceInterface
{
    public function __construct(
        public readonly string $iri,
        public readonly ?Representative $salesRepresentative,
        public readonly ?Representative $serviceRepresentative,
        public readonly ?Representative $partsRepresentative,
        public readonly ?Location $erpLocation,
        public readonly ?Location $serviceLocation,
        public readonly ?Location $partsLocation,
        public readonly Customer $customer,
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
            'salesRepresentative' => Type\nullable(Representative::getTypeStructure()),
            'serviceRepresentative' => Type\nullable(Representative::getTypeStructure()),
            'partsRepresentative' => Type\nullable(Representative::getTypeStructure()),
            'erpLocation' => Type\nullable(Location::getTypeStructure()),
            'serviceLocation' => Type\nullable(Location::getTypeStructure()),
            'partsLocation' => Type\nullable(Location::getTypeStructure()),
            'customer' => Customer::getTypeStructure(),
        ], allowUnknownFields: true);
    }
}
