# Snowflake Integration --- Technical Documentation

## Overview

This module exposes data living in Snowflake (accessed through the
[Snowflake SQL API](https://docs.snowflake.com/en/developer-guide/sql-api/index))
as regular API Platform resources, without going through Doctrine (there
is no Doctrine DBAL driver for Snowflake in this project).

It provides:

-   `App\Http\SnowflakeClient` --- a thin, recordable HTTP client around
    `POST /api/v2/statements`.
-   `App\Snowflake\QueryBuilder` --- a minimal SQL builder (SELECT /
    FROM / JOIN / WHERE / ORDER BY / LIMIT / OFFSET) so callers never
    concatenate raw SQL.
-   `App\Snowflake\Attribute\Column` + `App\Snowflake\ColumnMapper` ---
    a way to declare, once per resource property, which Snowflake
    column it maps to.
-   `App\Snowflake\Filter\SearchFilter` / `App\Snowflake\Filter\OrderFilter`
    --- ApiFilter-compatible filters translating query parameters into
    `WHERE`/`ORDER BY` clauses.
-   `App\Snowflake\FilterExtension` --- resolves and applies the
    filters declared on an operation, mirroring
    `ApiPlatform\Doctrine\Orm\Extension\FilterExtension`.
-   `App\Snowflake\AbstractCollectionProvider` --- a generic ApiPlatform
    collection `ProviderInterface`, wiring pagination + filters +
    column mapping together so a concrete provider only has to declare
    its `FROM`/`JOIN`s and how to map a row to a DTO.
-   `App\Snowflake\CollectionPaginator` --- the `PaginatorInterface`
    implementation returned by the provider.

In short: this is a small, purpose-built substitute for Doctrine's ORM
integration with ApiPlatform, scoped to what read-only, filterable,
paginated Snowflake-backed collections need.

------------------------------------------------------------------------

## Configuration

### Environment variables

``` dotenv
SNOWFLAKE_API_BASE_URL=https://<account_identifier>.snowflakecomputing.com/api/v2/
SNOWFLAKE_TOKEN=                 # Programmatic Access Token, set in .env.local only, never committed
SNOWFLAKE_WAREHOUSE=ALVEST_PRD_WH
```

`SNOWFLAKE_TOKEN` must live in `.env.local` (untracked) or in the
deployment secret store. Never commit a real token.

> **Database, schema and role are intentionally *not* environment
> variables.** Only the compute warehouse is a single, app-wide value.
> Which database/schema to query, and which role to query them with,
> depends on the data being fetched -- a resource backed by a different
> Snowflake database/schema (or requiring a different role) would
> silently break if these were global. They are configured **per
> provider** instead, via `QueryBuilder::useConnection()` (see below).

### Scoped HTTP client (`config/packages/framework.yaml`)

``` yaml
framework:
    http_client:
        scoped_clients:
            snowflake.client:
                base_uri: '%env(SNOWFLAKE_API_BASE_URL)%'
                headers:
                    Content-Type: 'application/json'
                    Accept: 'application/json'
                    Authorization: 'Bearer %env(SNOWFLAKE_TOKEN)%'
                    X-Snowflake-Authorization-Token-Type: 'PROGRAMMATIC_ACCESS_TOKEN'
```

### Service parameters (`config/services.yaml`)

``` yaml
parameters:
    snowflake.warehouse: '%env(string:SNOWFLAKE_WAREHOUSE)%'

services:
    _defaults:
        bind:
            string $snowflakeWarehouse: '%snowflake.warehouse%'
```

> **Gotcha:** if a service is re-declared with an explicit `arguments:`
> block in another config file (e.g. `config/services_test.yaml`), the
> `_defaults.bind` values from `services.yaml` are **not** inherited
> for that service anymore --- they must be repeated explicitly. This
> is why `App\Http\SnowflakeClient` in `services_test.yaml` re-lists
> `$snowflakeWarehouse` alongside `$projectDir`/`$record`/`$httpCallEnabled`
> (the same thing `App\Http\LnClient` does for `$ionCompany`).

------------------------------------------------------------------------

## App\\Http\\SnowflakeClient

**Namespace:** `App\Http`

Extends `AbstractRecordableClient` (see
[recordable-client.md](./recordable-client.md)), so it supports the
same record/replay mechanism as `LnClient`, `JiraClient`, etc. Fixtures
live under `tests/fixtures/snowflake/http`, one JSON file per distinct
query (statement + bindings + warehouse/database/schema/role, hashed).

### Public API

``` php
// Executes a statement and returns rows as associative arrays keyed by column name.
public function query(string $statement, string $database, string $schema, string $role, array $bindings = [], int $timeout = 60): array

// Executes a statement and returns the raw Snowflake SQL API payload
// (resultSetMetaData, statementHandle, etc.).
public function execute(string $statement, string $database, string $schema, string $role, array $bindings = [], int $timeout = 60): array

// Combines resultSetMetaData.rowType (column names) with data rows.
public function mapRows(array $result): array
```

`$database`/`$schema`/`$role` are required on every call --- there is no
app-wide default (see the "Database, schema and role" note above).
`$bindings` follow the
[Snowflake SQL API bind variable format](https://docs.snowflake.com/en/developer-guide/sql-api/submitting-requests#using-bind-variables-in-a-statement):

``` php
$snowflakeClient->query(
    'SELECT SITE, ITEM FROM V_VENDOR_XREF_COSTS WHERE SITE = ?',
    'LN_PRD',
    'SILVER',
    'READONLY',
    ['1' => ['type' => 'TEXT', 'value' => '300']],
);
```

In practice, application code should not call `SnowflakeClient`
directly for resource collections --- use `QueryBuilder` instead (see
below), which builds the statement and bindings for you.

### Known limitation

Only the first `resultSetMetaData.partitionInfo` partition is read. If
a query is expected to return a very large result set (multiple
partitions), this client does not currently follow pagination links.
Not a problem for the current, filtered/paginated use cases.

------------------------------------------------------------------------

## App\\Snowflake\\QueryBuilder

**Namespace:** `App\Snowflake`

A minimal, fluent SQL builder wrapping a `SnowflakeClient`. Not a
general-purpose query builder: no relations resolution, no write
support, joins use raw SQL conditions.

``` php
$queryBuilder = new QueryBuilder($snowflakeClient);

$rows = $queryBuilder
    ->select('c.SITE', 'c.ITEM', 'i.DSCA')
    ->from('V_VENDOR_XREF_COSTS', 'c')
    ->leftJoin('V_VENDOR_XREF_ITEMS', 'i', 'i.ITEM = c.ITEM')
    ->useConnection(database: 'LN_PRD', schema: 'SILVER', role: 'READONLY')
    ->andWhere('c.SITE = ?', ['300'])
    ->orderBy('c.ITEM', 'ASC')
    ->setFirstResult(0)
    ->setMaxResults(50)
    ->getResult();

$total = $queryBuilder->getCount(); // same WHERE, ignores LIMIT/OFFSET
```

### Methods

| Method | Purpose |
| --- | --- |
| `select(string ...$columns)` | SELECT list, defaults to `*` |
| `from(string $table, string $alias)` | required before `getResult()`/`getCount()` |
| `useConnection(string $database, string $schema, string $role)` | required before `getResult()`/`getCount()`; which database/schema to query and which role to use |
| `innerJoin()` / `leftJoin()` / `rightJoin()` | `(string $table, string $alias, string $condition)` |
| `andWhere(string $condition, array $params = [])` | `$condition` uses `?` placeholders; `$params` are bound positionally, type-inferred (`TEXT`/`FIXED`/`REAL`/`BOOLEAN`) |
| `orderBy(string $column, string $direction = 'ASC')` | can be called multiple times |
| `setFirstResult(int $offset)` / `setMaxResults(int $limit)` | LIMIT/OFFSET, only appended if set |
| `getResult(): array` | runs the built statement, returns mapped rows |
| `getCount(): int` | wraps the same query (without LIMIT/OFFSET) in `SELECT COUNT(*) AS CNT FROM (...) AS COUNT_QUERY` |

Calling `getResult()`/`getCount()` without calling `from()` first, or
without calling `useConnection()` first, throws a `LogicException`.

------------------------------------------------------------------------

## Column mapping: `#[Column]` + `ColumnMapper`

**Namespace:** `App\Snowflake\Attribute` / `App\Snowflake`

Declares, once per resource property, which Snowflake column (with its
table alias, e.g. `"c.SITE"`) it maps to --- so that column name is
never repeated in the provider, the filters, and the resource.

``` php
use App\Snowflake\Attribute\Column;

class VendorItemCost
{
    public function __construct(
        #[Column('c.SITE')]
        public readonly string $site,
        // ...
    ) {}
}
```

``` php
ColumnMapper::getColumns(VendorItemCost::class); // ['site' => 'c.SITE', 'item' => 'c.ITEM', ...]
ColumnMapper::getColumn(VendorItemCost::class, 'site'); // 'c.SITE'
```

`ColumnMapper` reads the attribute via `ReflectionClass`/`ReflectionParameter`
(the only way to read custom PHP attribute data) off the resource's
**constructor parameters**, in declaration order, and caches the
result per resource class for the lifetime of the request.

------------------------------------------------------------------------

## Filters

**Namespace:** `App\Snowflake\Filter`

Both filters implement `App\Snowflake\Filter\FilterInterface`, which
extends `ApiPlatform\Metadata\FilterInterface` (so `StrictSearchListener`
and ApiPlatform's own filter documentation/OpenAPI generation still
work) and adds:

``` php
public function apply(QueryBuilder $queryBuilder, string $resourceClass, ?Operation $operation = null, array $context = []): void;
```

### SearchFilter (exact / partial match)

``` php
#[ApiFilter(SearchFilter::class, properties: ['site', 'item', 'description' => 'partial'])]
```

-   A plain list entry (`'site'`) → exact match (`column = ?`).
-   A `property => 'partial'` entry → `column ILIKE ?` wrapped with
    `%...%`.
-   Empty/missing query values are skipped (no `WHERE` added).

### OrderFilter

``` php
#[ApiFilter(OrderFilter::class, properties: ['site', 'item', 'price'])]
```

Reads `?order[property]=asc|desc`, same convention as Doctrine's
`OrderFilter`. Invalid directions and properties not listed are
silently ignored.

> **Important implementation detail:** ApiPlatform's `AttributeFilterPass`
> always normalizes the `properties` argument of `#[ApiFilter]` into an
> **associative array keyed by property name**
> (e.g. `['site' => null, 'item' => null, 'price' => null]`) before
> injecting it into the filter's constructor --- regardless of whether
> the attribute was written as a plain list. Both `SearchFilter` and
> `OrderFilter` are written to read the array **keys** as property
> names, matching this behavior (the same convention Doctrine's own
> `OrderFilter` follows). A previous version of `OrderFilter` iterated
> the array *values* instead, which silently broke `order[...]`
> filtering (every property resolved to `order[]`, rejected by
> `StrictSearchListener` as "not available for the resource"). See
> `tests/Snowflake/Filter/OrderFilterTest.php` for the regression test.

### FilterExtension

Resolves each filter id declared on the operation (`$operation->getFilters()`)
through `api_platform.filter_locator` (already bound project-wide as
`$filterLocator`), applies every non-order filter first, then applies
order filters last --- mirroring
`ApiPlatform\Doctrine\Orm\Extension\FilterExtension`. This runs
automatically inside `AbstractCollectionProvider::provide()`; you do
not need to call it yourself.

------------------------------------------------------------------------

## AbstractCollectionProvider

**Namespace:** `App\Snowflake`

Generic ApiPlatform `ProviderInterface` for GET collections backed by
Snowflake. Handles: building the SELECT list from `#[Column]`,
applying filters, applying pagination (via ApiPlatform's own
`Pagination` service, including the count query), and building the
`CollectionPaginator`.

A concrete provider only implements three methods:

``` php
abstract class AbstractCollectionProvider implements ProviderInterface
{
    abstract protected function getResourceClass(): string;

    abstract protected function configureQuery(QueryBuilder $queryBuilder): void;

    abstract protected function mapRow(array $row): object;

    // Helper available to mapRow(): resolves $row['SITE'] from the
    // #[Column('c.SITE')] declared for $property.
    protected function columnValue(array $row, string $property): mixed;
}
```

------------------------------------------------------------------------

## Full example: `VendorItemCost`

### Resource (`src/Dto/Snowflake/VendorItemCost.php`)

``` php
use App\Snowflake\Attribute\Column as SnowflakeColumn;
use App\Snowflake\Filter\OrderFilter as SnowflakeOrderFilter;
use App\Snowflake\Filter\SearchFilter as SnowflakeSearchFilter;

#[ApiFilter(SnowflakeSearchFilter::class, properties: ['site', 'item', 'description' => 'partial'])]
#[ApiFilter(SnowflakeOrderFilter::class, properties: ['site', 'item', 'price'])]
#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/snowflake/vendor_item_costs',
            normalizationContext: ['groups' => ['vendor_item_cost']],
            provider: VendorItemCostProvider::class,
        ),
    ],
)]
class VendorItemCost
{
    public function __construct(
        #[SnowflakeColumn('c.SITE')]
        #[Groups(['vendor_item_cost'])]
        public readonly string $site,

        #[SnowflakeColumn('c.ITEM')]
        #[Groups(['vendor_item_cost'])]
        public readonly string $item,

        #[SnowflakeColumn('i.DSCA')]
        #[Groups(['vendor_item_cost'])]
        public readonly ?string $description,

        // ...
    ) {}
}
```

### Provider (`src/DataProvider/Snowflake/VendorItemCostProvider.php`)

``` php
use App\Snowflake\AbstractCollectionProvider;
use App\Snowflake\QueryBuilder as SnowflakeQueryBuilder;

/**
 * @extends AbstractCollectionProvider<VendorItemCost>
 */
class VendorItemCostProvider extends AbstractCollectionProvider
{
    protected function getResourceClass(): string
    {
        return VendorItemCost::class;
    }

    protected function configureQuery(SnowflakeQueryBuilder $queryBuilder): void
    {
        $queryBuilder
            ->useConnection(database: 'LN_PRD', schema: 'SILVER', role: 'READONLY')
            ->from('V_VENDOR_XREF_COSTS', 'c')
            ->leftJoin('V_VENDOR_XREF_ITEMS', 'i', 'i.ITEM = c.ITEM');
    }

    protected function mapRow(array $row): VendorItemCost
    {
        return new VendorItemCost(
            site: (string) $this->columnValue($row, 'site'),
            item: (string) $this->columnValue($row, 'item'),
            description: null !== ($value = $this->columnValue($row, 'description')) ? (string) $value : null,
        );
    }
}
```

That's the entire recipe for a new Snowflake-backed resource: one DTO,
one provider extending `AbstractCollectionProvider`, no raw SQL string
in the resource file.

------------------------------------------------------------------------

## Naming convention: alias when importing

Files under `src/Snowflake/` are **not** prefixed with `Snowflake`
(the namespace already says it: `QueryBuilder`, `ColumnMapper`,
`FilterExtension`, `Attribute\Column`, `Filter\SearchFilter`,
`Filter\OrderFilter`, ...). Several of these short names collide with
real, actively-used classes elsewhere in this codebase (Doctrine's own
`QueryBuilder`, `Column` mapping attribute, `SearchFilter`, `OrderFilter`).

**When importing these classes into a file that also deals with
Doctrine-backed resources (or could plausibly be confused with them),
alias the import:**

``` php
use App\Snowflake\QueryBuilder as SnowflakeQueryBuilder;
use App\Snowflake\Attribute\Column as SnowflakeColumn;
use App\Snowflake\Filter\SearchFilter as SnowflakeSearchFilter;
use App\Snowflake\Filter\OrderFilter as SnowflakeOrderFilter;
```

This is already done in `VendorItemCost.php` and
`VendorItemCostProvider.php` --- follow the same pattern for any new
Snowflake-backed resource/provider. Files *inside* `src/Snowflake/`
itself don't need this (they only reference sibling classes, no
Doctrine imports).

------------------------------------------------------------------------

## Testing

### PHPUnit (unit tests)

`tests/Snowflake/` mirrors `src/Snowflake/` (plus
`tests/Http/SnowflakeClientTest.php`). Pure unit tests, no kernel boot,
using `createMock()`/`PHPUnit\Framework\TestCase` --- see e.g.
`tests/Snowflake/QueryBuilderTest.php` or
`tests/Snowflake/Filter/OrderFilterTest.php`.

``` bash
docker compose -p tld exec php-api vendor/bin/phpunit tests/Snowflake tests/Http/SnowflakeClientTest.php
```

### Behat (functional tests)

A dedicated `snowflake` suite is declared in `api/behat.yaml`, feature
files under `api/features/snowflake/`. It runs against **recorded
fixtures** by default (`HTTP_CLIENT_ENABLED=0` in `.env.test.local`),
so it does not need real Snowflake access:

``` bash
docker compose -p tld exec php-api vendor/bin/behat --suite=snowflake
```

To add a new scenario that hits a query not yet recorded, run once
against real Snowflake to generate the fixture(s), then commit them:

``` bash
docker compose -p tld exec -e HTTP_CLIENT_RECORD=1 -e HTTP_CLIENT_ENABLED=1 php-api vendor/bin/behat --suite=snowflake
```

Fixtures are written to `tests/fixtures/snowflake/http/<hash>.json`
(one file per distinct statement/bindings/warehouse/database/schema/role
combination --- see
[recordable-client.md](./recordable-client.md#fixture-file-naming-strategy)).

------------------------------------------------------------------------

## Current limitations / things to revisit

-   **POC status.** `VendorItemCost` currently has no explicit
    `security` attribute on its `GetCollection` operation --- revisit
    access control before this is considered production-ready.
-   Read-only: no write operations are implemented (or planned) for
    Snowflake-backed resources.
-   `QueryBuilder` joins take raw SQL conditions (no relation
    resolution) --- by design, this is not a general-purpose ORM.
-   Only single-partition Snowflake responses are read (see
    `SnowflakeClient` limitation above).
