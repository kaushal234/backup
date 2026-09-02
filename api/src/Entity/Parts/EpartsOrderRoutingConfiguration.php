<?php

declare(strict_types=1);

namespace App\Entity\Parts;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Directory\Location;
use App\Validator\Constraints\Location as ValidLocation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[UniqueEntity(fields: ['sph'])]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['eparts_configuration', 'location_public']]),
        new Post(securityPostDenormalize: "is_granted('EPARTS_CONFIGURATION_WRITE_VOTER', object.sph)"),
        new Get(),
        new Put(
            denormalizationContext: ['groups' => ['eparts_configuration:update']],
            security: "is_granted('EPARTS_CONFIGURATION_WRITE_VOTER', object.sph)",
        ),
    ],
    routePrefix: 'parts',
    normalizationContext: ['groups' => ['eparts_configuration', 'eparts_configuration:detail', 'location_public']],
    denormalizationContext: ['groups' => ['eparts_configuration:create']],
)]
#[ORM\Table('eparts_order_routing_configurations')]
class EpartsOrderRoutingConfiguration
{
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['eparts_configuration', 'eparts_configuration:create'])]
    #[ValidLocation(sparePartsHub: true)]
    public Location $sph;

    #[ORM\Column(type: 'string', length: 3)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 3)]
    #[Groups(['eparts_configuration', 'eparts_configuration:create', 'eparts_configuration:update'])]
    public string $defaultRouting;

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    /**
     * @var Collection<AreaRouting>
     */
    #[ORM\OneToMany(mappedBy: 'epartsOrderRoutingConfiguration', targetEntity: 'App\Entity\Parts\AreaRouting', cascade: ['remove', 'persist'], orphanRemoval: true)]
    #[Assert\Valid]
    #[Groups(['eparts_configuration:detail', 'eparts_configuration:update', 'eparts_configuration:create'])]
    private Collection $areaRoutings;

    public function __construct()
    {
        $this->areaRoutings = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getAreaRoutings(): Collection
    {
        return $this->areaRoutings;
    }

    public function addAreaRouting(AreaRouting $areaRouting)
    {
        if (!$this->areaRoutings->contains($areaRouting)) {
            $areaRouting->epartsOrderRoutingConfiguration = $this;
            $this->areaRoutings->add($areaRouting);
        }

        return $this;
    }

    public function removeAreaRouting(AreaRouting $areaRouting)
    {
        if ($this->areaRoutings->contains($areaRouting)) {
            $this->areaRoutings->removeElement($areaRouting);
        }

        return $this;
    }
}
