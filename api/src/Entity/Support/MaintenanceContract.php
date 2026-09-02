<?php

declare(strict_types=1);

namespace App\Entity\Support;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Doctrine\Mapping\Attributes as App;
use App\Doctrine\Mapping\Attributes\Exclude;
use App\Entity\Directory\People;
use App\Entity\EquipmentRecord;
use App\Entity\Sales\Customer;
use App\Entity\Sales\ExtranetUser;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['maintenance_contract_detail', 'people_public', 'extranet_user_public', 'expose_legacy']]),
        new Post(security: "is_granted('FEATURE_MAINTENANCE_CONTRACT_WRITE')"),
        new Get(security: "is_granted('ACCESS_PEOPLE') or user in object.getEndUserRepresentatives().toArray() or user in object.getBuyerRepresentatives().toArray()"),
        new Put(
            denormalizationContext: ['groups' => ['maintenance_contract_edit']],
            security: "is_granted('FEATURE_MAINTENANCE_CONTRACT_WRITE')",
        ),
    ],
    normalizationContext: ['groups' => ['maintenance_contract_detail', 'equipment_record_detail', 'equipment_serial', 'component', 'manual_public', 'people_public', 'extranet_user_public', 'expose_legacy']],
    denormalizationContext: ['groups' => ['maintenance_contract_write']],
    security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_EXTRANET_USER')",
)]
#[ORM\Table(name: 'maintenance_contracts')]
#[ApiFilter(DateFilter::class, properties: ['startDate', 'expirationDate'])]
#[ApiFilter(SearchFilter::class, properties: ['description' => 'partial', 'buyer' => 'exact', 'endUser' => 'exact', 'equipmentRecords' => 'exact', 'equipmentRecords.serialNumber' => 'exact', 'equipmentRecords.legacyId' => 'exact', 'endUserRepresentatives' => 'exact'])]
#[App\Loggable]
class MaintenanceContract
{
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['maintenance_contract', 'maintenance_contract_detail'])]
    private int $id;

    #[ORM\Column(name: 'created_at', type: 'datetime', nullable: false)]
    #[Groups(['maintenance_contract', 'maintenance_contract_detail'])]
    #[Exclude]
    #[Gedmo\Timestampable(on: 'create')]
    private \DateTime $createdAt;

    #[ORM\Column(name: 'start_date', type: 'date', nullable: false)]
    #[Groups(['maintenance_contract', 'maintenance_contract_detail', 'maintenance_contract_write', 'maintenance_contract_edit'])]
    #[Assert\NotNull]
    private \DateTime $startDate;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\Customer')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['maintenance_contract', 'maintenance_contract_detail', 'maintenance_contract_write'])]
    private Customer $endUser;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\Customer')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['maintenance_contract', 'maintenance_contract_detail', 'maintenance_contract_write'])]
    private Customer $buyer;

    #[ORM\Column(name: 'expiration_date', type: 'date', nullable: false)]
    #[Groups(['maintenance_contract', 'maintenance_contract_detail', 'maintenance_contract_write', 'maintenance_contract_edit'])]
    #[Assert\NotNull]
    private \DateTime $expirationDate;

    /**
     * @var EquipmentRecord[]|ArrayCollection
     */
    #[ORM\ManyToMany(targetEntity: 'App\Entity\EquipmentRecord', inversedBy: 'contracts')]
    #[ORM\JoinTable(name: 'maintenance_contracts_equipment_records')]
    #[Assert\Count(min: 1)]
    #[Groups(['maintenance_contract', 'maintenance_contract_detail', 'maintenance_contract_write', 'maintenance_contract_edit'])]
    private Collection $equipmentRecords;

    /**
     * @var ExtranetUser[]|ArrayCollection
     */
    #[ORM\ManyToMany(targetEntity: 'App\Entity\Sales\ExtranetUser')]
    #[ORM\JoinTable(name: 'maintenance_contracts_user_xus')]
    #[Assert\Count(min: 1)]
    #[Groups(['maintenance_contract_user_list', 'maintenance_contract_write', 'maintenance_contract_edit'])]
    private Collection $endUserRepresentatives;

    /**
     * @var ExtranetUser[]|ArrayCollection
     */
    #[ORM\ManyToMany(targetEntity: 'App\Entity\Sales\ExtranetUser')]
    #[ORM\JoinTable(name: 'maintenance_contracts_buyer_xus')]
    #[Assert\Count(min: 1)]
    #[Groups(['maintenance_contract_user_list', 'maintenance_contract_write', 'maintenance_contract_edit'])]
    private Collection $buyerRepresentatives;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[Groups(['maintenance_contract', 'maintenance_contract_detail', 'maintenance_contract_write', 'maintenance_contract_edit'])]
    private ?People $representative = null;

    #[ORM\Column(name: 'description', type: 'string')]
    #[Assert\NotBlank]
    #[Groups(['maintenance_contract_detail', 'maintenance_contract_write', 'maintenance_contract_edit'])]
    private string $description;

    public function __construct()
    {
        $this->equipmentRecords = new ArrayCollection();
        $this->endUserRepresentatives = new ArrayCollection();
        $this->buyerRepresentatives = new ArrayCollection();
    }

    public function getStartDate(): \DateTime
    {
        return $this->startDate;
    }

    /**
     * @return $this
     */
    public function setStartDate(\DateTime $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getExpirationDate(): \DateTime
    {
        return $this->expirationDate;
    }

    /**
     * @return $this
     */
    public function setExpirationDate(\DateTime $expirationDate): self
    {
        $this->expirationDate = $expirationDate;

        return $this;
    }

    public function getRepresentative(): People
    {
        return $this->representative;
    }

    /**
     * @return $this
     */
    public function setRepresentative(People $representative): self
    {
        $this->representative = $representative;

        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @return $this
     */
    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return EquipmentRecord[]|ArrayCollection
     */
    public function getEquipmentRecords(): Collection
    {
        return $this->equipmentRecords;
    }

    /**
     * @return $this
     */
    public function addEquipmentRecord(EquipmentRecord $equipmentRecord): self
    {
        $this->equipmentRecords[] = $equipmentRecord;

        return $this;
    }

    /**
     * @return $this
     */
    public function removeEquipmentRecord(EquipmentRecord $equipmentRecord): self
    {
        $this->equipmentRecords->removeElement($equipmentRecord);

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return $this
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return ExtranetUser[]|ArrayCollection
     */
    public function getEndUserRepresentatives(): Collection
    {
        return $this->endUserRepresentatives;
    }

    /**
     * @return $this
     */
    public function addEndUserRepresentative(ExtranetUser $extranetUser): self
    {
        $this->endUserRepresentatives[] = $extranetUser;

        return $this;
    }

    /**
     * @return $this
     */
    public function removeEndUserRepresentative(ExtranetUser $extranetUser): self
    {
        $this->endUserRepresentatives->removeElement($extranetUser);

        return $this;
    }

    /**
     * @return ExtranetUser[]|ArrayCollection
     */
    public function getBuyerRepresentatives(): Collection
    {
        return $this->buyerRepresentatives;
    }

    /**
     * @return $this
     */
    public function addBuyerRepresentative(ExtranetUser $extranetUser): self
    {
        $this->buyerRepresentatives[] = $extranetUser;

        return $this;
    }

    /**
     * @return $this
     */
    public function removeBuyerRepresentative(ExtranetUser $extranetUser): self
    {
        $this->buyerRepresentatives->removeElement($extranetUser);

        return $this;
    }

    public function getEndUser(): Customer
    {
        return $this->endUser;
    }

    /**
     * @return $this
     */
    public function setEndUser(Customer $endUser): self
    {
        $this->endUser = $endUser;

        return $this;
    }

    public function getBuyer(): Customer
    {
        return $this->buyer;
    }

    /**
     * @return $this
     */
    public function setBuyer(Customer $buyer): self
    {
        $this->buyer = $buyer;

        return $this;
    }

    public function isActive(): bool
    {
        $now = new \DateTime();

        return ($this->startDate <= $now) && ($now <= $this->expirationDate);
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context)
    {
        $buyer = $this->getBuyer();
        $endUser = $this->getEndUser();

        foreach ($this->getEquipmentRecords() as $equipmentRecord) {
            if ($buyer !== $equipmentRecord->getBuyer()) {
                $context
                    ->buildViolation(\sprintf("Equipment S/N %s does belongs to '%s' and not to '%s'.", $equipmentRecord->getSerialNumber(), $equipmentRecord->getBuyer()->getName(), $buyer->getName()))
                    ->atPath('equipmentRecords')
                    ->addViolation()
                ;
            }
            if ($endUser !== $equipmentRecord->getEndUser()) {
                $context
                    ->buildViolation(\sprintf("Equipment S/N %s is used by '%s' but by '%s'", $equipmentRecord->getSerialNumber(), $equipmentRecord->getEndUser()->getName(), $endUser->getName()))
                    ->atPath('equipmentRecords')
                    ->addViolation()
                ;
            }
        }
    }
}
