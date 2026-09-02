<?php

declare(strict_types=1);

namespace App\Entity\Support\EquipmentRecord;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Directory\Location;
use App\Entity\EquipmentRecord;
use App\Repository\Support\EstimatedGreenTagQuantityReportRepository;
use App\Validator\Constraints\Location as ValidLocation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\InverseJoinColumn;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EstimatedGreenTagQuantityReportRepository::class)]
#[ORM\Table]
#[ApiFilter(DateFilter::class, properties: ['day'])]
#[ApiFilter(SearchFilter::class, properties: ['id', 'manufacturerLocation', 'equipmentRecords.combinationMode', 'equipmentRecords.product.family'])]
#[ApiResource(
    operations: [
        new Get(
        ),
        new GetCollection(
            normalizationContext: ['groups' => ['estimated_green_tag_quantity_report', 'green_tag_report']],
        ),
    ],
    normalizationContext: ['groups' => ['estimated_green_tag_quantity_report']]
)]
class EstimatedGreenTagQuantityReport
{
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Groups(['estimated_green_tag_quantity_report'])]
    #[ORM\Column(type: 'datetime')]
    public \DateTimeInterface $day;

    #[ValidLocation(factory: true)]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Groups(['estimated_green_tag_quantity_report'])]
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    public Location $manufacturerLocation;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[Groups(['estimated_green_tag_quantity_report'])]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    /**
     * @var Collection<EquipmentRecord>
     */
    #[Groups(['estimated_green_tag_quantity_report'])]
    #[JoinTable(name: 'estimated_green_tag_quantity_reports_equipment_records')]
    #[JoinColumn(name: 'estimated_green_tag_quantity_report_id', referencedColumnName: 'id')]
    #[InverseJoinColumn(name: 'equipment_record_id', referencedColumnName: 'id')]
    #[ManyToMany(targetEntity: 'App\Entity\EquipmentRecord')]
    private Collection $equipmentRecords;

    public function __construct()
    {
        $this->equipmentRecords = new ArrayCollection();
    }

    /**
     * @return Collection<EquipmentRecord>
     */
    public function getEquipmentRecords(): Collection
    {
        return $this->equipmentRecords;
    }

    public function addEquipmentRecord(EquipmentRecord $equipmentRecord): self
    {
        if (!$this->equipmentRecords->contains($equipmentRecord)) {
            $this->equipmentRecords->add($equipmentRecord);
        }

        return $this;
    }

    public function removeEquipmentRecord(EquipmentRecord $equipmentRecord): self
    {
        $this->equipmentRecords->removeElement($equipmentRecord);

        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }
}
