<?php

declare(strict_types=1);

namespace App\Http\Gitlab;

use App\Http\AbstractRecordableClient;
use App\Http\Fixture\Factory\HttpFixtureFactory;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class GitlabGraphQlClient extends AbstractRecordableClient
{
    private const int MAX_MERGE_REQUEST_PAGES = 10;
    private const int ITEMS_PER_PAGE = 100;

    public function __construct(
        private readonly HttpClientInterface $gitlabGraphQlClient,
        private readonly Filesystem $filesystem,
        private readonly HttpFixtureFactory $factory,
        private readonly string $projectDir,
        private readonly string $projectPath,
        private readonly bool $record = false,
        private readonly bool $httpCallEnabled = true,
    ) {
        parent::__construct(
            $this->filesystem,
            $this->factory,
            $this->record,
            $this->httpCallEnabled
        );
    }

    public function query(string $operation, string $query, array $variables = []): array
    {
        $payload = [
            'query' => $query,
        ];

        if ([] !== $variables) {
            $payload['variables'] = $variables;
        }

        $response = $this->processRequest(
            $operation,
            null,
            $payload,
            'POST'
        );

        $data = json_decode($response->getContent(), true, 512, \JSON_THROW_ON_ERROR);

        if (!empty($data['errors'])) {
            throw new \RuntimeException(json_encode($data['errors'], \JSON_THROW_ON_ERROR));
        }

        return $data['data'] ?? [];
    }

    public function getMergeRequests(array $filters = [], ?string $after = null): array
    {
        return $this->query('merge_requests', <<<'GRAPHQL'
            query(
                $projectPath: ID!,
                $first: Int!,
                $after: String,
                $mergedAfter: Time,
                $mergedBefore: Time,
                $sort: MergeRequestSort
            ) {
                project(fullPath: $projectPath) {
                    mergeRequests(
                        first: $first,
                        after: $after,
                        state: merged,
                        mergedAfter: $mergedAfter,
                        mergedBefore: $mergedBefore,
                        sort: $sort
                    ) {
                        count
                        pageInfo {
                            hasNextPage
                            endCursor
                        }
                        nodes {
                            iid
                            title
                            webUrl
                            mergedAt
                            updatedAt
                            approved
                            approvedBy {
                                nodes {
                                    name
                                    username
                                    publicEmail
                                }
                            }
                            author {
                                name
                                username
                                publicEmail
                            }
                        }
                    }
                }
            }
            GRAPHQL, [
            'projectPath' => $this->projectPath,
            'first' => self::ITEMS_PER_PAGE,
            'after' => $after,
            'mergedAfter' => $filters['mergedAfter'] ?? null,
            'mergedBefore' => $filters['mergedBefore'] ?? null,
            'sort' => $filters['sort'] ?? null,
        ]);
    }

    public function getAllMergeRequests(array $filters = []): array
    {
        $allNodes = [];
        $after = null;
        $total = 0;
        $page = 0;

        do {
            ++$page;

            if ($page > self::MAX_MERGE_REQUEST_PAGES) {
                throw new \RuntimeException(\sprintf('GitLab GraphQL limit reached (%d merge requests). Please narrow the date filters.', self::MAX_MERGE_REQUEST_PAGES * self::ITEMS_PER_PAGE));
            }

            $data = $this->getMergeRequests($filters, $after);

            $connection = $data['project']['mergeRequests'] ?? [];
            $nodes = $connection['nodes'] ?? [];

            $allNodes = array_merge($allNodes, $nodes);
            $total = (int) ($connection['count'] ?? \count($allNodes));

            $pageInfo = $connection['pageInfo'] ?? [];
            $after = $pageInfo['endCursor'] ?? null;
            $hasNextPage = (bool) ($pageInfo['hasNextPage'] ?? false);
        } while ($hasNextPage && null !== $after);

        return [
            'count' => $total,
            'nodes' => $allNodes,
        ];
    }

    public function getFixturesDirectory(): string
    {
        return $this->projectDir.'/tests/fixtures/gitlab/graphql';
    }

    public function getUrl(string $operation, int|string|null $id): string
    {
        return 'api/graphql';
    }

    public function getQueryParameters(array $options): array
    {
        return [
            'json' => $options,
        ];
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        if ('' !== $url && '/' === $url[0]) {
            $url = mb_ltrim($url, '/');
        }

        return $this->gitlabGraphQlClient->request($method, $url, $options);
    }
}
