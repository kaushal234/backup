<?php

declare(strict_types=1);

namespace App\Entity\Service\TechnicianOnCall;

use App\Entity\Country;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'technician_on_call_zone')]
class TechnicianOnCallZone
{
    #[Assert\Type('string')]
    #[Assert\NotBlank]
    #[Assert\Length(exactly: 1)]
    #[ORM\Column(type: Types::STRING)]
    public string $name;

    #[Assert\Type('integer')]
    #[Assert\NotBlank]
    #[ORM\Column(type: Types::INTEGER)]
    public int $delay = 48;
    #[ORM\ManyToMany(targetEntity: Country::class)]
    #[ORM\JoinTable(
        name: 'technician_on_call_zone_country',
        joinColumns: [new ORM\JoinColumn(name: 'zone_id', referencedColumnName: 'id')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'country_id', referencedColumnName: 'id', unique: true)]
    )]
    private Collection $countries;

    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    public function __construct()
    {
        $this->countries = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCountries(): Collection
    {
        return $this->countries;
    }

    public function addCountry(Country $country): self
    {
        if (!$this->countries->contains($country)) {
            $this->countries->add($country);
        }

        return $this;
    }

    public function removeCountry(Country $country): self
    {
        $this->countries->removeElement($country);

        return $this;
    }
}
