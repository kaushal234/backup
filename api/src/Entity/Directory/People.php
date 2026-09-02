<?php

declare(strict_types=1);

namespace App\Entity\Directory;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\ExistsFilter;
use ApiPlatform\Doctrine\Orm\Filter\NumericFilter;
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
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\Serializer\Filter\GroupFilter;
use App\Controller\Directory\AddAclController;
use App\Controller\Directory\ImportAclController;
use App\Controller\Directory\PeopleAlvestAuthController;
use App\Controller\Directory\PeoplePictureController;
use App\Controller\Directory\PeopleSageAuthController;
use App\Controller\File\DeleteController;
use App\Controller\File\DownloadController;
use App\Controller\File\UploadController;
use App\Controller\PeopleTransferController;
use App\DataProvider\Directory\RandomPeopleProvider;
use App\Doctrine\Mapping\Attributes as App;
use App\Doctrine\Mapping\Attributes\Transferable;
use App\Doctrine\ORM\Extension\PeopleExtension;
use App\Entity\AddressWithCountry;
use App\Entity\Common\Airport;
use App\Entity\PhoneInterface;
use App\Entity\SurveyTargetInterface;
use App\Entity\User;
use App\Filter\Directory\CategorizedPeopleFilter;
use App\Filter\Directory\ExcludeGroupFilter;
use App\Filter\Directory\OrPeopleGroupsFilter;
use App\Filter\Directory\PeopleCurrentlyOrFutureDisabledFilter;
use App\Filter\Directory\PeopleCurrentlyOrFutureEnabledFilter;
use App\Filter\Directory\PeopleWithOngoingUpdateTasksFilter;
use App\Filter\Directory\PositionCategoryFilter;
use App\Filter\SimpleSearchFilter;
use App\Security\JWT\PayloadGenerator\PayloadGeneratorInterface;
use App\Serializer\Filter\ContextFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\CollectionHandler\PhoneCollectionHandler;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\DateTimeToString;
use LegacyBundle\Doctrine\Transformer\ObjectToProperty;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use LegacyBundle\Entity\LegacyIdInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: 'App\Repository\Directory\PeopleRepository')]
#[UniqueEntity(fields: ['username'], message: 'This value is already used by another Intranet User.')]
#[UniqueEntity(fields: ['email'], message: 'This value is already used by another Intranet User.')]
#[ApiResource(
    operations: [
        new Post(
            denormalizationContext: ['groups' => ['user_write', 'people_write', 'phone:write', 'address_write', 'people_contract:create', 'user_identity_write']],
            security: "is_granted('FEATURE_PEOPLE_WRITE')",
            validationContext: ['groups' => ['Default', 'password']],
        ),
        new Post(
            uriTemplate: '/token-sage',
            controller: PeopleSageAuthController::class,
            security: "is_granted('PUBLIC_ACCESS')",
            deserialize: false,
            name: 'sage_oauth',
        ),
        new Post(
            uriTemplate: '/token-alvest',
            controller: PeopleAlvestAuthController::class,
            security: "is_granted('PUBLIC_ACCESS')",
            deserialize: false,
            name: 'alvest_oauth',
        ),
        new Post(
            uriTemplate: '/people/{id}/photo',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            defaults: ['method' => 'getPhoto', 'class' => PeopleFile::class],
            controller: UploadController::class,
            security: "is_granted('FEATURE_PEOPLE_UPDATE_VOTER', object)",
            deserialize: false,
            name: 'upload_people_photo',
        ),
        new Post(
            uriTemplate: '/people/{id}/import_acl',
            controller: ImportAclController::class,
            security: "is_granted('FEATURE_ACL_WRITE')",
            deserialize: false,
            name: 'import_acl',
        ),
        new Post(
            uriTemplate: '/people/{id}/add_acl',
            controller: AddAclController::class,
            security: "is_granted('FEATURE_PEOPLE_ADD_ACL_VOTER', object)",
            deserialize: false,
            name: 'add_acl',
        ),
        new GetCollection(
            formats: ['jsonld', 'json'],
            openapi: true,
            normalizationContext: ['groups' => ['user', 'people', 'expose_legacy', 'file:light', 'business_unit_public']],
            security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_VENDOR_USER') or is_granted('AUTHORIZED_APPLICATION_FEATURE_PEOPLE_READ')",
        ),
        new GetCollection(
            uriTemplate: '/people/download_directory',
            formats: ['jsonld', 'csv' => ['text/csv']],
            normalizationContext: ['groups' => ['user', 'people', 'expose_legacy', 'file:light', 'business_unit_public']],
            security: "is_granted('FEATURE_DOWNLOAD_DIRECTORY') or is_granted('FEATURE_DOWNLOAD_DIRECTORY_CONFIDENTIAL') or is_granted('AUTHORIZED_APPLICATION_FEATURE_DOWNLOAD_DIRECTORY')",
        ),
        new GetCollection(
            uriTemplate: '/buyers',
            normalizationContext: ['groups' => ['user', 'people', 'expose_legacy', 'file:light', 'business_unit_public', 'people:buyer', 'department', 'phone', 'business_unit', 'premise']],
            security: "is_granted('ACCESS_VENDOR_USER')",
            name: PeopleExtension::BUYERS_OPERATION_NAME,
        ),
        new GetCollection(
            uriTemplate: '/people/search',
            description: 'Quick search people, useful for autocomplete.',
            normalizationContext: ['groups' => ['people:search']],
            security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_VENDOR_USER') or is_granted('AUTHORIZED_APPLICATION_FEATURE_PEOPLE_READ')",
            name: 'people_search',
        ),
        new Get(
            requirements: ['id' => '[1-9]\d*'],
            openapi: true,
            security: "is_granted('ACCESS_PEOPLE') or is_granted('AUTHORIZED_APPLICATION_FEATURE_PEOPLE_ITEM_READ')"
        ),
        new Get(
            uriTemplate: '/people/random',
            name: 'get_random_people',
            provider: RandomPeopleProvider::class
        ),
        new Get(
            uriTemplate: 'public/people/{id}/photo/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: PeopleFile::class),
                'id' => new Link(fromClass: People::class),
            ],
            controller: PeoplePictureController::class,
            security: "is_granted('PUBLIC_ACCESS')",
            name: 'download_public_photo',
        ),
        new Get(
            uriTemplate: '/people/{id}/photo/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: PeopleFile::class, description: 'File ID (integer)'),
                'id' => new Link(fromClass: People::class, description: 'User ID (integer)'),
            ],
            defaults: ['parentProperty' => 'people', 'class' => PeopleFile::class],
            controller: DownloadController::class,
            openapi: new Operation(
                summary: 'Get user photo',
            ),
            security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_VENDOR_USER') or is_granted('ACCESS_EXTRANET_USER')",
            name: 'download_people_photo'
        ),
        new Put(
            security: "is_granted('FEATURE_PEOPLE_UPDATE_VOTER', object)",
            validationContext: ['groups' => ['Default', 'User', 'password']],
        ),
        new Put(
            uriTemplate: '/people/{id}/update_planned_disable_date',
            denormalizationContext: ['groups' => ['people_planned_disable_at_write']],
            security: "is_granted('FEATURE_PEOPLE_UPDATE_VOTER', object) or object.getSupervisor() == user",
            name: 'update_planned_disable_at',
        ),
        new Put(
            uriTemplate: '/people/{id}/transfer',
            controller: PeopleTransferController::class,
            security: "is_granted('FEATURE_PEOPLE_UPDATE')",
            deserialize: false,
            name: 'transfer_people',
        ),
        new Delete(),
        new Delete(
            uriTemplate: '/people/{id}/photo/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: PeopleFile::class),
                'id' => new Link(fromClass: People::class),
            ],
            defaults: ['parentProperty' => 'people', 'class' => PeopleFile::class],
            controller: DeleteController::class,
            security: "is_granted('FEATURE_PEOPLE_UPDATE_VOTER', object)",
            name: 'delete_people_photo',
        ),
    ],
    normalizationContext: ['groups' => ['user', 'people', 'people_detail', 'expose_legacy', 'user_detail', 'file:light', 'tag', 'premise:detail', 'iata_code', 'support_team']],
    denormalizationContext: ['groups' => ['user_write', 'people_write', 'phone:write', 'address_write']],
    extraProperties: [
        PhoneCollectionHandler::ATTRIBUTE_KEY => [
            Phone::TYPE_RECEPTION => 'phone',
            Phone::TYPE_PHONE => 'direct_phone',
            Phone::TYPE_FAX => 'fax',
            Phone::TYPE_MOBILE => 'mobile',
            Phone::TYPE_HOME => 'home_phone',
        ],
    ],
)]
#[ApiFilter(DateFilter::class, properties: ['createdAt', 'disabledAt', 'enableAt', 'plannedDisableAt'])]
#[ApiFilter(BooleanFilter::class)]
#[ApiFilter(OrderFilter::class, properties: ['firstname' => 'ASC', 'lastname' => 'ASC', 'id', 'enableAt', 'businessUnit.name', 'position.description', 'disabledAt', 'premise.supportTeam.name', 'plannedDisableAt', 'premise.name'])]
#[ApiFilter(NumericFilter::class, properties: ['acls.location.erp'])]
#[ApiFilter(SimpleSearchFilter::class, properties: ['email' => 'partial', 'firstname' => 'partial', 'lastname' => 'partial', 'nickname' => 'partial', 'department.name' => 'partial', 'jobTitle' => 'partial', 'position.code', 'premise.supportTeam.name' => 'partial'])]
#[ApiFilter(SearchFilter::class, properties: [
    'id',
    'email',
    'username',
    'legacyId',
    'position',
    'position.code',
    'gender',
    'supervisor',
    'acls.group',
    'acls.group.name',
    'acls.group.features',
    'acls.group.features.name',
    'acls.location',
    'acls.location.name',
    'businessUnit',
    'businessUnit.location',
    'businessUnit.region',
    'businessUnit.region.subDivision',
    'businessUnit.region.subDivision.division',
    'legalEntity',
    'legalEntity.name',
    'department',
    'businessUnit.location.network',
    'erpIdentifier',
    'contractType',
    'premise',
    'mentor',
    'lastname',
    'firstname',
    'closestAirport',
    'premise.supportTeam',
])]
#[ApiFilter(GroupFilter::class, id: 'override', arguments: ['parameterName' => 'normalization_groups_override', 'overrideDefaultGroups' => true, 'whitelist' => ['people_list', 'people:export', 'expose_legacy', 'region:list', 'map_premise_people', 'premise:detail', 'airport_list']])]
#[ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'normalization_groups', 'overrideDefaultGroups' => false, 'whitelist' => ['people_detail', 'group_member', 'expose_legacy', 'people:division', 'linked_account', 'update_task:read', 'module', 'application', 'premise', 'support_team']])]
#[ApiFilter(CategorizedPeopleFilter::class)]
#[ApiFilter(PeopleCurrentlyOrFutureEnabledFilter::class)]
#[ApiFilter(PeopleCurrentlyOrFutureDisabledFilter::class)]
#[ApiFilter(PeopleWithOngoingUpdateTasksFilter::class)]
#[ApiFilter(PositionCategoryFilter::class)]
#[ApiFilter(ContextFilter::class)]
#[ApiFilter(ExcludeGroupFilter::class)]
#[ApiFilter(OrPeopleGroupsFilter::class)]
#[ApiFilter(ExistsFilter::class, properties: ['mentor', 'premise'])]
#[App\Loggable]
#[Legacy\Synchronize(table: 'people')]
class People extends User implements \Stringable, PhoneInterface, LegacyIdInterface, SurveyTargetInterface
{
    use LegacyIdentifierTrait;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Common\Airport')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['people_detail', 'people_write', 'people:export', 'map_premise_people'])]
    public ?Airport $closestAirport = null;

