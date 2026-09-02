# HTTP Recordable Client --- Technical Documentation

## Overview

This module provides an abstraction for an HTTP client capable of
recording and replaying responses using JSON fixtures.

It allows you to:

-   Execute real HTTP calls
-   Record responses as JSON fixtures
-   Replay recorded fixtures without performing real HTTP calls
-   Dynamically control whether external calls are enabled

This design is particularly useful for:

-   Integration testing
-   Offline environments
-   Debugging external API integrations
-   Stabilizing tests that depend on third-party services

------------------------------------------------------------------------

## Architecture

### 1. RecordableClientInterface

**Namespace:** `App\Http`

Defines the minimal contract required for a recordable HTTP client.

``` php
interface RecordableClientInterface
{
    public function getFixturesDirectory(): string;

    public function getUrl(string $operation, string|int|null $id): string;

    public function getQueryParameters(array $options): array;

    public function request(string $method, string $url, array $options = []): ResponseInterface;
}
```

### Responsibilities

-   Define the fixtures directory
-   Build the target URL
-   Convert options into query parameters
-   Perform the actual HTTP request

------------------------------------------------------------------------

### 2. AbstractRecordableClient

**Namespace:** `App\Http`

An abstract class implementing `RecordableClientInterface` and providing
the fixture recording/replay logic.

### Constructor

``` php
public function __construct(
    private readonly Filesystem $filesystem,
    private readonly HttpFixtureFactory $factory,
    private readonly bool $record = false,
    private readonly bool $httpCallEnabled = true,
)
```

### Parameters

-   `Filesystem`: Handles file storage operations
-   `HttpFixtureFactory`: Creates fixture objects from JSON or HTTP
    responses
-   `$record`:
    -   `true` → Record HTTP responses to disk
    -   `false` → Do not save responses
-   `$httpCallEnabled`:
    -   `true` → Perform real HTTP calls
    -   `false` → Replay fixtures only

------------------------------------------------------------------------

## Fixture File Naming Strategy

``` php
public function getFixtureFileName(string $operation, array $query = []): string
```

The filename is generated using:

``` php
hash('sha256', serialize([
    'operation' => $operation,
    'query' => $query,
]));
```

### Final format:

    {fixtures_directory}/{sha256_hash}.json

This ensures:

-   Uniqueness
-   Stability
-   No collisions
-   Independence from query parameter ordering

------------------------------------------------------------------------

## Request Execution Flow

``` php
public function processRequest(...)
```

### Detailed Steps

1.  Build the URL using `getUrl()`
2.  Call the `beforeRequest()` hook
3.  If `httpCallEnabled = false`:
    -   Load fixture from disk
    -   Create `HttpFixture` instance
    -   Call the `afterResponse()` hook with the fixture
    -   Return a Symfony `Response` based on fixture data
4.  Otherwise:
    -   Execute real HTTP request via `request()`
    -   Call the `afterResponse()` hook with the response
    -   If `$record = true`:
        -   Convert response into `HttpFixture`
        -   Save fixture JSON to disk
    -   Return a Symfony `Response`

> Note: `afterResponse()` is invoked in **both** branches — its parameter
> type is `ResponseInterface|HttpFixture` precisely so hooks can run in
> replay mode too.

------------------------------------------------------------------------

## Extension Hooks

### beforeRequest()

``` php
protected function beforeRequest(string $url, array $options): void
```

Can be used to:

-   Log outgoing requests
-   Modify headers
-   Inject metrics
-   Add tracing

------------------------------------------------------------------------

### afterResponse()

``` php
protected function afterResponse(ResponseInterface|HttpFixture $response): void
```

Can be used to:

-   Log responses
-   Add monitoring
-   Transform responses
-   Dispatch events

------------------------------------------------------------------------

## Operating Modes

| Mode           | httpCallEnabled | record | Behavior                       |
| -------------- | --------------- | ------ | ------------------------------ |
| Normal         | true            | false  | Real HTTP calls                |
| Record         | true            | true   | Real calls + fixture recording |
| Replay         | false           | false  | Fixture replay only            |
| Strict Offline | false           | true   | Fixture replay only            |

------------------------------------------------------------------------

## Typical Testing Workflow

1.  Run tests with `$record = true`
2.  Fixtures are automatically generated
3.  Disable HTTP calls (`httpCallEnabled = false`)
4.  Tests become deterministic and stable

------------------------------------------------------------------------

## Example Concrete Implementation

``` php
final class MyApiClient extends AbstractRecordableClient
{
    public function getFixturesDirectory(): string
    {
        return __DIR__.'/fixtures';
    }

    public function getUrl(string $operation, string|int|null $id): string
    {
        return 'https://api.example.com/'.$operation.'/'.$id;
    }

    public function getQueryParameters(array $options): array
    {
        return ['query' => $options];
    }

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        return $this->httpClient->request($method, $url, $options);
    }
}
```

------------------------------------------------------------------------

## Best Practices

-   Never commit fixtures containing sensitive data.
-   Use a dedicated fixture directory per client.
-   Regularly clean obsolete fixtures.
-   Never enable recording in production.
-   Log fixture loading errors explicitly.

------------------------------------------------------------------------

## Important Considerations

-   Fixture hash depends on `serialize()` --- any structural change in
    options will generate new fixtures.
-   In replay mode, missing fixtures will cause runtime errors.
-   No built-in fixture invalidation mechanism is provided.
