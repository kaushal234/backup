<?php

declare(strict_types=1);

namespace App\Entity\Sales;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
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
use ApiPlatform\Serializer\Filter\GroupFilter;
use App\Controller\Sales\ExtranetUser\ExtranetUserArchiveAccountController;
use App\Controller\Sales\ExtranetUser\ExtranetUserDisableAccountController;
use App\Controller\Sales\ExtranetUser\ExtranetUserPasswordUpdateLinkController;
use App\Controller\Sales\ExtranetUser\ExtranetUserRequestAccessController;
use App\Controller\Sales\ExtranetUser\ExtranetUserUpdateController;
use App\DataProcessor\Sales\ExtranetUserConfirmationEmailDataProcessor;
use App\DataProcessor\Sales\ExtranetUserWithCrtAndGroupDataProcessor;
use App\Doctrine\Mapping\Attributes as App;
use App\Dto\Sales\ExtranetUserConfirmationEmailInput;
use App\Dto\Sales\ExtranetUserWithCrtInput;
use App\Entity\Address;
use App\Entity\Communication\ContactCampaign;
use App\Entity\Directory\Phone;
use App\Entity\PhoneInterface;
use App\Entity\SurveyTargetInterface;
use App\Entity\User;
use App\Filter\ColumnsFilter;
use App\Filter\ExtranetUser\ExtranetUserCustomerHierarchyFilter;
use App\Filter\ExtranetUser\ExtranetUserRelatedToCustomerFilter;
use App\Filter\ExtranetUser\ExtranetUserRelatedToEquipmentRecordFilter;
use App\Filter\SimpleSearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\CollectionHandler\PhoneCollectionHandler;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\BooleanToChar;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use LegacyBundle\Entity\LegacyIdInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[UniqueEntity(fields: ['email'], message: 'This value is already used by another Extranet User.')]
#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(
            formats: [
                'jsonld',
                'json',
                'csv' => ['text/csv'],
                'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            ],
            normalizationContext: ['groups' => ['user', 'extranet_user', 'user_profile', 'expose_legacy', 'phone', 'location_public', 'customer_list']],
            name: 'get_extranet_users',
        ),
        new GetCollection(
            uriTemplate: '/contact_campaign/{id}/contacts',
            formats: ['jsonld', 'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']],
            uriVariables: [
                'id' => new Link(
                    fromProperty: 'contacts',
                    fromClass: ContactCampaign::class
                ),
            ],
            routePrefix: '',
            normalizationContext: ['groups' => ['people_public', 'extranet_user_address_campaign', 'extranet_user_campaign', 'extranet_user_profile_campaign', 'customer_type_detail', 'extranet_user_is_verified_campaign', 'extranet_user_id_campaign']],
            name: 'get_campaign_contacts',
        ),
        new Put(
            controller: ExtranetUserUpdateController::class,
            security: "is_granted('FEATURE_EXTRANET_USER_EDIT', object)",
            validationContext: ['groups' => ['Default', 'User', 'password']],
        ),
        new Put(
            uriTemplate: '/extranet_users/{id}/disable_account',
            controller: ExtranetUserDisableAccountController::class,
            security: "is_granted('FEATURE_EXTRANET_USER_EDIT', object)",
            input: false,
            deserialize: false,
            validate: false,
            name: 'disable_account',
        ),
        new Put(
            uriTemplate: '/extranet_users/{id}/archive_account',
            controller: ExtranetUserArchiveAccountController::class,
            security: "is_granted('FEATURE_EXTRANET_USER_EDIT', object)",
            input: false,
            deserialize: false,
            validate: false,
            name: 'archive_account',
        ),
        new Put(
            uriTemplate: '/extranet_users/{id}/confirmation_mail',
            denormalizationContext: [],
            security: "is_granted('FEATURE_EXTRANET_USER_CREATE')",
            input: ExtranetUserConfirmationEmailInput::class,
            output: false,
            validate: false,
            name: 'extranet_user_mail',
            processor: ExtranetUserConfirmationEmailDataProcessor::class
        ),
        new Get(security: "is_granted('ACCESS_PEOPLE') or is_granted('FEATURE_EXTRANET_USER_EDIT', object)"),
        new Delete(security: "is_granted('FEATURE_EXTRANET_USER_DELETE')"),
        new Post(
            security: "is_granted('FEATURE_EXTRANET_USER_CREATE', object)",
            validationContext: ['groups' => ['Default', 'postValidation']],
        ),
        new Post(
            uriTemplate: '/extranet_users/with_crt_and_group',
            denormalizationContext: ['groups' => ['user_write', 'extranet_user_write', 'phone:write', 'user_profile:write', 'customer_relationship_team']],
            security: "is_granted('FEATURE_EXTRANET_USER_CREATE', object)",
            validationContext: ['groups' => ['Default', 'postValidation']],
            input: ExtranetUserWithCrtInput::class,
            name: 'create_extranet_user_with_crt_and_group',
            processor: ExtranetUserWithCrtAndGroupDataProcessor::class,
        ),
        new Post(
            uriTemplate: '/extranet_users/{id}/extranet_request',
            controller: ExtranetUserRequestAccessController::class,
            security: "is_granted('FEATURE_EXTRANET_USER_EDIT', object)",
            deserialize: false,
            name: 'extranet_request',
        ),
        new Post(
            uriTemplate: '/extranet_users/{id}/update_password',
            controller: ExtranetUserPasswordUpdateLinkController::class,
            security: "is_granted('FEATURE_EXTRANET_USER_EDIT', object)",
            deserialize: false,
            name: 'update__extranet_user_password',
        ),
    ],
    routePrefix: 'sales',
    normalizationContext: ['groups' => ['user', 'extranet_user', 'extranet_user_detail', 'user_profile', 'user_profile:detail', 'address', 'location_public', 'expose_legacy', 'country_list', 'user_detail', 'airport_list', 'customer_list', 'phone']],
    denormalizationContext: ['groups' => ['user_write', 'extranet_user_write', 'phone:write', 'address_write', 'user_profile:write']],
    security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_EXTRANET_USER')",
    extraProperties: [
        PhoneCollectionHandler::ATTRIBUTE_KEY => [
            Phone::TYPE_RECEPTION => 'phone',
            Phone::TYPE_PHONE => 'direct_phone',
            Phone::TYPE_FAX => 'fax',
            Phone::TYPE_MOBILE => 'mobile',
            Phone::TYPE_HOME => 'home_phone',
        ],
    ]
)]
#[ApiFilter(ExtranetUserCustomerHierarchyFilter::class)]
#[ApiFilter(BooleanFilter::class, properties: ['hidden', 'disabled', 'extranetUserProfile.archived'])]
#[ApiFilter(DateFilter::class, properties: ['lastLogin'])]
#[ApiFilter(OrderFilter::class, properties: ['id' => 'DESC', 'extranetUserProfile.customer.name', 'extranetUserProfile.country.name', 'address.state', 'address.city', 'address.postalCode', 'address.street1', 'address.street2', 'extranetUserProfile.erpLocation.name', 'lastname', 'firstname', 'email'])]
#[ApiFilter(SearchFilter::class, properties: [
    'email' => 'exact',
    'legacyId' => 'exact',
    'extranetUserProfile.customer' => 'exact',
    'extranetUserProfile.customer.name' => 'exact',
    'extranetUserProfile.erpLocation.name' => 'exact',
    'extranetUserProfile.erpLocation' => 'exact',
    'extranetUserProfile.airport' => 'exact',
    'extranetUserAcls.crt' => 'exact',
    'extranetUserAcls.crt.erpLocation' => 'exact',
    'extranetUserAcls.crt.partsLocation' => 'exact',
    'extranetUserAcls.crt.serviceLocation' => 'exact',
    'extranetUserAcls.crt.customer' => 'exact',
    'extranetUserAcls.extranetUserGroup' => 'exact',
    'extranetUserAcls.extranetUserGroup.name' => 'exact',
])]
#[ApiFilter(SimpleSearchFilter::class, properties: ['id' => 'partial', 'lastname' => 'partial', 'firstname' => 'partial', 'extranetUserProfile.customer.name' => 'partial', 'email' => 'partial', 'username' => 'partial'])]
#[ApiFilter(GroupFilter::class, id: 'override', arguments: ['parameterName' => 'normalization_groups_override', 'overrideDefaultGroups' => true, 'whitelist' => ['extranet_user_list']])]
#[ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'normalization_groups', 'overrideDefaultGroups' => false, 'whitelist' => ['extranet_user_acls', 'user_profile_language']])]
#[ApiFilter(ColumnsFilter::class)]
#[ApiFilter(ExtranetUserRelatedToEquipmentRecordFilter::class)]
#[ApiFilter(ExtranetUserRelatedToCustomerFilter::class)]
#[App\Loggable]
#[Legacy\Synchronize(table: 'extranet_users')]
class ExtranetUser extends User implements \Stringable, LegacyIdInterface, SurveyTargetInterface, PhoneInterface
{
    use LegacyIdentifierTrait;

