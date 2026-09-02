# AI — Filterable Entities

## Overview

The filterable pattern lets the LLM agent **filter business entities by arbitrary structured criteria** (status, dates, amounts, related ids…) from a natural-language prompt, then get back a compact list of matching records.

It is **registry-based and declarative**: each business entity is described once by a *definition* file that lists the filterable fields and how each one maps to the database. A single generic provider + query interprets every definition, so the agent never sees a per-entity tool — it discovers what is filterable at runtime through a **3-tool workflow**, which keeps the static tool schemas tiny regardless of how many entities are registered.

This is structured/criteria filtering against the database (Doctrine), **not** vector/semantic search — for that, see [ai-searcher.md](ai-searcher.md).

---

## The 3-tool discovery workflow

The agent uses three generic tools (all under `src/AI/Tool/Filterable/`, tagged `ai.tool` + exposed as MCP tools). The discovery design means the model asks *which entities exist*, then *which filters they support*, then *runs the filter* — so no entity-specific schema is ever hard-coded into a tool.

```
User: "show me the open VWCs for supplier TIE0002"
        │
        ▼
1. list_filterables          → which entity names can I filter?
        │   returns [{name, description}, …]
        ▼
2. describe_filterable(entity)→ which filters does "vendor_warranty_claim" accept?
        │   returns {name, description, fields:[{name,type,multi,enum,description}, …]}
        ▼
3. filter_entity(entity, filters, limit)
        │   validates + runs the definition's specs
        ▼
   {entity, count, truncated, results:[…compact summaries…]}
```

| Tool | File | Params | Returns |
|------|------|--------|---------|
| `list_filterables` | `ListFilterablesTool.php` | — | `{entities:[{name, description}]}` |
| `describe_filterable` | `DescribeFilterableTool.php` | `entity` | `{name, description, fields:[…]}` or `{error}` |
| `filter_entity` | `FilterEntityTool.php` | `entity`, `filters` (map), `limit` | `{entity, count, truncated, results}` or `{error}` |

### `filter_entity` payload tolerance

The LLM occasionally sends `filters` as a list of `{name, value}` objects (sometimes JSON-encoded strings) instead of the canonical `{key: value}` map. `FilterEntityTool::normalizeFilters()` detects a list payload and remaps it back to the expected map before validation, so both forms work. The tool description carries a correct/incorrect JSON example to steer the model toward the map form.

`limit` is clamped between 1 and `MAX_LIMIT` (200), default 50. `truncated` is `true` when the result count reaches the effective limit.

---

## Architecture — declarative definitions + generic engine

There used to be one trio (`Filter/*Filters.php` DTO + `Query/*FilterQuery.php` + `Provider/*FilterableProvider.php`) per entity. The repeated boilerplate (~3 files × 21 entities) has been collapsed to **one definition per entity** plus a single shared engine.

```
src/AI/Filterable/
├── FilterableField.php                     ← schema entry exposed to the LLM
├── FilterableRegistry.php                  ← indexes definitions by name via the `ai.filterable` tag iterator
├── Query/
│   └── GenericFilterQuery.php              ← builds the QueryBuilder, calls every spec, applies order + distinct + limit
└── Definition/
    ├── AbstractFilterableDefinition.php    ← base class; carries `#[AutoconfigureTag('ai.filterable')]`, owns the security gate
    ├── Filter.php                          ← façade: Filter::in, Filter::like, Filter::dateRange, … (returns FilterSpec)
    ├── FilterSpec.php                      ← spec interface: toFields(): FilterableField[], applyTo(QB, paths, filters)
    ├── PathResolver.php                    ← parses dotted paths → DQL fragment, auto-joins (multi-hop, embeddables), flags distinct on to-many
    ├── Spec/                               ← one class per operator (In, Eq, Like, InAny, Range, Bool, Exists, HasMany, ConcatLike, Custom)
    └── {Engineering,MIS,Materials,…}/      ← one file per business entity (21 in total)
```

`AbstractFilterableDefinition` is the only public contract: tools and registry type-hint it directly. It carries `#[AutoconfigureTag('ai.filterable')]`, so every concrete sub-class is auto-registered as a tagged service that the `FilterableRegistry` aggregates via `#[AutowireIterator]`. The base class owns the security gate (`final search()` that runs every result through `EntityAccessCheckerRegistry::isGranted()` before summarizing) and exposes `final fieldSchema()` which flattens the declarative `fields()` (a list of `FilterSpec`) into `FilterableField[]` for the LLM. The two engine deps (`GenericFilterQuery`, `EntityAccessCheckerRegistry`) are autowired through the base constructor — sub-classes stay parameterless `final readonly`.

---

## What a definition looks like

