<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\Serializer\Filter\GroupFilter;
use App\Controller\User\MyAccountController;
use App\Controller\User\ResetPasswordCheckController;
use App\Controller\User\ResetPasswordConfirmController;
use App\Controller\User\ResetPasswordController;
use App\Controller\User\SwitchInController;
use App\Controller\User\SwitchOutController;
use App\Doctrine\Mapping\Attributes\Exclude;
use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\Position;
use App\Entity\Module\ThirdPartyApp\Member;
use App\Entity\Module\ThirdPartyApp\UpdateTask;
use App\Entity\Purchasing\VendorUser;
use App\Entity\Sales\ExtranetUser;
use App\Security\JWT\PayloadGenerator\PayloadGeneratorInterface;
use App\Validator\Constraints as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\BooleanToChar;
use LegacyBundle\Doctrine\Transformer\ClearPassword;
use LegacyBundle\Doctrine\Transformer\DateTimeToString;
use LegacyBundle\Doctrine\Transformer\Utf8ToHtmlEntities;
use Symfony\Component\Security\Core\User\LegacyPasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: 'App\Repository\UserRepository')]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'discr', type: 'string')]
#[ORM\DiscriminatorMap([
    'user' => 'App\Entity\User',
    'people' => 'App\Entity\Directory\People',
    'extranet_user' => 'App\Entity\Sales\ExtranetUser',
    'vendor_user' => 'App\Entity\Purchasing\VendorUser',
    'guest_user' => 'App\Entity\MIS\GuestUser\GuestUser',
])]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['user', 'expose_legacy']]),
        new Delete(
            uriTemplate: '/user-tokens',
            controller: SwitchOutController::class,
            read: false,
            name: 'switch_out',
        ),
        new Post(
            uriTemplate: '/reset_password',
            controller: ResetPasswordController::class,
            security: "is_granted('PUBLIC_ACCESS')",
            deserialize: false,
            name: 'reset_password',
        ),
        new Post(
            uriTemplate: '/user-tokens/{id}',
            controller: SwitchInController::class,
            security: "is_granted('USER_IMPERSONATE_VOTER', object)",
            deserialize: false,
            name: 'switch_in',
        ),
        new Get(),
        new Get(
            uriTemplate: '/reset_password_confirmation/{id}/{token}',
            controller: ResetPasswordCheckController::class,
            openapi: new Operation(
                parameters: [
                    new Parameter(
                        name: 'token',
                        in: 'path',
                        description: 'Token',
                        required: true,
                        schema: ['type' => 'string'],
                    ),
                ],
            ),
            security: "is_granted('PUBLIC_ACCESS')",
            read: false,
            name: 'user_reset_password_confirmation',
        ),
        new Post(
            uriTemplate: '/reset_password_confirmation/{id}/{token}',
            controller: ResetPasswordConfirmController::class,
            openapi: new Operation(
                parameters: [
                    new Parameter(
                        name: 'token',
                        in: 'path',
                        description: 'Token',
                        required: true,
                        schema: ['type' => 'string'],
                    ),
                ],
            ),
            security: "is_granted('PUBLIC_ACCESS')",
            read: false,
            deserialize: false,
            name: 'user_reset_password_confirm',
        ),
        new Get(
            uriTemplate: '/me',
            controller: MyAccountController::class,
            normalizationContext: ['groups' => ['user', 'expose_legacy', 'user:me']],
            security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_EXTRANET_USER') or is_granted('ACCESS_VENDOR_USER')",
            read: false,
            name: 'my_account',
        ),
    ],
    normalizationContext: ['groups' => ['user', 'expose_legacy', 'user_detail']],
    denormalizationContext: ['groups' => ['user_write', 'people_write', 'extranet_user_public', 'extranet_users_write']]
)]
#[ORM\Table(name: 'user')]
#[ORM\Index(
    name: 'user_discr_hidden_disabled_index',
    columns: ['discr', 'hidden', 'disabled']
)]
#[ORM\Index(
    name: 'user_lastname_firstname_index',
    columns: ['lastname', 'firstname']
)]
#[ORM\Index(
    name: 'user_discr_username_index',
    columns: ['discr', 'username']
)]
#[ApiFilter(SearchFilter::class, properties: ['username' => 'exact'])]
#[ApiFilter(BooleanFilter::class)]
#[ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'normalizationGroups', 'overrideDefaultGroups' => false, 'whitelist' => ['extranet_user_acls', 'user_profile_language']])]
class User implements \Stringable, UserInterface, LegacyPasswordAuthenticatedUserInterface
{
    /**
     * @var string
     */
    public const MALE = 'Mr';

