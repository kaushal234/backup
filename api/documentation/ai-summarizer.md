# AI — Summarizer

## Overview

The summarizer pipeline lets the UI trigger an AI summary of a business entity (SalesForecast, DMS, TOC) via a direct HTTP endpoint.

It delegates entity loading, access control and serialization to the [Extractor](ai-extractor.md) layer, then sends the resulting content to the LLM with a domain-specific `SummarizePrompt`.

---

## Endpoint — `GET /api/[entity]/{id}/summarize`

**File:** `src/AI/DataProvider/Summarize/SummarizeDataProvider.php`

Flow:
1. Checks global permission `ACCESS_PEOPLE`
2. Delegates to `GenericSummarizer::summarize($class, $uriVariables, createLog: true)`
3. `EntityNotFoundException` → returns `null` (implicit 404)
4. `AccessDeniedException` → throws `AccessDeniedHttpException`

---

## `GenericSummarizer`

**File:** `src/AI/Service/Summarizer/GenericSummarizer.php`

```
summarize($class, $uriVariables, $createLog)
    │
    ├── findExtractor($class)        ← iterates services tagged `ai.extractor`
    │       └── null → returns null
    │
    ├── $extractor->extract($uriVariables)   ← see ai-extractor.md
    │       (loads entity, checks access, returns JSON/text)
    │
    └── TextPlatform::ask(
            new SummarizePrompt(promptPath, $uriVariables),
            $content,
            $createLog,
        ) → PlatformResult → SummaryOutput
```

The `promptPath` is resolved from a static map `class => path` and selects the prompt template per entity:

| Entity | Prompt path |
|--------|-------------|
| `DMS` | `summarize/dms` |
| `SalesForecast` | `summarize/sfr` |
| `TOC` | `summarize/toc` |

For DMS the Extractor returns the file text (extracted via `FileTextExtractor`); for SFR and TOC it returns the JSON of the entity's `Model` DTO.

---

## Platform layer — `AbstractPlatform`

**File:** `src/AI/Platform/AbstractPlatform.php`

`invoke()`:
1. `AILogFactory::createRequest($source, $options)` — creates a `Request` entity (not yet persisted)
2. `PlatformInvoker::invokeAsText($model, $messageBag)` — calls Mistral
3. If `$createLog = true` → `AILogFactory::createLog($request, $result)` — persists `AILog` + `Response`
4. Returns `PlatformResult(result, logIri)`

Concrete platform used by the summarizer: `TextPlatform` — wraps content as `system + user(instructions: text)`.

---

## Adding a new summarizable entity

1. Create an `Extractor` for the entity (see [ai-extractor.md](ai-extractor.md)) — handles loading, access check, serialization.
2. Add an entry `EntityClass::class => 'summarize/xxx'` in `GenericSummarizer::PROMPT_PATHS`.
3. Create the prompt template at the configured path.
4. Expose the API Platform route with `provider: SummarizeDataProvider::class` and `output: SummaryOutput::class`.

No new Summarizer class is needed — the `GenericSummarizer` covers all entities.
