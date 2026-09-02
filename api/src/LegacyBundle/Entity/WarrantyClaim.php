<?php

declare(strict_types=1);

namespace LegacyBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Repository\WarrantyClaimRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: WarrantyClaimRepository::class, readOnly: true)]
#[ORM\Table(name: 'warranty')]
class WarrantyClaim
{
    public const PENDING = 'PENDING';
    public const REJECTED = 'REJECTED';

    public const TO_BE_FILTERED = 'TO BE FILTERED';
    public const FILTERED = 'FILTERED';

    #[ORM\ManyToOne(targetEntity: EquipmentRecord::class)]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id')]
    public EquipmentRecord $equipmentRecord;

    #[ORM\Column(name: 'problem_desc', type: Types::TEXT)]
    public string $description;

    #[ORM\Column(name: 'er_operation_status', length: 3)]
    #[Assert\Choice(callback: [UnitOperationalStatusChoices::class, 'names'])]
    public string $unitOperationalStatus;

    #[ORM\Column(name: 'warranty_status', length: 20)]
    public string $status;

    #[ORM\Column(name: 'filtering_flag', length: 60)]
    public string $filteringFlag;

    #[ORM\Column(name: 'entered_by', length: 80)]
    public string $createdBy;

    #[ORM\Column(name: 'claim_date', type: Types::DATETIME_IMMUTABLE)]
    public \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'warranty_details', type: Types::TEXT)]
    public string $details;

    #[ORM\Column(name: 'customer_name', length: 80)]
    public string $customerName;

    #[ORM\Column(name: 'equipment_location', type: Types::TEXT)]
    public string $location;

    #[ORM\Column(length: 50)]
    public string $type;

    #[ORM\Column]
    public string $model;

    #[ORM\Column(name: 'man_location', length: 30)]
    public string $factory;

    #[ORM\Column(name: 'sales_org', length: 30)]
    public string $salesOrganisation;

    #[ORM\Column(name: 'serial_number', length: 30)]
    public string $serialNumber;

    #[ORM\Column(type: Types::INTEGER)]
    public int $hours;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