```php
final readonly class WarrantyClaimFilterable extends AbstractFilterableDefinition
{
    public function name(): string         { return 'warranty_claim'; }
    public function entityClass(): string  { return WarrantyClaim::class; }
    public function defaultAlias(): string { return 'wc'; }
    public function defaultOrder(): array  { return ['claimDate' => 'DESC']; }

    public function description(): string {
        return 'Warranty claims (WC) — customer/field warranty requests …';
    }

    public function fields(): array {
        return [
            Filter::in('statuses', 'status', enum: ['ACCEPTED', 'REJECTED', …], desc: '…'),
            Filter::like('customerNameLike', 'customerName', desc: '…'),
            Filter::in('manufacturingLocationNames', 'manufacturingLocation.name', desc: '…'),   // join auto
            Filter::in('partNumbers', 'parts.partNumber', desc: '…'),                              // to-many → distinct auto
            Filter::inAny('failureCodes', ['failureCode1', 'failureCode2'], desc: '…'),            // OR across columns
            Filter::dateRange('claimDate', afterName: 'claimDateAfter', beforeName: 'claimDateBefore',
                afterDesc: 'ISO-8601 — claims with a claim date on/after this date.',
                beforeDesc: 'ISO-8601 — claims with a claim date on/before this date.'),
            Filter::intRange('equipmentHours', minName: 'minEquipmentHours', maxName: 'maxEquipmentHours',
                minDesc: 'Minimum equipment running hours.', maxDesc: 'Maximum equipment running hours.'),
        ];
    }

    public function summarize(object $entity): array {
        \assert($entity instanceof WarrantyClaim);
        return [
            'id' => $entity->getId(), 'status' => $entity->status,
            'manufacturingLocation' => $entity->manufacturingLocation?->getName(),
            'enteredBy' => $this->legacyPersonName($entity->enteredBy),
            // …keep it lean: the LLM gets a list of these
        ];
    }
}
```

## The `Filter` DSL — vocabulary of operators

Each factory returns a `FilterSpec`. Use named parameters for clarity. `desc` is the LLM-facing description.

| Factory | DQL emitted | Notes |
|---|---|---|
| `Filter::in($name, $path, enum?, desc?)` | `path IN (:values)` (cast string); or `IDENTITY(rootAlias.relation) IN` when $path is a bare to-one relation | multi-valued |
| `Filter::inInt($name, $path, enum?, desc?)` | same, values cast to int | people ids, fk ids |
| `Filter::eq($name, $path, desc?)` / `Filter::eqInt(…)` | `path = :value` | scalar equality |
| `Filter::like($name, $path, desc?)` | `path LIKE %:value%` | |
| `Filter::inAny($name, [$path1, $path2, …], desc?)` | `(path1 IN (:vals) OR path2 IN (:vals) …)` | OR across N columns |
| `Filter::concatLike($name, [$path1, $path2, …], desc?)` | `CONCAT(p1, ' ', p2, …) LIKE %:v%` | typical for "firstname lastname" |
| `Filter::dateRange($path, afterName, beforeName, desc?, afterDesc?, beforeDesc?)` | `path >= :after` and/or `path <= :before` | two fields |
| `Filter::intRange / floatRange($path, minName, maxName, desc?, minDesc?, maxDesc?)` | same with numeric coercion | two fields |
| `Filter::bool($name, $path, trueValue=true, falseValue=false, desc?)` | `path = :v` | pass `'Y'`/`'N'` when the column stores a flag |
| `Filter::exists($name, $path, desc?)` | `path IS [NOT] NULL` | bool toggle on a nullable column or to-one relation |
| `Filter::hasMany($name, $collection, desc?)` | `rootAlias.collection IS [NOT] EMPTY` | bool toggle on a to-many collection |
| `Filter::custom(fields: […], apply: closure)` | escape hatch — closure receives `(QueryBuilder, PathResolver, array $filters)` | use sparingly (e.g. `INSTANCE OF` discriminator, status-list toggle) |

### Paths in 30 seconds

`PathResolver` interprets the dotted path:

- `status` → `wc.status` (root field)
- `scoring.importanceFactor` → `m.scoring.importanceFactor` (embedded → no join, Doctrine handles it)
- `enteredBy.username` → `LEFT JOIN wc.enteredBy enteredBy` then `enteredBy.username`
- `createdBy.businessUnit.region.name` → cascaded left-joins
- `parts.partNumber` where `parts` is a to-many → join + `distinct()` flagged automatically
- bare relation like `'enteredBy'` passed to `Filter::in/inInt` → uses `IDENTITY(wc.enteredBy) IN (…)` (no join)

Joins are memoized per path, so two specs referencing the same relation create only one join.

---

## Security — per-entity access filtering

The `final search()` defined in `AbstractFilterableDefinition` runs every result through `EntityAccessCheckerRegistry::isGranted($class, $entity)` before summarizing — the same per-entity checks used by the extractor (see [ai-extractor.md](ai-extractor.md)). `$class` comes from the sub-class's `entityClass()`. Because the method is `final`, sub-classes cannot bypass or forget the gate. To restrict an entity, register an `EntityAccessCheckerInterface` for its class; with no checker the registry allows everything.

**Limitation — filtering is post-query.** Access checks run in PHP on the rows already returned by `setMaxResults($limit)`. If some of the first *N* rows are masked, fewer than *N* are returned even though more granted rows may exist beyond the window.

---

## Adding a new filterable entity

1. Drop a definition file in `src/AI/Filterable/Definition/{Subdomain}/` extending `AbstractFilterableDefinition` (see the example above).
2. Implement `name()`, `entityClass()`, `defaultAlias()`, `defaultOrder()`, `description()`, `fields()`, `summarize()`. Use `Filter::xxx()` for the spec list.
3. (Optional) Register an `EntityAccessCheckerInterface` for the entity class under `src/AI/Service/Security/` to restrict visibility.

No service config needed: the definition is auto-registered through the `ai.filterable` tag the interface carries, and the registry picks it up on boot. It will appear in `list_filterables` immediately.
