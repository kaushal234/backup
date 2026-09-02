<?php

declare(strict_types=1);

namespace App\SageParts\Resources;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\SageParts\DataProvider\SageItemDataProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(
            requirements: ['alvestId' => '.*'],
            provider: SageItemDataProvider::class,
        ),
    ],
    routePrefix: 'sage',
    normalizationContext: ['groups' => ['sage_part']],
    denormalizationContext: [],
    security: "is_granted('ACCESS_PEOPLE')",
)]
class SagePart
{
    #[ApiProperty(identifier: true)]
    #[Groups(['sage_part'])]
    public string $alvestId = '';

    #[Groups(['sage_part'])]
    public ?string $sageId = null;

    #[Groups(['sage_part'])]
    public ?string $description = null;

    #[Groups(['sage_part'])]
    public ?string $sageUID = null;

    /**
     * @var Collection<Location>
     */
    #[Groups(['sage_part'])]
    protected Collection $locations;

    public function __construct()
    {
        $this->locations = new ArrayCollection();
    }

    public function getLocations(): Collection
    {
        return $this->locations;
    }

    public function addLocation(Location $location): self
    {
        $this->locations->add($location);

        return $this;
    }

    public function removeLocation(Location $item): self
    {
        // do nothing, we do not remove element from this resource
        return $this;
    }
}
