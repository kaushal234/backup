<?php

declare(strict_types=1);

namespace LegacyBundle\Entity\Directory;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(readOnly: true)]
#[ORM\Table(name: 'locations')]
class LocationByName extends AbstractLocation
{
    #[ORM\Id]
    #[ORM\Column(name: 'location', type: 'string')]
    public string $name;

    #[ORM\Column(type: 'integer')]
    protected int $id;

    public function getName(): string
    {
        return $this->name;
    }
}
