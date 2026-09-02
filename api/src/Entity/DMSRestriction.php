<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\Department;
use App\Entity\Directory\Division;
use App\Entity\Directory\Position;
use App\Entity\Directory\Region;
use App\Entity\Directory\SubDivision;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Entity\LegacyIdentifierTrait;

#[ORM\Entity]
#[ORM\Table(name: 'dms_restrictions')]
class DMSRestriction
{
    use LegacyIdentifierTrait;

    #[ORM\ManyToOne(targetEntity: BusinessUnit::class)]
    public ?BusinessUnit $businessUnit = null;

    #[ORM\ManyToOne(targetEntity: Region::class)]
    public ?Region $region = null;

    #[ORM\ManyToOne(targetEntity: Division::class)]
    public ?Division $division = null;

    #[ORM\ManyToOne(targetEntity: SubDivision::class)]
    public ?SubDivision $subDivision = null;

    #[ORM\ManyToOne(targetEntity: Position::class)]
    public ?Position $position = null;

    #[ORM\ManyToOne(targetEntity: Department::class)]
    public ?Department $department = null;

    #[ORM\ManyToOne(targetEntity: DMS::class, inversedBy: 'restrictions')]
    #[ORM\JoinColumn(nullable: false)]
    public DMS $dms;
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
