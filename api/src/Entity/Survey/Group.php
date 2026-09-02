<?php

declare(strict_types=1);

namespace App\Entity\Survey;

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
use App\Traits\ORM\Survey\ModifiedByTrait;
use App\Traits\ORM\Survey\TimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Group.
 */
#[ORM\Entity]
#[ApiResource(
    shortName: 'survey_group',
    operations: [
        new GetCollection(
            uriTemplate: '/surveys/groups',
            normalizationContext: ['groups' => ['survey_group', 'people_photo', 'file:light', 'people_public']],
        ),
        new Post(
            uriTemplate: '/surveys/groups',
            security: "is_granted('FEATURE_SURVEY_WRITE')",
        ),
        new Get(uriTemplate: '/surveys/groups/{id}'),
        new Put(
            uriTemplate: '/surveys/groups/{id}',
            security: "is_granted('FEATURE_SURVEY_WRITE')",
        ),
        new Delete(
            uriTemplate: '/surveys/groups/{id}',
            security: "is_granted('FEATURE_SURVEY_DELETE')",
        ),
    ],
    normalizationContext: ['groups' => ['translations', 'survey_group_detail', 'people_photo', 'file:light', 'people_public']],
    denormalizationContext: ['groups' => ['survey_group_write']],
)]
#[ORM\Table(name: 'survey_groups')]
#[ApiFilter(SearchFilter::class, properties: ['name' => 'partial', 'survey' => 'exact', 'createdBy' => 'exact', 'updatedBy' => 'exact'])]
#[App\Loggable]
#[Gedmo\SoftDeleteable]
class Group
{
    use ModifiedByTrait;
    use TimestampableTrait;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['survey_detail', 'survey_group', 'survey_group_detail', 'survey_item_detail', 'survey_group_write'])]
    private int $id;

    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[ORM\Column(name: 'name', type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 255)]
    #[Groups(['survey_detail', 'survey_target_detail', 'survey_group', 'survey_group_detail', 'survey_item_detail', 'survey_group_write', 'survey_item'])]
    #[Gedmo\Translatable]
    private string $name;

    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    #[Groups(['survey_detail', 'survey_group', 'survey_group_detail', 'survey_item_detail', 'survey_group_write'])]
    #[Gedmo\Translatable]
    private ?string $description = null;

    #[ORM\Column(name: 'sorting', type: 'integer', nullable: true)]
    #[ORM\OrderBy(['name' => 'ASC'])]
    #[Groups(['survey_group', 'survey_group_detail', 'survey_group_write'])]
    private ?int $sorting = null;

    /**
     * @var ArrayCollection
     */
    #[ORM\OneToMany(mappedBy: 'group', targetEntity: 'App\Entity\Survey\Item')]
    #[Groups(['survey_group', 'survey_group_detail'])]
    private Collection $items;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Survey\Survey', inversedBy: 'groups')]
    #[ORM\JoinColumn(name: 'survey_id', referencedColumnName: 'id')]
    #[Groups(['survey_group', 'survey_group_detail', 'survey_group_write'])]
    private ?Survey $survey = null;

    #[ORM\Column(name: 'deletedAt', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    /**
     * Get id.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set name.
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set description.
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Set sorting.
     */
    public function setSorting(?int $sorting): self
    {
        $this->sorting = $sorting;

        return $this;
    }

    /**
     * Get sorting.
     */
    public function getSorting(): ?int
    {
        return $this->sorting;
    }

    /**
     * Set deletedAt.
     *
     * @param \DateTime $deletedAt
     */
    public function setDeletedAt(?\DateTimeInterface $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Get deletedAt.
     */
    public function getDeletedAt(): \DateTimeInterface
    {
        return $this->deletedAt;
    }

    /**
     * Add item.
     */
    public function addItem(Item $item): self
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * Remove item.
     */
    public function removeItem(Item $item): self
    {
        $this->items->removeElement($item);

        return $this;
    }

    /**
     * Get items.
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    /**
     * Set survey.
     */
    public function setSurvey(?Survey $survey = null): self
    {
        $this->survey = $survey;

        return $this;
    }

    /**
     * Get survey.
     */
    public function getSurvey(): ?Survey
    {
        return $this->survey;
    }
}
