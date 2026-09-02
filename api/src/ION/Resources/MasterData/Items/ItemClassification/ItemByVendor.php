<?php

declare(strict_types=1);

namespace App\ION\Resources\MasterData\Items\ItemClassification;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\ION\DataProvider\CachedIONItemDataProvider;
use App\ION\Filter\IONFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new Get(requirements: ['id' => '.*']),
    ],
    routePrefix: 'ion',
    normalizationContext: ['groups' => ['item']],
    denormalizationContext: [],
    security: "is_granted('ACCESS_PEOPLE')",
    provider: CachedIONItemDataProvider::class,
)]
#[ApiFilter(IONFilter::class, properties: ['item', 'itemCodeSystem'])]
class ItemByVendor
{
    public const ITEM_CODE_SYSTEM_SUPPLIER_TYPE = 'SUP';

    public const DATAAREA_FILTERS = ['itemCodeSystem' => self::ITEM_CODE_SYSTEM_SUPPLIER_TYPE];

    #[ApiProperty(identifier: true)]
    #[Groups(['item'])]
    public string $item;

    /**
     * @var Collection<Reference>
     */
    #[Groups(['item'])]
    private Collection $references;

    public function __construct()
    {
        $this->references = new ArrayCollection();
    }

    /**
     * @return Collection<Reference>
     */
    public function getReferences(): Collection
    {
        return $this->references;
    }

    public function addReference(Reference $reference): self
    {
        $this->references->add($reference);

        return $this;
    }

    public function removeReference(Reference $reference): self
    {
        $this->references->removeElement($reference);

        return $this;
    }
}
