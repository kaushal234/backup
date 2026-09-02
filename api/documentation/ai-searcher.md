# AI — Searcher

## Overview

The searcher pipeline lets the agent (via a Tool) or the UI (via a direct HTTP endpoint) perform a vector-based semantic search over business entities (DMS, Intranet modules, TOC).

---

## Search endpoints — `GET /ai/search/{module}`

**File:** `src/AI/DataProvider/Search/SearchDataProvider.php`

Three endpoints share a single provider (`SearchDataProvider`), each backed by a different DTO:

| Endpoint | DTO | Searcher |
|----------|-----|---------|
| `GET /ai/search/dms` | `DmsSearch` | `DMSSearcher` |
| `GET /ai/search/intranet` | `IntranetSearch` | `IntranetSearcher` |
| `GET /ai/search/toc` | `TechnicianOnCallSearch` | `TechnicianOnCallSearcher` |

Flow:
1. Checks global permission `ACCESS_PEOPLE`
2. Reads the `query` parameter from the request (required)
3. Iterates over searchers tagged `ai.searcher`
4. Calls `$searcher->supports($operation->getClass())` to find the right one
5. Returns a `SearchOutput` with the results, or `null` if `NoResultException` is thrown

---

## Searcher pipeline — `AbstractSearcher`

**File:** `src/AI/Service/Search/AbstractSearcher.php`

Core method: `search(string $query, int $limit = 10, bool $createLog = false): SearchOutput`

```
search('query string')
    │
    ├── retriever->retrieve($query, limit * 20)   ← vector search via Qdrant (searcher-specific retriever)
    │
    ├── getUniqueId($metadata) per document       ← abstract: each searcher defines its dedup key
    │
    ├── deduplicate: keeps highest score per unique id
    │
    ├── slice top $limit results
    │
    ├── processResults($metadataResults)          ← abstract: entity-specific hydration → Collection<SearchResult>
    │
    └── if createLog: create AILog, return SearchOutput with results + logIri
```

Each concrete searcher injects its own dedicated retriever service (e.g. `ai.retriever.dms`), not a shared default.

---

## Concrete searchers

| Searcher | DTO class | Retriever | `getUniqueId` key | `processResults` behaviour |
|----------|-----------|-----------|-------------------|---------------------------|
| `DMSSearcher` | `DmsSearch` | `ai.retriever.dms` | `metadata['dms_id']` | Loads `DMS` entity, applies `DMS_PEOPLE_VIEW_VOTER`, generates URL |
| `IntranetSearcher` | `IntranetSearch` | `ai.retriever.intranet` | `metadata['src'] + '_' + metadata['id']` | Dispatches to `search.handler` services via module name |
| `TechnicianOnCallSearcher` | `TechnicianOnCallSearch` | `ai.retriever.toc` | `metadata['tocid']` | Calls `LegacySearchSourceHandler::handle()`, generates URL |

Each searcher implements `getDtoClass()` returning its DTO class — used by `supports()` in `AbstractSearcher` to dispatch from `SearchDataProvider`.

---

## Adding a new Searcher

1. Create a class extending `AbstractSearcher` in `src/AI/Service/Search/{Module}/`
2. Implement:
   - `getDtoClass(): string` — return the DTO class (e.g. `MySearch::class`)
   - `getUniqueId(array $metadata): ?string` — return a unique key for deduplication (return `null` to skip the document)
   - `processResults(array $results): Collection` — hydrate metadata into `Collection<SearchResult>`
   - `getOperation(): string` — return the operation path string used for AI log creation (e.g. `'/search/mymodule'`)
3. Inject a dedicated retriever service (e.g. `ai.retriever.mymodule`) via `#[Autowire(service: '...')]`
4. Create the corresponding DTO in `src/AI/Dto/` extending `AbstractQuerySearch`, with `provider: SearchDataProvider::class`
5. Create the corresponding Tool in `src/AI/Tool/` wrapping the searcher — see [ai-chat.md](ai-chat.md)
6. Tag the service with `ai.searcher` (auto-detected if `autoconfigure: true`)
