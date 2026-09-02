<?php

declare(strict_types=1);

namespace App\Http;

use App\Http\Fixture\Factory\HttpFixtureFactory;
use App\Jira\Http\JiraClientInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

abstract class AbstractJiraClient extends AbstractRecordableClient implements JiraClientInterface
{
    final public const REST_URL = '/rest/api/3';

    protected HttpClientInterface $httpClient;

    public function __construct(
        protected readonly Filesystem $filesystem,
        protected readonly HttpFixtureFactory $factory,
        protected readonly string $projectDir,
        protected readonly bool $record = false,
        protected readonly bool $httpCallEnabled = true,
    ) {
        parent::__construct($filesystem, $factory, $record, $httpCallEnabled);
    }

    public function doRequest(
        string $operation,
        ?string $id = null,
        array $options = [],
        string $method = Request::METHOD_GET
    ): Response {
        return $this->processRequest($operation, $id, $options, $method);
    }

    abstract public function getFixturesDirectory(): string;

    public function getUrl(string $operation, string|int|null $id): string
    {
        $url = \sprintf('%s/%s', self::REST_URL, $operation);
        if (null !== $id) {
            $url = \sprintf('%s/%s', $url, $id);
        }

        return $url;
    }

    public function getQueryParameters(array $options): array
    {
        return $options;
    }

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        return $this->httpClient->request($method, $url, $options);
    }
}
