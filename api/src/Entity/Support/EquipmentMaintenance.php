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
use App\Entity\EquipmentRecord;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['equipment_maintenance']],
            security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_EXTRANET_USER')",
        ),
        new Get(security: "is_granted('EQUIPMENT_END_USER_VOTER', object)"),
        new Get(
            uriTemplate: '/equipment_maintenances/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'maintenanceFiles', fromClass: MaintenanceFile::class),
                'id' => new Link(fromClass: EquipmentMaintenance::class),
            ],
            defaults: ['parentProperty' => 'maintenance', 'class' => MaintenanceFile::class],
            controller: DownloadController::class,
            security: "is_granted('EQUIPMENT_END_USER_VOTER', object)",
            name: 'download_equipment_maintenance_file',
        ),
        new Post(
            uriTemplate: '/equipment_maintenances/{id}/files',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            defaults: ['method' => 'getMaintenanceFiles', 'class' => MaintenanceFile::class],
            controller: UploadController::class,
            security: "is_granted('EQUIPMENT_END_USER_VOTER', object)",
            deserialize: false,
            name: 'upload_equipment_maintenance_file',
        ),
    ],
    normalizationContext: ['groups' => ['equipment_maintenance_detail', 'file', 'expose_legacy', 'extranet_user_public']],
    denormalizationContext: ['groups' => ['equipment_maintenance_write']]
)]
#[ORM\Table(name: 'equipment_maintenances')]
#[ApiFilter(SearchFilter::class, properties: ['followUpReport.equipmentRecord' => 'exact', 'followUpReport.equipmentRecord.serialNumber' => 'exact', 'followUpReport.equipmentRecord.legacyId' => 'exact', 'followUpReport.createdBy' => 'exact'])]
#[App\Loggable]
class EquipmentMaintenance
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['equipment_maintenance', 'equipment_maintenance_detail', 'equipment_follow_up_report_detail'])]
    private int $id;

    #[ORM\Column(name: 'maitenance_date', type: 'date', nullable: false)]
    #[Groups(['equipment_maintenance', 'equipment_maintenance_detail', 'equipment_maintenance_write', 'equipment_follow_up_report_detail'])]
    private \DateTimeInterface $date;

    #[ORM\Column(name: 'comment', type: 'text', length: 65535)]
    #[Groups(['equipment_maintenance', 'equipment_maintenance_detail', 'equipment_maintenance_write', 'equipment_follow_up_report_detail'])]
    #[Assert\NotBlank]
    private string $comment;

    #[ORM\Column(type: 'integer', nullable: false)]
    #[Assert\Type(type: 'integer')]
    #[Assert\Choice(choices: [500, 1000, 2000], strict: true)]
    #[Groups(['equipment_maintenance', 'equipment_maintenance_detail', 'equipment_maintenance_write', 'equipment_follow_up_report_detail'])]
    private int $type;

    /**
     * @var Collection<MaintenanceFile>
     */
    #[ORM\OneToMany(mappedBy: 'maintenance', targetEntity: 'App\Entity\Support\MaintenanceFile', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['equipment_maintenance_detail', 'equipment_follow_up_report_detail'])]
    private Collection $maintenanceFiles;

    #[ORM\OneToOne(mappedBy: 'maintenance', targetEntity: 'App\Entity\Support\EquipmentFollowUpReport')]
    #[Groups(['equipment_maintenance_detail'])]
    private ?EquipmentFollowUpReport $followUpReport = null;

    public function __construct()
    {
        $this->maintenanceFiles = new ArrayCollection();
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

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

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
     * @return Collection<MaintenanceFile>
     */
    public function getMaintenanceFiles(): Collection
    {
        return $this->maintenanceFiles;
    }

    public function addMaintenanceFile(MaintenanceFile $file): self
    {
        $this->maintenanceFiles[] = $file;
        $file->setMaintenance($this);

        return $this;
    }

    public function removeMaintenanceFile(MaintenanceFile $file): self
    {
        $this->maintenanceFiles->removeElement($file);

        return $this;
    }

    public function getEquipmentRecord(): EquipmentRecord
    {
        return $this->getFollowUpReport()->getEquipmentRecord();
    }
}
