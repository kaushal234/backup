<?php

declare(strict_types=1);

namespace App\Entity\Service\CustomerServiceRecord;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Put;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ApiResource(
    shortName: 'default_customer_service_record',
    operations: [
        new Get(),
        new GetCollection(),
        new Put(
            denormalizationContext: ['groups' => ['customer_service_record:create', 'customer_service_record:detail', 'customer_service_record:update']],
            security: "is_granted('FEATURE_CUSTOMER_SERVICE_RECORD_EDIT')",
        ),
    ],
    routePrefix: 'service',
    normalizationContext: [
        'groups' => self::NORMALIZATION_GROUP,
    ],
    denormalizationContext: [
        'groups' => ['customer_service_record:create', 'customer_service_record:detail'],
    ],
)]
class CustomerServiceRecord extends AbstractCustomerServiceRecord
{
    public function getLegacyModuleName(): string
    {
        return '';
    }

    public function getLegacyModuleId(): ?int
    {
        return null;
    }
}
