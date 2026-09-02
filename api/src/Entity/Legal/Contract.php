<?php

declare(strict_types=1);

namespace App\Entity\Legal;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\File\DeleteController;
use App\Controller\File\DownloadController;
use App\Controller\File\UploadController;
use App\Doctrine\Mapping\Attributes as App;
use App\Doctrine\Mapping\Attributes\Exclude;
use App\Doctrine\Mapping\Attributes\Transferable;
use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\Division;
use App\Entity\Directory\People;
use App\Entity\Directory\Premise;
use App\Entity\Directory\Region;
use App\Entity\Finance\Currency;
use App\Entity\Sales\Customer;
use App\Filter\SimpleSearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Blameable;
use Gedmo\Mapping\Annotation\Timestampable;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity]
#[ORM\Table]
#[ApiResource(
    operations: [
        new Get(security: "is_granted('CONTRACT_READ_VOTER', object)"),
        new GetCollection(
            normalizationContext: ['groups' => ['contract', 'people_public', 'sub_category', 'category', 'file:light', 'currency', 'business_unit']],
        ),
        new Post(),
        new Put(
            security: "is_granted('CONTRACT_EDIT_VOTER', object)"
        ),
        new Get(
            uriTemplate: '/contracts/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: ContractFile::class),
                'id' => new Link(fromClass: Contract::class),
            ],
            defaults: ['parentProperty' => 'contract', 'class' => ContractFile::class],
            controller: DownloadController::class,
            security: "is_granted('CONTRACT_UPLOAD_DOWNLOAD_FILES', object)",
            name: 'download_contract_file',
        ),
        new Post(
            uriTemplate: '/contracts/{id}/files',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            defaults: ['method' => 'getFiles', 'class' => ContractFile::class],
            controller: UploadController::class,
            security: "is_granted('CONTRACT_UPLOAD_DOWNLOAD_FILES', object)",
            deserialize: false,
            name: 'upload_contract_file'
        ),
        new Put(
            uriTemplate: '/contracts/{id}/status',
            denormalizationContext: ['groups' => ['contract:update_status']],
            security: "is_granted('CONTRACT_EDIT_VOTER', object)",
            validationContext: ['groups' => ['contract:update_status']],
            name: 'update_contract_status'
        ),
        new Delete(
            uriTemplate: '/contracts/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: ContractFile::class),
                'id' => new Link(fromClass: Contract::class),
            ],
            defaults: ['parentProperty' => 'contract', 'class' => ContractFile::class],
            controller: DeleteController::class,
            security: "is_granted('CONTRACT_UPLOAD_DOWNLOAD_FILES', object)",
            name: 'delete_contract_file'
        ),
    ],
    normalizationContext: ['groups' => ['contract', 'contract:item', 'people_public', 'sub_category', 'category', 'file', 'currency', 'region_light', 'division', 'premise', 'business_unit', 'address', 'customer_list']],
    denormalizationContext: ['groups' => ['contract:write']]
)]
#[App\Loggable]
#[ApiFilter(OrderFilter::class, properties: ['id', 'value', 'renewalPeriod'])]
#[ApiFilter(BooleanFilter::class, properties: ['indefinitePeriodType'])]
#[ApiFilter(SearchFilter::class, properties: ['id', 'status', 'renewalUnit', 'shortDescription', 'externalParty', 'internalParty', 'createdBy', 'owner', 'jurisdiction', 'currency', 'subCategory', 'subCategory.category', 'parentContract', 'premises', 'divisions', 'regions', 'businessUnits'])]
#[ApiFilter(SimpleSearchFilter::class, properties: ['shortDescription' => 'partial', 'owner.lastname' => 'partial', 'subCategory.name' => 'partial', 'status', 'createdBy.lastname' => 'partial', 'externalParty' => 'partial', 'subCategory.category.name' => 'partial'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt' => 'exact', 'startDate' => 'exact', 'signatureDate' => 'exact', 'expirationDate' => 'exact'])]
class Contract
{
    final public const string ACTIVE = 'ACTIVE';
    final public const string EXPIRED = 'EXPIRED';
    final public const string ARCHIVED = 'ARCHIVED';
    final public const string DAY = 'DAY';
    final public const string MONTH = 'MONTH';
    final public const string YEAR = 'YEAR';

