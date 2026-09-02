<?php

declare(strict_types=1);

namespace LegacyBundle\Entity\Quality;

use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Entity\Directory\LocationById;
use LegacyBundle\Entity\Directory\PeopleById;

#[ORM\Entity(readOnly: true)]
#[ORM\Table(name: 'cpa')]
class CorrectivePreventiveAction
{
    #[ORM\Column(name: 'dept', type: 'string')]
    public string $department = '';

    #[ORM\ManyToOne(targetEntity: LocationById::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'bu', referencedColumnName: 'id', nullable: true)]
    public ?LocationById $location = null;

    #[ORM\ManyToOne(targetEntity: PeopleById::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'proj_leader', referencedColumnName: 'id', nullable: true)]
    public ?PeopleById $projectLeader = null;

    #[ORM\Column(name: 'type', type: 'string')]
    public string $type = '';

    #[ORM\Column(name: 'status', type: 'string')]
    public string $status = '';

    #[ORM\Column(name: 'last_status', type: 'string', nullable: true)]
    public ?string $lastStatus = null;

    #[ORM\Column(name: 'date', type: 'nullable_zero_date', nullable: true)]
    public ?\DateTimeInterface $openedDate = null;

    #[ORM\Column(name: 'date_target', type: 'nullable_zero_date', nullable: true)]
    public ?\DateTimeInterface $targetDate = null;

    #[ORM\Column(name: 'date_closed', type: 'nullable_zero_date', nullable: true)]
    public ?\DateTimeInterface $closedDate = null;

    #[ORM\Column(name: 'date_suspended', type: 'nullable_zero_date', nullable: true)]
    public ?\DateTimeInterface $suspendedDate = null;

    #[ORM\Column(name: 'days_suspended', type: 'integer')]
    public int $daysSuspended = 0;

    #[ORM\Column(name: 'short_desc', type: 'string')]
    public string $shortDescription = '';

    #[ORM\Column(name: 'description', type: 'text')]
    public string $description = '';

    #[ORM\Column(name: 'containment_action', type: 'text')]
    public string $containmentAction = '';

    #[ORM\Column(name: 'root_cause', type: 'text')]
    public string $rootCause = '';

    #[ORM\Column(name: 'corrective_action', type: 'text')]
    public string $correctiveAction = '';

    #[ORM\Column(name: 'preventive_action', type: 'text')]
    public string $preventiveAction = '';

    #[ORM\Column(name: 'resolution', type: 'text')]
    public string $resolution = '';

    #[ORM\Column(name: 'rejection_reason', type: 'text')]
    public string $rejectionReason = '';

    #[ORM\Column(name: 'ifactor', type: 'integer')]
    public int $importanceFactor = 1;

    #[ORM\Column(name: 'final_fweight', type: 'integer')]
    public int $finalWeight = 0;

    #[ORM\ManyToOne(targetEntity: PeopleById::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'poster', referencedColumnName: 'id', nullable: true)]
    public ?PeopleById $poster = null;

    #[ORM\ManyToOne(targetEntity: PeopleById::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'initiator', referencedColumnName: 'id', nullable: true)]
    public ?PeopleById $initiator = null;

    #[ORM\Column(name: 'verification_description', type: 'text', nullable: true)]
    public ?string $verificationDescription = null;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
