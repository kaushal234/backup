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
use App\Controller\Survey\SurveyPostCommentController;
use App\Traits\ORM\Survey\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;

/**
 * Answer.
 */
#[ORM\Entity]
#[UniqueEntity(fields: ['publishedSurvey', 'item'], message: 'survey.comments.duplicate_item', errorPath: 'item')]
#[ApiResource(
    shortName: 'survey_comments',
    operations: [
        new GetCollection(
            uriTemplate: '/surveys/comments',
            normalizationContext: ['groups' => ['survey_comment', 'people_public']],
        ),
        new Post(
            uriTemplate: '/public/surveys/{token}/comments',
            uriVariables: ['token'],
            controller: SurveyPostCommentController::class,
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
            name: 'post_public_survey_comment',
        ),
        new Post(
            uriTemplate: '/surveys/comments',
            security: "is_granted('FEATURE_SURVEY_WRITE')",
        ),
        new Get(uriTemplate: '/surveys/comments/{id}'),
        new Put(
            uriTemplate: '/surveys/comments/{id}',
            security: "is_granted('FEATURE_SURVEY_WRITE')",
        ),
        new Delete(
            uriTemplate: '/surveys/comments/{id}',
            security: "is_granted('FEATURE_SURVEY_DELETE')",
        ),
    ],
    normalizationContext: ['groups' => ['survey_comment_detail', 'people_public']],
    denormalizationContext: ['groups' => ['survey_comment_write']],
    security: "is_granted('FEATURE_SURVEY_VIEW')",
)]
#[ORM\Table(name: 'survey_comments')]
#[Gedmo\SoftDeleteable]
class Comment
{
    use TimestampableTrait;
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Survey\Item', inversedBy: 'comments')]
    #[ORM\JoinColumn(name: 'item_id', referencedColumnName: 'id')]
    #[Groups(['published_detail', 'survey_comment_write'])]
    private ?Item $item = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Survey\PublishedSurvey', inversedBy: 'comments')]
    #[ORM\JoinColumn(name: 'published_survey_id', referencedColumnName: 'id')]
    #[Groups(['survey_comment_write'])]
    private ?PublishedSurvey $publishedSurvey = null;

    #[ORM\Column(name: 'content', type: 'text')]
    #[Groups(['survey_comment_detail', 'published_detail', 'survey_comment_write'])]
    private string $content;

    #[ORM\Column(name: 'deletedAt', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setDeletedAt(?\DateTimeInterface $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    public function getDeletedAt(): \DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setItem(?Item $item = null): self
    {
        $this->item = $item;

        return $this;
    }

    public function getItem(): ?Item
    {
        return $this->item;
    }

    public function setPublishedSurvey(PublishedSurvey $publishedSurvey): self
    {
        $this->publishedSurvey = $publishedSurvey;

        return $this;
    }

    public function getPublishedSurvey(): ?PublishedSurvey
    {
        return $this->publishedSurvey;
    }
}