    #[ORM\Column(type: 'string')]
    #[Groups(groups: ['contract', 'contract:write'])]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    public string $shortDescription;

    #[ORM\Column(type: 'text')]
    #[Groups(groups: ['contract', 'contract:write'])]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    public string $description;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(groups: ['contract', 'contract:write'])]
    public ?string $observationValue = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(groups: ['contract', 'contract:write'])]
    public ?string $observationTerm = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(groups: ['contract', 'contract:update_status'])]
    #[Assert\NotNull(groups: ['contract:update_status'])]
    public ?string $observationStatus = null;

    #[ORM\Column(type: 'datetime')]
    #[Groups(groups: ['contract', 'contract:write'])]
    #[Timestampable(on: 'create')]
    public \DateTime $createdAt;

    #[ORM\Column(type: 'string')]
    #[Assert\Choice(choices: [self::ACTIVE, self::EXPIRED, self::ARCHIVED])]
    #[Groups(groups: ['contract', 'contract:write', 'contract:update_status'])]
    public string $status = self::ACTIVE;

    #[ORM\Column(type: 'datetime')]
    #[Groups(groups: ['contract', 'contract:write'])]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    public \DateTime $startDate;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(groups: ['contract', 'contract:write'])]
    public ?\DateTime $expirationDate = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(groups: ['contract:item', 'contract:write'])]
    public bool $indefinitePeriodType = false;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(groups: ['contract', 'contract:write'])]
    public ?int $renewalPeriod = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\Choice(choices: [self::DAY, self::MONTH, self::YEAR])]
    #[Groups(groups: ['contract', 'contract:write'])]
    public ?string $renewalUnit = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(groups: ['contract:item', 'contract:write'])]
    public bool $automaticRenewal = false;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(groups: ['contract', 'contract:write'])]
    public string $externalParty;

    #[ORM\Column(type: 'json', options: ['default' => '[]'])]
    #[Groups(groups: ['contract', 'contract:write'])]
    #[Assert\NotBlank]
    public array $internalParty = [];

    #[ORM\ManyToOne(targetEntity: People::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Transferable]
    #[Groups(groups: ['contract', 'contract:write'])]
    #[Blameable(on: 'create')]
    public People $createdBy;

    #[ORM\ManyToOne(targetEntity: People::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Transferable]
    #[Groups(groups: ['contract', 'contract:write'])]
    #[Blameable(on: 'create')]
    public People $owner;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(groups: ['contract', 'contract:write'])]
    public ?int $value = null;

    #[ORM\ManyToOne(targetEntity: Currency::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(groups: ['contract', 'contract:write'])]
    public ?Currency $currency = null;

    #[ORM\ManyToOne(targetEntity: SubCategory::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(groups: ['contract', 'contract:write'])]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    public SubCategory $subCategory;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(groups: ['contract', 'contract:write'])]
    public ?string $jurisdiction = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'childContracts')]
    #[Groups(groups: ['contract:item', 'contract:write'])]
    public ?Contract $parentContract = null;

    #[ORM\Column(type: 'json', options: ['default' => '[]'])]
    #[Groups(groups: ['contract', 'contract:write'])]
    public array $otherPartySignatories = [];

    #[ORM\Column(type: 'boolean')]
    #[Groups(groups: ['contract:item', 'contract:write'])]
    public bool $confidential = false;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(groups: ['contract:item', 'contract:write'])]
    public ?string $comment = null;

    /**
     * @var Collection<Division>
     */
    #[ORM\ManyToMany(targetEntity: Division::class)]
    #[Groups(groups: ['contract', 'contract:write'])]
    private Collection $divisions;

    /**
     * @var Collection<BusinessUnit>
     */
    #[ORM\ManyToMany(targetEntity: BusinessUnit::class)]
    #[Assert\Count(min: 1)]
    #[Groups(groups: ['contract', 'contract:write'])]
    private Collection $businessUnits;

    /**
     * @var Collection<Region>
     */
    #[ORM\ManyToMany(targetEntity: Region::class)]
    #[Groups(groups: ['contract', 'contract:write'])]
    private Collection $regions;

    /**
     * @var Collection<Premise>
     */
    #[ORM\ManyToMany(targetEntity: Premise::class)]
    #[Groups(groups: ['contract', 'contract:write'])]
    private Collection $premises;

    /**
     * @var Collection<Contract>
     */
    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: 'siblingTos')]
    #[ORM\JoinTable(name: 'contract_siblings')]
    #[ORM\JoinColumn(name: 'contract_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'contract_sibling_id', referencedColumnName: 'id')]
    #[MaxDepth(1)]
    #[Exclude]
    #[Groups(groups: ['contract:item', 'contract:write'])]
    private Collection $siblingFroms;

    /**
     * @var Collection<Contract>
     */
    #[ORM\ManyToMany(targetEntity: self::class, mappedBy: 'siblingFroms')]
    #[Groups(groups: ['contract:item', 'contract:write'])]
    #[MaxDepth(1)]
    #[Exclude]
    private Collection $siblingTos;

    /**
     * @var Collection<People>
     */
    #[ORM\ManyToMany(targetEntity: People::class)]
    #[Groups(groups: ['contract', 'contract:write'])]
    private Collection $alvestSignatories;

    /**
     * @var Collection<Contract>
     */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'parentContract')]
    #[MaxDepth(maxDepth: 1)]
    #[Groups(groups: ['contract:item', 'contract:write'])]
    private Collection $childContracts;

    /**
     * @var Collection<ContractFile>
     */
    #[ORM\OneToMany(targetEntity: ContractFile::class, mappedBy: 'contract', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(groups: ['contract:item', 'contract:write'])]
    private Collection $files;

    /**
     * @var Collection<Customer>
     */
    #[Groups(groups: ['contract', 'contract:write'])]
    #[ORM\JoinTable(name: 'contracts_customers')]
    #[ORM\JoinColumn(name: 'contract_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'customer_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: Customer::class)]
    private Collection $customers;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(groups: ['contract'])]
    private int $id;

    public function __construct()
    {
        $this->siblingTos = new ArrayCollection();
        $this->siblingFroms = new ArrayCollection();
        $this->alvestSignatories = new ArrayCollection();
        $this->childContracts = new ArrayCollection();
        $this->files = new ArrayCollection();
        $this->premises = new ArrayCollection();
        $this->businessUnits = new ArrayCollection();
        $this->regions = new ArrayCollection();
        $this->divisions = new ArrayCollection();
        $this->customers = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getSiblingsTos(): Collection
    {
        return $this->siblingTos;
    }

    public function addSiblingTo(self $sibling): self
    {
        if (!$this->siblingTos->contains($sibling)) {
            $this->siblingTos->add($sibling);

            $sibling->siblingFroms->add($this);
        }

        return $this;
    }

    public function removeSiblingTo(self $sibling): self
    {
        if ($this->siblingTos->contains($sibling)) {
            $this->siblingTos->removeElement($sibling);
        }

        return $this;
    }

    public function getAlvestSignatories(): Collection
    {
        return $this->alvestSignatories;
    }

    public function addAlvestSignatory(People $alvestSignatory): self
    {
        if (!$this->alvestSignatories->contains($alvestSignatory)) {
            $this->alvestSignatories->add($alvestSignatory);
        }

        return $this;
    }

    public function removeAlvestSignatory(People $alvestSignatory): self
    {
        if ($this->alvestSignatories->contains($alvestSignatory)) {
            $this->alvestSignatories->removeElement($alvestSignatory);
        }

        return $this;
    }

    public function getChildContracts(): Collection
    {
        return $this->childContracts;
    }

    public function addChildContract(self $contract): self
    {
        if (!$this->childContracts->contains($contract)) {
            $this->childContracts->add($contract);
        }

        return $this;
    }

    public function removeChildContract(self $contract): self
    {
        if ($this->childContracts->contains($contract)) {
            $this->childContracts->removeElement($contract);
        }

        return $this;
    }

    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(ContractFile $file): self
    {
        if (!$this->files->contains($file)) {
            $this->files->add($file);
            $file->contract = $this;
        }

        return $this;
    }

    public function removeFile(ContractFile $file): self
    {
        if ($this->files->contains($file)) {
            $this->files->removeElement($file);
        }

        return $this;
    }

    public function getPremises(): Collection
    {
        return $this->premises;
    }

    public function addPremise(Premise $premise): self
    {
        if (!$this->premises->contains($premise)) {
            $this->premises->add($premise);
        }

        return $this;
    }

    public function removePremise(Premise $premise): self
    {
        if ($this->premises->contains($premise)) {
            $this->premises->removeElement($premise);
        }

        return $this;
    }

    /**
     * @return Collection<Customer>
     */
    public function getCustomers(): Collection
    {
        return $this->customers;
    }

    public function addCustomer(Customer $customer): self
    {
        if (!$this->customers->contains($customer)) {
            $this->customers->add($customer);
        }

        return $this;
    }

    public function removeCustomer(Customer $customer): self
    {
        if ($this->customers->contains($customer)) {
            $this->customers->removeElement($customer);
        }

        return $this;
    }

    /**
     * @return Collection<BusinessUnit>
     */
    public function getBusinessUnits(): Collection
    {
        return $this->businessUnits;
    }

    public function addBusinessUnit(BusinessUnit $businessUnit): self
    {
        if (!$this->businessUnits->contains($businessUnit)) {
            $this->businessUnits->add($businessUnit);
        }

        return $this;
    }

    public function removeBusinessUnit(BusinessUnit $businessUnit): self
    {
        if ($this->businessUnits->contains($businessUnit)) {
            $this->businessUnits->removeElement($businessUnit);
        }

        return $this;
    }

    public function getDivisions(): Collection
    {
        return $this->divisions;
    }

    public function addDivision(Division $division): self
    {
        if (!$this->divisions->contains($division)) {
            $this->divisions->add($division);
        }

        return $this;
    }

    public function removeDivision(Division $division): self
    {
        if ($this->divisions->contains($division)) {
            $this->divisions->removeElement($division);
        }

        return $this;
    }

    public function getRegions(): Collection
    {
        return $this->regions;
    }

    public function addRegion(Region $region): self
    {
        if (!$this->regions->contains($region)) {
            $this->regions->add($region);
        }

        return $this;
    }

    public function removeRegion(Region $region): self
    {
        if ($this->regions->contains($region)) {
            $this->regions->removeElement($region);
        }

        return $this;
    }

    public function getEffectiveExpirationDate(): ?\DateTimeInterface
    {
        if (null === $this->expirationDate) {
            return null;
        }

        if (!$this->automaticRenewal || null === $this->renewalUnit || null === $this->renewalPeriod || $this->renewalPeriod <= 0) {
            return $this->expirationDate;
        }

        $modifier = match ($this->renewalUnit) {
            self::DAY => \sprintf('+%d days', $this->renewalPeriod),
            self::MONTH => \sprintf('+%d months', $this->renewalPeriod),
            self::YEAR => \sprintf('+%d years', $this->renewalPeriod),
            default => null,
        };

        if (null === $modifier) {
            return $this->expirationDate;
        }

        return \DateTimeImmutable::createFromInterface($this->expirationDate)->modify($modifier);
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context): void
    {
        if (!isset($this->subCategory)) {
            return;
        }

        if (
            Category::CUSTOMERS === $this->subCategory->category->name
            && $this->customers->isEmpty()
        ) {
            $context
                ->buildViolation('Customers must not be empty.')
                ->atPath('customers')
                ->addViolation();
        }
    }
}
