<?php

declare(strict_types=1);

namespace LegacyBundle\Entity\Engineering;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Entity\Directory\LocationById;
use LegacyBundle\Entity\Directory\PeopleById;
use LegacyBundle\Entity\Engineering\MasterEngineeringActivityProcess\MasterEngineeringActivityProcess;

#[ORM\Entity(readOnly: true)]
#[ORM\Table(name: 'eap')]
class EngineeringActivityProcess
{
    #[ORM\Column(name: 'short_desc', type: 'string')]
    public string $shortDescription;

    #[ORM\Column(type: 'text')]
    public string $description;

    #[ORM\Column(type: 'string')]
    public string $status;

    #[ORM\Column(name: 'dt_opened', type: 'datetime')]
    public \DateTimeInterface $openedAt;

    #[ORM\Column(name: 'dt_closed', type: 'nullable_zero_date')]
    public ?\DateTimeInterface $closedAt = null;

    #[ORM\Column(type: 'string')]
    public string $category;

    #[ORM\Column(name: 'ifactor', type: 'string')]
    public string $importanceFactor = '';

    #[ORM\Column(type: 'string')]
    public string $type;

    #[ORM\Column(type: 'string')]
    public string $model;

    #[ORM\Column(name: 'action_plan', type: 'string')]
    public string $actionPlan = '';

    #[ORM\Column(type: 'string')]
    public string $currency = '';

    #[ORM\Column(name: 'info', type: 'string')]
    public string $additionalInformation = '';

    #[ORM\Column(name: 'expected_hours', type: 'string')]
    public ?int $expectedHours = null;

    #[ORM\ManyToOne(targetEntity: MasterEngineeringActivityProcess::class, fetch: 'EAGER', inversedBy: 'engineeringActivityProcesses')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id')]
    public ?MasterEngineeringActivityProcess $masterEngineeringActivityProcess = null;

    #[ORM\ManyToOne(targetEntity: LocationById::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'factory', referencedColumnName: 'id')]
    public ?LocationById $factory = null;

    #[ORM\ManyToOne(targetEntity: PeopleById::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'reporter', referencedColumnName: 'id')]
    public ?PeopleById $reportedBy = null;

    #[ORM\ManyToOne(targetEntity: PeopleById::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'poster', referencedColumnName: 'id')]
    public ?PeopleById $poster = null;

    #[ORM\ManyToOne(targetEntity: PeopleById::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'assignee', referencedColumnName: 'id')]
    public ?PeopleById $assignee = null;

    /** @var Collection<EngineeringActivityProcessPart> */
    #[ORM\OneToMany(targetEntity: EngineeringActivityProcessPart::class, mappedBy: 'engineeringActivityProcess')]
    public Collection $parts;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;

    public function __construct()
    {
        $this->parts = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }
}
