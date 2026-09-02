<?php

declare(strict_types=1);

namespace App\Http;

use App\Http\Fixture\Factory\HttpFixtureFactory;
use App\ION\Token\IONTokenProvider;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class LnClient extends AbstractRecordableClient
{
    public function __construct(
        private readonly HttpClientInterface $lnClient,
        private readonly HttpFixtureFactory $factory,
        private readonly IONTokenProvider $tokenProvider,
        private readonly Filesystem $filesystem,
        private readonly string $projectDir,
        private readonly int $ionCompany,
        private readonly bool $record = false,
        private readonly bool $httpCallEnabled = true,
    ) {
        parent::__construct($this->filesystem, $this->factory, $this->record, $this->httpCallEnabled);
    }

    public function doRequest(string $operation, array $options = []): Response
    {
        return $this->processRequest($operation, null, $options);
    }

    public function getFixturesDirectory(): string
    {
        return $this->projectDir.'/tests/fixtures/ln/http';
    }

    public function getUrl(string $operation, int|string|null $id): string
    {
        return \sprintf('LN/lnapi/odata/%s', $operation);
    }

    public function getQueryParameters(array $options): array
    {
        if (!empty($options)) {
            return ['$filter' => implode(' and ', $options)];
        }

        return [];
    }

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        $token = $this->tokenProvider->getToken();

        return $this->lnClient->request(
            $method,
            $url,
            [
                'headers' => [
                    'Authorization' => \sprintf('Bearer %s', $token),
                    'X-Infor-LnCompany' => $this->ionCompany,
                ],
                'query' => $options,
            ])
        ;
    }
}
