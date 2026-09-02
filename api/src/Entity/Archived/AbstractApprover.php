<?php

declare(strict_types=1);

namespace App\Entity\Archived;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Validator\Constraints as AppAssert;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Entity\LegacyIdentifierTrait;

#[ORM\Entity]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'discr', type: 'string')]
#[ORM\DiscriminatorMap(['supplier' => SupplierApprover::class, 'analytical_dimension' => AnalyticalDimensionApprover::class])]
#[ApiResource(operations: [])]
#[ORM\Table(name: 'approvers')]
abstract class AbstractApprover
{
    use LegacyIdentifierTrait;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[ORM\JoinColumn(nullable: false)]
    #[AppAssert\Location(erpInLN: true)]
    public Location $location;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    public ?People $assignor = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(nullable: false)]
    public People $assignee;

    #[ORM\Column(type: 'boolean')]
    public bool $paymentBlocked = false;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
