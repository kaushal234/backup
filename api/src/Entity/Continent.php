<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
    ],
    normalizationContext: ['groups' => ['continent']])]
#[ORM\Table(name: 'continents')]
#[ApiFilter(OrderFilter::class, properties: ['name' => 'ASC'])]
#[ApiFilter(SearchFilter::class, properties: ['id' => 'exact', 'isoCode2' => 'exact', 'name' => 'partial'])]
class Continent
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\Column(name: 'iso_code_2', type: 'string', length: 2, unique: true, nullable: false)]
    #[Groups(['continent'])]
    #[ApiProperty(iris: ['https://schema.org/text'])]
    private string $isoCode2;

    #[ORM\Column(name: 'name', unique: true, nullable: false)]
    #[Groups(['continent'])]
    #[ApiProperty(iris: ['https://schema.org/name'])]
    private string $name;

    /**
     * @var Collection<Country>
     */
    #[ORM\OneToMany(mappedBy: 'continent', targetEntity: 'App\Entity\Country')]
    private Collection $countries;

    public function __construct()
    {
        $this->countries = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setIsoCode2(string $isoCode2): self
    {
        $this->isoCode2 = $isoCode2;

        return $this;
    }

    public function getIsoCode2(): string
    {
        return $this->isoCode2;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function removeCountry(Country $country): self
    {
        $this->countries->removeElement($country);

        return $this;
    }

    public function addCountry(Country $country): self
    {
        $this->countries->add($country);

        return $this;
    }

    /**
     * @return Collection<Country>
     */
    public function getCountries(): Collection
    {
        return $this->countries;
    }
}
