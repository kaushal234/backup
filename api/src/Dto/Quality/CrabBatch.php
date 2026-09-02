<?php

declare(strict_types=1);

namespace App\Dto\Quality;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Controller\Quality\CrabBatchController;
use App\Entity\EquipmentRecord;
use App\Entity\Quality\Crab;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/quality/crabs/duplicate',
            controller: CrabBatchController::class,
            output: false,
            validate: false,
            name: 'duplicate',
        ),
    ],
    denormalizationContext: ['groups' => ['crab:write']]
)]
class CrabBatch
{
    #[Groups(['crab:write'])]
    public Crab $crab;

    #[Assert\Valid]
    #[Groups(['crab:write'])]
    private readonly ArrayCollection $equipmentRecords;

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
}
