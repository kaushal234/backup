<?php

declare(strict_types=1);

namespace App\Entity\Directory;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\File;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(operations: [new Get()])]
#[ORM\Entity]
#[ORM\Table(name: 'people_files')]
#[App\Loggable(owner: 'people', ownerRelation: 'files')]
class PeopleFile extends File
{
    #[ApiProperty(iris: ['https://schema.org/Boolean'])]
    #[Groups(['file', 'file_public_write'])]
    protected bool $public = true;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People', inversedBy: 'files')]
    private ?People $people = null;

    public function getPeople(): People
    {
        return $this->people;
    }

    public function setPeople(People $people): self
    {
        $this->people = $people;

        return $this;
    }
}
