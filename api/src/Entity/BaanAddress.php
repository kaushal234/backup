<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Embeddable]
class BaanAddress
{
    #[ORM\Column(type: 'string', length: 35)]
    #[Assert\Length(max: 35)]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Groups(['baan_address', 'baan_address_write'])]
    protected string $name = '';

    #[ORM\Column(type: 'string', length: 30)]
    #[Assert\Length(max: 30)]
    #[Assert\NotBlank]
    #[Groups(['baan_address', 'baan_address_write'])]
    protected string $nameExtra = '';

    #[ORM\Column(type: 'string', length: 30)]
    #[Assert\Length(max: 30)]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Groups(['baan_address', 'baan_address_write'])]
    protected string $address = '';

    #[ORM\Column(type: 'string', length: 30)]
    #[Assert\Length(max: 30)]
    #[Groups(['baan_address', 'baan_address_write'])]
    protected string $addressExtra = '';

    #[ORM\Column(type: 'string', length: 10)]
    #[Assert\Length(max: 10)]
    #[Assert\NotBlank]
    #[Groups(['baan_address', 'baan_address_write'])]
    protected string $zip = '';

    #[ORM\Column(type: 'string', length: 30)]
    #[Assert\Length(max: 30)]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Groups(['baan_address', 'baan_address_write'])]
    protected string $city = '';

    #[ORM\Column(type: 'string', length: 30)]
    #[Assert\Length(max: 30)]
    #[Groups(['baan_address', 'baan_address_write'])]
    protected string $cityExtra = '';

    #[ORM\Column(type: 'string', length: 3)]
    #[Assert\Length(min: 3, max: 3, exactMessage: 'Country code must be 3 characters long')]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Groups(['baan_address', 'baan_address_write'])]
    protected string $country = '';

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    public function getNameExtra(): ?string
    {
        return $this->nameExtra;
    }

    /**
     * @return $this
     */
    public function setNameExtra(string $nameExtra)
    {
        $this->nameExtra = $nameExtra;

        return $this;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @return $this
     */
    public function setAddress(string $address)
    {
        $this->address = $address;

        return $this;
    }

    public function getAddressExtra(): ?string
    {
        return $this->addressExtra;
    }

    /**
     * @return $this
     */
    public function setAddressExtra(string $addressExtra)
    {
        $this->addressExtra = $addressExtra;

        return $this;
    }

    public function getZip(): ?string
    {
        return $this->zip;
    }

    /**
     * @return $this
     */
    public function setZip(string $zip)
    {
        $this->zip = $zip;

        return $this;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @return $this
     */
    public function setCity(string $city)
    {
        $this->city = $city;

        return $this;
    }

    public function getCityExtra(): ?string
    {
        return $this->cityExtra;
    }

    /**
     * @return $this
     */
    public function setCityExtra(string $cityExtra)
    {
        $this->cityExtra = $cityExtra;

        return $this;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * @return $this
     */
    public function setCountry(string $country)
    {
        $this->country = $country;

        return $this;
    }
}
