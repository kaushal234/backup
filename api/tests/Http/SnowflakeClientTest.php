<?php

declare(strict_types=1);

namespace App\Tests\Http;

use App\Http\Fixture\Factory\HttpFixtureFactory;
use App\Http\SnowflakeClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class SnowflakeClientTest extends TestCase
{
    private const string PROJECT_DIR = '/tmp/project';

    public function testGetFixturesDirectoryUsesProjectDir(): void
    {
        $client = $this->createClient($this->createMock(HttpClientInterface::class));

        self::assertSame(self::PROJECT_DIR.'/tests/fixtures/snowflake/http', $client->getFixturesDirectory());
    }

    public function testGetUrlAlwaysReturnsStatementsRegardlessOfOperationOrId(): void
    {
        $client = $this->createClient($this->createMock(HttpClientInterface::class));

        self::assertSame('statements', $client->getUrl('anything', null));
        self::assertSame('statements', $client->getUrl('anything', 42));
    }

    public function testGetQueryParametersWrapsOptionsUnderJsonKey(): void
    {
        $client = $this->createClient($this->createMock(HttpClientInterface::class));

        self::assertSame(['json' => ['statement' => 'SELECT 1']], $client->getQueryParameters(['statement' => 'SELECT 1']));
    }

    public function testExecuteSendsExpectedPayloadWithoutBindingsWhenNoneGiven(): void
    {
        $httpResponse = $this->createHttpResponseMock(200, json_encode([
            'resultSetMetaData' => ['rowType' => []],
            'data' => [],
        ]));

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->with(Request::METHOD_POST, 'statements', [
                'json' => [
                    'statement' => 'SELECT 1',
                    'timeout' => 60,
                    'warehouse' => 'ALVEST_PRD_WH',
                    'database' => 'LN_PRD',
                    'schema' => 'SILVER',
                    'role' => 'READONLY',
                ],
            ])
            ->willReturn($httpResponse);

        $client = $this->createClient($httpClient);
        $client->execute('SELECT 1', 'LN_PRD', 'SILVER', 'READONLY');

        self::assertTrue(true);
    }

    public function testExecuteUsesDatabaseSchemaAndRoleGivenPerCall(): void
    {
        $httpResponse = $this->createHttpResponseMock(200, json_encode([
            'resultSetMetaData' => ['rowType' => []],
            'data' => [],
        ]));

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->with(Request::METHOD_POST, 'statements', self::callback(
                static fn (array $options): bool => 'OTHER_DB' === ($options['json']['database'] ?? null)
                    && 'OTHER_SCHEMA' === ($options['json']['schema'] ?? null)
                    && 'OTHER_ROLE' === ($options['json']['role'] ?? null)
                    // warehouse stays app-wide, taken from the constructor, not from the call.
                    && 'ALVEST_PRD_WH' === ($options['json']['warehouse'] ?? null)
            ))
            ->willReturn($httpResponse);

        $client = $this->createClient($httpClient);
        $client->execute('SELECT 1', 'OTHER_DB', 'OTHER_SCHEMA', 'OTHER_ROLE');

        self::assertTrue(true);
    }

    public function testExecuteIncludesBindingsWhenProvided(): void
    {
        $bindings = ['1' => ['type' => 'TEXT', 'value' => '300']];

        $httpResponse = $this->createHttpResponseMock(200, json_encode([
            'resultSetMetaData' => ['rowType' => []],
            'data' => [],
        ]));

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->with(Request::METHOD_POST, 'statements', self::callback(
                static fn (array $options): bool => ($options['json']['bindings'] ?? null) === $bindings
            ))
            ->willReturn($httpResponse);

        $client = $this->createClient($httpClient);
        $client->execute('SELECT 1 WHERE a = ?', 'LN_PRD', 'SILVER', 'READONLY', $bindings);

        self::assertTrue(true);
    }

    public function testExecuteThrowsWithMessageFromResponseBodyWhenNotSuccessful(): void
    {
        $httpResponse = $this->createHttpResponseMock(400, json_encode([
            'message' => 'Role "PBI_READ_ROLE" specified in the connect string is not granted to this user.',
        ]));

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->method('request')->willReturn($httpResponse);

        $client = $this->createClient($httpClient);

        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('Snowflake SQL API error (HTTP 400): Role "PBI_READ_ROLE" specified in the connect string is not granted to this user.');

        $client->execute('SELECT 1', 'LN_PRD', 'SILVER', 'READONLY');
    }

    public function testExecuteFallsBackToRawContentWhenErrorBodyHasNoMessageKey(): void
    {
        $httpResponse = $this->createHttpResponseMock(500, 'Internal Server Error');

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->method('request')->willReturn($httpResponse);

        $client = $this->createClient($httpClient);

        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('Snowflake SQL API error (HTTP 500): Internal Server Error');

        $client->execute('SELECT 1', 'LN_PRD', 'SILVER', 'READONLY');
    }

    public function testQueryMapsRowsUsingColumnNamesFromResultSetMetadata(): void
    {
        $httpResponse = $this->createHttpResponseMock(200, json_encode([
            'resultSetMetaData' => ['rowType' => [['name' => 'SITE'], ['name' => 'ITEM']]],
            'data' => [['300', '43305281'], ['420', '075832-U']],
        ]));

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->method('request')->willReturn($httpResponse);

        $client = $this->createClient($httpClient);

        self::assertSame(
            [
                ['SITE' => '300', 'ITEM' => '43305281'],
                ['SITE' => '420', 'ITEM' => '075832-U'],
            ],
            $client->query('SELECT SITE, ITEM FROM A', 'LN_PRD', 'SILVER', 'READONLY'),
        );
    }

    public function testMapRowsReturnsEmptyArrayWhenResultHasNoData(): void
    {
        $client = $this->createClient($this->createMock(HttpClientInterface::class));

        self::assertSame([], $client->mapRows([]));
        self::assertSame(
            [],
            $client->mapRows(['resultSetMetaData' => ['rowType' => [['name' => 'SITE']]], 'data' => []]),
        );
    }

    private function createClient(HttpClientInterface $httpClient): SnowflakeClient
    {
        return new SnowflakeClient(
            $httpClient,
            'ALVEST_PRD_WH',
            $this->createMock(Filesystem::class),
            $this->createMock(HttpFixtureFactory::class),
            self::PROJECT_DIR,
            record: false,
            httpCallEnabled: true,
        );
    }

    private function createHttpResponseMock(int $statusCode, string $content, array $headers = []): ResponseInterface
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn($statusCode);
        $response->method('getContent')->willReturn($content);
        $response->method('getHeaders')->willReturn($headers);

        return $response;
    }
}
