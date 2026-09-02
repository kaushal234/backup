<?php

declare(strict_types=1);

namespace App\Entity\Quality;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\File;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(operations: [new Get()])]
#[ORM\Entity]
#[ORM\Table(name: 'non_conformity_main_files')]
#[App\Loggable(owner: 'nonConformity', ownerRelation: 'mainFiles')]
class NonConformityMainFile extends File
{
    #[ApiProperty(iris: ['https://schema.org/Boolean'])]
    #[Groups(['file', 'file_public_write'])]
    protected bool $public = true;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Quality\NonConformity', inversedBy: 'mainFiles')]
    private ?NonConformity $nonConformity = null;

    public function getNonConformity(): ?NonConformity
    {
        return $this->nonConformity;
    }

    public function setNonConformity(?NonConformity $nonConformity): self
    {
        $this->nonConformity = $nonConformity;

        return $this;
    }
}
