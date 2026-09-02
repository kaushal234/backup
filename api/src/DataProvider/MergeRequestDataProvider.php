<?php

declare(strict_types=1);

namespace App\DataProvider;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\MIS\Gitlab\MergeRequest;
use App\Entity\Directory\People;
use App\Http\Gitlab\GitlabGraphQlClient;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

readonly class MergeRequestDataProvider implements ProviderInterface
{
    public function __construct(
        private IriConverterInterface $iriConverter,
        private GitlabGraphQlClient $gitlabGraphQlClient,
        private DenormalizerInterface $denormalizer,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $filters = $context['filters'] ?? [];

        $paginationEnabled = filter_var(
            $filters['pagination'] ?? true,
            \FILTER_VALIDATE_BOOLEAN
        );

        if ($paginationEnabled) {
            throw new BadRequestException('This endpoint currently supports only requests with pagination=false.');
        }

        $order = $filters['order'] ?? [];

        $orderBy = array_key_first($order);
        $sort = null;

        if (null !== $orderBy) {
            $sort = mb_strtolower((string) ($order[$orderBy] ?? 'desc'));
        }

        $graphqlFilters = [
            'mergedAfter' => isset($filters['merged_after'])
                ? (new \DateTimeImmutable($filters['merged_after']))->format(\DATE_ATOM)
                : null,
            'mergedBefore' => isset($filters['merged_before'])
                ? (new \DateTimeImmutable($filters['merged_before']))->format(\DATE_ATOM)
                : null,
            'sort' => match ($orderBy) {
                'created_at' => 'asc' === $sort ? 'CREATED_ASC' : 'CREATED_DESC',
                'updated_at' => 'asc' === $sort ? 'UPDATED_ASC' : 'UPDATED_DESC',
                'merged_at' => 'asc' === $sort ? 'MERGED_AT_ASC' : 'MERGED_AT_DESC',
                default => null,
            },
        ];

        $authorEmail = null;
        if (!empty($filters['author'])) {
            try {
                /** @var People $user */
                $user = $this->iriConverter->getResourceFromIri($filters['author']);
                $authorEmail = $user->getEmail();
            } catch (\Exception) {
                throw new BadRequestException('author is not valid');
            }
        }

        $data = $this->gitlabGraphQlClient->getAllMergeRequests($graphqlFilters);

        $nodes = $data['nodes'] ?? [];

        if (null !== $authorEmail) {
            $nodes = array_values(array_filter(
                $nodes,
                static fn (array $node): bool => ($node['author']['publicEmail'] ?? null) === $authorEmail
            ));
        }

        $rows = array_map(static function (array $node): array {
            return [
                'iid' => (int) $node['iid'],
                'title' => $node['title'],
                'web_url' => $node['webUrl'],
                'merged_at' => $node['mergedAt'],
                'updated_at' => $node['updatedAt'],
                'approved' => (bool) $node['approved'],
                'author' => $node['author']['name']
                    ?? $node['author']['username']
                        ?? '',
                'approvers' => array_values(array_filter(array_map(
                    static fn (array $approver): string => $approver['name']
                        ?? $approver['username']
                        ?? '',
                    $node['approvedBy']['nodes'] ?? []
                ))),
            ];
        }, $nodes);

        return $this->denormalizer->denormalize(
            $rows,
            MergeRequest::class.'[]',
        );
    }
}
