<?php

declare(strict_types=1);

namespace App\Entity\Sales;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\AbstractInspection;
use App\Entity\EquipmentRecord;
use App\Validator\Constraints as AppAssert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(
            security: "is_granted('FEATURE_PRE_DELIVERY_INSPECTION_WRITE') or is_granted('MOO_ER')",
            name: 'pre_delivery_inspection_create',
        ),
        new Put(
            denormalizationContext: ['groups' => ['inspection:write']],
            security: "is_granted('FEATURE_PRE_DELIVERY_INSPECTION_WRITE') or is_granted('MOO_ER')",
            name: 'pre_delivery_inspection_edit',
        ),
        new Put(
            uriTemplate: '/pre_delivery_inspections/{id}/status',
            normalizationContext: ['groups' => ['inspection', 'inspection:status_update']],
            denormalizationContext: ['groups' => ['inspection:status_update']],
            security: "is_granted('FEATURE_PRE_DELIVERY_INSPECTION_WRITE') or is_granted('MOO_ER')",
            name: 'update_pre_delivery_inspection_status',
        ),
    ],
    normalizationContext: ['groups' => ['inspection', 'odp:view']],
    denormalizationContext: ['groups' => ['inspection:write', 'odp:write']]
)]
#[AppAssert\UniqueOpenInspection]
class PreDeliveryInspection extends AbstractInspection
{
    #[ORM\ManyToOne(targetEntity: EquipmentRecord::class, inversedBy: 'preDeliveryInspections')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['inspection', 'inspection:write'])]
    private ?EquipmentRecord $equipmentRecord = null;

    #[Groups(['odp:view', 'odp:write'])]
    private ?\DateTimeInterface $plannedAt = null;

    public function getEquipmentRecord(): ?EquipmentRecord
    {
        return $this->equipmentRecord;
    }

    public function setEquipmentRecord(?EquipmentRecord $equipmentRecord): self
    {
        $this->equipmentRecord = $equipmentRecord;

        return $this;
    }
}
