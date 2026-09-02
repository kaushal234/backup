<?php

declare(strict_types=1);

namespace App\Entity\Survey;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Survey\SurveyCampaignPublishController;
use App\Controller\Survey\SurveyCampaignSendController;
use App\Entity\Directory\People;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(security: "is_granted('FEATURE_SURVEY_VIEW')"),
        new Get(
            normalizationContext: ['groups' => ['campaign_detail', 'survey_target_detail', 'campaign', 'survey', 'translations', 'people_public', 'extranet_user']],
            security: "is_granted('FEATURE_SURVEY_VIEW')",
        ),
        new Put(security: "is_granted('FEATURE_SURVEY_EDIT_ADMIN')"),
        new Delete(security: "is_granted('FEATURE_SURVEY_DELETE_ADMIN')"),
        new Post(
            uriTemplate: '/models/{id}/publish',
            controller: SurveyCampaignPublishController::class,
            normalizationContext: ['groups' => ['campaign_detail', 'survey_target_detail', 'campaign', 'survey', 'translations', 'people_public', 'extranet_user']],
            security: "is_granted('FEATURE_SURVEY_WRITE')",
            read: false,
            deserialize: false,
            name: 'survey_publish'
        ),
        new Patch(
            uriTemplate: '/campaigns/{id}/send',
            controller: SurveyCampaignSendController::class,
            security: "is_granted('FEATURE_SURVEY_CAMPAIGN_SEND')",
            deserialize: false,
            name: 'survey_send',
        ),
    ],
    routePrefix: 'surveys',
    normalizationContext: ['groups' => ['campaign', 'user']],
    denormalizationContext: ['groups' => ['campaign_write']],
)]
#[ORM\Table(name: 'survey_campaigns')]
#[ApiFilter(SearchFilter::class, properties: ['model'])]
#[Gedmo\SoftDeleteable]
class Campaign
{
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[Groups(['campaign'])]
    private int $id;

    #[ORM\Column(type: 'string', nullable: false)]
    #[Groups(['campaign', 'campaign_write'])]
    private string $description;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Survey\Survey')]
    #[Groups(['campaign', 'survey_public'])]
    private ?Survey $model = null;

    /**
     * @var Collection<PublishedSurvey>
     */
    #[ORM\OneToMany(mappedBy: 'campaign', targetEntity: 'App\Entity\Survey\PublishedSurvey', cascade: ['remove', 'persist'], orphanRemoval: true)]
    private Collection $surveys;

    /**
     * Many tools have one people.
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(name: 'created_by')]
    #[Groups(['campaign'])]
    #[Gedmo\Blameable(on: 'create')]
    private ?People $createdBy = null;

    #[ORM\Column(type: 'datetime', nullable: false)]
    #[Groups(['campaign'])]
    #[Gedmo\Timestampable(on: 'create')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['campaign'])]
    private ?\DateTimeInterface $sentAt = null;

    #[ORM\Column(name: 'deletedAt', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    public function __construct()
    {
        $this->surveys = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getModel(): ?Survey
    {
        return $this->model;
    }

    public function setModel(Survey $model): self
    {
        $this->model = $model;

        return $this;
    }

    /**
     * @return Collection<PublishedSurvey>
     */
    public function getSurveys(): Collection
    {
        return $this->surveys;
    }

    public function addSurvey(PublishedSurvey $survey): self
    {
        $survey->setCampaign($this);
        $this->surveys->add($survey);

        return $this;
    }

    public function removeSurvey(PublishedSurvey $survey): self
    {
        $this->surveys->removeElement($survey);

        return $this;
    }

    public function getCreatedBy(): People
    {
        return $this->createdBy;
    }

    public function setCreatedBy(People $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getSentAt(): ?\DateTimeInterface
    {
        return $this->sentAt;
    }

    public function setSentAt(?\DateTime $sentAt = null): self
    {
        $this->sentAt = $sentAt;

        return $this;
    }

    public function setDeletedAt(?\DateTime $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    public function getDeletedAt(): \DateTimeInterface
    {
        return $this->deletedAt;
    }
}
