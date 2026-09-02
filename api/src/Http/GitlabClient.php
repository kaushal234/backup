<?php

declare(strict_types=1);

namespace App\Http;

use App\Http\Fixture\Factory\HttpFixtureFactory;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class GitlabClient extends AbstractRecordableClient
{
    final public const string REST_URL = 'projects';

    public function __construct(
        private readonly HttpClientInterface $gitlabClient,
        private readonly Filesystem $filesystem,
        private readonly HttpFixtureFactory $factory,
        private readonly string $projectDir,
        private readonly bool $record = false,
        private readonly bool $httpCallEnabled = true,
    ) {
        parent::__construct($this->filesystem, $this->factory, $this->record, $this->httpCallEnabled);
    }

    public function getCommits(int $projectId, \DateTime $since, \DateTime $until): array
    {
        $response = $this->processRequest(
            'repository/commits',
            $projectId,
            [
                'query' => [
                    'since' => $since->format('Y-m-d'),
                    'until' => $until->format('Y-m-d'),
                    'per_page' => 100,
                    'ref_name' => 'main',
                ],
            ]
        );

        return json_decode($response->getContent(), true, 512, \JSON_THROW_ON_ERROR);
    }

    public function getMergeRequests(int $projectId, array $query = []): Response
    {
        return $this->processRequest('merge_requests', $projectId, [
            'query' => $query,
        ]);
    }

    public function getApprovals(int $projectId, int $iid): Response
    {
        return $this->processRequest(\sprintf('merge_requests/%d/approvals', $iid), $projectId);
    }

    public function getFixturesDirectory(): string
    {
        return $this->projectDir.'/tests/fixtures/gitlab/http';
    }

    public function getUrl(string $operation, int|string|null $id): string
    {
        return null !== $id
            ? \sprintf('%s/%s/%s', self::REST_URL, $id, $operation)
            : \sprintf('%s/%s', self::REST_URL, $operation);
    }

    public function getQueryParameters(array $options): array
    {
        return $options;
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        if ('' !== $url && '/' === $url[0]) {
            $url = mb_ltrim($url, '/');
        }

        return $this->gitlabClient->request($method, $url, $options);
    }
}
