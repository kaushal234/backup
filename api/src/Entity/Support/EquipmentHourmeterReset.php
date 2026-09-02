<?php

declare(strict_types=1);

namespace App\Entity\Support;

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
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: 'App\Repository\Support\EquipmentHourmeterResetRepository')]
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['equipment_hourmeter_reset', 'expose_legacy', 'people_public', 'extranet_user_public']],
            security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_EXTRANET_USER')",
        ),
        new Post(securityPostDenormalize: "is_granted('EQUIPMENT_END_USER_VOTER', object)"),
        new Get(security: "is_granted('EQUIPMENT_END_USER_VOTER', object)"),
        new Put(securityPostDenormalize: "is_granted('EQUIPMENT_END_USER_VOTER', object)"),
    ],
    normalizationContext: ['groups' => ['equipment_hourmeter_reset_detail', 'expose_legacy', 'extranet_user_public', 'file']],
    denormalizationContext: ['groups' => ['equipment_hourmeter_reset_write']],
    security: "is_granted('ACCESS_EXTRANET_USER')",
)]
#[ORM\Table(name: 'equipment_hourmeter_resets')]
#[ApiFilter(SearchFilter::class, properties: ['createdBy' => 'exact', 'equipmentRecord' => 'exact', 'equipmentRecord.serialNumber' => 'exact', 'equipmentRecord.legacyId' => 'exact'])]
#[App\Loggable]
class EquipmentHourmeterReset
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['equipment_hourmeter_reset', 'equipment_hourmeter_reset_detail'])]
    private int $id;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    #[Groups(['equipment_hourmeter_reset', 'equipment_hourmeter_reset_detail'])]
    #[Exclude]
    #[Gedmo\Timestampable(on: 'create')]
    private \DateTimeInterface $createdAt;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\ExtranetUser')]
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id', nullable: false)]
    #[Groups(['extranet_user_public'])]
    #[Gedmo\Blameable(on: 'create')]
    private ExtranetUser $createdBy;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\EquipmentRecord')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['equipment_hourmeter_reset', 'equipment_hourmeter_reset_detail', 'equipment_hourmeter_reset_write'])]
    private EquipmentRecord $equipmentRecord;

    #[ORM\Column(name: 'comment', type: 'text', length: 65535)]
    #[Assert\NotBlank]
    #[Groups(['equipment_hourmeter_reset', 'equipment_hourmeter_reset_detail', 'equipment_hourmeter_reset_write'])]
    private string $comment;

    #[ORM\Column(name: 'hourmeter', type: 'integer', nullable: false)]
    #[Assert\Type(type: 'int')]
    #[Assert\Range(min: 0)]
    #[Groups(['equipment_hourmeter_reset', 'equipment_hourmeter_reset_detail', 'equipment_hourmeter_reset_write'])]
    private int $hourmeter;

    #[ORM\Column(name: 'hourmeter_date', type: 'date', nullable: false)]
    #[Assert\NotNull]
    #[Assert\LessThanOrEqual('midnight tomorrow')]
    #[Assert\GreaterThanOrEqual('-90 days midnight')]
    #[Groups(['equipment_hourmeter_reset', 'equipment_hourmeter_reset_detail', 'equipment_hourmeter_reset_write'])]
    private \DateTimeInterface $hourmeterDate;

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

    public function getCreatedBy(): ExtranetUser
    {
        return $this->createdBy;
    }

    public function setCreatedBy(ExtranetUser $createdBy): self
    {
        $this->createdBy = $createdBy;

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

    public function getHourmeterDate(): \DateTimeInterface
    {
        return $this->hourmeterDate;
    }

    public function setHourmeterDate(\DateTime $hourmeterDate): self
    {
        $this->hourmeterDate = $hourmeterDate;

        return $this;
    }
}
