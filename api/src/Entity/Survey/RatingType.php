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
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * RatingType.
 */
#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['people_photo', 'file:light', 'survey_rating_type', 'people_public']]),
        new Post(security: "is_granted('FEATURE_SURVEY_WRITE')"),
        new Get(),
        new Put(security: "is_granted('FEATURE_SURVEY_WRITE')"),
        new Delete(security: "is_granted('FEATURE_SURVEY_DELETE')"),
    ],
    routePrefix: 'surveys',
    normalizationContext: ['groups' => ['translations', 'survey_rating_type_detail', 'people_photo', 'file:light', 'people_public']],
    denormalizationContext: ['groups' => ['survey_rating_type_write']],
)]
#[ORM\Table(name: 'survey_rating_types')]
#[ApiFilter(SearchFilter::class, properties: ['description' => 'partial', 'survey' => 'exact'])]
#[Gedmo\SoftDeleteable]
class RatingType
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['survey_detail', 'survey_rating_type', 'survey_rating_type_detail', 'survey_target_detail', 'survey_rating_type_write'])]
    private int $id;

    #[ORM\Column(name: 'min', type: 'integer')]
    #[Assert\NotBlank]
    #[Assert\Range(min: 0, max: 99999999)]
    #[Groups(['survey_detail', 'survey_rating_type', 'survey_rating_type_detail', 'survey_target_detail', 'survey_rating_type_write'])]
    private int $min;

    #[ORM\Column(name: 'max', type: 'integer')]
    #[Assert\NotBlank]
    #[Assert\Range(min: 0, max: 99999999)]
    #[Groups(['survey_detail', 'survey_rating_type', 'survey_rating_type_detail', 'survey_target_detail', 'survey_rating_type_write'])]
    private int $max;

    #[ORM\Column(name: 'min_label', type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 255)]
    #[Groups(['survey_detail', 'survey_rating_type', 'survey_rating_type_detail', 'survey_target_detail', 'survey_rating_type_write'])]
    #[Gedmo\Translatable]
    private ?string $minLabel = null;

    #[ORM\Column(name: 'max_label', type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 255)]
    #[Groups(['survey_detail', 'survey_rating_type', 'survey_rating_type_detail', 'survey_target_detail', 'survey_rating_type_write'])]
    #[Gedmo\Translatable]
    private ?string $maxLabel = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Survey\Survey', inversedBy: 'ratingTypes')]
    #[ORM\JoinColumn(name: 'survey_id', referencedColumnName: 'id')]
    #[Groups(['survey_rating_type', 'survey_rating_type_detail', 'survey_rating_type_write'])]
    private ?Survey $survey = null;

    #[ORM\Column(name: 'description', type: 'text')]
    #[Groups(['survey_detail', 'survey_rating_type', 'survey_rating_type_detail', 'survey_target_detail', 'survey_rating_type_write'])]
    #[Gedmo\Translatable]
    private ?string $description = null;

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
     * Set min.
     */
    public function setMin(int $min): self
    {
        $this->min = $min;

        return $this;
    }

    /**
     * Get min.
     */
    public function getMin(): ?int
    {
        return $this->min;
    }

    /**
     * Set max.
     */
    public function setMax(int $max): self
    {
        $this->max = $max;

        return $this;
    }

    /**
     * Get max.
     */
    public function getMax(): ?int
    {
        return $this->max;
    }

    /**
     * Set minLabel.
     */
    public function setMinLabel(?string $minLabel): self
    {
        $this->minLabel = $minLabel;

        return $this;
    }

    /**
     * Get minLabel.
     */
    public function getMinLabel(): ?string
    {
        return $this->minLabel;
    }

    /**
     * Set maxLabel.
     */
    public function setMaxLabel(?string $maxLabel): self
    {
        $this->maxLabel = $maxLabel;

        return $this;
    }

    /**
     * Get maxLabel.
     */
    public function getMaxLabel(): ?string
    {
        return $this->maxLabel;
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
