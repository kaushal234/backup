<?php

declare(strict_types=1);

namespace App\ION\Resources\Engineering;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\ION\DataProvider\CachedIONItemDataProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new Get(requirements: ['id' => '.*']),
    ],
    routePrefix: 'ion',
    normalizationContext: ['groups' => ['engineering:item', 'engineering:revision']],
    denormalizationContext: [],
    provider: CachedIONItemDataProvider::class,
)]
class EngineeringItem
{
    #[ApiProperty(identifier: true)]
    #[Groups(['engineering:item'])]
    public string $item;

    #[ApiProperty(identifier: true)]
    #[Groups(['engineering:item'])]
    public string $project = '';

    #[Groups(['engineering:item'])]
    public ?string $signalCode;

    /**
     * @var Collection<Revision>
     */
    #[Groups(['engineering:item', 'engineering:revision'])]
    private Collection $revisions;

    public function __construct()
    {
        $this->revisions = new ArrayCollection();
    }

    /**
     * @return Collection<Revision>
     */
    public function getRevisions(): Collection
    {
        return $this->revisions;
    }

    public function addRevision(Revision $revision): self
    {
        $this->revisions->add($revision);

        return $this;
    }

    public function removeRevision(Revision $revision): self
    {
        $this->revisions->removeElement($revision);

        return $this;
    }
}
