<?php

declare(strict_types=1);

namespace App\Entity\Sales\AircraftCompatibility;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\File;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(operations: [new Get()])]
#[ORM\Entity]
#[ORM\Table(name: 'aircraft_compatibility_files')]
#[App\Loggable(owner: 'aircraftCompatibility', ownerRelation: 'files')]
class AircraftCompatibilityFile extends File
{
    public const string NTO = 'NTO';
    public const string SIL = 'SIL';
    public const string OTHER = 'OTHER';

    #[ORM\ManyToOne(targetEntity: AircraftCompatibility::class, inversedBy: 'files')]
    private AircraftCompatibility $aircraftCompatibility;

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotNull]
    #[Assert\Choice(choices: [self::NTO, self::SIL, self::OTHER])]
    #[Groups(groups: ['file'])]
    private string $type;

    public function getAircraftCompatibility(): AircraftCompatibility
    {
        return $this->aircraftCompatibility;
    }

    public function setAircraftCompatibility(AircraftCompatibility $aircraftCompatibility): self
    {
        $this->aircraftCompatibility = $aircraftCompatibility;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }
}
