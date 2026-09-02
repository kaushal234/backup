<?php

declare(strict_types=1);

namespace App\Entity;

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
use App\Filter\MIS\RestrictedTeamMembersGroupFilter;
use App\Filter\SimpleSearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use LegacyBundle\Entity\LegacyIdInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Group.
 */
#[ORM\Entity]
#[UniqueEntity(fields: ['name'])]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['group', 'expose_legacy']]),
        new Post(security: "is_granted('FEATURE_GROUPS_ADMIN')"),
        new Get(),
        new Put(security: "is_granted('FEATURE_GROUPS_ADMIN')"),
        new Delete(security: "is_granted('FEATURE_GROUPS_ADMIN')"),
    ],
    normalizationContext: ['groups' => ['group_detail', 'expose_legacy']],
    denormalizationContext: ['groups' => ['group_write']],
)]
#[ORM\Table(name: 'user_group')]
#[ApiFilter(OrderFilter::class, properties: ['name' => 'ASC'])]
#[ApiFilter(SearchFilter::class, properties: ['legacyId' => 'exact', 'name' => 'partial'])]
#[ApiFilter(SimpleSearchFilter::class, properties: [
    'id' => 'exact',
    'legacyId' => 'exact',
    'name' => 'partial',
    'description' => 'partial',
])]
#[ApiFilter(RestrictedTeamMembersGroupFilter::class)]
#[App\Loggable]
#[Legacy\Synchronize(table: 'people_groups_select')]
class Group implements \Stringable, LegacyIdInterface
{
    use LegacyIdentifierTrait;

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['group', 'group_detail', 'group:list'])]
    private int $id;

    #[ORM\Column(length: 50, unique: true, nullable: false)]
    #[Assert\Type(type: 'string')]
    #[Assert\NotBlank]
    #[Assert\Regex('/^\S+$/')]
    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[Groups(['group', 'group_detail', 'group_write', 'feature', 'group_member', 'acl_list', 'group:list'])]
    #[Legacy\Column(column: 'group_name')]
    #[Legacy\Copy(table: 'people_groups', columns: ['group_name'])]
    #[Legacy\Copy(table: 'cal_seq_tpl_nodes', columns: ['group_name'])]
    private string $name;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\NotBlank]
    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[Groups(['group', 'group_detail', 'group_write'])]
    #[Legacy\Column(column: 'description')]
    private ?string $description = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['group_detail', 'group_write'])]
    private ?string $legacyPermissions = null;

    /**
     * @var Collection<Feature>
     */
    #[ORM\ManyToMany(targetEntity: 'App\Entity\Feature', mappedBy: 'groups', cascade: ['persist'])]
    #[Groups(['group', 'group_detail'])]
    private Collection $features;

    /**
     * @var Collection<Acl>
     */
    #[ORM\OneToMany(mappedBy: 'group', targetEntity: 'App\Entity\Acl')]
    private Collection $acls;

    public function __construct()
    {
        $this->features = new ArrayCollection();
        $this->acls = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * Gets id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    public function addFeature(Feature $feature): self
    {
        if (!$this->features->contains($feature)) {
            $this->features->add($feature);
            $feature->addGroup($this);
        }

        return $this;
    }

    public function removeFeature(Feature $feature): self
    {
        if ($this->features->contains($feature)) {
            $this->features->removeElement($feature);
        }

        return $this;
    }

    public function getFeatures(): Collection
    {
        return $this->features;
    }

    /**
     * Get acls.
     *
     * @return Collection
     */
    public function getAcls()
    {
        return $this->acls;
    }

    public function getLegacyPermissions(): ?string
    {
        return $this->legacyPermissions;
    }

    public function setLegacyPermissions(?string $legacyPermissions): self
    {
        $this->legacyPermissions = $legacyPermissions;

        return $this;
    }
}
