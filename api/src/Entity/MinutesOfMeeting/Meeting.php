<?php

declare(strict_types=1);

namespace App\Entity\MinutesOfMeeting;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
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
use App\Controller\MinutesOfMeeting\MeetingDuplicateController;
use App\Controller\MinutesOfMeeting\MeetingSummaryPdfController;
use App\Doctrine\Mapping\Attributes\Loggable;
use App\Doctrine\Mapping\Attributes\Transferable;
use App\Entity\ConfidentialInterface;
use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\People;
use App\Entity\Sales\Competitor;
use App\Entity\Sales\Customer;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Sales\ProductType;
use App\Entity\UpdatableStatusEntityInterface;
use App\Filter\MinutesOfMeeting\MyMeetingFilter;
use App\Filter\MinutesOfMeeting\MyTeamMeetingFilter;
use App\Filter\SimpleSearchFilter;
use App\Filter\SubscriberFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['meeting', 'customer_list', 'people_public']],
            forceEager: false,
        ),
        new Get(security: "is_granted('MEETING_READ_VOTER', object)"),
        new Get(
            uriTemplate: '/meetings/{id}/summary',
            formats: ['pdf' => 'application/pdf'],
            controller: MeetingSummaryPdfController::class,
            security: "is_granted('MEETING_READ_VOTER', object)",
            name: 'pdf_summary'
        ),
        new Get(
            uriTemplate: '/meetings/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: MeetingFile::class),
                'id' => new Link(fromClass: Meeting::class),
            ],
            defaults: ['parentProperty' => 'meeting', 'class' => MeetingFile::class],
            controller: DownloadController::class,
            security: "is_granted('MEETING_READ_VOTER', object)",
            name: 'download_meeting_file',
        ),
        new Put(security: "is_granted('MEETING_WRITE_VOTER', object)"),
        new Put(
            uriTemplate: '/meetings/{id}/status',
            denormalizationContext: ['groups' => ['meeting:update_status']],
            security: "is_granted('MEETING_WRITE_VOTER', object)",
            name: 'update_meeting_status',
        ),
        new Delete(security: "is_granted('MEETING_WRITE_VOTER', object)"),
        new Delete(
            uriTemplate: '/meetings/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: MeetingFile::class),
                'id' => new Link(fromClass: Meeting::class),
            ],
            defaults: ['parentProperty' => 'meeting', 'class' => MeetingFile::class],
            controller: DeleteController::class,
            security: "is_granted('MEETING_WRITE_VOTER', object)",
            name: 'delete_meeting_file',
        ),
        new Post(),
        new Post(
            uriTemplate: '/meetings/{id}/duplicate',
            controller: MeetingDuplicateController::class,
            validationContext: ['groups' => ['meeting_clone']],
            name: 'duplicate_meeting',
        ),
        new Post(
            uriTemplate: '/meetings/{id}/files',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            defaults: ['method' => 'getFiles', 'class' => MeetingFile::class],
            controller: UploadController::class,
            security: "is_granted('MEETING_WRITE_VOTER', object)",
            deserialize: false,
            name: 'upload_meeting_file',
        ),
    ],
    routePrefix: 'minutes_of_meeting',
    normalizationContext: ['groups' => ['meeting', 'meeting:detail', 'customer_list', 'people_public', 'competitor_public', 'catalogue_public', 'business_unit', 'file']],
    denormalizationContext: ['groups' => ['meeting:write']],
)]
#[ORM\Table(name: 'meetings')]
#[ApiFilter(SearchFilter::class, properties: ['status', 'customers', 'competitors', 'productTypes', 'businessUnits', 'createdBy', 'customerContacts'])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt' => 'DESC'])]
#[ApiFilter(SimpleSearchFilter::class, properties: ['title' => 'partial', 'description' => 'partial', 'customers.name' => 'partial', 'competitors.name' => 'partial', 'location' => 'partial'])]
#[ApiFilter(MyTeamMeetingFilter::class)]
#[ApiFilter(SubscriberFilter::class)]
#[ApiFilter(MyMeetingFilter::class)]
#[Loggable]
class Meeting implements UpdatableStatusEntityInterface, ConfidentialInterface
{
    /**
     * @var string
     */
    final public const OPEN = 'OPEN';
    /**
     * @var string
     */
    final public const RELEASED = 'RELEASED';
    /**
     * @var string
     */
    final public const CLOSED = 'CLOSED';

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['meeting'])]
    private ?int $id = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['meeting:detail', 'meeting:write'])]
    #[Assert\Type(type: 'boolean')]
    #[Assert\NotNull]
    private bool $confidential = false;

    #[ORM\Column(type: 'string', length: 8)]
    #[Groups(['meeting', 'meeting:detail', 'meeting:update_status'])]
    private string $status = self::OPEN;

    #[ORM\Column(type: 'string', length: 128)]
    #[Groups(['meeting', 'meeting:detail', 'meeting:write'])]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Length(max: 128)]
    private string $title;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['meeting:detail', 'meeting:write', 'meeting:duplicate'])]
    #[Assert\NotBlank(groups: ['Default', 'meeting_duplicate'])]
    private ?string $description = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['meeting:detail', 'meeting:write'])]
    private ?string $fullDescription = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['meeting:detail', 'meeting:write', 'meeting:duplicate'])]
    #[Assert\NotBlank(groups: ['Default', 'meeting_duplicate'])]
    #[Assert\Length(max: 64, groups: ['Default', 'meeting_duplicate'])]
    private ?string $location = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['meeting', 'meeting:detail', 'meeting:write'])]
    #[Assert\Type(type: 'boolean')]
    #[Assert\NotNull]
    private bool $quick = false;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(name: 'created_by')]
    #[Groups(['meeting', 'meeting:detail'])]
    #[Transferable(handler: 'meeting.poster')]
    #[Gedmo\Blameable(on: 'create')]
    private ?People $createdBy = null;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['meeting', 'meeting:detail'])]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['meeting:detail'])]
    #[Gedmo\Timestampable(on: 'change', field: 'status', value: [self::CLOSED])]
    private ?\DateTimeInterface $closedAt = null;

    /**
     * @var Collection<Competitor>
     */
    #[ApiProperty(fetchEager: false)]
    #[ORM\ManyToMany(targetEntity: 'App\Entity\Sales\Competitor')]
    #[Groups(['meeting:detail', 'meeting:write'])]
    private Collection $competitors;

    /**
     * @var Collection<Customer>
     */
    #[ApiProperty(fetchEager: false)]
    #[ORM\ManyToMany(targetEntity: 'App\Entity\Sales\Customer')]
    #[Groups(['meeting:detail', 'meeting:write'])]
    private Collection $customers;

    /**
     * @var Collection<ExtranetUser>
     */
    #[ApiProperty(fetchEager: false)]
    #[ORM\ManyToMany(targetEntity: 'App\Entity\Sales\ExtranetUser')]
    #[Groups(['meeting:detail', 'meeting:write'])]
    private Collection $customerContacts;

    /**
     * @var Collection<Contact>
     */
    #[ApiProperty(fetchEager: false)]
    #[ORM\OneToMany(mappedBy: 'meeting', targetEntity: 'App\Entity\MinutesOfMeeting\Contact', cascade: ['remove', 'persist'], orphanRemoval: true)]
    #[Groups(['meeting:detail', 'meeting:write'])]
    #[Assert\Valid]
    private Collection $contacts;

    /**
     * @var Collection<Action>
     */
    #[ApiProperty(fetchEager: false)]
    #[ORM\OneToMany(mappedBy: 'meeting', targetEntity: 'App\Entity\MinutesOfMeeting\Action', cascade: ['remove', 'persist'], orphanRemoval: true)]
    #[Groups(['meeting:detail'])]
    #[Assert\Valid]
    private Collection $actions;

    /**
     * @var Collection<ProductType>
     */
    #[ApiProperty(fetchEager: false)]
    #[ORM\ManyToMany(targetEntity: 'App\Entity\Sales\ProductType')]
    #[Groups(['meeting:detail', 'meeting:write'])]
    private Collection $productTypes;

    /**
     * @var Collection<MeetingFile>
     */
    #[ApiProperty(fetchEager: false)]
    #[ORM\OneToMany(mappedBy: 'meeting', targetEntity: 'App\Entity\MinutesOfMeeting\MeetingFile', cascade: ['remove', 'persist'], orphanRemoval: true)]
    #[Groups(['meeting:detail'])]
    private Collection $files;

    /**
     * @var Collection<BusinessUnit>
     */
    #[ORM\ManyToMany(targetEntity: 'App\Entity\Directory\BusinessUnit')]
    #[Groups(['meeting:detail', 'meeting:write'])]
    private Collection $businessUnits;

    /**
     * @var Collection<People>
     */
    #[ORM\ManyToMany(targetEntity: 'App\Entity\Directory\People')]
    #[Groups(['meeting:detail', 'meeting:write'])]
    private Collection $attendees;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotNull(groups: ['Default', 'meeting_duplicate'])]
    #[Groups(['meeting', 'meeting:detail', 'meeting:write', 'meeting:duplicate'])]
    private \DateTimeInterface $meetingDate;

    public function __construct()
    {
        $this->competitors = new ArrayCollection();
        $this->customers = new ArrayCollection();
        $this->customerContacts = new ArrayCollection();
        $this->contacts = new ArrayCollection();
        $this->actions = new ArrayCollection();
        $this->productTypes = new ArrayCollection();
        $this->files = new ArrayCollection();
        $this->businessUnits = new ArrayCollection();
        $this->attendees = new ArrayCollection();
    }

    public function reset(): void
    {
        $this->id = null;
        $this->createdAt = null;
        $this->createdBy = null;
        $this->closedAt = null;
        $this->status = self::OPEN;
        $this->files = new ArrayCollection();
        $this->actions = new ArrayCollection();
        $this->contacts = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function isConfidential(): bool
    {
        return $this->confidential;
    }

    public function setConfidential(bool $confidential): self
    {
        $this->confidential = $confidential;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function isQuick(): bool
    {
        return $this->quick;
    }

    public function setQuick(bool $quick): self
    {
        $this->quick = $quick;

        return $this;
    }

    public function getCreatedBy(): People
    {
        return $this->createdBy;
    }

    public function setCreatedBy(People $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
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

    public function getClosedAt(): ?\DateTimeInterface
    {
        return $this->closedAt;
    }

    public function setClosedAt(?\DateTime $closedAt): self
    {
        $this->closedAt = $closedAt;

        return $this;
    }

    /**
     * @return Collection<Competitor>
     */
    public function getCompetitors(): Collection
    {
        return $this->competitors;
    }

    public function addCompetitor(Competitor $competitor): self
    {
        $this->competitors->add($competitor);

        return $this;
    }

    public function removeCompetitor(Competitor $competitor): self
    {
        $this->competitors->removeElement($competitor);

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
        $this->customers->add($customer);

        return $this;
    }

    public function removeCustomer(Customer $customer): self
    {
        $this->customers->removeElement($customer);

        return $this;
    }

    /**
     * @return Collection<ExtranetUser>
     */
    public function getCustomerContacts(): Collection
    {
        return $this->customerContacts;
    }

    public function addCustomerContact(ExtranetUser $customerContact): self
    {
        $this->customerContacts->add($customerContact);

        return $this;
    }

    public function removeCustomerContact(ExtranetUser $customerContact): self
    {
        $this->customerContacts->removeElement($customerContact);

        return $this;
    }

    /**
     * @return Collection<Contact>
     */
    public function getContacts(): Collection
    {
        return $this->contacts;
    }

    public function addContact(Contact $contact): self
    {
        $contact->setMeeting($this);
        $this->contacts->add($contact);

        return $this;
    }

    public function removeContact(Contact $contact): self
    {
        $this->contacts->removeElement($contact);

        return $this;
    }

    /**
     * @return Collection<Action>
     */
    public function getActions(): Collection
    {
        return $this->actions;
    }

    public function addAction(Action $action): self
    {
        $action->setMeeting($this);
        $this->actions->add($action);

        return $this;
    }

    public function removeAction(Action $action): self
    {
        $this->actions->removeElement($action);

        return $this;
    }

    /**
     * @return Collection<ProductType>
     */
    public function getProductTypes(): Collection
    {
        return $this->productTypes;
    }

    public function addProductType(ProductType $productType): self
    {
        $this->productTypes->add($productType);

        return $this;
    }

    public function removeProductType(ProductType $productType): self
    {
        $this->productTypes->removeElement($productType);

        return $this;
    }

    public function getFiles()
    {
        return $this->files;
    }

    public function addFile(MeetingFile $file): self
    {
        $this->files->add($file);
        $file->setMeeting($this);

        return $this;
    }

    public function removeFile(MeetingFile $file): self
    {
        $this->files->removeElement($file);
        $file->setMeeting($this);

        return $this;
    }

    public function getBusinessUnits(): Collection
    {
        return $this->businessUnits;
    }

    public function addBusinessUnit(BusinessUnit $businessUnit): self
    {
        $this->businessUnits->add($businessUnit);

        return $this;
    }

    public function removeBusinessUnit(BusinessUnit $businessUnit): self
    {
        $this->businessUnits->removeElement($businessUnit);

        return $this;
    }

    public function getAttendees(): Collection
    {
        return $this->attendees;
    }

    public function addAttendee(People $attendee): self
    {
        $this->attendees->add($attendee);

        return $this;
    }

    public function removeAttendee(People $attendee): self
    {
        $this->attendees->removeElement($attendee);

        return $this;
    }

    public function getFullDescription(): ?string
    {
        return $this->fullDescription;
    }

    public function setFullDescription(?string $fullDescription): self
    {
        $this->fullDescription = $fullDescription;

        return $this;
    }

    public function getMeetingDate(): \DateTimeInterface
    {
        return $this->meetingDate;
    }

    public function setMeetingDate(\DateTimeInterface $meetingDate): self
    {
        $this->meetingDate = $meetingDate;

        return $this;
    }
}