    #[ApiProperty(iris: ['https://schema.org/email'])]
    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Email(mode: 'strict')]
    #[Assert\Length(max: 255)]
    #[Groups(['people:alternate_email', 'people_write', 'user:me'])]
    protected ?string $alternateEmail = null;

    #[Assert\Type(type: 'string')]
    #[Groups(['people_detail', 'people_write', 'user:me', 'user_partial_write', 'training_attendee:reports'])]
    #[Legacy\Column(column: 'baan_employee_id')]
    protected ?string $erpIdentifier = null;

    #[Groups(['people', 'people_write', 'people_list', 'group_member', 'user:me', 'training_attendee:reports', 'people:export', 'map_premise_people', PayloadGeneratorInterface::PAYLOAD_NORMALIZATION_GROUP, 'region:list', 'people:business_unit'])]
    #[Legacy\Column(column: 'bu_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    #[Legacy\Column(column: 'div_id', transformer: ObjectToProperty::class, options: ['property' => 'region.legacyId'])]
    protected ?BusinessUnit $businessUnit = null;

    #[Groups(['people_detail', 'people_write', 'people_list', 'group_member', 'user:me', 'people:export', 'map_premise_people'])]
    #[Legacy\Column(column: 'fct_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    protected ?Position $position = null;

    #[Groups(['people_detail', 'people_write', 'people'])]
    #[Legacy\Column(column: 'enable_at', transformer: DateTimeToString::class, options: ['format' => 'Y-m-d'])]
    protected ?\DateTimeInterface $enableAt = null;

    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 50)]
    #[Groups(['people', 'people_write', 'user:me', 'people:export'])]
    #[Legacy\Column(column: 'nickname')]
    private ?string $nickname = null;

    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[ORM\Column(name: 'job_title', length: 255, nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 255)]
    #[Groups(['people', 'people_write', 'people_list', 'user:me', 'people:export', 'people:search'])]
    #[Legacy\Column(column: 'title')]
    private ?string $jobTitle = null;

    /**
     * @var Collection<Phone>
     */
    #[ORM\ManyToMany(targetEntity: 'App\Entity\Directory\Phone', inversedBy: 'people', cascade: ['all'], orphanRemoval: true)]
    #[Assert\Valid]
    #[Groups(['people_detail', 'people_write', 'user:me', 'people:export', 'people:buyer'])]
    private Collection $phones;

    /**
     * @var Collection<PeopleFile>
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Directory\PeopleFile', mappedBy: 'people', cascade: ['persist'], orphanRemoval: true)]
    private Collection $files;

    #[ApiProperty(iris: ['https://schema.org/PostalAddress'])]
    #[ORM\Embedded(class: '\App\Entity\AddressWithCountry')]
    #[Assert\Valid]
    #[Groups(['people_detail', 'people_write', 'user:me', 'people:export'])]
    #[Legacy\Column(column: 'address', options: ['embeddedFields' => ['address.street1', 'address.street2', 'address.town', 'address.postalCode', 'address.city', 'address.state', 'address.country']])]
    private AddressWithCountry $address;

    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[ORM\Column(length: 8, nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 8)]
    #[Groups(['people_detail', 'people_write', 'user:me'])]
    #[Legacy\Column(column: 'baan_id')]
    private ?string $erpLogin = null;

    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[ORM\Column(length: 40, nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 40)]
    #[Groups(['people_detail', 'people_write', 'user:me'])]
    #[Legacy\Column(column: 'windows_id')]
    private ?string $windowsLogin = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\BusinessUnit')]
    #[ORM\JoinColumn(name: 'legal_entity_id')]
    #[Groups(['people', 'people_write', 'user:me', 'people:export'])]
    private ?BusinessUnit $legalEntity = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Department')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['people_detail', 'people_write', 'user:me', 'training_attendee:reports', 'people:export', 'people:buyer', 'map_premise_people', 'people:department'])]
    #[Legacy\Column(column: 'dpt_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    private ?Department $department = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['people_detail', 'people_write', 'user_partial_write', 'people:export'])]
    #[MaxDepth(1)]
    #[Transferable(handler: 'handler.transfer.supervisor')]
    #[Legacy\Column(column: 'reports_to', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    private ?People $supervisor = null;

    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[ORM\Column(length: 2)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 2)]
    #[Groups(['people_detail', 'people_write', 'user:me', 'user_partial_write'])]
    private ?string $locale = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\ContractType')]
    #[Groups(['people_contract:create', 'people_contract:edit', 'contract_type', 'people:export:restricted'])]
    #[Legacy\Column(column: 'contract_type', transformer: ObjectToProperty::class, options: ['property' => 'name'])]
    private ?ContractType $contractType = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['people_contract:create', 'people_contract:edit', 'contract_type', 'people:export:restricted'])]
    #[Assert\GreaterThanOrEqual(0)]
    #[Assert\LessThanOrEqual(100)]
    #[Legacy\Column(column: 'coefficient')]
    private ?int $coefficient = 100;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Premise', inversedBy: 'employees')]
    #[Groups(['people_detail', 'people_write', 'people:export', 'map_premise_people', 'people:buyer', 'people', 'user:me'])]
    #[Transferable(conditions: ['disabled' => false, 'hidden' => false], manager: 'manager.premise')]
    private ?Premise $premise = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People', inversedBy: 'mentees')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['people_write', 'user_partial_write', 'people:export', 'people:mentoring'])]
    #[MaxDepth(1)]
    #[Transferable(conditions: ['disabled' => false, 'hidden' => false])]
    #[Assert\When(
        expression: 'this.getPosition() !== null && this.getPosition().isMentorMandatory() === true',
        constraints: [
            new Assert\NotBlank(
                message: 'people.mentor_required_for_position',
            ),
        ],
    )]
    private ?People $mentor = null;

    /**
     * @var Collection<People>
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Directory\People', mappedBy: 'mentor', fetch: 'EXTRA_LAZY')]
    #[Groups(['people:mentoring'])]
    #[MaxDepth(1)]
    private Collection $mentees;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['people_planned_disable_at_write', 'people'])]
    private ?\DateTimeInterface $plannedDisableAt = null;

    public function __construct()
    {
        parent::__construct();
        $this->files = new ArrayCollection();
        $this->phones = new ArrayCollection();
        $this->address = new AddressWithCountry();
        $this->mentees = new ArrayCollection();
        $this->updateTasks = new ArrayCollection();
        $this->appMembers = new ArrayCollection();
    }

    /**
     * check https://github.com/symfony/symfony/issues/35660
     * and https://github.com/symfony/symfony/issues/35574.
     */
    public function __serialize()
    {
        return [];
    }

    public function __toString()
    {
        return parent::getLastname().', '.parent::getFirstname();
    }

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function setNickname(?string $nickname): self
    {
        $this->nickname = $nickname;

        return $this;
    }

    public function getJobTitle(): ?string
    {
        return $this->jobTitle;
    }

    public function setJobTitle(?string $jobTitle): self
    {
        $this->jobTitle = $jobTitle;

        return $this;
    }

    /**
     * @return Collection<Phone>
     */
    public function getPhones()
    {
        return $this->phones;
    }

    public function addPhone(Phone $phone): self
    {
        $this->phones->add($phone);
        $phone->addPeople($this);

        return $this;
    }

    public function removePhone(Phone $phone): self
    {
        $this->phones->removeElement($phone);

        return $this;
    }

    public function getAddress(): AddressWithCountry
    {
        return $this->address;
    }

    public function setAddress(AddressWithCountry $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getErpLogin(): ?string
    {
        return $this->erpLogin;
    }

    public function setErpLogin(?string $erpLogin): self
    {
        $this->erpLogin = $erpLogin;

        return $this;
    }

    public function getWindowsLogin(): ?string
    {
        return $this->windowsLogin;
    }

    public function setWindowsLogin(?string $windowsLogin): self
    {
        $this->windowsLogin = $windowsLogin;

        return $this;
    }

    public function getBusinessUnit(): ?BusinessUnit
    {
        return $this->businessUnit;
    }

    public function setBusinessUnit(?BusinessUnit $businessUnit): self
    {
        $this->businessUnit = $businessUnit;

        return $this;
    }

    public function getLegalEntity(): ?BusinessUnit
    {
        return $this->legalEntity;
    }

    public function setLegalEntity(?BusinessUnit $legalEntity): self
    {
        $this->legalEntity = $legalEntity;

        return $this;
    }

    public function getPosition(): ?Position
    {
        return $this->position;
    }

    public function setPosition(?Position $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    public function setDepartment(?Department $department): self
    {
        $this->department = $department;

        return $this;
    }

    public function getSupervisor(): ?self
    {
        return $this->supervisor;
    }

    public function setSupervisor(?self $supervisor): self
    {
        $this->supervisor = $supervisor;

        return $this;
    }

    public function getHierarchy(): array
    {
        $hierarchy = [];

        $current = $this->getSupervisor();

        while (null !== $current) {
            $hierarchy[] = $current;
            $current = $current->getSupervisor();
        }

        return $hierarchy;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(?string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    #[Groups(['survey_target_detail'])]
    public function getDisplayName(): string
    {
        return \sprintf('%s %s', $this->getLastname(), $this->getFirstname());
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context)
    {
        if (null !== $this->supervisor && $this->supervisor->getId() === $this->getId()) {
            $context
                ->buildViolation('Can not be your own supervisor')
                ->atPath('supervisor')
                ->addViolation();
        }
    }

    #[Groups(['user', 'user_detail', 'people_photo', 'user:me', PayloadGeneratorInterface::PAYLOAD_NORMALIZATION_GROUP])]
    public function getPhoto(): ?PeopleFile
    {
        if (0 === $this->files->count()) {
            return null;
        }

        return $this->files->first();
    }

    public function setPhoto(?PeopleFile $file): self
    {
        if (null === $file) {
            $this->files = new ArrayCollection();

            return $this;
        }

        return $this->addFile($file);
    }

    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(PeopleFile $file): self
    {
        $file->setPeople($this);
        $this->files->add($file);

        return $this;
    }

    public function removeFile(PeopleFile $file): self
    {
        $this->files->removeElement($file);

        return $this;
    }

    public function getAlternateEmail(): ?string
    {
        return $this->alternateEmail;
    }

    public function setAlternateEmail(?string $alternateEmail): self
    {
        $this->alternateEmail = $alternateEmail;

        return $this;
    }

    public function getContractType(): ?ContractType
    {
        return $this->contractType;
    }

    public function setContractType(?ContractType $contractType): self
    {
        $this->contractType = $contractType;

        return $this;
    }

    public function getCoefficient(): ?int
    {
        return $this->coefficient;
    }

    public function setCoefficient(?int $coefficient): self
    {
        $this->coefficient = $coefficient;

        return $this;
    }

    public function getPremise(): ?Premise
    {
        return $this->premise;
    }

    public function setPremise(?Premise $premise): self
    {
        $this->premise = $premise;

        return $this;
    }

    public function getMentor(): ?self
    {
        return $this->mentor;
    }

    public function setMentor(?self $mentor): self
    {
        $this->mentor = $mentor;

        return $this;
    }

    /**
     * @return Collection<People>
     */
    public function getMentees(): Collection
    {
        return $this->mentees;
    }

    public function getEnableAt(): ?\DateTimeInterface
    {
        return $this->enableAt;
    }

    public function setEnableAt(?\DateTimeInterface $enableAt): self
    {
        $this->enableAt = $enableAt;

        return $this;
    }

    public function getGroupsForDivision(): Collection
    {
        foreach ($this->position->getDivisionGroups() as $divisionGroup) {
            if ($divisionGroup->division === $this->getBusinessUnit()?->getRegion()?->getSubDivision()?->division) {
                return $divisionGroup->getGroups();
            }
        }

        return new ArrayCollection();
    }

    #[Groups(['map_premise_people'])]
    public function getAirport(): ?Airport
    {
        if (null !== $this->premise && null !== $this->premise->airport && $this->premise->airport->hasCoordinate()) {
            return $this->premise->airport;
        }

        if (null !== $this->closestAirport && $this->closestAirport->hasCoordinate()) {
            return $this->closestAirport;
        }

        return null;
    }

    public function setDisabled(bool $disabled): self
    {
        $this->disabled = $disabled;

        return $this;
    }

    public function getPlannedDisableAt(): ?\DateTimeInterface
    {
        return $this->plannedDisableAt;
    }

    public function setPlannedDisableAt(?\DateTimeInterface $plannedDisableAt): self
    {
        $this->plannedDisableAt = $plannedDisableAt;

        return $this;
    }
}
