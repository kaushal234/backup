# AI — Related-Entity Source

## Overview

The related-entity-source pipeline is the third step of the entity-introspection chain, after the [extractor](ai-extractor.md) and the [comment-source](ai-comment-source.md): once the agent has the structured payload and the activity history, it calls `fetch_related_entities` to retrieve the **other business entities related to the current one** — the legacy `mod_links` rows that connect, for example, a Sales Forecast to its related CRABs, TOCs, DMS documents, etc.

These related entities are intentionally kept out of the extractor payload — they live in the legacy database, span every domain, and are only useful when the agent needs to navigate from one entity to its siblings.

The pipeline performs **no LLM call**: it resolves the legacy `parent_id` for the entity, queries `mod_links` via `LegacyBundle\Manager\ModLinkManager`, and Symfony Serializer returns the JSON array to the tool.

A single shared loader (`RelatedEntityLoader`) serves every entity. The per-entity behaviour is declared in the central [config registry](ai-comment-source.md#config-registry).

---

## Config

Each entry under `alvest_ai.entities` in `config/packages/alvest_ai.yaml` may declare a `related_entities` block:

```yaml
alvest_ai:
  entities:
    sales_forecast:
      class: App\Entity\Sales\SalesForecast
      related_entities: { parent_id: legacy_id, module: SFR }
    trouble_ticket:
      class: App\Entity\MIS\TroubleTicket\TroubleTicket
      related_entities: { parent_id: id, module: TTS }
```

| Field | Values | Meaning |
|---|---|---|
| `parent_id` | `'id'` \| `'legacy_id'` | Which getter on the entity maps to the legacy id used by `mod_links`. `legacy_id` is the default for modern App entities that wrap legacy data; `id` is used for legacy-only entities and modern entities that share their id with the legacy table. |
| `module` | string | Legacy `mod_links` module code of **this** entity (e.g. `SFR`, `TTS`, `CRAB`). Used to query both directions of the link — see [`RelatedEntityLoader`](#relatedentityloader). |

Omit `related_entities` entirely to skip the related-entity source for an entity. An entity may also declare `comments` (see [ai-comment-source.md](ai-comment-source.md)) and/or `search` / `legacy_search` sub-blocks for the semantic-search pipeline — they are independent of `related_entities`.

---

## `RelatedEntityLoader`

**File:** `src/AI/Service/Loader/RelatedEntity/RelatedEntityLoader.php`

Delegates the query to `ModLinkManager::getFromToLinks($parentId, $module)` and maps each row to a `RelatedEntityModel`:

```
findRelatedEntities($entity, $parentIdStrategy, $module)
    │
    ├── resolveParentId($entity, $parentIdStrategy)
    │       ├── 'legacy_id' → $entity->getLegacyId()
    │       ├── 'id'        → $entity->getId()
    │       └── null → return []
    │
    ├── ModLinkManager::getFromToLinks($parentId, $module)
    │       SELECT ml.* FROM mod_links ml
    │       WHERE (ml.module = :module AND ml.parent_id = :id)
    │          OR (ml.type   = :module AND ml.item      = :id)
    │
    └── for each row, pick the *other* side of the link:
            ├── if the row's (module, parent_id) matches this entity → keep (type, item)
            ├── otherwise                                            → keep (module, parent_id)
            ├── rows whose resulting type is empty are skipped
            └── emit RelatedEntityModel(type, item)
```

`mod_links` is directional (`parent_id`/`module` on one side, `item`/`type` on the other), so the loader queries both directions and always returns the *opposite* endpoint — the related entity, never the current one.

The DTO `App\AI\Dto\Activity\RelatedEntityModel` is a flat readonly record (`type`, `item`) — `type` is the legacy module code of the related entity (e.g. `CRAB`, `TOC`, `DMS`), `item` is its legacy id.

### `legacyId` vs `id`

`mod_links.parent_id` references the legacy primary key. For modern App entities that wrap legacy data, the legacy key is exposed via `getLegacyId()` (the `'legacy_id'` strategy). A few entity types use the entity's own `id` instead:

- Modern entities that share their id with the legacy table (`TroubleTicket`, `VendorWarrantyClaim`, `NonConformity`, `SupplierCorrectiveActionRequest`).
- All legacy-only entities under `LegacyBundle\Entity\…` (`EAP`, `MEAP`, `PIP`, `ISR`, `CPA`, `WC`, `SB`/`ServiceBulletin`, `PDC`) — their primary key is already what `mod_links.parent_id` references.

If the entity does not expose the getter required by the chosen strategy, `RelatedEntityLoader` throws a `\LogicException` to fail fast.

---

## Tool — `FetchRelatedEntitiesTool`

**File:** `src/AI/Tool/Common/FetchRelatedEntitiesTool.php`

Extends `AbstractFetchEntityTool` and delegates the data fetch to `AIEntityRegistry::findRelatedEntitiesFor()`:

```
fetch_related_entities(entityType, id)
    │
    ├── AIEntityRegistry::getEntityClass(entityType)   ← throws if unknown slug
    │
    ├── entityManager->getRepository(class)->findOneBy(['id' => $id])
    │       └── null → throw EntityNotFoundException
    │
    ├── EntityAccessCheckerRegistry::isGranted(class, entity)
    │       └── false → throw AccessDeniedException
    │
    └── serializer->serialize(AIEntityRegistry::findRelatedEntitiesFor(entityType, entity), 'json')
```

Both `#[AsTool]` and `#[McpTool]` are declared, so the same class is callable from the in-process chat agent and from external MCP clients. The tool description instructs the agent to call it **after** `fetch_entity_comments`, reusing the same `entityType` and id — see [ai-extractor.md](ai-extractor.md) and [ai-comment-source.md](ai-comment-source.md).

---

## Adding a new related-entity source

Add or update one entry under `alvest_ai.entities` in `config/packages/alvest_ai.yaml`:

```yaml
alvest_ai:
  entities:
    my_entity:
      class: App\Entity\MyEntity
      # comments: ...                                          # optional, see ai-comment-source.md
      related_entities: { parent_id: legacy_id, module: MYMOD }
```

No code class to write. If `mod_links.parent_id` for this entity references the modern primary key directly, use `parent_id: id`; otherwise `legacy_id`. The bundle's `Configuration` class validates both values.

### Tests

`tests/AI/Service/RelatedEntityLoader/RelatedEntityLoaderTest.php` covers both strategies.

---

## Error handling

| Condition | Exception | Caller behaviour |
|---|---|---|
| Unknown `entityType` | `\InvalidArgumentException` | Programming/agent error — the LLM should retry with a valid slug |
| Entry has no `related_entities` field | `\InvalidArgumentException` | Programming error — the agent called the tool on an entity not configured for it |
| Entity not found | `App\AI\Exception\EntityNotFoundException` | Surfaces to the agent as a tool error |
| Access denied | `App\AI\Exception\AccessDeniedException` | Same — the agent reports access denied |
| Entity does not expose the configured getter | `\LogicException` | Programming error — fix the `parent_id` strategy in the config |
