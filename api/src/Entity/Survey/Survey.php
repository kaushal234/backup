<?php

declare(strict_types=1);

namespace App\Entity\Survey;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
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
 * Survey.
 */
#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/surveys/models',
            normalizationContext: ['groups' => ['survey', 'people_photo', 'file:light', 'people_public']],
        ),
        new Post(
            uriTemplate: '/surveys/models',
            security: "is_granted('FEATURE_SURVEY_WRITE')",
        ),
        new Get(uriTemplate: '/surveys/models/{id}'),
        new Put(
            uriTemplate: '/surveys/models/{id}',
            security: "is_granted('FEATURE_SURVEY_WRITE')",
        ),
        new Delete(
            uriTemplate: '/surveys/models/{id}',
            security: "is_granted('FEATURE_SURVEY_DELETE')",
        ),
    ],
    normalizationContext: ['groups' => ['translations', 'survey_detail', 'people_public', 'people_photo', 'file:light']],
    denormalizationContext: ['groups' => ['survey_write']],
)]
#[ORM\Table(name: 'surveys')]
#[ApiFilter(SearchFilter::class, properties: ['name' => 'partial', 'description' => 'partial', 'createdBy' => 'exact', 'updatedBy' => 'exact'])]
#[App\Loggable]
#[Gedmo\SoftDeleteable]
class Survey
{
    use ModifiedByTrait;
    use TimestampableTrait;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['survey', 'survey_detail', 'survey_group_detail', 'survey_item_detail', 'survey_rating_type_detail', 'survey_write'])]
    private int $id;

    #[ORM\Column(name: 'name', type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 255)]
    #[Groups(['survey', 'survey_detail', 'survey_item', 'survey_group', 'survey_rating_type', 'survey_group_detail', 'survey_item_detail', 'survey_target_detail', 'survey_rating_type_detail', 'survey_write'])]
    #[Gedmo\Translatable]
    private ?string $name = null;

    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    #[Groups(['survey', 'survey_detail', 'survey_group_detail', 'published_detail', 'survey_item_detail', 'survey_rating_type_detail', 'survey_write'])]
    #[Gedmo\Translatable]
    private ?string $description = null;

    #[ORM\Column(name: 'expiration_date', type: 'datetime', nullable: true)]
    #[Groups(['survey', 'survey_detail', 'survey_group_detail', 'survey_target_detail', 'survey_item_detail', 'survey_rating_type_detail', 'survey_write'])]
    private ?\DateTimeInterface $expirationDate = null;

    /**
     * @var ArrayCollection
     */
    #[ORM\OneToMany(mappedBy: 'survey', targetEntity: 'App\Entity\Survey\Group')]
    #[ORM\OrderBy(['sorting' => 'ASC'])]
    #[Groups(['survey_detail'])]
    private Collection $groups;

    #[ORM\OneToMany(mappedBy: 'survey', targetEntity: 'App\Entity\Survey\Item')]
    #[Groups(['survey_detail', 'survey_target_detail'])]
    private Collection $items;

    /**
     * @var ArrayCollection
     */
    #[ORM\OneToMany(mappedBy: 'survey', targetEntity: 'App\Entity\Survey\RatingType')]
    #[Groups(['survey_detail', 'survey_target_detail'])]
    private Collection $ratingTypes;

    #[ORM\Column(name: 'deletedAt', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->groups = new ArrayCollection();
        $this->items = new ArrayCollection();
        $this->ratingTypes = new ArrayCollection();
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
    public function setName(?string $name): self
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
     * Set expirationDate.
     */
    public function setExpirationDate(?\DateTime $expirationDate): self
    {
        $this->expirationDate = $expirationDate;

        return $this;
    }

    /**
     * Get expirationDate.
     */
    public function getExpirationDate(): ?\DateTimeInterface
    {
        return $this->expirationDate;
    }

    /**
     * Set deletedAt.
     */
    public function setDeletedAt(?\DateTime $deletedAt): self
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
     * Add group.
     */
    public function addGroup(Group $group): self
    {
        $this->groups[] = $group;

        return $this;
    }

    /**
     * Remove group.
     */
    public function removeGroup(Group $group): self
    {
        $this->groups->removeElement($group);

        return $this;
    }

    /**
     * Get groups.
     */
    public function getGroups(): Collection
    {
        return $this->groups;
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

    public function setItems(Collection $items): self
    {
        $this->items = $items;

        return $this;
    }

    /**
     * Add ratingType.
     */
    public function addRatingType(RatingType $ratingType): self
    {
        $this->ratingTypes[] = $ratingType;

        return $this;
    }

    /**
     * Remove ratingType.
     */
    public function removeRatingType(RatingType $ratingType): self
    {
        $this->ratingTypes->removeElement($ratingType);

        return $this;
    }

    /**
     * Get ratingTypes.
     */
    public function getRatingTypes(): Collection
    {
        return $this->ratingTypes;
    }
}
