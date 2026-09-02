<?php

declare(strict_types=1);

namespace App\Dto\Quality\FirstArticleQualification;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\DataProcessor\Quality\FirstArticleQualification\DuplicatePlanProcessor;
use App\Entity\Quality\FirstArticleQualification\FirstArticleQualification;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/quality/first_article_qualifications/duplicate_plan',
            normalizationContext: ['groups' => ['plan_duplication:read']],
            denormalizationContext: ['groups' => ['plan_duplication:write']],
            security: "is_granted('FEATURE_FAQ_CREATE')",
            name: 'duplicate_qualification_plan',
            processor: DuplicatePlanProcessor::class,
        ),
    ],
)]
final class PlanDuplication
{
    #[Groups(['plan_duplication:write'])]
    #[Assert\NotNull]
    public ?FirstArticleQualification $source = null;

    /** @var list<FirstArticleQualification> */
    #[Groups(['plan_duplication:write'])]
    #[Assert\Count(min: 1, minMessage: 'Provide at least one target FAQ.')]
    public array $targets = [];

    /**
     * Populated by the processor once the duplication succeeds.
     *
     * @var list<string>
     */
    #[Groups(['plan_duplication:read'])]
    public array $duplicatedFor = [];
}
