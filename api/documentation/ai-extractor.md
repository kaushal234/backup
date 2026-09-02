# AI — Extractor

## Overview

The extractor pipeline lets the agent (via a Tool) fetch a structured JSON snapshot of a business entity. Unlike the [summarizer](ai-summarizer.md), an extractor performs **no LLM call**: it loads the entity, maps it to a domain DTO via a `ModelFactory`, and serializes the DTO to JSON. The agent itself reads the JSON in its tool-call context.

This decouples *what data the agent sees* (the DTO shape) from *what the entity looks like in Doctrine*, so we can expose a stable, narrow projection without leaking persistence details.

Comments / activity history are **not** part of the extracted payload — they are served by the companion [comment-source pipeline](ai-comment-source.md). Cross-module references stored in the legacy `mod_links` table — the other business entities related to this one — are **also** out of the extractor payload — they are served by the [related-entity-source pipeline](ai-related-entity-source.md). Each `Extract<Entity>InformationTool` description explicitly instructs the agent to chain `fetch_entity_comments` and `fetch_related_entities` with the matching `entityType` (slug) and the same id whenever it needs the conversation history or the related entities.

---

## Architecture

The pipeline has **one public entry point** — `GenericExtractor` — and an extension point for entities that need custom behaviour.

```
Tool ──► GenericExtractor::extract($class, $uriVariables)
            │
            ├── foreach customExtractors (tagged ai.extractor.custom):
            │       if $custom->supports($class) → return $custom->extract(...)
            │
            └── fallback: AbstractExtractor::extract(...)
                    │
                    ├── getManagerForClass($class) → findOneBy($uriVariables)
                    ├── canBeExtracted($object)                  ← hook
                    │       └── false → throw EntityNotFoundException
                    ├── accessCheckers->isGranted($class, …)     ← entity-specific security
                    │       └── false → throw AccessDeniedException
                    ├── findFactory($class)                      ← picks the matching ModelFactory
                    └── serializer->serialize(factory->create($object), 'json')
```

Tools and the [summarizer](ai-summarizer.md) only depend on **`GenericExtractor`** and pass the class as an argument — they do not know about the custom extractors.

---

## Contracts

### `ExtractorInterface`

**File:** `src/AI/Service/Extractor/ExtractorInterface.php`

```php
interface ExtractorInterface
{
    public function extract(string $class, array $uriVariables): string;
}
```

The minimal public contract. Used by callers (tools, summarizer).

### `CustomExtractorInterface`

**File:** `src/AI/Service/Extractor/CustomExtractorInterface.php`

```php
#[AutoconfigureTag('ai.extractor.custom')]
interface CustomExtractorInterface extends ExtractorInterface
{
    public function supports(string $class): bool;
}
```

Implemented by entity-specific extractors that need to override behaviour (e.g. `DMSExtractor`). The interface itself carries the `ai.extractor.custom` tag, so any implementation is automatically registered for `GenericExtractor`'s dispatch — no per-class attribute required.

### `AbstractExtractor`

**File:** `src/AI/Service/Extractor/AbstractExtractor.php`

Holds the default fetch-check-serialize sequence. Custom extractors extend it to reuse the helpers and override only what changes (`getIdValue`, `canBeExtracted`, `serialize`, …).

| Method | Default | Override when |
|--------|---------|---------------|
| `getIdValue(): string` | `'id'` | the entity's identifier column is not `id` (e.g. `DMS` uses `legacyId`) |
| `canBeExtracted(?object): bool` | `null !== $object` | extra business preconditions before serialization |
| `getShortName(string $class): string` | last segment of `$class` | rarely — used only in the `EntityNotFoundException` message |
| `serialize(string $class, object): string` | DTO via `ModelFactory` + Symfony Serializer | the extractor returns something other than a serialized DTO (e.g. `DMSExtractor` returns the textual content of the file via `FileTextExtractor`, bypassing the factory entirely) |

### `GenericExtractor`

**File:** `src/AI/Service/Extractor/GenericExtractor.php`

Extends `AbstractExtractor`. Receives the list of `CustomExtractorInterface` services via `#[AutowireIterator('ai.extractor.custom')]` and dispatches to the first one whose `supports($class)` returns `true`. Otherwise falls back to `AbstractExtractor::extract`. This is the only extractor service callers depend on.

---

## Model factories — `ModelFactoryInterface`

**File:** `src/AI/Factory/ModelFactoryInterface.php`

```php
interface ModelFactoryInterface
{
    public function supports(string $class): bool;
    public function create(object $entity): object;
}
```

Tagged `ai.model_factory`. `AbstractExtractor` injects them via `#[AutowireIterator('ai.model_factory')]` and selects the matching one via `supports($class)` — chain-of-responsibility, no central registry.