    /**
     * @var string
     */
    public const FEMALE = 'Mrs';

    #[ORM\OneToMany(targetEntity: UpdateTask::class, mappedBy: 'user', cascade: ['remove'], orphanRemoval: true)]
    #[Groups(['update_task:read', 'update_task:light'])]
    #[ORM\OrderBy(['id' => 'DESC'])]
    #[MaxDepth(1)]
    public Collection $updateTasks;

    #[ORM\OneToMany(targetEntity: Member::class, mappedBy: 'user', cascade: ['remove'], orphanRemoval: true)]
    public Collection $appMembers;

    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 255)]
    #[Groups(['user', 'user_identity_write', 'people_public', PayloadGeneratorInterface::PAYLOAD_NORMALIZATION_GROUP, 'guest:create', 'guest:edit'])]
    #[Legacy\Column(column: 'username')]
    protected ?string $username = null;

    /**
     * The password is not synchronized with the legacy table
     * As we store it hashed and the legacy stores it in plain text.
     */
    #[ORM\Column(name: 'password', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 255)]
    #[Groups(['user_write', 'people_write', 'extranet_user_write', 'vendor_user:write'])]
    #[Exclude]
    #[Legacy\Column(column: 'password', transformer: ClearPassword::class)]
    protected string $encodedPassword;

    /**
     * The clearPassword is not stored but used to change the password through the API.
     */
    #[Assert\Type(type: 'string')]
    #[Assert\Length(min: 15, max: 255)]
    #[Groups(['user_write', 'people_write', 'extranet_user_write', 'vendor_user:write', 'user:reset_password'])]
    #[Exclude]
    #[AppAssert\Password(groups: ['password'])]
    protected ?string $clearPassword = null;

    /**
     * Does not exist in legacy.
     */
    #[ORM\Column(length: 64)]
    #[Assert\NotBlank]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 64)]
    #[Exclude]
    protected string $salt;

    #[ApiProperty(iris: ['https://schema.org/Boolean'])]
    #[ORM\Column(type: 'boolean')]
    #[Assert\Type(type: 'boolean')]
    #[Groups(['user', 'user_write', 'vendor_user', PayloadGeneratorInterface::PAYLOAD_NORMALIZATION_GROUP, 'guest:edit', 'guest'])]
    #[Legacy\Column(column: 'hidden')]
    protected bool $hidden = true;

    #[ApiProperty(iris: ['https://schema.org/Boolean'])]
    #[ORM\Column(type: 'boolean')]
    #[Assert\Type(type: 'boolean')]
    #[Groups(['user', 'user_write', 'people:export', 'vendor_user', PayloadGeneratorInterface::PAYLOAD_NORMALIZATION_GROUP, 'guest:edit', 'guest'])]
    #[Legacy\Column(column: 'disabled', transformer: BooleanToChar::class)]
    protected bool $disabled = true;

    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 255)]
    #[Groups(['token_write', 'extranet_password_email', 'user:reset_password'])]
    #[Exclude]
    protected ?string $token = null;

    #[ORM\Column(name: 'password_updated_at', type: 'datetime', nullable: false)]
    #[Groups(['user', 'people:export:restricted'])]
    #[Exclude]
    #[Gedmo\Timestampable(on: 'change', field: ['encodedPassword'])]
    protected ?\DateTime $passwordUpdatedAt = null;

    /**
     * @var Collection<Acl>
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: 'App\Entity\Acl', cascade: ['remove'])]
    #[Groups(['group_member'])]
    protected Collection $acls;

    #[ApiProperty(iris: ['https://schema.org/email'])]
    #[ORM\Column(length: 255)]
    #[Assert\Email(mode: 'strict')]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['user', 'user_identity_write', 'people_list', 'people_public', 'extranet_user_public', 'extranet_user_write', 'people:export', 'vendor_user', 'vendor_user:detail', 'guest:create', 'guest:edit', 'people:search'])]
    #[Legacy\Column(column: 'email')]
    #[Legacy\Copy(table: 'people_groups', columns: ['email'])]
    #[Legacy\Copy(table: 'file', columns: ['poster'])]
    protected string $email;

    #[ORM\Column(type: 'string', length: 15, nullable: true)]
    #[Groups(['people_detail', 'user_write', 'user:me', 'people:export:restricted'])]
    #[Assert\Choice(choices: [self::MALE, self::FEMALE])]
    protected ?string $gender = null;

    #[ApiProperty(iris: ['https://schema.org/Integer'])]
    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['vendor_user', 'vendor_user:detail', 'vendor_user:write_admin'])]
    protected ?string $erpIdentifier = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['user_detail'])]
    #[Gedmo\Timestampable(on: 'update')]
    protected ?\DateTimeInterface $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: Position::class, inversedBy: 'users')]
    #[ORM\JoinColumn(name: 'position_id', nullable: true)]
    protected ?Position $position = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected ?\DateTimeInterface $enableAt = null;

    #[ORM\ManyToOne(targetEntity: BusinessUnit::class)]
    #[ORM\JoinColumn(name: 'business_unit_id')]
    protected ?BusinessUnit $businessUnit = null;

    #[ApiProperty(iris: ['https://schema.org/givenName'])]
    #[ORM\Column(length: 50)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(min: 1, max: 50)]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Assert\Regex('/^(\S)+(.*?\S+$|$)/')]
    #[Groups(['user', 'user_identity_write', 'people_public', 'people_detail', 'extranet_user_public', 'people_list', 'extranet_user_list', 'extranet_user_write', 'extranet_password_email', 'people:export', 'vendor_user', 'vendor_user:detail', 'map_premise_people', PayloadGeneratorInterface::PAYLOAD_NORMALIZATION_GROUP, 'guest:create', 'guest:edit', 'people:search'])]
    #[Legacy\Column(column: 'firstname', transformer: Utf8ToHtmlEntities::class)]
    private ?string $firstname = null;

    #[ApiProperty(iris: ['https://schema.org/familyName'])]
    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(min: 1, max: 50)]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Assert\Regex('/^(\S)+(.*?\S+$|$)/')]
    #[Groups(['user', 'user_identity_write', 'people_public', 'extranet_user_public', 'people_detail', 'people_list', 'extranet_user_list', 'extranet_user_write', 'extranet_password_email', 'people:export', 'vendor_user', 'vendor_user:detail', 'map_premise_people', PayloadGeneratorInterface::PAYLOAD_NORMALIZATION_GROUP, 'guest:create', 'guest:edit', 'people:search'])]
    #[Legacy\Column(column: 'lastname', transformer: Utf8ToHtmlEntities::class)]
    private ?string $lastname = null;

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['people', 'people_detail', 'extranet_user', 'extranet_user_detail', 'user:me', 'people:export', 'extranet_user_list', 'vendor_user', 'vendor_user:detail', 'map_premise_people', 'extranet_user_id_campaign', PayloadGeneratorInterface::PAYLOAD_NORMALIZATION_GROUP, 'guest', 'people:search'])]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['user_detail', 'people:export', 'vendor_user'])]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['user_detail', 'people:export', 'people'])]
    #[Gedmo\Timestampable(on: 'change', field: 'disabled', value: [true])]
    private ?\DateTimeInterface $disabledAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['user_detail', 'vendor_user', 'people:export:restricted'])]
    #[Exclude]
    #[Legacy\Column(column: 'last', transformer: DateTimeToString::class)]
    #[Legacy\Column(column: 'login', transformer: DateTimeToString::class)]
    private ?\DateTimeInterface $lastLogin = null;

    #[ORM\OneToOne(targetEntity: 'App\Entity\Sales\ExtranetUser')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[MaxDepth(1)]
    #[Groups(['linked_account'])]
    private ?ExtranetUser $extranetUserLinked = null;

    #[ORM\OneToOne(targetEntity: 'App\Entity\Purchasing\VendorUser')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[MaxDepth(1)]
    #[Groups(['linked_account'])]
    private ?VendorUser $vendorUserLinked = null;

    public function __construct()
    {
        $this->passwordUpdatedAt = new \DateTime();
        $this->acls = new ArrayCollection();
    }

    public function __toString()
    {
        return (string) $this->username;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): self
    {
        $this->lastname = mb_strtoupper($lastname);

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function addAcl(Acl $acl): self
    {
        $this->acls->add($acl);
        $acl->setUser($this);

        return $this;
    }

    /**
     * @return Collection<Acl>
     */
    public function getAcls(): Collection
    {
        return $this->acls;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEncodedPassword(): string
    {
        return $this->encodedPassword;
    }

    public function setEncodedPassword($encodedPassword): self
    {
        $this->encodedPassword = $encodedPassword;

        return $this;
    }

    public function getClearPassword(): ?string
    {
        return $this->clearPassword;
    }

    public function setClearPassword(?string $clearPassword): self
    {
        $this->clearPassword = $clearPassword;

        return $this;
    }

    public function getSalt(): ?string
    {
        return $this->salt;
    }

    public function setSalt($salt): self
    {
        $this->salt = $salt;

        return $this;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function setHidden(bool $hidden): self
    {
        $this->hidden = $hidden;

        return $this;
    }

    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    public function setDisabled(bool $disabled): self
    {
        $this->disabled = $disabled;
        if ($disabled) {
            $this->setHidden(true);
        }

        return $this;
    }

    public function getUsername(): string
    {
        return $this->username ?? '';
    }

    public function getUserIdentifier(): string
    {
        return $this->getUsername();
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->encodedPassword;
    }

    #[Groups(['user_write', 'user_partial_write'])]
    public function setPassword($password)
    {
        $this->clearPassword = $password;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken($token): self
    {
        $this->token = $token;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles(): array
    {
        return [];
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled(): bool
    {
        return !$this->disabled;
    }

    public function validate(ExecutionContextInterface $context)
    {
        if ($this->disabled && !$this->hidden) {
            $context
                ->buildViolation('Can not be visible and disabled at the same time')
                ->atPath('hidden')
                ->addViolation()
            ;
        }
    }

    public function getPasswordUpdatedAt(): ?\DateTime
    {
        return $this->passwordUpdatedAt;
    }

    public function setPasswordUpdatedAt(?\DateTime $date): self
    {
        $this->passwordUpdatedAt = $date ?? new \DateTime();

        return $this;
    }

    #[Groups(['user', 'user_detail', 'extranet_user_list', PayloadGeneratorInterface::PAYLOAD_NORMALIZATION_GROUP, 'vendor_user:detail'])]
    public function getPasswordExpirationDate(): ?\DateTimeInterface
    {
        if (!$this->getPasswordUpdatedAt() instanceof \DateTimeInterface) {
            return null;
        }
        $date = clone $this->getPasswordUpdatedAt();

        return $date->add($this->getExpirationInterval());
    }

    public function isPasswordExpired(): bool
    {
        return null === $this->getPasswordExpirationDate() ? true : (new \DateTime()) > $this->getPasswordExpirationDate();
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getDisabledAt(): ?\DateTimeInterface
    {
        return $this->disabledAt;
    }

    public function setDisabledAt(?\DateTimeInterface $disabledAt): self
    {
        $this->disabledAt = $disabledAt;

        return $this;
    }

    public function getLastLogin(): ?\DateTimeInterface
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?\DateTimeInterface $lastLogin): self
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    public function getErpIdentifier(): ?string
    {
        return $this->erpIdentifier;
    }

    public function setErpIdentifier(?string $erpIdentifier): self
    {
        $this->erpIdentifier = $erpIdentifier;

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getExtranetUserLinked(): ?ExtranetUser
    {
        return $this->extranetUserLinked;
    }

    public function setExtranetUserLinked(?ExtranetUser $extranetUserLinked): self
    {
        $this->extranetUserLinked = $extranetUserLinked;

        return $this;
    }

    public function getVendorUserLinked(): ?VendorUser
    {
        return $this->vendorUserLinked;
    }

    public function setVendorUserLinked(?VendorUser $vendorUserLinked): self
    {
        $this->vendorUserLinked = $vendorUserLinked;

        return $this;
    }

    /**
     * Get non-expired and standard groups of the user.
     */
    public function getValidGroups(): array
    {
        return array_filter(array_map(static function (Acl $acl) {
            if (null === $acl->getExpiredAt() || $acl->getExpiredAt() >= new \DateTime()) {
                return $acl->getGroup();
            }

            return null;
        }, $this->getAcls()->toArray()));
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

    public function getPosition(): ?Position
    {
        return $this->position;
    }

    public function setPosition(?Position $position): self
    {
        $this->position = $position;

        return $this;
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

    public function getFullName(): string
    {
        return $this->getLastname().' '.$this->getFirstname();
    }

    protected function getExpirationInterval(): \DateInterval
    {
        return new \DateInterval('P6M');
    }
}
