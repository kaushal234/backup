<?php

declare(strict_types=1);

namespace LegacyBundle\Entity\Support;

use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Entity\Directory\LocationById;
use LegacyBundle\Entity\Directory\PeopleById;

#[ORM\Entity(readOnly: true)]
#[ORM\Table(name: 'demerit')]
class ProductDemeritClaim
{
    #[ORM\ManyToOne(targetEntity: LocationById::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'factory', referencedColumnName: 'id', nullable: true)]
    public ?LocationById $factory = null;

    #[ORM\Column(name: 'product_type', type: 'string')]
    public string $productType = '';

    #[ORM\Column(name: 'model', type: 'string')]
    public string $productModel = '';

    #[ORM\Column(name: 'last_status', type: 'string')]
    public string $lastStatus = '';

    #[ORM\Column(name: 'status', type: 'string')]
    public string $status = '';

    #[ORM\Column(name: 'date', type: 'nullable_zero_date', nullable: true)]
    public ?\DateTimeInterface $openingDate = null;

    #[ORM\Column(name: 'date_closed', type: 'nullable_zero_date', nullable: true)]
    public ?\DateTimeInterface $closingDate = null;

    #[ORM\Column(name: 'date_suspended', type: 'nullable_zero_date', nullable: true)]
    public ?\DateTimeInterface $suspensionDate = null;

    #[ORM\Column(name: 'days_suspended', type: 'integer')]
    public int $daysSuspended = 0;

    #[ORM\Column(name: 'short_desc', type: 'string')]
    public string $shortDescription;

    #[ORM\Column(type: 'text')]
    public string $description;

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
    public int $finalFocusWeight = 0;

    #[ORM\ManyToOne(targetEntity: PeopleById::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'poster', referencedColumnName: 'id', nullable: true)]
    public ?PeopleById $poster = null;

    #[ORM\ManyToOne(targetEntity: PeopleById::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'initiator', referencedColumnName: 'id', nullable: true)]
    public ?PeopleById $initiator = null;

    #[ORM\ManyToOne(targetEntity: PeopleById::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'assignee', referencedColumnName: 'id', nullable: true)]
    public ?PeopleById $assignee = null;

    #[ORM\Column(name: 'verification_description', type: 'text', nullable: true)]
    public ?string $verificationDescription = null;

    #[ORM\Column(name: 'status_updated_at', type: 'nullable_zero_date', nullable: true)]
    public ?\DateTimeInterface $statusUpdatedAt = null;

    #[ORM\Column(name: 'is_ibs', type: 'boolean', nullable: true)]
    public ?bool $involvesIbs = false;

    #[ORM\Column(name: 'is_ihs', type: 'boolean', nullable: true)]
    public ?bool $involvesIhs = false;

    #[ORM\Column(name: 'is_link', type: 'boolean', nullable: true)]
    public ?bool $involvesLink = false;

    #[ORM\Column(name: 'is_ready_to_close', type: 'boolean', nullable: true)]
    public ?bool $readyToClose = false;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
