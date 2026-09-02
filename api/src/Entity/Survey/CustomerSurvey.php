<?php

declare(strict_types=1);

namespace App\Entity\Survey;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Sales\ExtranetUser;
use App\Entity\SurveyTargetInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ApiResource(
    shortName: 'survey_customer',
    operations: [
        new GetCollection(
            uriTemplate: '/surveys/published_customers',
            security: "is_granted('FEATURE_SURVEY_VIEW')",
        ),
        new Get(
            uriTemplate: '/surveys/published/{token}',
            normalizationContext: ['groups' => ['translations', 'survey_target_detail', 'published_detail', 'people_photo', 'file:light', 'people_public', 'extranet_user']],
            security: "is_granted('FEATURE_SURVEY_VIEW')",
        ),
    ],
    normalizationContext: ['groups' => ['people_photo', 'file:light', 'survey_target_detail', 'user', 'people_public']],
    denormalizationContext: ['groups' => ['survey_target_write']]
)]
#[ApiFilter(SearchFilter::class, properties: ['campaign' => 'exact', 'campaign.model' => 'exact', 'customer' => 'exact'])]
class CustomerSurvey extends PublishedSurvey
{
    #[ORM\ManyToOne(targetEntity: '\App\Entity\Sales\ExtranetUser')]
    #[ORM\JoinColumn]
    #[Groups(['survey_detail', 'survey_target_detail', 'survey_target_write'])]
    private ?SurveyTargetInterface $customer = null;

    /**
     * @return ExtranetUser|SurveyTargetInterface
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    public function setCustomer(ExtranetUser $extranetUser): self
    {
        $this->customer = $extranetUser;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[Groups(['survey', 'survey_target', 'survey_target_detail'])]
    public function getTarget(): SurveyTargetInterface
    {
        return $this->customer;
    }

    public function setTarget(SurveyTargetInterface $target): self
    {
        $this->customer = $target;

        return $this;
    }
}