    #[ORM\Embedded(class: '\App\Entity\Address')]
    #[Assert\Valid]
    #[Groups(['extranet_user_detail', 'extranet_user_write', 'extranet_user_address_campaign'])]
    #[Legacy\Column(column: 'address', options: ['embeddedFields' => ['address.street1', 'address.street2', 'address.town', 'address.postalCode', 'address.city', 'address.state']])]
    public ?Address $address = null;

    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[Assert\NotBlank]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 255)]
    #[Groups(['extranet_user', 'user_write', 'extranet_user_full_write', 'extranet_user_list', 'extranet_password_email', 'extranet_user_write', 'extranet_user_public'])]
    #[Legacy\Column(column: 'email')]
    #[Legacy\Column(column: 'userid')]
    protected ?string $username = null;

    #[Groups(['extranet_user_detail', 'extranet_user_write', 'extranet_password_email'])]
    #[Assert\Choice(choices: [self::MALE, self::FEMALE])]
    #[Legacy\Column(column: 'salutation')]
    protected ?string $gender = null;

    #[ApiProperty(
        securityPostDenormalize: "is_granted('ACCESS_PEOPLE')",
        iris: ['https://schema.org/email'],
    )]
    #[Assert\Email(mode: 'strict')]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['extranet_user', 'extranet_user_public', 'extranet_user_write'])]
    protected string $email;

    #[ApiProperty(iris: ['https://schema.org/Boolean'])]
    #[Assert\Type(type: 'boolean')]
    #[Groups(['extranet_user', 'extranet_user_write', 'extranet_user_full_write'])]
    #[Legacy\Column(column: 'enable', transformer: BooleanToChar::class, options: ['trueValue' => 'N', 'falseValue' => 'Y'])]
    protected bool $disabled = true;

    #[ORM\OneToOne(inversedBy: 'extranetUser', targetEntity: 'App\Entity\Sales\ExtranetUserProfile', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Assert\Valid]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[Groups(['extranet_user', 'extranet_user_write', 'extranet_user_full_write', 'extranet_password_email', 'extranet_user_campaign', 'user_profile_language'])]
    private ?ExtranetUserProfile $extranetUserProfile = null;

    /**
     * @var ExtranetUserFavorite[]|ArrayCollection
     */
    #[ORM\OneToMany(mappedBy: 'extranetUser', targetEntity: 'App\Entity\Sales\ExtranetUserFavorite', cascade: ['remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $extranetUserFavorites;

    /**
     * @var Collection<ExtranetUserAcl>
     */
    #[ORM\OneToMany(mappedBy: 'extranetUser', targetEntity: 'App\Entity\Sales\ExtranetUserAcl', cascade: ['persist'], orphanRemoval: true)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    #[Groups(['extranet_user_acls', 'extranet_user_fetch_eager', 'extranet_user_full_write'])]
    private Collection $extranetUserAcls;

    /**
     * @var Collection<Phone>
     */
    #[ORM\ManyToMany(targetEntity: 'App\Entity\Directory\Phone', inversedBy: 'extranetUser', cascade: ['all'], orphanRemoval: true)]
    #[Assert\Valid]
    #[Groups(['extranet_user', 'extranet_user_write'])]
    private Collection $phones;

    public function __construct()
    {
        parent::__construct();
        $this->extranetUserAcls = new ArrayCollection();
        $this->extranetUserFavorites = new ArrayCollection();
        $this->phones = new ArrayCollection();
    }

    public function __toString()
    {
        return (string) $this->getUserIdentifier();
    }

    public function getExtranetUserProfile(): ExtranetUserProfile
    {
        return $this->extranetUserProfile;
    }

    /**
     * @return $this
     */
    public function setExtranetUserProfile(ExtranetUserProfile $extranetUserProfile): self
    {
        $this->extranetUserProfile = $extranetUserProfile;

        $extranetUserProfile->extranetUser = $this;

        return $this;
    }

    /**
     * @return Collection<ExtranetUserAcl>
     */
    public function getExtranetUserAcls(): Collection
    {
        return $this->extranetUserAcls;
    }

    public function addExtranetUserAcl(ExtranetUserAcl $extranetUserAcl): self
    {
        if (!$this->extranetUserAcls->contains($extranetUserAcl)) {
            $this->extranetUserAcls->add($extranetUserAcl);
            $extranetUserAcl->setExtranetUser($this);
        }

        return $this;
    }

    /**
     * @return Collection<ExtranetUserFavorite>
     */
    public function getExtranetUserFavorites(): Collection
    {
        return $this->extranetUserFavorites;
    }

    #[Groups(['survey_target_detail'])]
    public function getDisplayName(): string
    {
        return $this->getLastname().' '.$this->getFirstname();
    }

    public function setLegacyId(int $legacyId): self
    {
        $this->legacyId = $legacyId;
        $this->extranetUserProfile->setLegacyId($legacyId);

        return $this;
    }

    public function setDisabled(bool $disabled): self
    {
        $this->disabled = $disabled;

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
        if (!$this->phones->contains($phone)) {
            $this->phones->add($phone);
            $phone->addExtranetUser($this);
        }

        return $this;
    }

    public function removePhone(Phone $phone): self
    {
        if ($this->phones->contains($phone)) {
            $this->phones->removeElement($phone);
        }

        return $this;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(?Address $address): self
    {
        $this->address = $address;

        return $this;
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context): void
    {
        if ($this->username !== $this->email) {
            $context
                ->buildViolation('Email can not be different from username')
                ->atPath('email')
                ->addViolation();
        }
    }

    #[Assert\Callback(null, groups: ['postValidation'])]
    public function validateCountry(ExecutionContextInterface $context): void
    {
        if ($this->extranetUserProfile instanceof ExtranetUserProfile && null === $this->extranetUserProfile->country) {
            $context
                ->buildViolation('Please select a valid country.')
                ->atPath('country')
                ->addViolation();
        }
    }

    protected function getExpirationInterval(): \DateInterval
    {
        return new \DateInterval('P12M');
    }
}
