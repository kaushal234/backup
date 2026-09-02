<?php

declare(strict_types=1);

namespace App\Entity\PowerBI;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Group;
use App\EventListener\PowerBI\UuidListener;
use App\Filter\SimpleSearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(
            security: "is_granted('FEATURE_POWER_BI_REPORT_READ_VOTER', object)"
        ),
        new Post(
            security: "is_granted('FEATURE_POWER_BI_REPORT_CREATE')",
        ),
        new Put(
            security: "is_granted('FEATURE_POWER_BI_REPORT_UPDATE')",
        ),
        new Delete(
            security: "is_granted('FEATURE_POWER_BI_REPORT_DELETE')",
        ),
    ],
    routePrefix: 'power_bi',
    normalizationContext: ['groups' => ['power_bi:report:read', 'power_bi:category:read', 'group:list']],
    denormalizationContext: ['groups' => ['power_bi:report:write']],
    security: "is_granted('FEATURE_POWER_BI_REPORT_READ')",
)]
#[ORM\Table(name: 'power_bi_reports')]
#[ApiFilter(OrderFilter::class, properties: ['id', 'title', 'description', 'category'])]
#[ApiFilter(SimpleSearchFilter::class, properties: ['id', 'title' => 'partial', 'description' => 'partial', 'category', 'subCategories' => 'partial'])]
#[ApiFilter(SearchFilter::class, properties: ['id', 'title' => 'partial', 'description' => 'partial', 'category', 'subCategories' => 'partial'])]
#[UniqueEntity('powerBiUuidObject')]
class Report
{
    public const string SSO_CONTROLLING = 'SSO CONTROLLING';
    public const string FACTORY_CONTROLLING = 'FACTORY CONTROLLING';
    public const string ACCOUNTING = 'ACCOUNTING';

    #[ORM\Column(type: 'string')]
    #[Groups(['power_bi:report:read', 'power_bi:report:write'])]
    public string $title;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['power_bi:report:read', 'power_bi:report:write'])]
    public ?string $description = null;

    #[ORM\Column(type: 'string', enumType: Category::class)]
    #[Groups(['power_bi:report:read', 'power_bi:report:write'])]
    public Category $category;

    #[ORM\Column(type: 'json', options: ['default' => '[]'])]
    #[Assert\All([new Assert\Choice(choices: [self::SSO_CONTROLLING, self::FACTORY_CONTROLLING, self::ACCOUNTING])])]
    #[Groups(['power_bi:report:read', 'power_bi:report:write'])]
    public array $subCategories = [];

    /**
     * Use to validate Uuid before create Uuid object @see UuidListener.
     */
    #[Assert\Uuid]
    #[Groups(['power_bi:report:read', 'power_bi:report:write'])]
    public string $powerBiUuid;

    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private Uuid $powerBiUuidObject;

    /**
     * @var Collection<Group>
     */
    #[ORM\ManyToMany(targetEntity: Group::class, cascade: ['persist'])]
    #[ORM\JoinTable(name: 'power_bi_reports_groups')]
    #[Groups(['power_bi:report:read', 'power_bi:report:write'])]
    private Collection $groups;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups('power_bi:report:read')]
    private int $id;

    public function __construct()
    {
        $this->groups = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
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

    public function getGroups(): Collection
    {
        return $this->groups;
    }

    public function setPowerBiUuidObject(Uuid $powerBiUuid): self
    {
        $this->powerBiUuidObject = $powerBiUuid;

        return $this;
    }

    public function getPowerBiUuid(): string
    {
        return (string) $this->powerBiUuidObject;
    }
}
