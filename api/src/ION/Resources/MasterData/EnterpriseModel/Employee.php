<?php

declare(strict_types=1);

namespace App\ION\Resources\MasterData\EnterpriseModel;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\ION\DataProvider\CachedIONCollectionDataProvider;
use App\ION\DataProvider\CachedIONItemDataProvider;
use App\ION\Filter\IONFilter;
use App\Security\JWT\PayloadGenerator\PayloadGeneratorInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new GetCollection(provider: CachedIONCollectionDataProvider::class),
        new Get(requirements: ['id' => '.*'], provider: CachedIONItemDataProvider::class),
    ],
    routePrefix: 'ion',
    normalizationContext: ['groups' => ['employee']],
    denormalizationContext: [],
    security: "is_granted('ACCESS_PEOPLE')",
)]
#[ApiFilter(IONFilter::class, properties: ['fullName'])]
class Employee
{
    #[ApiProperty(identifier: true)]
    #[Groups(['employee'])]
    public string $employeeCode;

    #[Groups(['employee'])]
    public string $fullName;

    #[Groups(['employee'])]
    public ?string $erp = null;

    #[Groups(['employee'])]
    public string $baanLegacyId;

    #[Groups(['employee', PayloadGeneratorInterface::PAYLOAD_NORMALIZATION_GROUP])]
    public ?string $emailAddress;
}
