<?php

declare(strict_types=1);

namespace App\Snowflake;

use App\Http\SnowflakeClient;

/**
 * Minimal SQL query builder for Snowflake, used by Snowflake-backed
 * ApiPlatform providers so that filters/pagination extensions can compose
 * a query without concatenating raw SQL strings.
 *
 * Not a general-purpose query builder: no relations resolution, no
 * write support.
 */
class QueryBuilder
{
    /** @var list<string> */
    private array $select = ['*'];

    private ?string $from = null;
    private ?string $fromAlias = null;

    private ?string $database = null;
    private ?string $schema = null;
    private ?string $role = null;

    /** @var list<array{type: string, table: string, alias: string, condition: string}> */
    private array $joins = [];

    /** @var list<string> */
    private array $where = [];

    /** @var list<string> */
    private array $orderBy = [];

    /** @var array<string, array{type: string, value: string}> positional Snowflake SQL API bindings, keyed "1", "2", ... in "?" order */
    private array $bindings = [];

    private ?int $maxResults = null;
    private ?int $firstResult = null;

    public function __construct(
        private readonly SnowflakeClient $snowflakeClient,
    ) {
    }

    public function select(string ...$columns): static
    {
        $this->select = $columns;

        return $this;
    }

    public function from(string $table, string $alias): static
    {
        $this->from = $table;
        $this->fromAlias = $alias;

        return $this;
    }

    /**
     * Which Snowflake database/schema to query, and which role to query them
     * with. Required before getResult()/getCount(): unlike the compute
     * warehouse (a single, app-wide value), these depend on the data being
     * queried, so every provider states them explicitly instead of relying
     * on a single global default.
     */
    public function useConnection(string $database, string $schema, string $role): static
    {
        $this->database = $database;
        $this->schema = $schema;
        $this->role = $role;

        return $this;
    }

    public function innerJoin(string $table, string $alias, string $condition): static
    {
        return $this->addJoin('INNER', $table, $alias, $condition);
    }

    public function leftJoin(string $table, string $alias, string $condition): static
    {
        return $this->addJoin('LEFT', $table, $alias, $condition);
    }

    public function rightJoin(string $table, string $alias, string $condition): static
    {
        return $this->addJoin('RIGHT', $table, $alias, $condition);
    }

    /**
     * @param string                           $condition raw SQL condition, using "?" placeholders, e.g. "c.SITE = ?"
     * @param list<bool|float|int|string|null> $params    values for the "?" placeholders in $condition, in order
     */
    public function andWhere(string $condition, array $params = []): static
    {
        foreach ($params as $value) {
            $index = (string) (\count($this->bindings) + 1);
            $this->bindings[$index] = ['type' => $this->bindingType($value), 'value' => null === $value ? '' : (string) $value];
        }

        $this->where[] = $condition;

        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): static
    {
        $this->orderBy[] = \sprintf('%s %s', $column, mb_strtoupper($direction));

        return $this;
    }

    public function setFirstResult(int $offset): static
    {
        $this->firstResult = $offset;

        return $this;
    }

    public function setMaxResults(int $limit): static
    {
        $this->maxResults = $limit;

        return $this;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getResult(): array
    {
        $this->assertConnectionIsConfigured();

        return $this->snowflakeClient->query($this->buildSql(), $this->database, $this->schema, $this->role, $this->bindings);
    }

    /**
     * Total number of rows matching the query, ignoring LIMIT/OFFSET
     * (needed for ApiPlatform's pagination metadata).
     */
    public function getCount(): int
    {
        $this->assertConnectionIsConfigured();

        $sql = \sprintf('SELECT COUNT(*) AS CNT FROM (%s) AS COUNT_QUERY', $this->buildSql(withPagination: false));

        // Same WHERE clause (same "?" order) as getResult(), so the same bindings apply.
        $rows = $this->snowflakeClient->query($sql, $this->database, $this->schema, $this->role, $this->bindings);

        return (int) ($rows[0]['CNT'] ?? 0);
    }

    private function assertConnectionIsConfigured(): void
    {
        if (null === $this->database || null === $this->schema || null === $this->role) {
            throw new \LogicException('The query builder has no Snowflake connection: call useConnection() first.');
        }
    }

    private function addJoin(string $type, string $table, string $alias, string $condition): static
    {
        $this->joins[] = ['type' => $type, 'table' => $table, 'alias' => $alias, 'condition' => $condition];

        return $this;
    }

    private function bindingType(bool|float|int|string|null $value): string
    {
        return match (true) {
            \is_bool($value) => 'BOOLEAN',
            \is_int($value) => 'FIXED',
            \is_float($value) => 'REAL',
            default => 'TEXT',
        };
    }

    private function buildSql(bool $withPagination = true): string
    {
        if (null === $this->from) {
            throw new \LogicException('The query builder has no FROM table: call from() first.');
        }

        $sql = \sprintf('SELECT %s FROM %s %s', implode(', ', $this->select), $this->from, $this->fromAlias);

        foreach ($this->joins as $join) {
            $sql .= \sprintf(' %s JOIN %s %s ON %s', $join['type'], $join['table'], $join['alias'], $join['condition']);
        }

        if ([] !== $this->where) {
            $sql .= ' WHERE '.implode(' AND ', $this->where);
        }

        if ([] !== $this->orderBy) {
            $sql .= ' ORDER BY '.implode(', ', $this->orderBy);
        }

        if ($withPagination) {
            if (null !== $this->maxResults) {
                $sql .= \sprintf(' LIMIT %d', $this->maxResults);
            }

            if (null !== $this->firstResult) {
                $sql .= \sprintf(' OFFSET %d', $this->firstResult);
            }
        }

        return $sql;
    }
}
