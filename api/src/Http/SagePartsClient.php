<?php

declare(strict_types=1);

namespace App\Http;

use App\Http\Fixture\Factory\HttpFixtureFactory;
use App\SageParts\P21\ResourceSourceProvider\ResourceSourceProviderInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class SagePartsClient extends AbstractRecordableClient
{
    private ?string $token = null;

    public function __construct(
        private readonly HttpClientInterface $sagepartsClient,
        private readonly HttpFixtureFactory $factory,
        private readonly string $sagePartsConsumerKey,
        private readonly CacheInterface $cache,
        private readonly Filesystem $filesystem,
        private readonly string $projectDir,
        private readonly bool $record = false,
        private readonly bool $httpCallEnabled = true,
    ) {
        if (null === $this->token) {
            $this->token = $this->cache->get('sageparts_token', function (ItemInterface $item) {
                // Cache expire after 7h and 59 minutes because this the expiration time of the token
                $item->expiresAfter(28799);

                return $this->getToken();
            });
        }

        parent::__construct($this->filesystem, $this->factory, $this->record, $this->httpCallEnabled);
    }

    public function getToken(): string
    {
        if (!$this->httpCallEnabled) {
            return file_get_contents($this->getFixtureFileName('token'));
        }

        $response = $this->sagepartsClient->request(Request::METHOD_POST, 'api/security/token', ['headers' => ['Accept' => 'application/json', 'grant_type' => 'client_credentials', 'consumer_key' => $this->sagePartsConsumerKey]])->toArray();
        $token = $response['AccessToken'];

        if ($this->record) {
            $this->filesystem->dumpFile($this->getFixtureFileName('token'), $response['AccessToken']);
        }

        return $token;
    }

    public function doRequest(ResourceSourceProviderInterface $resourceSourceProvider, array $context = []): Response
    {
        return $this->processRequest($resourceSourceProvider->getOperation(), null, $context['filters']);
    }

    public function getFixturesDirectory(): string
    {
        return $this->projectDir.'/tests/fixtures/sage/http';
    }

    public function getUrl(string $operation, int|string|null $id): string
    {
        return \sprintf('odataservice/odata/view/%s', $operation);
    }

    public function getQueryParameters(array $options): array
    {
        return ['$top' => 50, '$filter' => implode('', $options)];
    }

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        return $this->sagepartsClient->request(
            $method,
            $url,
            [
                'headers' => ['Authorization' => \sprintf('Bearer %s', $this->token)],
                'query' => $options,
            ])
        ;
    }
}