The DTOs returned by `create()` live in `src/AI/Dto/{Domain}/` (e.g. `Engineering/EngineeringActivityProcessModel`). They are plain readonly classes; serialization is driven by Symfony Serializer's default property-name strategy.

---

## Custom extractors

The current set lives under `src/AI/Service/Extractor/`:

```
src/AI/Service/Extractor/
├── AbstractExtractor.php           # default fetch/access-check/serialize
├── ExtractorInterface.php          # public contract: extract($class, $uriVariables)
├── CustomExtractorInterface.php    # extends + adds supports(); tag ai.extractor.custom
├── GenericExtractor.php            # entry point: dispatches to customs or falls back
└── DMS/
    └── DMSExtractor.php            # supports(DMS::class); overrides serialize() to return file text
```

`DMSExtractor` is currently the only entity needing custom behaviour. Every other entity is served by `GenericExtractor` directly, paired with its own `ModelFactory`.

---

## Tools — `src/AI/Tool/Extract/{Domain}/`

Each entity has one Tool that the agent can call. The tools are thin: they inject the shared `GenericExtractor` and forward the class + id. The tool description nudges the agent to chain a `fetch_entity_comments` and `fetch_related_entities` call right after — see [ai-comment-source.md](ai-comment-source.md) and [ai-related-entity-source.md](ai-related-entity-source.md).

```php
#[AsTool(name: self::NAME, description: self::DESCRIPTION)]
#[McpTool(name: self::NAME, description: self::DESCRIPTION)]
readonly class Extract<Entity>InformationTool implements ToolInterface
{
    private const string NAME = 'extract_<entity_snake>_information';
    private const string DESCRIPTION = 'Request information precisely about a <Entity> (<ABBR>).';

    public function __construct(private GenericExtractor $extractor) {}

    public function __invoke(int $id): string
    {
        return $this->extractor->extract(<Entity>::class, ['id' => $id]);
    }
}
```

Both `#[AsTool]` (Symfony AI Toolbox) and `#[McpTool]` (Model Context Protocol) are declared so the same class is callable from the in-process chat agent and from external MCP clients.

---

## Service registration

| Service | Tag | Resolved by |
|---------|-----|-------------|
| `GenericExtractor` | — | Injected directly by tools and `GenericSummarizer` |
| Custom extractors | `ai.extractor.custom` | Auto-applied via `CustomExtractorInterface`; consumed by `GenericExtractor` |
| Model factories | `ai.model_factory` | `AbstractExtractor` via `#[AutowireIterator]` |
| Tools | `ai.tool` | Agent Toolbox via `#[AutowireIterator('ai.tool')]` — see [ai-chat.md](ai-chat.md) |

---

## Adding a new entity to extract

For the common case (default fetch/serialize behaviour suffices):

1. **DTO** — create `src/AI/Dto/{Domain}/<Entity>Model.php` with the fields the agent should see (readonly, scalar-friendly).
2. **Factory** — create `src/AI/Factory/{Domain}/<Entity>ModelFactory.php` implementing `ModelFactoryInterface`:
   - `supports(string $class): bool { return $class === <Entity>::class; }`
   - `create(object $entity): <Entity>Model` — map fields from the Doctrine entity to the DTO.
3. **Tool** — create `src/AI/Tool/Extract/{Domain}/Extract<Entity>InformationTool.php` implementing `ToolInterface`, decorated with `#[AsTool]` + `#[McpTool]`, injecting `GenericExtractor` and calling `extract(<Entity>::class, ['id' => $id])`.
4. **Tests** — a factory test asserting `supports()` + scalar field mapping. `GenericExtractor` / `AbstractExtractor` are covered centrally.

No extractor class to write — `GenericExtractor` handles it.

### When custom behaviour is needed

If the entity needs a different id column, custom pre-conditions, or a different serialization output (e.g. raw file content like DMS):

1. Create `src/AI/Service/Extractor/{Domain}/<Entity>Extractor.php`:
   ```php
   final class <Entity>Extractor extends AbstractExtractor implements CustomExtractorInterface
   {
       public function supports(string $class): bool { return <Entity>::class === $class; }

       // override getIdValue() / canBeExtracted() / serialize() as needed
   }
   ```
2. No tag attribute needed — `CustomExtractorInterface` carries `#[AutoconfigureTag('ai.extractor.custom')]`.
3. The tool stays unchanged — it still injects `GenericExtractor`, which will route to the custom one transparently.

---

## Error handling

| Condition | Exception | Caller behaviour |
|-----------|-----------|------------------|
| Entity not found / `canBeExtracted` returns false | `App\AI\Exception\EntityNotFoundException` | Surfaces to the agent as a tool error; the LLM can apologize / retry with another id |
| `EntityAccessChecker` denies access | `App\AI\Exception\AccessDeniedException` | Same — the agent reports access denied |
| No matching `ModelFactory` registered | `\LogicException` | Programming error; should be caught in CI via DI compile or factory tests |
