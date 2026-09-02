<?php

declare(strict_types=1);

namespace App\Entity\Support;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Doctrine\Mapping\Attributes as App;
use App\Doctrine\Mapping\Attributes\Exclude;
use App\Entity\EquipmentRecord;
use App\Entity\Sales\ExtranetUser;
use App\Validator\Constraints\EquipmentHourmeterTotalizer;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: 'App\Repository\Support\EquipmentFollowUpReportRepository')]
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['equipment_follow_up_report', 'unit_operational_status_detail', 'expose_legacy', 'people_public', 'extranet_user_public']],
            security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_EXTRANET_USER')",
        ),
        new Post(securityPostDenormalize: "is_granted('EQUIPMENT_END_USER_VOTER', object)"),
        new Get(security: "is_granted('EQUIPMENT_END_USER_VOTER', object)"),
        new Put(
            denormalizationContext: ['groups' => ['equipment_follow_up_report_edit', 'equipment_accident_write', 'equipment_maintenance_write']],
            securityPostDenormalize: "is_granted('EQUIPMENT_END_USER_VOTER', object)",
        ),
    ],
    normalizationContext: ['groups' => ['equipment_follow_up_report_detail', 'equipment_record_detail', 'equipment_serial', 'component', 'manual_public', 'people_public', 'unit_operational_status_detail', 'expose_legacy', 'extranet_user_public', 'file']],
    denormalizationContext: ['groups' => ['equipment_follow_up_report_write', 'equipment_accident_write', 'equipment_maintenance_write']],
    security: "is_granted('ACCESS_EXTRANET_USER')",
)]
#[ORM\Table(name: 'equipment_follow_up_reports')]
#[ApiFilter(OrderFilter::class, properties: ['hourmeterDate', 'createdAt', 'equipmentRecord.serialNumber'])]
#[ApiFilter(SearchFilter::class, properties: ['createdBy' => 'exact', 'equipmentRecord' => 'exact', 'equipmentRecord.serialNumber' => 'exact', 'equipmentRecord.legacyId' => 'exact'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt', 'updatedAt', 'hourmeterDate'])]
#[App\Loggable]
#[EquipmentHourmeterTotalizer(errorPath: 'hourmeter')]
class EquipmentFollowUpReport
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['equipment_follow_up_report', 'equipment_follow_up_report_detail'])]
    private int $id;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    #[Groups(['equipment_follow_up_report', 'equipment_follow_up_report_detail'])]
    #[Exclude]
    #[Gedmo\Timestampable(on: 'create')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime')]
    #[Groups(['equipment_follow_up_report_detail'])]
    #[Exclude]
    #[Gedmo\Timestampable]
    private \DateTimeInterface $updatedAt;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\ExtranetUser')]
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id', nullable: false)]
    #[Groups(['equipment_follow_up_report', 'equipment_follow_up_report_detail'])]
    #[Gedmo\Blameable(on: 'create')]
    private ExtranetUser $createdBy;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\ExtranetUser')]
    #[ORM\JoinColumn(name: 'updated_by', referencedColumnName: 'id', nullable: true)]
    #[Groups(['equipment_follow_up_report_detail'])]
    #[Gedmo\Blameable(on: 'update')]
    private ?ExtranetUser $updatedBy = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\EquipmentRecord')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['equipment_follow_up_report', 'equipment_follow_up_report_detail', 'equipment_follow_up_report_write'])]
    private EquipmentRecord $equipmentRecord;

    #[ORM\Column(name: 'comment', type: 'text', length: 65535)]
    #[Assert\NotBlank]
    #[Groups(['equipment_follow_up_report_detail', 'equipment_follow_up_report_write', 'equipment_follow_up_report_edit'])]
    private string $comment;

    #[ORM\Column(name: 'hourmeter', type: 'integer', nullable: false)]
    #[Assert\Type(type: 'int')]
    #[Assert\Range(min: 0)]
    #[Groups(['equipment_follow_up_report', 'equipment_follow_up_report_detail', 'equipment_follow_up_report_write', 'equipment_follow_up_report_edit'])]
    private int $hourmeter;

    #[ORM\Column(name: 'hourmeter_totalizer', type: 'integer', nullable: false)]
    #[Assert\Type(type: 'int')]
    #[Assert\Range(min: 0)]
    #[Groups(['equipment_follow_up_report', 'equipment_follow_up_report_detail'])]
    private int $hourmeterTotalizer;

    #[ORM\Column(name: 'hourmeter_date', type: 'date', nullable: false)]
    #[Assert\NotNull]
    #[Assert\LessThanOrEqual('midnight tomorrow')]
    #[Assert\GreaterThanOrEqual('-90 days midnight')]
    #[Groups(['equipment_follow_up_report', 'equipment_follow_up_report_detail', 'equipment_follow_up_report_write', 'equipment_follow_up_report_edit'])]
    private \DateTimeInterface $hourmeterDate;

    #[ORM\Column(name: 'odometer', type: 'integer', nullable: true)]
    #[Assert\Type(type: 'int')]
    #[Assert\AtLeastOneOf([new Assert\Blank(), new Assert\Length(min: 0)])]
    #[Groups(['equipment_follow_up_report_detail', 'equipment_follow_up_report_write', 'equipment_follow_up_report_edit'])]
    private ?int $odometer = null;

    #[ORM\Column(name: 'odometer_date', type: 'date', nullable: true)]
    #[Groups(['equipment_follow_up_report_detail', 'equipment_follow_up_report_write', 'equipment_follow_up_report_edit'])]
    private ?\DateTimeInterface $odometerDate = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Support\UnitOperationalStatus')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['equipment_follow_up_report', 'equipment_follow_up_report_detail', 'equipment_follow_up_report_write', 'equipment_follow_up_report_edit'])]
    private UnitOperationalStatus $operationalStatus;

    #[ORM\OneToOne(inversedBy: 'followUpReport', targetEntity: 'App\Entity\Support\EquipmentAccident', cascade: ['persist'], orphanRemoval: true)]
    #[Assert\Valid]
    #[ORM\JoinColumn(name: 'equipment_accident_id', referencedColumnName: 'id', nullable: true)]
    #[Groups(['equipment_follow_up_report', 'equipment_follow_up_report_detail', 'equipment_follow_up_report_write', 'equipment_follow_up_report_edit'])]
    private ?EquipmentAccident $accident = null;

    #[ORM\OneToOne(inversedBy: 'followUpReport', targetEntity: 'App\Entity\Support\EquipmentMaintenance', cascade: ['persist'], orphanRemoval: true)]
    #[ORM\JoinColumn(name: 'equipment_maintenance_id', referencedColumnName: 'id', nullable: true)]
    #[Assert\Valid]
    #[Groups(['equipment_follow_up_report', 'equipment_follow_up_report_detail', 'equipment_follow_up_report_write', 'equipment_follow_up_report_edit', 'equipment_maintenance'])]
    private ?EquipmentMaintenance $maintenance = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getCreatedBy(): ExtranetUser
    {
        return $this->createdBy;
    }

    public function setCreatedBy(ExtranetUser $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getUpdatedBy(): ?ExtranetUser
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?ExtranetUser $updatedBy): self
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    public function getEquipmentRecord(): EquipmentRecord
    {
        return $this->equipmentRecord;
    }

    public function setEquipmentRecord(EquipmentRecord $equipmentRecord): self
    {
        $this->equipmentRecord = $equipmentRecord;

        return $this;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getHourmeter(): int
    {
        return $this->hourmeter;
    }

    public function setHourmeter(int $hourmeter): self
    {
        $this->hourmeter = $hourmeter;

        return $this;
    }

    public function getHourmeterTotalizer(): int
    {
        return $this->hourmeterTotalizer;
    }

    public function setHourmeterTotalizer(int $hourmeterTotalizer): self
    {
        $this->hourmeterTotalizer = $hourmeterTotalizer;

        return $this;
    }

    public function getHourmeterDate(): \DateTimeInterface
    {
        return $this->hourmeterDate;
    }

    public function setHourmeterDate(\DateTime $hourmeterDate): self
    {
        $this->hourmeterDate = $hourmeterDate;

        return $this;
    }

    public function getOdometer(): ?int
    {
        return $this->odometer;
    }

    public function setOdometer(?int $odometer): self
    {
        $this->odometer = $odometer;

        return $this;
    }

    public function getOdometerDate(): ?\DateTimeInterface
    {
        return $this->odometerDate;
    }

    public function setOdometerDate(?\DateTime $odometerDate): self
    {
        $this->odometerDate = $odometerDate;

        return $this;
    }

    public function getOperationalStatus(): UnitOperationalStatus
    {
        return $this->operationalStatus;
    }

    public function setOperationalStatus(UnitOperationalStatus $operationalStatus): self
    {
        $this->operationalStatus = $operationalStatus;

        return $this;
    }

    public function getAccident(): ?EquipmentAccident
    {
        return $this->accident;
    }

    public function setAccident(?EquipmentAccident $accident): self
    {
        $this->accident = $accident;

        return $this;
    }

    public function getMaintenance(): ?EquipmentMaintenance
    {
        return $this->maintenance;
    }

    public function setMaintenance(?EquipmentMaintenance $maintenance): self
    {
        $this->maintenance = $maintenance;

        return $this;
    }
}
