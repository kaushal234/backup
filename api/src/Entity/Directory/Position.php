<?php

declare(strict_types=1);

namespace App\Entity\Directory;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\Group;
use App\Entity\User;
use App\Filter\RelationDiscrFilter;
use App\Filter\SimpleSearchFilter;
use App\Validator\Constraints\LockedValue;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\ObjectToProperty;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use LegacyBundle\Entity\LegacyIdInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: "App\Repository\Directory\PositionRepository")]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['position']]),
        new Post(security: "is_granted('FEATURE_POSITION_WRITE')"),
        new Get(),
        new Put(security: "is_granted('FEATURE_POSITION_WRITE')"),
        new Delete(security: "is_granted('FEATURE_POSITION_DELETE')"),
    ],
    normalizationContext: ['groups' => ['position_detail', 'expose_legacy', 'division', 'division_group', 'group:list']],
    denormalizationContext: ['groups' => ['position_write', 'division_group:write']],
)]
#[UniqueEntity(fields: ['code'])]
#[ORM\Table(name: 'directory_position')]
#[ApiFilter(OrderFilter::class, properties: ['description' => 'ASC'])]
#[ApiFilter(SearchFilter::class, properties: ['legacyId', 'users.businessUnit'])]
#[ApiFilter(SimpleSearchFilter::class, properties: [
    'code' => 'partial',
    'description' => 'partial',
])]
#[ApiFilter(RelationDiscrFilter::class, properties: ['discriminator' => 'users'])]
#[ApiFilter(BooleanFilter::class, properties: ['users.disabled'])]
#[App\Loggable]
#[Legacy\Synchronize(table: 'tld_functions')]
#[LockedValue(value: Position::GUEST, propertyPath: 'code')]
class Position implements \Stringable, LegacyIdInterface
{
    use LegacyIdentifierTrait;
    public const string SHOP_FLOOR_EMPLOYEE = 'SHOP FLOOR EMPLOYEE';
    public const string GUEST = 'GUEST';

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['position', 'position_detail'])]
    private int $id;

    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[ORM\Column(name: 'code', type: 'string', length: 12, unique: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\NotBlank]
    #[Assert\Length(max: 12)]
    #[Groups(['position', 'position_detail', 'position_write', 'people_detail', 'user:me', 'people:export', 'map_premise_people'])]
    #[Legacy\Column(column: 'code')]
    private string $code;

    #[ApiProperty(iris: ['https://schema.org/description'])]
    #[ORM\Column(name: 'description', type: 'string', length: 250)]
    #[Assert\Type(type: 'string')]
    #[Assert\NotBlank]
    #[Assert\Length(max: 250)]
    #[Groups(['position', 'position_detail', 'position_write', 'people_detail', 'group_member', 'user:me', 'map_premise_people'])]
    #[Legacy\Column(column: 'dsc')]
    private string $description;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\PositionLevel')]
    #[Assert\NotBlank]
    #[Assert\Valid]
    #[Groups(['position_detail', 'position_write', 'people_detail', 'user:me'])]
    #[Legacy\Column(column: 'level', transformer: ObjectToProperty::class, options: ['property' => 'label'])]
    private ?PositionLevel $level = null;

    /**
     * @var Collection<User>
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\User', mappedBy: 'position')]
    private Collection $users;

    #[ORM\ManyToMany(targetEntity: 'App\Entity\Directory\PositionClassification', mappedBy: 'positions')]
    private Collection $positionClassifications;

    /**
     * @var Collection<Group>
     */
    #[ORM\ManyToMany(targetEntity: 'App\Entity\Group')]
    private Collection $groups;

    /**
     * @var Collection<DivisionGroup>
     */
    #[ORM\OneToMany(targetEntity: DivisionGroup::class, mappedBy: 'position', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['position_detail', 'position_write', 'group_member'])]
    private Collection $divisionGroups;

    #[ORM\Column(type: 'boolean')]
    #[Assert\Type(type: 'boolean')]
    #[Groups(['position_detail', 'position_write'])]
    private bool $mentorMandatory = false;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->groups = new ArrayCollection();
        $this->divisionGroups = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->code;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get code.
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set code.
     *
     * @param string $code
     *
     * @return $this
     */
    public function setCode($code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get level.
     */
    public function getLevel(): ?PositionLevel
    {
        return $this->level;
    }

    /**
     * Set level.
     *
     * @return $this
     */
    public function setLevel(PositionLevel $level): self
    {
        $this->level = $level;

        return $this;
    }

    /**
     * @return Collection<User>
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @return $this
     */
    public function addUser(User $user): self
    {
        $this->users->add($user);

        return $this;
    }

    /**
     * @return $this
     */
    public function removeUser(User $user): self
    {
        $this->users->removeElement($user);

        return $this;
    }

    public function addGroup(Group $group): self
    {
        if (!$this->groups->contains($group)) {
            $this->groups->add($group);
        }

        return $this;
    }

    public function removeGroup(Group $group): self
    {
        if ($this->groups->contains($group)) {
            $this->groups->removeElement($group);
        }

        return $this;
    }

    /**
     * @return Collection<Group>
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    public function addDivisionGroup(DivisionGroup $divisionGroup): self
    {
        if (!$this->divisionGroups->contains($divisionGroup)) {
            $this->divisionGroups->add($divisionGroup);
            $divisionGroup->position = $this;
        }

        return $this;
    }

    public function removeDivisionGroup(DivisionGroup $divisionGroup): self
    {
        if ($this->divisionGroups->contains($divisionGroup)) {
            $this->divisionGroups->removeElement($divisionGroup);
        }

        return $this;
    }

    /**
     * @return Collection<DivisionGroup>
     */
    public function getDivisionGroups(): Collection
    {
        return $this->divisionGroups;
    }

    public function isMentorMandatory(): bool
    {
        return $this->mentorMandatory;
    }

    public function setMentorMandatory(bool $mentorMandatory): self
    {
        $this->mentorMandatory = $mentorMandatory;

        return $this;
    }
}
