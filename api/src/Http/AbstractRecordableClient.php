<?php

declare(strict_types=1);

namespace App\Http;

use App\Http\Fixture\Factory\HttpFixtureFactory;
use App\Http\Fixture\Resource\HttpFixture;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\ResponseInterface;

abstract class AbstractRecordableClient implements RecordableClientInterface
{
    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly HttpFixtureFactory $factory,
        private readonly bool $record = false,
        private readonly bool $httpCallEnabled = true,
    ) {
    }

    public function getFixtureFileName(string $operation, array $query = []): string
    {
        return \sprintf('%s/%s.json', $this->getFixturesDirectory(), hash('sha256', serialize([
            'operation' => $operation,
            'query' => $query,
        ])));
    }

    public function processRequest(string $operation, int|string|null $id = null, array $options = [], string $method = Request::METHOD_GET): Response
    {
        $url = $this->getUrl($operation, $id);

        $this->beforeRequest($url, $options);

        if (!$this->httpCallEnabled) {
            $fixture = $this->factory->createFromJSON(file_get_contents($this->getFixtureFileName($url, $options)));

            $this->afterResponse($fixture);

            return new Response($fixture->content, $fixture->statusCode, $fixture->headers);
        }

        $response = $this->request($method, $url, $this->getQueryParameters($options));

        $this->afterResponse($response);

        if ($this->record) {
            $fixture = $this->factory->createFromResponse($response);
            $this->filesystem->dumpFile($this->getFixtureFileName($url, $options), json_encode($fixture));
        }

        return new Response($response->getContent(false), $response->getStatusCode(), $response->getHeaders());
    }

    protected function beforeRequest(string $url, array $options): void
    {
        // do nothing by default
    }

    protected function afterResponse(ResponseInterface|HttpFixture $response): void
    {
        // do nothing by default
    }
}
