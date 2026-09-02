<?php

declare(strict_types=1);

namespace App\Http;

use App\Http\Fixture\Factory\HttpFixtureFactory;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class AzureDocumentIntelligenceClient extends AbstractRecordableClient
{
    private const string ANALYZE_PATH = 'documentintelligence/documentModels/prebuilt-read:analyze';
    private const string API_VERSION = '2024-11-30';
    private const int    MAX_POLLS = 30;
    private const int    POLL_SLEEP = 2;

    public function __construct(
        private readonly HttpClientInterface $azureDocumentIntelligenceClient,
        private readonly Filesystem $filesystem,
        private readonly HttpFixtureFactory $factory,
        private readonly string $projectDir,
        private readonly bool $record = false,
        private readonly bool $httpCallEnabled = true,
    ) {
        parent::__construct($this->filesystem, $this->factory, $this->record, $this->httpCallEnabled);
    }

    /**
     * Submit raw bytes of a document to Azure Document Intelligence
     * (prebuilt-read) and return the extracted plain text.
     *
     * Supported formats: PDF, JPEG, PNG, BMP, TIFF, HEIF, DOCX, XLSX,
     * PPTX, HTML. Azure infers the format from the bytes.
     */
    public function doRequest(string $content): ?string
    {
        try {
            $operationLocation = $this->submitDocument($content);
            if (null === $operationLocation) {
                return null;
            }

            return $this->pollForResult($operationLocation);
        } catch (ExceptionInterface|\JsonException) {
            return null;
        }
    }

    public function getFixturesDirectory(): string
    {
        return \sprintf('%s/tests/fixtures/azure/document-intelligence/http', $this->projectDir);
    }

    public function getUrl(string $operation, string|int|null $id): string
    {
        return $operation;
    }

    public function getQueryParameters(array $options): array
    {
        return $options;
    }

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        return $this->azureDocumentIntelligenceClient->request($method, $url, $options);
    }

    private function submitDocument(string $content): ?string
    {
        $url = \sprintf('%s?api-version=%s', self::ANALYZE_PATH, self::API_VERSION);

        $response = $this->processRequest($url, null, ['body' => $content], Request::METHOD_POST);

        if (202 !== $response->getStatusCode()) {
            return null;
        }

        return $response->headers->get('operation-location');
    }

    /**
     * Azure processes documents asynchronously: polls $operationLocation until the status is
     * "succeeded" (returns extracted text) or "failed" / timeout (returns null).
     * Max wait: MAX_POLLS × POLL_SLEEP seconds.
     */
    private function pollForResult(string $operationLocation): ?string
    {
        for ($i = 0; $i < self::MAX_POLLS; ++$i) {
            if ($i > 0) {
                sleep(self::POLL_SLEEP);
            }

            $data = json_decode(
                $this->processRequest($operationLocation)->getContent(),
                true,
                512,
                \JSON_THROW_ON_ERROR,
            );
            $status = $data['status'] ?? 'unknown';

            if ('succeeded' === $status) {
                return $data['analyzeResult']['content'] ?? null;
            }

            if ('failed' === $status) {
                return null;
            }
        }

        return null;
    }
}
