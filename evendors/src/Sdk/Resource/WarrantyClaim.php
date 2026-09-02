<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use Psl\Type;

/**
 * @phpstan-type WarrantyClaimStructure array{"id": int, "status": string, "enteredBy": string, "claimantDetails": string, "warrantyDetails": string, "customer": string, "problemDescription": string, "claimDate": string, "type": string, "model": string, "manufacturerLocation": string, "salesOrganization": string, "serialNumber": string, "equipmentLocation": string, "hours": int}
 *
 * @psalm-type WarrantyClaimStructure = array{"id": int, "status": string, "enteredBy": string, "claimantDetails": string, "warrantyDetails": string, "customer": string, "problemDescription": string, "claimDate": string, "type": string, "model": string, "manufacturerLocation": string, "salesOrganization": string, "serialNumber": string, "equipmentLocation": string, "hours": int}
 */
final class WarrantyClaim
{
    public function __construct(
        public readonly int $id,
        public readonly string $status,
        public readonly string $enteredBy,
        public readonly string $warrantyDetails,
        public readonly string $customer,
        public readonly string $problemDescription,
        public readonly string $claimDate,
        public readonly string $type,
        public readonly string $model,
        public readonly string $manufacturerLocation,
        public readonly string $salesOrganization,
        public readonly string $serialNumber,
        public readonly string $equipmentLocation,
        public readonly int $hours,
        public readonly ?string $claimantDetails = null,
    ) {
    }

    /**
     * @return Type\TypeInterface<WarrantyClaimStructure>
     */
    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            'id' => Type\int(),
            'status' => Type\string(),
            'enteredBy' => Type\string(),
            'claimantDetails' => Type\optional(Type\nullable(Type\string())),
            'warrantyDetails' => Type\string(),
            'customer' => Type\string(),
            'problemDescription' => Type\string(),
            'claimDate' => Type\string(),
            'type' => Type\string(),
            'model' => Type\string(),
            'manufacturerLocation' => Type\string(),
            'salesOrganization' => Type\string(),
            'serialNumber' => Type\string(),
            'equipmentLocation' => Type\string(),
            'hours' => Type\int(),
        ], allow_unknown_fields: true);
    }
}
