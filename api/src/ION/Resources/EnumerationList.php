<?php

declare(strict_types=1);

namespace App\ION\Resources;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\ION\DataProvider\CachedIONItemDataProvider;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new Get(requirements: ['id' => '.*']),
    ],
    routePrefix: 'ion',
    normalizationContext: ['groups' => ['enumeration']],
    denormalizationContext: [],
    provider: CachedIONItemDataProvider::class,
)]
class EnumerationList
{
    #[ApiProperty(identifier: true)]
    #[Groups(['enumeration'])]
    public string $package;

    #[ApiProperty(identifier: true)]
    #[Groups(['enumeration'])]
    public string $domain;

    /**
     * @var Enumeration[]
     */
    #[Groups(['enumeration'])]
    private array $enumerations = [];

    public function getEnumerations(): array
    {
        return $this->enumerations;
    }

    public function addEnumeration(Enumeration $enumeration): self
    {
        $this->enumerations[] = $enumeration;

        return $this;
    }

    public function removeEnumeration(Enumeration $enumeration): self
    {
        // do nothing, we do not remove element from this resource
        return $this;
    }
}
