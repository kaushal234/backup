<?php

declare(strict_types=1);

namespace App\Entity\Survey;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Directory\People;
use App\Entity\SurveyTargetInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

/**
 * PeopleSurvey.
 */
#[ORM\Entity]
#[ApiResource(
    shortName: 'survey_people',
    operations: [
        new GetCollection(
            uriTemplate: '/surveys/published_people',
            security: "is_granted('FEATURE_SURVEY_VIEW')",
        ),
        new Get(
            uriTemplate: '/surveys/published/{token}',
            normalizationContext: ['groups' => ['translations', 'survey_target_detail', 'published_detail', 'user', 'people_photo', 'file:light', 'people_public']],
            security: "is_granted('FEATURE_SURVEY_VIEW')",
        ),
    ],
    normalizationContext: ['groups' => ['people_photo', 'file:light', 'survey_target_detail', 'user', 'people_public']],
    denormalizationContext: ['groups' => ['survey_target_write']]
)]
#[ApiFilter(SearchFilter::class, properties: ['campaign' => 'partial', 'campaign.model' => 'exact', 'people' => 'exact'])]
class PeopleSurvey extends PublishedSurvey
{
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn]
    #[Groups(['survey_detail', 'survey_target', 'survey_target_detail', 'survey_target_write'])]
    private ?SurveyTargetInterface $people = null;

    /**
     * @return SurveyTargetInterface|People
     */
    public function getPeople()
    {
        return $this->people;
    }

    #[Groups(['survey', 'survey_target', 'survey_target_detail'])]
    public function getTarget(): SurveyTargetInterface
    {
        return $this->people;
    }

    public function setTarget(SurveyTargetInterface $target): self
    {
        $this->people = $target;

        return $this;
    }
}
