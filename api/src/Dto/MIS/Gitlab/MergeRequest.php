<?php

declare(strict_types=1);

namespace App\Dto\MIS\Gitlab;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\DataProvider\MergeRequestDataProvider;
use App\Filter\MIS\Gitlab\MergeRequestFilter;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedPath;

#[ApiResource(operations: [
    new GetCollection(
        uriTemplate: '/gitlab/projects/merge_requests',
        normalizationContext: [
            'groups' => ['mr:read'],
        ],
        security: 'is_granted("FEATURE_READ_GITLAB_MERGE_REQUEST")',
        provider: MergeRequestDataProvider::class,
    ),
])]
#[ApiFilter(MergeRequestFilter::class)]
final class MergeRequest
{
    #[ApiProperty(identifier: true)]
    #[Groups(['mr:read'])]
    public int $iid;

    #[Groups(['mr:read'])]
    public string $title;

    #[Groups(['mr:read'])]
    #[SerializedPath('[web_url]')]
    public string $link;

    #[Groups(['mr:read'])]
    #[SerializedPath('[merged_at]')]
    public \DateTimeImmutable $mergedAt;

    #[Groups(['mr:read'])]
    #[SerializedPath('[updated_at]')]
    public \DateTimeImmutable $updatedAt;

    #[Groups(['mr:read'])]
    public ?string $author = null;

    #[Groups(['mr:read'])]
    public bool $approved = false;

    #[Groups(['mr:read'])]
    public array $approvers = [];
}
