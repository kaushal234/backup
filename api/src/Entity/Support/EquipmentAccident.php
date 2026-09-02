<?php

declare(strict_types=1);

namespace App\Entity\Support;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use App\Controller\File\DownloadController;
use App\Controller\File\UploadController;
use App\Doctrine\Mapping\Attributes as App;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['equipment_accident']],
            security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_EXTRANET_USER')",
        ),
        new Post(
            uriTemplate: '/equipment_accidents/{id}/files',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            defaults: ['method' => 'getAccidentFiles', 'class' => AccidentFile::class],
            controller: UploadController::class,
            security: "is_granted('EQUIPMENT_END_USER_VOTER', object)",
            deserialize: false,
            name: 'upload_equipment_accident_file',
        ),
        new Get(security: "is_granted('EQUIPMENT_END_USER_VOTER', object)"),
        new Get(
            uriTemplate: '/equipment_accidents/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'accidentFiles', fromClass: AccidentFile::class),
                'id' => new Link(fromClass: EquipmentAccident::class),
            ],
            defaults: ['parentProperty' => 'accident', 'class' => AccidentFile::class],
            controller: DownloadController::class,
            security: "is_granted('EQUIPMENT_END_USER_VOTER', object)",
            name: 'download_equipment_accident_file',
        ),
    ],
    normalizationContext: ['groups' => ['equipment_accident_detail', 'file', 'expose_legacy', 'extranet_user_public']],
    denormalizationContext: ['groups' => ['equipment_accident_write']]
)]
#[ORM\Table(name: 'equipment_accidents')]
#[ApiFilter(SearchFilter::class, properties: ['followUpReport.equipmentRecord' => 'exact', 'followUpReport.equipmentRecord.serialNumber' => 'exact', 'followUpReport.equipmentRecord.legacyId' => 'exact', 'followUpReport.createdBy' => 'exact'])]
#[App\Loggable]
class EquipmentAccident
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['equipment_accident', 'equipment_accident_detail', 'equipment_follow_up_report_detail'])]
    private int $id;

    #[ORM\Column(name: 'accident_date', type: 'date', nullable: false)]
    #[Assert\NotNull]
    #[Groups(['equipment_accident', 'equipment_accident_detail', 'equipment_accident_write', 'equipment_follow_up_report_detail'])]
    private \DateTimeInterface $date;

    #[ORM\Column(name: 'comment', type: 'text', length: 65535)]
    #[Assert\NotBlank]
    #[Groups(['equipment_accident', 'equipment_accident_detail', 'equipment_accident_write', 'equipment_follow_up_report_detail'])]
    private string $comment;

    #[ORM\Column(name: 'human_injuries', type: 'boolean', nullable: false)]
    #[Assert\Type(type: 'boolean')]
    #[Assert\NotNull]
    #[Groups(['equipment_accident', 'equipment_accident_detail', 'equipment_accident_write', 'equipment_follow_up_report_detail'])]
    private bool $humanInjuries;

    #[ORM\Column(name: 'plane_damages', type: 'boolean', nullable: false)]
    #[Assert\Type(type: 'boolean')]
    #[Assert\NotNull]
    #[Groups(['equipment_accident', 'equipment_accident_detail', 'equipment_accident_write', 'equipment_follow_up_report_detail'])]
    private bool $planeDamages;

    #[ORM\Column(name: 'environment_damages', type: 'boolean', nullable: false)]
    #[Assert\Type(type: 'boolean')]
    #[Assert\NotNull]
    #[Groups(['equipment_accident', 'equipment_accident_detail', 'equipment_accident_write', 'equipment_follow_up_report_detail'])]
    private bool $environmentDamages;

    #[ORM\Column(name: 'equipment_damages', type: 'boolean', nullable: false)]
    #[Assert\Type(type: 'boolean')]
    #[Assert\NotNull]
    #[Groups(['equipment_accident', 'equipment_accident_detail', 'equipment_accident_write', 'equipment_follow_up_report_detail'])]
    private bool $equipmentDamages;

    /**
     * @var Collection<AccidentFile>
     */
    #[ORM\OneToMany(mappedBy: 'accident', targetEntity: 'App\Entity\Support\AccidentFile', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['equipment_accident_detail', 'equipment_follow_up_report_detail'])]
    private Collection $accidentFiles;

    #[ORM\OneToOne(mappedBy: 'accident', targetEntity: 'App\Entity\Support\EquipmentFollowUpReport')]
    #[Groups(['equipment_accident_detail'])]
    private ?EquipmentFollowUpReport $followUpReport = null;

    public function __construct()
    {
        $this->accidentFiles = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): self
    {
        $this->date = $date;

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

    public function isHumanInjuries(): bool
    {
        return $this->humanInjuries;
    }

    public function setHumanInjuries(bool $humanInjuries): self
    {
        $this->humanInjuries = $humanInjuries;

        return $this;
    }

    public function isPlaneDamages(): bool
    {
        return $this->planeDamages;
    }

    public function setPlaneDamages(bool $planeDamages): self
    {
        $this->planeDamages = $planeDamages;

        return $this;
    }

    public function isEnvironmentDamages(): bool
    {
        return $this->environmentDamages;
    }

    public function setEnvironmentDamages(bool $environmentDamages): self
    {
        $this->environmentDamages = $environmentDamages;

        return $this;
    }

    public function isEquipmentDamages(): bool
    {
        return $this->equipmentDamages;
    }

    public function setEquipmentDamages(bool $equipmentDamages): self
    {
        $this->equipmentDamages = $equipmentDamages;

        return $this;
    }

    public function getFollowUpReport(): EquipmentFollowUpReport
    {
        return $this->followUpReport;
    }

    public function setFollowUpReport(EquipmentFollowUpReport $followUpReport): self
    {
        $this->followUpReport = $followUpReport;

        return $this;
    }

    /**
     * @return Collection<AccidentFile>
     */
    public function getAccidentFiles(): Collection
    {
        return $this->accidentFiles;
    }

    public function addAccidentFile(AccidentFile $file): self
    {
        $this->accidentFiles[] = $file;
        $file->setAccident($this);

        return $this;
    }

    public function removeAccidentFile(AccidentFile $file): self
    {
        $this->accidentFiles->removeElement($file);

        return $this;
    }
}
