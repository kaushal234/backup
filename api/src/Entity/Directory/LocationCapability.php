<?php

declare(strict_types=1);

namespace App\Entity\Directory;

use ApiPlatform\Metadata\ApiProperty;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Embeddable]
class LocationCapability
{
    #[ApiProperty(
        description: 'TRUE, if you want only Sales Orgnaisation',
        iris: ['https://schema.org/Boolean']
    )]
    #[ORM\Column(name: 'sso', type: 'boolean')]
    #[Assert\Type(type: 'boolean')]
    #[Groups(['location', 'location_detail', 'location_write', 'user:me'])]
    private bool $sso = false;

    #[ApiProperty(
        description: 'TRUE, if you want only Factory',
        iris: ['https://schema.org/Boolean']
    )]
    #[ORM\Column(name: 'factory', type: 'boolean')]
    #[Assert\Type(type: 'boolean')]
    #[Groups(['location', 'location_detail', 'location_write', 'user:me'])]
    private bool $factory = false;

    #[ApiProperty(iris: ['https://schema.org/Boolean'])]
    #[ORM\Column(name: 'warehouse', type: 'boolean')]
    #[Assert\Type(type: 'boolean')]
    #[Groups(['location', 'location_detail', 'location_write', 'user:me'])]
    private bool $warehouse = false;

    #[ApiProperty(iris: ['https://schema.org/Boolean'])]
    #[ORM\Column(name: 'spare_parts_hub', type: 'boolean')]
    #[Assert\Type(type: 'boolean')]
    #[Groups(['location', 'location_detail', 'location_write', 'user:me'])]
    private bool $sparePartsHub = false;

    #[ApiProperty(iris: ['https://schema.org/Boolean'])]
    #[ORM\Column(name: 'service_hub', type: 'boolean')]
    #[Assert\Type(type: 'boolean')]
    #[Groups(['location', 'location_detail', 'location_write', 'user:me'])]
    private bool $serviceHub = false;

    #[ApiProperty(iris: ['https://schema.org/Boolean'])]
    #[ORM\Column(name: 'head_quarter', type: 'boolean')]
    #[Assert\Type(type: 'boolean')]
    #[Groups(['location', 'location_detail', 'location_write', 'user:me'])]
    private bool $headQuarter = false;

    /**
     * @return bool
     */
    public function isSso()
    {
        return $this->sso;
    }

    /**
     * @param bool $sso
     *
     * @return $this
     */
    public function setSso($sso)
    {
        $this->sso = $sso;

        return $this;
    }

    /**
     * @return bool
     */
    public function isFactory()
    {
        return $this->factory;
    }

    /**
     * @param bool $factory
     *
     * @return LocationCapability
     */
    public function setFactory($factory)
    {
        $this->factory = $factory;

        return $this;
    }

    /**
     * @return bool
     */
    public function isWarehouse()
    {
        return $this->warehouse;
    }

    /**
     * @param bool $warehouse
     *
     * @return LocationCapability
     */
    public function setWarehouse($warehouse)
    {
        $this->warehouse = $warehouse;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSparePartsHub()
    {
        return $this->sparePartsHub;
    }

    /**
     * @param bool $sparePartsHub
     *
     * @return LocationCapability
     */
    public function setSparePartsHub($sparePartsHub)
    {
        $this->sparePartsHub = $sparePartsHub;

        return $this;
    }

    /**
     * @return bool
     */
    public function isServiceHub()
    {
        return $this->serviceHub;
    }

    /**
     * @param bool $serviceHub
     *
     * @return LocationCapability
     */
    public function setServiceHub($serviceHub)
    {
        $this->serviceHub = $serviceHub;

        return $this;
    }

    /**
     * @return bool
     */
    public function isHeadQuarter()
    {
        return $this->headQuarter;
    }

    /**
     * @param bool $headQuarter
     *
     * @return LocationCapability
     */
    public function setHeadQuarter($headQuarter)
    {
        $this->headQuarter = $headQuarter;

        return $this;
    }
}
