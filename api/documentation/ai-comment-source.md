# AI — Comment Source

## Overview

The comment-source pipeline is the **companion** to the [extractor](ai-extractor.md): after fetching a structured snapshot of an entity via `extract_*`, the agent calls `fetch_entity_comments` to retrieve the user comments / activity history attached to that entity. Comments are intentionally kept out of the extractor payload — they grow unboundedly and the agent only needs them when the question requires conversation context. The chain continues with [`fetch_related_entities`](ai-related-entity-source.md) for the other business entities related to this one (cross-module references stored in `mod_links`).

The pipeline performs **no LLM call**: it loads comments from a storage backend, maps each one to a `CommentModel` DTO, and Symfony Serializer returns the JSON array to the tool.

Two storage backends are supported and selected per entity via the central [config registry](#config-registry):

| Backend | Service | Source of truth |
|---|---|---|
| Modern entities (`App\Entity\…`) | `CommentLoader` | `comments` table via `CommentRepository::findCommentsForEntity()` |
| Legacy entities (`LegacyBundle\Entity\…`) | `LegacyCommentLoader` | `mod_logs` legacy table, scoped by `module` + `parent_id` |

The DTO `App\AI\Dto\Activity\CommentModel` is a flat readonly record (`message`, `createdAt`, `authorEmail`, `authorFirstname`, `authorLastname`) — the agent sees a uniform shape regardless of backend.

---

## Config registry

**File:** `config/packages/alvest_ai.yaml` (section `alvest_ai.entities`)

The `AlvestAIBundle` configuration is the single source of truth that maps every slug exposed to the agent to its Doctrine class and the comment + related-entity strategy. The schema is enforced by `App\DependencyInjection\Configuration`. Each entry declares:

```yaml
alvest_ai:
  entities:
    sales_forecast:
      class: App\Entity\Sales\SalesForecast
      comments: { type: default }                          # → CommentLoader
      related_entities: { parent_id: legacy_id, module: SFR }
    engineering_activity_process:
      class: LegacyBundle\Entity\Engineering\EngineeringActivityProcess
      comments: { type: legacy, legacy_module: EAP }       # → LegacyCommentLoader with module=EAP
      related_entities: { parent_id: id, module: EAP }
```

| Field | Values | Meaning |
|---|---|---|
| `class` | Doctrine FQCN | Used by `Fetch*Tool` to load the entity. Validated to exist at boot. |
| `comments.type` | `default` \| `legacy` | Selects the loader (`CommentLoader` vs `LegacyCommentLoader`). Omit the whole `comments` block to skip. |
| `comments.legacy_module` | string | Required when `type: legacy` — the `mod_logs.module` value. |

An entity may also declare `related_entities` (see [ai-related-entity-source.md](ai-related-entity-source.md)) and/or `search` / `legacy_search` sub-blocks for the semantic-search pipeline — they are independent of `comments`.

The processed configuration is exposed as the container parameter `alvest_ai.entities`. `App\AI\Service\AIEntityRegistry` receives it via `#[Autowire(param: 'alvest_ai.entities')]` and exposes `getEntityClass(slug)`, `findCommentsFor(slug, entity)`, and `findRelatedEntitiesFor(slug, entity)`. The slug is what the LLM passes to `fetch_entity_comments` / `fetch_related_entities` and matches the `extract_<slug>_information` tool family.

---

## Modern entities — `CommentLoader`

**File:** `src/AI/Service/Loader/Comment/CommentLoader.php`

Delegates to `CommentRepository::findCommentsForEntity($entity)`. Blank messages are skipped; the author (`People`) is read from `Comment::getUser()` and mapped to `authorEmail` / `authorFirstname` / `authorLastname`.

---

## Legacy entities — `LegacyCommentLoader`

**File:** `src/AI/Service/Loader/Comment/LegacyCommentLoader.php`

Reads from the legacy `mod_logs` table via the `legacyConnection` DBAL connection:

```
findComments($entity, $module)
    │
    ├── $entity->getId()                          ← used as parent_id
    │       └── absent/null → throw or return []
    │
    ├── SELECT comment, date, poster
    │   FROM mod_logs
    │   WHERE module = :module
    │     AND parent_id = :parent_id
    │   ORDER BY date ASC
    │
    ├── loadAuthors(rows)                         ← single SELECT on PeopleRepository::findBy(['legacyId' => …])
    │
    └── map each row → CommentModel (skip blank messages)
```

The `$module` argument is what the registry passes from the config entry (e.g. `'EAP'`). It is matched as-is against the legacy column (no case folding), so the literal in the config must be the same casing as the data.

Why the entity's own id (and not a separate `legacyId`)? Legacy entities live **in the legacy database** — their primary key is already what `mod_logs.parent_id` references.

---

## Tool — `FetchEntityCommentsTool`

**File:** `src/AI/Tool/Common/FetchEntityCommentsTool.php`

Extends `AbstractFetchEntityTool` and delegates the data fetch to `AIEntityRegistry::findCommentsFor()`:

```
fetch_entity_comments(entityType, id)
    │
    ├── AIEntityRegistry::getEntityClass(entityType)   ← throws if unknown slug
    │
    ├── entityManager->getRepository(class)->findOneBy(['id' => $id])
    │       └── null → throw EntityNotFoundException
    │
    ├── EntityAccessCheckerRegistry::isGranted(class, entity)
    │       └── false → throw AccessDeniedException
    │
    └── serializer->serialize(AIEntityRegistry::findCommentsFor(entityType, entity), 'json')
```

`AbstractFetchEntityTool` holds the load → access-check → serialize sequence shared with [`FetchRelatedEntitiesTool`](ai-related-entity-source.md). Both `#[AsTool]` and `#[McpTool]` are declared, so the same class is callable from the in-process chat agent and from external MCP clients.

---

## Service registration

| Service | Resolved by |
|---|---|
| `AIEntityRegistry` | Autowired ; `$config` injected from the `alvest_ai.entities` container parameter (populated by `AlvestAIExtension` from `config/packages/alvest_ai.yaml`) |
| `CommentLoader`, `LegacyCommentLoader` | Autowired |
| `FetchEntityCommentsTool` | `ai.tool` via `#[AsTool]` — see [ai-chat.md](ai-chat.md) |

---

## Adding a new comment source

Add or update one entry under `alvest_ai.entities` in `config/packages/alvest_ai.yaml`:

```yaml
alvest_ai:
  entities:
    my_entity:
      class: App\Entity\MyEntity
      comments: { type: default }                  # or { type: legacy, legacy_module: MYMOD }
      # related_entities: { parent_id: legacy_id, module: MYMOD }   # optional, see ai-related-entity-source.md
```

No code class to write. Make sure the entity has rows in the modern `comments` table (modern case) or in `mod_logs` with the matching `module` value (legacy case). The bundle's `Configuration` class enforces the shape at compile time.

### Tests

Mirror `tests/AI/Service/CommentLoader/CommentLoaderTest.php` for the modern path, or `tests/AI/Service/CommentLoader/LegacyCommentLoaderTest.php` for the legacy one.

---

## Error handling

| Condition | Exception | Caller behaviour |
|---|---|---|
| Unknown `entityType` | `\InvalidArgumentException` | Programming/agent error — the LLM should retry with a valid slug |
| Entry has no `comments` field | `\InvalidArgumentException` | Programming error — the agent called comments on an entity not configured for them |
| Entity not found | `App\AI\Exception\EntityNotFoundException` | Surfaces to the agent as a tool error |
| Access denied | `App\AI\Exception\AccessDeniedException` | Same — the agent reports access denied |
| Legacy entity missing `getId()` | `\LogicException` | Programming error — should never happen for entities served by this tool |
