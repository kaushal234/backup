<?php

declare(strict_types=1);

namespace App\Tests\Http;

use App\Http\AzureDocumentIntelligenceClient;
use App\Http\Fixture\Factory\HttpFixtureFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class AzureDocumentIntelligenceClientTest extends TestCase
{
    private const string OPERATION_LOCATION = 'https://azure.example/operations/abc';
    private const string PROJECT_DIR = '/tmp/project';
    private const string PDF_CONTENT = '%PDF-1.4 fake content';

    public function testReturnsExtractedContentWhenAnalysisSucceeds(): void
    {
        $submitResponse = $this->createResponseMock(
            Response::HTTP_ACCEPTED,
            '',
            ['operation-location' => [self::OPERATION_LOCATION]],
        );

        $pollResponse = $this->createResponseMock(
            Response::HTTP_OK,
            json_encode([
                'status' => 'succeeded',
                'analyzeResult' => ['content' => 'Extracted text from PDF'],
            ]),
        );

        $httpClientMock = $this->createMock(HttpClientInterface::class);
        $httpClientMock->expects($this->exactly(2))
            ->method('request')
            ->willReturnCallback(static function (string $method, string $url) use ($submitResponse, $pollResponse): ResponseInterface {
                if (Request::METHOD_POST === $method) {
                    return $submitResponse;
                }

                return $pollResponse;
            });

        $client = $this->createClient($httpClientMock);

        $this->assertSame('Extracted text from PDF', $client->doRequest(self::PDF_CONTENT));
    }

    public function testReturnsNullWhenSubmitDoesNotReturn202(): void
    {
        $submitResponse = $this->createResponseMock(Response::HTTP_BAD_REQUEST, '');

        $httpClientMock = $this->createMock(HttpClientInterface::class);
        $httpClientMock->expects($this->once())
            ->method('request')
            ->willReturn($submitResponse);

        $client = $this->createClient($httpClientMock);

        $this->assertNull($client->doRequest(self::PDF_CONTENT));
    }

    public function testReturnsNullWhenAnalysisFails(): void
    {
        $submitResponse = $this->createResponseMock(
            Response::HTTP_ACCEPTED,
            '',
            ['operation-location' => [self::OPERATION_LOCATION]],
        );

        $pollResponse = $this->createResponseMock(
            Response::HTTP_OK,
            json_encode(['status' => 'failed']),
        );

        $httpClientMock = $this->createMock(HttpClientInterface::class);
        $httpClientMock->method('request')->willReturnCallback(
            static function (string $method) use ($submitResponse, $pollResponse): ResponseInterface {
                return Request::METHOD_POST === $method ? $submitResponse : $pollResponse;
            }
        );

        $client = $this->createClient($httpClientMock);

        $this->assertNull($client->doRequest(self::PDF_CONTENT));
    }

    public function testReturnsNullWhenHttpClientThrows(): void
    {
        $exception = new class extends \RuntimeException implements TransportExceptionInterface {};

        $httpClientMock = $this->createMock(HttpClientInterface::class);
        $httpClientMock->method('request')->willThrowException($exception);

        $client = $this->createClient($httpClientMock);

        $this->assertNull($client->doRequest(self::PDF_CONTENT));
    }

    public function testReturnsNullWhenJsonIsInvalid(): void
    {
        $submitResponse = $this->createResponseMock(
            Response::HTTP_ACCEPTED,
            '',
            ['operation-location' => [self::OPERATION_LOCATION]],
        );

        $pollResponse = $this->createResponseMock(Response::HTTP_OK, 'not-json');

        $httpClientMock = $this->createMock(HttpClientInterface::class);
        $httpClientMock->method('request')->willReturnCallback(
            static function (string $method) use ($submitResponse, $pollResponse): ResponseInterface {
                return Request::METHOD_POST === $method ? $submitResponse : $pollResponse;
            }
        );

        $client = $this->createClient($httpClientMock);

        $this->assertNull($client->doRequest(self::PDF_CONTENT));
    }

    public function testGetFixturesDirectoryUsesProjectDir(): void
    {
        $client = $this->createClient($this->createMock(HttpClientInterface::class));

        $this->assertSame(
            self::PROJECT_DIR.'/tests/fixtures/azure/document-intelligence/http',
            $client->getFixturesDirectory(),
        );
    }

    public function testGetUrlReturnsOperationUnchanged(): void
    {
        $client = $this->createClient($this->createMock(HttpClientInterface::class));

        $this->assertSame('some/operation', $client->getUrl('some/operation', null));
        $this->assertSame('some/operation', $client->getUrl('some/operation', 42));
    }

    public function testGetQueryParametersReturnsOptionsUnchanged(): void
    {
        $client = $this->createClient($this->createMock(HttpClientInterface::class));

        $this->assertSame(['body' => 'foo'], $client->getQueryParameters(['body' => 'foo']));
    }

    private function createClient(HttpClientInterface $httpClient): AzureDocumentIntelligenceClient
    {
        return new AzureDocumentIntelligenceClient(
            $httpClient,
            $this->createMock(Filesystem::class),
            $this->createMock(HttpFixtureFactory::class),
            self::PROJECT_DIR,
            record: false,
            httpCallEnabled: true,
        );
    }

    /**
     * @param array<string, string[]> $headers
     */
    private function createResponseMock(int $statusCode, string $content, array $headers = []): ResponseInterface
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn($statusCode);
        $response->method('getContent')->willReturn($content);
        $response->method('getHeaders')->willReturn($headers);

        return $response;
    }
}
