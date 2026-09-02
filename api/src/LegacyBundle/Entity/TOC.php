<?php

declare(strict_types=1);

namespace LegacyBundle\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Entity\Directory\LocationById;
use LegacyBundle\Entity\Directory\PeopleById;

#[ApiResource(
    operations: [
        new Get(),
    ],
)]
#[ORM\Entity(readOnly: true)]
#[ORM\Table(name: 'toc')]
class TOC
{
    #[ORM\Column(name: 'parent_id')]
    public int $parentId = 0;

    #[ORM\ManyToOne(targetEntity: PeopleById::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'postid', referencedColumnName: 'id', nullable: true)]
    public ?PeopleById $poster = null;

    #[ORM\ManyToOne(targetEntity: LocationById::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'ssoid', referencedColumnName: 'id', nullable: true)]
    public ?LocationById $serviceOrganizationLocation = null;

    #[ORM\Column(name: 'dt', type: 'datetime')]
    public \DateTimeInterface $createdAt;

    #[ORM\Column(name: 'dt_closed', type: 'nullable_zero_date')]
    public ?\DateTimeInterface $closedAt = null;

    #[ORM\Column(name: 'status')]
    public string $status;

    #[ORM\ManyToOne(targetEntity: PeopleById::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'tecid', referencedColumnName: 'id', nullable: true)]
    public ?PeopleById $technician = null;

    #[ORM\ManyToOne(targetEntity: PeopleById::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'assid', referencedColumnName: 'id', nullable: true)]
    public ?PeopleById $assignee = null;

    #[ORM\ManyToOne(targetEntity: PeopleById::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'cuid', referencedColumnName: 'id', nullable: true)]
    public ?PeopleById $customer = null;

    #[ORM\ManyToOne(targetEntity: PeopleById::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'conid', referencedColumnName: 'id', nullable: true)]
    public ?PeopleById $customerContact = null;

    #[ORM\ManyToOne(targetEntity: EquipmentRecord::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'erid', referencedColumnName: 'id', nullable: true)]
    public ?EquipmentRecord $equipmentRecord = null;

    #[ORM\Column(name: 'hours')]
    public int $hours = 0;

    #[ORM\Column(name: 'short_desc')]
    public string $shortDescription;

    #[ORM\Column(name: 'prob_dsca', type: 'text')]
    public string $description;

    #[ORM\Column(name: 'disp_tec')]
    public string $dispatchedTechnicianFlag = '';

    #[ORM\Column(name: 'ifactor')]
    public string $importanceFactor = '';

    #[ORM\Column(name: 'wfactor')]
    public float $weightFactor = 0.0;

    #[ORM\Column(name: 'est_hours')]
    public int $estimatedHours = 0;

    #[ORM\Column(name: 'third_party')]
    public string $thirdPartyFlag = '';

    #[ORM\Column(name: 'apc')]
    public string $airportCode = '';

    #[ORM\Column(name: 'activity_type')]
    public string $activityType = '';

    #[ORM\Column(name: 'notification')]
    public string $notificationFlag = '';

    #[ORM\Column(name: 'unit_operation_status')]
    public string $unitOperationStatus = '';

    #[ORM\Column(name: 'action_module')]
    public string $furtherActionModule = '';

    #[ORM\Column(name: 'action_ref')]
    public int $furtherActionReference = 0;

    #[ORM\Column(name: 'survey_work')]
    public string $surveyWorkRating = '';

    #[ORM\Column(name: 'survey_responsiveness')]
    public string $surveyResponsivenessRating = '';

    #[ORM\Column(name: 'survey_communication')]
    public string $surveyCommunicationRating = '';

    #[ORM\Column(name: 'survey_attitude')]
    public string $surveyAttitudeRating = '';

    #[ORM\Column(name: 'survey_comment', type: 'text')]
    public string $surveyComment = '';

    #[ORM\Column(name: 'factory_support_flag', type: 'boolean')]
    public bool $isFactorySupportRequired = false;

    #[ORM\Column(name: 'parts_notification_at', type: 'nullable_zero_date', nullable: true)]
    public ?\DateTimeInterface $partsNotifiedAt = null;

    #[ORM\Column(name: 'parts_received_at', type: 'nullable_zero_date', nullable: true)]
    public ?\DateTimeInterface $partsReceivedAt = null;

    #[ORM\Column(name: 'ast_arrived_at', type: 'nullable_zero_date', nullable: true)]
    public ?\DateTimeInterface $technicianArrivedAt = null;

    #[ORM\Column(name: 'ast_left_at', type: 'nullable_zero_date', nullable: true)]
    public ?\DateTimeInterface $technicianLeftAt = null;

    #[ORM\Column(name: 'factory_support_required_at', type: 'nullable_zero_date', nullable: true)]
    public ?\DateTimeInterface $factorySupportRequiredAt = null;

    #[ORM\Column(name: 'factory_support_given_at', type: 'nullable_zero_date', nullable: true)]
    public ?\DateTimeInterface $factorySupportGivenAt = null;

    #[ORM\Column(name: 'tld_notification', type: 'boolean', nullable: true)]
    public ?bool $tldNotificationEnabled = null;

    #[ORM\Column(name: 'error_codes', nullable: true)]
    public ?string $errorCodes = null;

    #[ORM\Column(name: 'toc_type', nullable: true)]
    public ?string $type = null;

    #[ORM\Column(name: 'is_ibs', type: 'boolean', nullable: true)]
    public ?bool $involvesIbs = null;

    #[ORM\Column(name: 'is_link', type: 'boolean', nullable: true)]
    public ?bool $involvesLink = null;

    #[ORM\Column(name: 'warranty_id', nullable: true)]
    public ?int $warrantyId = null;

    #[ORM\Column(name: 'parts_added', type: 'boolean', nullable: true)]
    public ?bool $partsAdded = null;

    #[ORM\Column(name: 'metadata', type: 'text', nullable: true)]
    public ?string $metadata = null;

    #[ORM\Column(name: 'is_ihs', type: 'boolean', nullable: true)]
    public ?bool $involvesIhs = null;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
