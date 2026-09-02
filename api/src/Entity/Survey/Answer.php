<?php

declare(strict_types=1);

namespace App\Entity\Survey;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use App\Controller\Survey\SurveysPostAnswerController;
use App\Doctrine\Constraints\Survey as SurveyConstraints;
use App\Traits\ORM\Survey\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[SurveyConstraints\AnswerValueInRatingType]
#[SurveyConstraints\ItemBelongsToPublishedSurvey]
#[ORM\Entity]
#[UniqueEntity(fields: ['publishedSurvey', 'item', 'ratingType'], message: 'survey.answers.duplicate_item', errorPath: 'item')]
#[ApiResource(
    shortName: 'survey_answers',
    operations: [
        new GetCollection(
            uriTemplate: '/surveys/answers',
            normalizationContext: ['groups' => ['survey_answer', 'people_public']],
            security: "is_granted('FEATURE_SURVEY_VIEW')",
        ),
        new Post(
            uriTemplate: '/public/surveys/{token}/answers',
            uriVariables: ['token'],
            controller: SurveysPostAnswerController::class,
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
            write: false,
            name: 'post_public_survey_answer',
        ),
        new Post(
            uriTemplate: '/surveys/answers',
            security: "is_granted('FEATURE_SURVEY_WRITE')",
        ),
        new Get(
            uriTemplate: '/surveys/answers/{id}',
            security: "is_granted('FEATURE_SURVEY_VIEW')",
        ),
        new Put(
            uriTemplate: '/surveys/answers/{id}',
            security: "is_granted('FEATURE_SURVEY_WRITE')",
        ),
        new Delete(
            uriTemplate: '/surveys/answers/{id}',
            security: "is_granted('FEATURE_SURVEY_DELETE')",
        ),
    ],
    normalizationContext: ['groups' => ['survey_answer_detail', 'people_public']],
    denormalizationContext: ['groups' => ['survey_answer_write']],
)]
#[ORM\Table(name: 'survey_answers')]
#[Gedmo\SoftDeleteable]
class Answer
{
    use TimestampableTrait;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Survey\PublishedSurvey', inversedBy: 'answers')]
    #[ORM\JoinColumn(name: 'published_survey_id', referencedColumnName: 'id')]
    private ?PublishedSurvey $publishedSurvey = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Survey\Item', inversedBy: 'answers')]
    #[ORM\JoinColumn(name: 'item_id', referencedColumnName: 'id')]
    #[Groups(['survey_answer_detail', 'published_detail', 'survey_answer_write'])]
    private ?Item $item = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Survey\RatingType')]
    #[ORM\JoinColumn(name: 'rating_type_id', referencedColumnName: 'id')]
    #[Groups(['survey_answer_detail', 'published_detail', 'survey_answer_write'])]
    private ?RatingType $ratingType = null;

    #[ORM\Column(name: 'value', type: 'integer')]
    #[Assert\NotNull]
    #[Groups(['survey_answer_detail', 'published_detail', 'survey_answer_write'])]
    private int $value;

    #[ORM\Column(name: 'deletedAt', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    /**
     * Get id.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set value.
     */
    public function setValue(int $value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value.
     */
    public function getValue(): int
    {
        return $this->value;
    }

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
     * Set publishedSurvey.
     */
    public function setPublishedSurvey(PublishedSurvey $publishedSurvey): self
    {
        $this->publishedSurvey = $publishedSurvey;

        return $this;
    }

    /**
     * Get publishedSurvey.
     */
    public function getPublishedSurvey(): PublishedSurvey
    {
        return $this->publishedSurvey;
    }

    /**
     * Set item.
     */
    public function setItem(?Item $item = null): self
    {
        $this->item = $item;

        return $this;
    }

    /**
     * Get item.
     */
    public function getItem(): Item
    {
        return $this->item;
    }

    /**
     * Set ratingType.
     */
    public function setRatingType(RatingType $ratingType): self
    {
        $this->ratingType = $ratingType;

        return $this;
    }

    /**
     * Get ratingType.
     */
    public function getRatingType(): RatingType
    {
        return $this->ratingType;
    }
}
