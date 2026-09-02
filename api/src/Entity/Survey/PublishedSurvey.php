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
use App\Controller\Survey\SurveyGetPublishSurveyController;
use App\Entity\SurveyTargetInterface;
use App\Traits\ORM\Survey\TimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;

/**
 * Published.
 */
#[ORM\Entity(repositoryClass: 'App\Repository\Survey\PublishedSurveyRepository')]
#[ORM\EntityListeners(['App\Doctrine\EventListener\PublishedSurveyTokenListener'])]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'target_type', type: 'string')]
#[ORM\DiscriminatorMap(['people' => 'App\Entity\Survey\PeopleSurvey', 'customer' => 'App\Entity\Survey\CustomerSurvey'])]
#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/surveys/published',
            security: "is_granted('FEATURE_SURVEY_VIEW')",
        ),
        new Get(
            uriTemplate: '/surveys/published/{token}',
            normalizationContext: ['groups' => ['survey_target_detail', 'published_detail', 'user', 'people_photo', 'file:light', 'people_public', 'extranet_user', 'user_profile']],
            security: "is_granted('SURVEY_VIEW_VOTER')",
        ),
        new Get(
            uriTemplate: '/public/surveys/{token}',
            controller: SurveyGetPublishSurveyController::class,
            normalizationContext: ['groups' => ['survey_public', 'survey_detail', 'user', 'people_photo', 'file:light', 'survey_target_detail', 'extranet_user', 'user_profile', 'customer_list', 'location_public']],
            security: "is_granted('PUBLIC_ACCESS')",
            read: false,
            name: 'get_public_survey',
        ),
        new Delete(
            uriTemplate: '/surveys/published/{token}',
            security: "is_granted('FEATURE_SURVEY_DELETE')",
        ),
    ],
    normalizationContext: ['groups' => ['survey_detail', 'user', 'people_photo', 'file:light', 'survey_target_detail', 'extranet_user']]
)]
#[UniqueEntity('token')]
#[ORM\Table(name: 'survey_published_surveys')]
#[ApiFilter(SearchFilter::class, properties: ['campaign' => 'exact', 'campaign.model' => 'exact'])]
#[Gedmo\SoftDeleteable]
abstract class PublishedSurvey
{
    use TimestampableTrait;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ApiProperty(identifier: false)]
    protected int $id;

    #[ORM\Column(name: 'token', type: 'string', length: 255, unique: true)]
    #[Groups(['survey_target', 'survey_target_detail', 'survey_target_write'])]
    #[ApiProperty(identifier: true)]
    protected ?string $token = null;

    /**
     * @var ArrayCollection
     */
    #[ORM\OneToMany(mappedBy: 'publishedSurvey', targetEntity: 'App\Entity\Survey\Answer')]
    #[Groups(['survey_public', 'published_detail'])]
    protected Collection $answers;

    /**
     * @var ArrayCollection
     */
    #[ORM\OneToMany(mappedBy: 'publishedSurvey', targetEntity: 'App\Entity\Survey\Comment')]
    #[Groups(['survey_public', 'published_detail'])]
    protected Collection $comments;

    #[ORM\Column(name: 'deletedAt', type: 'datetime', nullable: true)]
    protected ?\DateTimeInterface $deletedAt = null;

    #[Groups(['survey_target', 'survey_target_detail'])]
    protected int $totalItems = 0;

    #[Groups(['survey_target', 'survey_target_detail'])]
    protected int $totalItemsAnswered = 0;

    #[Groups(['survey_target', 'survey_target_detail'])]
    protected int $totalRemainingItems = 0;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Survey\Campaign', inversedBy: 'surveys')]
    #[Groups(['survey_public', 'published_detail'])]
    protected ?Campaign $campaign = null;

    #[ORM\Column(type: 'boolean')]
    protected bool $sent = false;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->answers = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    #[Groups(['survey', 'survey_target', 'survey_target_detail'])]
    abstract public function getTarget(): SurveyTargetInterface;

    abstract public function setTarget(SurveyTargetInterface $target);

    /**
     * Get id.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * Set deletedAt.
     */
    public function setDeletedAt(?\DateTimeInterface $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Get deletedAt.
     */
    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
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
     *
     * @return Collection<Answer>
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
     *
     * @return Collection<Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function getCampaign(): Campaign
    {
        return $this->campaign;
    }

    /**
     * @return $this
     */
    public function setCampaign(Campaign $campaign)
    {
        $this->campaign = $campaign;

        return $this;
    }

    public function getTotalItems(): int
    {
        return $this->totalItems;
    }

    /**
     * @return $this
     */
    public function setTotalItems(int $totalItems): self
    {
        $this->totalItems = $totalItems;

        return $this;
    }

    public function getTotalItemsAnswered(): int
    {
        return $this->totalItemsAnswered;
    }

    /**
     * @return $this
     */
    public function setTotalItemsAnswered(int $totalItemsAnswered): self
    {
        $this->totalItemsAnswered = $totalItemsAnswered;

        return $this;
    }

    public function getTotalRemainingItems(): int
    {
        return $this->totalRemainingItems;
    }

    /**
     * @return $this
     */
    public function setTotalRemainingItems(int $totalRemainingItems): self
    {
        $this->totalRemainingItems = $totalRemainingItems;

        return $this;
    }

    #[Groups(['survey_target_detail'])]
    public function getIsOpened(): bool
    {
        return $this->getTotalRemainingItems() > 0;
    }

    public function isSent(): bool
    {
        return $this->sent;
    }

    public function setSent(bool $sent): self
    {
        $this->sent = $sent;

        return $this;
    }
}
