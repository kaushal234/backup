<?php

declare(strict_types=1);

namespace LegacyBundle\Entity\Engineering\MasterEngineeringActivityProcess;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Entity\Directory\LocationById;
use LegacyBundle\Entity\Directory\PeopleById;
use LegacyBundle\Entity\Engineering\EngineeringActivityProcess;

#[ORM\Entity(readOnly: true)]
#[ORM\Table(name: 'meap')]
class MasterEngineeringActivityProcess
{
    #[ORM\Column(name: 'product_type', type: 'string', length: 50)]
    public string $productType = '';

    #[ORM\Column(type: 'string', length: 255)]
    public string $model = '';

    #[ORM\Column(type: 'string', length: 15)]
    public string $status = 'PROPOSAL';

    #[ORM\Column(name: 'pvt', type: 'string', length: 1)]
    public string $isPrivate = 'N';

    #[ORM\Column(type: 'string', length: 15, nullable: true)]
    public ?string $type = null;

    #[ORM\Column(type: 'string', length: 25)]
    public string $purpose = '';

    #[ORM\Column(name: 'short_desc', type: 'string', length: 150)]
    public string $shortDescription = '';

    #[ORM\Column(type: 'text')]
    public string $description = '';

    #[ORM\Column(type: 'text')]
    public string $resolution = '';

    #[ORM\Column(name: 'rejection_reason', type: 'text')]
    public string $rejectionReason = '';

    #[ORM\ManyToOne(targetEntity: LocationById::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'factory', referencedColumnName: 'id')]
    public ?LocationById $factory = null;

    #[ORM\ManyToOne(targetEntity: PeopleById::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'poster', referencedColumnName: 'id')]
    public ?PeopleById $poster = null;

    #[ORM\ManyToOne(targetEntity: PeopleById::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'proj_leader', referencedColumnName: 'id')]
    public ?PeopleById $projectLeader = null;

    #[ORM\Embedded(class: EconomicsHeader::class, columnPrefix: false)]
    public EconomicsHeader $economicsHeader;

    #[ORM\Embedded(class: NonRecurringCosts::class, columnPrefix: false)]
    public NonRecurringCosts $nonRecurringCosts;

    #[ORM\Embedded(class: RecurringCosts::class, columnPrefix: false)]
    public RecurringCosts $recurringCosts;

    #[ORM\Embedded(class: PlannedCompletionDates::class, columnPrefix: false)]
    public PlannedCompletionDates $plannedCompletionDates;

    #[ORM\Embedded(class: LifecycleDates::class, columnPrefix: false)]
    public LifecycleDates $lifecycleDates;

    #[ORM\Embedded(class: Scoring::class, columnPrefix: false)]
    public Scoring $scoring;

    /** @var Collection<EngineeringActivityProcess> */
    #[ORM\OneToMany(targetEntity: EngineeringActivityProcess::class, mappedBy: 'masterEngineeringActivityProcess')]
    public Collection $engineeringActivityProcesses;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
