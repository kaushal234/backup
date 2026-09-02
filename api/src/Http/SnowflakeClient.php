<?php

declare(strict_types=1);

namespace App\Http;

use App\Http\Fixture\Factory\HttpFixtureFactory;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Thin wrapper around the Snowflake SQL API (POST /api/v2/statements).
 *
 * Authentication (Bearer PAT) and base URL are configured on the
 * `snowflake.client` scoped HTTP client (see config/packages/framework.yaml).
 * The compute warehouse is a single, app-wide value injected here as a plain
 * value (see config/services.yaml). Database, schema and role are NOT
 * app-wide: which one to use depends on the data being queried, so every
 * call site provides them explicitly (see App\Snowflake\QueryBuilder::useConnection(),
 * which is what App\Snowflake\AbstractCollectionProvider-based providers use).
 *
 * Supports the same record/replay mechanism as the other external API
 * clients (LnClient, JiraClient, ...): set HTTP_CLIENT_RECORD=1 to make real
 * calls to Snowflake and save the responses as fixtures, or leave
 * HTTP_CLIENT_ENABLED=0 (the Behat/test default, see config/services_test.yaml)
 * to replay saved fixtures with no real Snowflake access at all. Fixtures
 * live under tests/fixtures/snowflake/http, one JSON file per distinct query
 * (hashed from the statement + bindings + warehouse/database/schema/role).
 *
 * Limitation (acceptable for the current use case): this does not follow
 * the `resultSetMetaData.partitionInfo` pagination links. If a query ever
 * returns more than one partition, only the first one is read. Revisit this
 * if/when a query is expected to return large result sets.
 */
class SnowflakeClient extends AbstractRecordableClient
{
    public function __construct(
        private readonly HttpClientInterface $snowflakeClient,
        private readonly string $snowflakeWarehouse,
        Filesystem $filesystem,
        HttpFixtureFactory $factory,
        private readonly string $projectDir,
        bool $record = false,
        bool $httpCallEnabled = true,
    ) {
        parent::__construct($filesystem, $factory, $record, $httpCallEnabled);
    }

    public function getFixturesDirectory(): string
    {
        return $this->projectDir.'/tests/fixtures/snowflake/http';
    }

    public function getUrl(string $operation, int|string|null $id): string
    {
        // Single fixed endpoint: the fixture is keyed on $options (the
        // statement/bindings/...), not on the URL.
        return 'statements';
    }

    public function getQueryParameters(array $options): array
    {
        return ['json' => $options];
    }

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        return $this->snowflakeClient->request($method, $url, $options);
    }

    /**
     * Executes a SQL statement and returns the rows as associative arrays keyed by column name.
     *
     * @param array<int|string, mixed> $bindings see https://docs.snowflake.com/en/developer-guide/sql-api/submitting-requests#using-bind-variables-in-a-statement
     *
     * @return array<int, array<string, mixed>>
     */
    public function query(string $statement, string $database, string $schema, string $role, array $bindings = [], int $timeout = 60): array
    {
        return $this->mapRows($this->execute($statement, $database, $schema, $role, $bindings, $timeout));
    }

    /**
     * Executes a SQL statement and returns the raw Snowflake SQL API payload
     * (useful if you need resultSetMetaData, statementHandle, etc.).
     *
     * @param array<int|string, mixed> $bindings
     *
     * @return array<string, mixed>
     */
    public function execute(string $statement, string $database, string $schema, string $role, array $bindings = [], int $timeout = 60): array
    {
        $payload = [
            'statement' => $statement,
            'timeout' => $timeout,
            'warehouse' => $this->snowflakeWarehouse,
            'database' => $database,
            'schema' => $schema,
            'role' => $role,
        ];

        if ([] !== $bindings) {
            $payload['bindings'] = $bindings;
        }

        // "statements" label is only for readability: getUrl() always
        // returns the same endpoint, the fixture file is keyed on $payload.
        $response = $this->processRequest('statements', null, $payload, Request::METHOD_POST);

        $result = json_decode($response->getContent(), true) ?? [];

        if (!$response->isSuccessful()) {
            throw new \RuntimeException(\sprintf('Snowflake SQL API error (HTTP %d): %s', $response->getStatusCode(), $result['message'] ?? $response->getContent()));
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $result raw payload as returned by execute()
     *
     * @return array<int, array<string, mixed>>
     */
    public function mapRows(array $result): array
    {
        $columns = array_map(
            static fn (array $column): string => (string) $column['name'],
            $result['resultSetMetaData']['rowType'] ?? []
        );

        $rows = [];
        foreach ($result['data'] ?? [] as $rawRow) {
            $rows[] = array_combine($columns, $rawRow);
        }

        return $rows;
    }
}
