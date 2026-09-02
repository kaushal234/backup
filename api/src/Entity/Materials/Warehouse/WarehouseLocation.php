<?php

declare(strict_types=1);

namespace App\Entity\Materials\Warehouse;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(readOnly: true)]
#[UniqueEntity(fields: ['erp', 'warehouse', 'locationNumber'])]
#[ORM\Table(name: 'warehouse_locations')]
#[ORM\UniqueConstraint(name: 'unique_location_per_warehouse_per_erp', columns: ['erp', 'warehouse', 'location_number'])]
class WarehouseLocation
{
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\Column(type: 'integer', length: 3)]
    private int $erp;

    #[ORM\Column(type: 'string', length: 3)]
    private string $warehouse;

    #[ORM\Column(type: 'string', length: 8)]
    private string $locationNumber;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Materials\Warehouse\Sector', inversedBy: 'warehouseLocations')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Sector $sector = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $deletedInBaanAt = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getErp(): int
    {
        return $this->erp;
    }

    public function setErp(int $erp): self
    {
        $this->erp = $erp;

        return $this;
    }

    public function getWarehouse(): string
    {
        return $this->warehouse;
    }

    public function setWarehouse(string $warehouse): self
    {
        $this->warehouse = $warehouse;

        return $this;
    }

    public function getLocationNumber(): string
    {
        return $this->locationNumber;
    }

    public function setLocationNumber(string $locationNumber): self
    {
        $this->locationNumber = $locationNumber;

        return $this;
    }

    public function getSector(): ?Sector
    {
        return $this->sector;
    }

    public function setSector(Sector $sector): self
    {
        $this->sector = $sector;

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTime $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    public function getDeletedInBaanAt(): ?\DateTimeInterface
    {
        return $this->deletedInBaanAt;
    }

    public function setDeletedInBaanAt(?\DateTime $deletedInBaanAt): self
    {
        $this->deletedInBaanAt = $deletedInBaanAt;

        return $this;
    }
}
