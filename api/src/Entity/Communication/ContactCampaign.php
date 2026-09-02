<?php

declare(strict_types=1);

namespace App\Entity\Communication;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Doctrine\Mapping\Attributes\Transferable;
use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\People;
use App\Entity\Sales\ExtranetUser;
use App\Filter\ColumnsFilter;
use App\Filter\SimpleSearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Blameable;
use Gedmo\Mapping\Annotation\Timestampable;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table]
#[ApiResource(
    operations: [
        new GetCollection(
            formats: ['jsonld', 'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']],
            normalizationContext: ['groups' => ['contact_campaign', 'people_public', 'file', 'people_photo', 'business_unit_detail']],
        ),
        new Get(),
        new Post(
            security: 'is_granted("FEATURE_CREATE_CONTACT_CAMPAIGN")',
        ),
        new Put(
            denormalizationContext: ['groups' => ['contact_campaign:update']],
            security: 'is_granted("FEATURE_UPDATE_CONTACT_CAMPAIGN")',
        ),
    ],
    normalizationContext: ['groups' => ['contact_campaign', 'people_public', 'people_photo', 'file', 'business_unit_detail', 'extranet_user_address_campaign', 'extranet_user_campaign', 'extranet_user_profile_campaign', 'customer_campaign', 'sales_representative_campaign', 'extranet_user_is_verified_campaign', 'extranet_user_id_campaign']],
    denormalizationContext: ['groups' => ['contact_campaign:create']]
)]
#[ApiFilter(OrderFilter::class, properties: ['id', 'name', 'startedAt', 'endedAt', 'status', 'owner.lastname'])]
#[ApiFilter(SearchFilter::class, properties: ['status', 'owner', 'businessUnits', 'contacts', 'contacts.extranetUserProfile.customer'])]
#[ApiFilter(DateFilter::class, properties: ['startedAt', 'endedAt', 'createdAt'])]
#[ApiFilter(ColumnsFilter::class)]
#[ApiFilter(SimpleSearchFilter::class, properties: [
    'id' => 'partial',
    'name' => 'partial',
    'status',
    'description' => 'partial',
    'owner.lastname' => 'partial',
    'owner.firstname' => 'partial',
    'businessUnits.name' => 'partial',
])]
#[ApiFilter(ColumnsFilter::class)]
class ContactCampaign
{
    final public const string DRAFT = 'DRAFT';
    final public const string ACTIVE = 'ACTIVE';
    final public const string CLOSED = 'CLOSED';

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['contact_campaign', 'contact_campaign:create', 'contact_campaign:update'])]
    public string $name;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['contact_campaign', 'contact_campaign:create', 'contact_campaign:update'])]
    public \DateTimeInterface $startedAt;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['contact_campaign', 'contact_campaign:create', 'contact_campaign:update'])]
    public \DateTimeInterface $endedAt;

    #[Assert\Choice(choices: [self::DRAFT, self::ACTIVE, self::CLOSED])]
    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['contact_campaign', 'contact_campaign:create', 'contact_campaign:update'])]
    public string $status = self::DRAFT;

    #[ORM\Column(type: 'text')]
    #[Groups(['contact_campaign', 'contact_campaign:create', 'contact_campaign:update'])]
    public string $description;

    #[Blameable(on: 'create')]
    #[ORM\ManyToOne(targetEntity: People::class)]
    #[ORM\JoinColumn(name: 'owner', referencedColumnName: 'id', nullable: false)]
    #[Groups(['contact_campaign', 'contact_campaign:update'])]
    #[Transferable(handler: 'handler.contact_campaign.owner')]
    public People $owner;

    #[Timestampable(on: 'create')]
    #[ORM\Column(type: 'datetime')]
    #[Groups(['contact_campaign'])]
    public \DateTimeInterface $createdAt;

    /**
     * @var Collection<ExtranetUser>
     */
    #[ORM\ManyToMany(targetEntity: ExtranetUser::class)]
    #[ORM\JoinTable(name: 'contact_campaign_extranet_user')]
    #[Groups(['contact_campaign', 'contact_campaign:create', 'contact_campaign:update'])]
    private Collection $contacts;

    /**
     * @var Collection<BusinessUnit>
     */
    #[ORM\ManyToMany(targetEntity: BusinessUnit::class)]
    #[ORM\JoinTable(name: 'contact_campaign_business_unit')]
    #[Groups(['contact_campaign', 'contact_campaign:create', 'contact_campaign:update'])]
    private Collection $businessUnits;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['contact_campaign'])]
    private ?int $id = null;

    public function __construct()
    {
        $this->businessUnits = new ArrayCollection();
        $this->contacts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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
        $this->businessUnits->removeElement($businessUnit);

        return $this;
    }

    /**
     * @return Collection<ExtranetUser>
     */
    public function getContacts(): Collection
    {
        return $this->contacts;
    }

    public function addContact(ExtranetUser $contact): self
    {
        if (!$this->contacts->contains($contact)) {
            $this->contacts->add($contact);
        }

        return $this;
    }

    public function removeContact(ExtranetUser $contact): self
    {
        $this->contacts->removeElement($contact);

        return $this;
    }
}
