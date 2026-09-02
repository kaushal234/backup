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
 * Item.
 */
#[ORM\Entity]
#[ApiResource(
    shortName: 'survey_items',
    operations: [
        new GetCollection(
            uriTemplate: '/surveys/items',
            normalizationContext: ['groups' => ['survey_item', 'people_photo', 'file:light', 'people_public']],
        ),
        new Post(
            uriTemplate: '/surveys/items',
            security: "is_granted('FEATURE_SURVEY_WRITE')",
        ),
        new Get(uriTemplate: '/surveys/items/{id}'),
        new Put(
            uriTemplate: '/surveys/items/{id}',
            security: "is_granted('FEATURE_SURVEY_WRITE')",
        ),
        new Delete(
            uriTemplate: '/surveys/items/{id}',
            security: "is_granted('FEATURE_SURVEY_DELETE')",
        ),
    ],
    normalizationContext: ['groups' => ['translations', 'survey_item_detail', 'people_photo', 'file:light', 'people_public']],
    denormalizationContext: ['groups' => ['survey_item_write']],
)]
#[ORM\Table(name: 'survey_items')]
#[ApiFilter(SearchFilter::class, properties: ['description' => 'partial', 'group' => 'exact', 'survey' => 'exact', 'createdBy' => 'exact', 'updatedBy' => 'exact'])]
#[App\Loggable]
#[Gedmo\SoftDeleteable]
class Item
{
    use ModifiedByTrait;
    use TimestampableTrait;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['survey_detail', 'survey_group_detail', 'survey_target_detail', 'survey_item', 'survey_item_detail', 'survey_item_write'])]
    private int $id;

    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[ORM\Column(name: 'description', type: 'text', nullable: false)]
    #[Assert\NotBlank]
    #[Groups(['survey_detail', 'survey_group_detail', 'survey_item', 'survey_item_detail', 'survey_target_detail', 'survey_item_write'])]
    #[Gedmo\Translatable]
    private string $description;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Survey\Group', inversedBy: 'items')]
    #[ORM\JoinColumn(name: 'group_id', referencedColumnName: 'id')]
    #[Groups(['survey_item', 'survey_item_detail', 'survey_item_write', 'survey_target_detail'])]
    private ?Group $group = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Survey\Survey', inversedBy: 'items')]
    #[ORM\JoinColumn(name: 'survey_id', referencedColumnName: 'id')]
    #[Groups(['survey_item', 'survey_item_detail', 'survey_item_write'])]
    private ?Survey $survey = null;

    /**
     * @var ArrayCollection
     */
    #[ORM\OneToMany(mappedBy: 'item', targetEntity: 'App\Entity\Survey\Answer')]
    #[Groups(['survey_item_write'])]
    private Collection $answers;

    /**
     * @var ArrayCollection
     */
    #[ORM\OneToMany(mappedBy: 'item', targetEntity: 'App\Entity\Survey\Comment')]
    #[Groups(['survey_item_write'])]
    private Collection $comments;

    #[ORM\Column(name: 'deletedAt', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->answers = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    /**
     * Get id.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set description.
     */
    public function setDescription(string $description): self
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
     * Set deletedAt.
     *
     * @param \DateTime $deletedAt
     */
    public function setDeletedAt(\DateTimeInterface $deletedAt): self
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
     * Set group.
     */
    public function setGroup(?Group $group = null): self
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get group.
     */
    public function getGroup(): ?Group
    {
        return $this->group;
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

    /**
     * Add answer.
     */
    public function addAnswer(Answer $answer): self
    {
        $this->answers[] = $answer;

        return $this;
    }

    /**
     * Remove answer.
     */
    public function removeAnswer(Answer $answer): self
    {
        $this->answers->removeElement($answer);

        return $this;
    }

    /**
     * Get answers.
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    /**
     * Add comment.
     */
    public function addComment(Comment $comment): self
    {
        $this->comments[] = $comment;

        return $this;
    }

    /**
     * Remove comment.
     */
    public function removeComment(Comment $comment): self
    {
        $this->comments->removeElement($comment);

        return $this;
    }

    /**
     * Get comments.
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    #[Groups(['survey_item'])]
    public function getTotalAnswers(): int
    {
        return $this->answers->count();
    }

    #[Groups(['survey_item'])]
    public function getTotalComments(): int
    {
        return $this->comments->count();
    }
}
