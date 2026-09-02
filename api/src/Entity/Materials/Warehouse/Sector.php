<?php

declare(strict_types=1);

namespace App\Entity\Materials\Warehouse;

use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(readOnly: true)]
#[UniqueEntity(fields: ['name', 'location'])]
#[ORM\Table(name: 'sectors')]
#[ORM\UniqueConstraint(name: 'unique_sector_name_per_location', columns: ['name', 'location_id'])]
class Sector
{
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\Column(type: 'string')]
    private string $name;

    #[ORM\Column(type: 'integer')]
    private int $inboundRate = 0;

    #[ORM\Column(type: 'integer')]
    private int $outboundRate = 0;

    /**
     * @var Collection<People>
     */
    #[ORM\ManyToMany(targetEntity: 'App\Entity\Directory\People')]
    private Collection $warehouseKeepers;

    /**
     * @var Collection<WarehouseLocation>
     */
    #[ORM\OneToMany(mappedBy: 'sector', targetEntity: 'App\Entity\Materials\Warehouse\WarehouseLocation')]
    private Collection $warehouseLocations;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    private ?Location $location = null;

    public function __construct()
    {
        $this->warehouseKeepers = new ArrayCollection();
        $this->warehouseLocations = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getInboundRate(): int
    {
        return $this->inboundRate;
    }

    public function setInboundRate(int $inboundRate): self
    {
        $this->inboundRate = $inboundRate;

        return $this;
    }

    public function getOutboundRate(): int
    {
        return $this->outboundRate;
    }

    public function setOutboundRate(int $outboundRate): self
    {
        $this->outboundRate = $outboundRate;

        return $this;
    }

    /**
     * @return Collection<People>
     */
    public function getWarehouseKeepers(): Collection
    {
        return $this->warehouseKeepers;
    }

    public function addWarehouseKeeper(People $warehouseKeeper): self
    {
        if (!$this->warehouseKeepers->contains($warehouseKeeper)) {
            $this->warehouseKeepers->add($warehouseKeeper);
        }

        return $this;
    }

    public function removeWarehouseKeeper(People $warehouseKeeper): self
    {
        if ($this->warehouseKeepers->contains($warehouseKeeper)) {
            $this->warehouseKeepers->removeElement($warehouseKeeper);
        }

        return $this;
    }

    /**
     * @return Collection<WarehouseLocation>
     */
    public function getWarehouseLocations(): Collection
    {
        return $this->warehouseLocations;
    }

    public function addWarehouseLocation(WarehouseLocation $warehouseLocation): self
    {
        if (!$this->warehouseLocations->contains($warehouseLocation)) {
            $this->warehouseLocations->add($warehouseLocation);
        }

        return $this;
    }

    public function removeWarehouseLocation(WarehouseLocation $warehouseLocation): self
    {
        if ($this->warehouseLocations->contains($warehouseLocation)) {
            $this->warehouseLocations->removeElement($warehouseLocation);
        }

        return $this;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }

    public function setLocation(Location $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getWarehouseLocationsCount(): int
    {
        return $this->warehouseLocations->count();
    }
}
