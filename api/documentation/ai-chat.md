# AI — Chat

## Overview

The chat feature lets a user hold a conversation with an agent. The agent can call **tools** to fetch or summarize business data. It is built on top of [Symfony AI](https://github.com/symfony/ai) and the **Mistral** model (`mistral-small-latest`).

---

## High-level architecture

```
HTTP Client
    │
    ▼
POST /ai/dispatch          (Dispatch DTO → DispatchDataProcessor)
    │
    ├── ChatFactory::createChat(logIri)
    │       ├── Agent (Symfony AI)                ← orchestrates LLM calls + tool calls
    │       └── AILogStore                        ← persisted message history
    │
    ├── chat->submit(userMessage)
    │       ├── AILogStore::load()                ← rebuilds MessageBag from AILog
    │       ├── Agent → Mistral API
    │       │       └── optional tool_call → Tool::__invoke()
    │       └── AILogStore::save()                ← persists Request + Response
    │
    ├── Publisher::publish()                      ← Mercure: pushes response to client
    │
    └── (first request only) AILogRepository::addTitle() ← generates title via AILogSummarizer
```

---

## Detailed flow

### 1. Entry point — `POST /ai/dispatch`

**File:** `src/AI/Dto/Dispatch.php`

```php
class Dispatch
{
    public string $input;  // user message
    public AILog  $log;    // target conversation (already created via POST /ai_logs)
}
```

The endpoint accepts `multipart/form-data`, which allows an optional `file` field alongside `input` and `log`. The file is read directly from the `RequestStack` in the processor.

The DTO is declared as an `ApiResource` with `DispatchDataProcessor` as its processor.

---

### 2. `DispatchDataProcessor`

**File:** `src/AI/DataProcessor/DispatchDataProcessor.php`

Responsibilities:
1. Resolves the IRI of the `AILog` (`/ai_logs/{id}`)
2. Creates a `Chat` via `ChatFactory::createChat($iri)`
3. Reads the optional uploaded file from `RequestStack`
4. Submits the user message via `UserMessageBuilder::build()` (includes the file as a `DocumentUrl` if present)
5. Publishes the response via Mercure
6. On the **first request** of a conversation → generates the title via `AILogRepository::addTitle()`

---

### 3. `ChatFactory` — chat construction

**File:** `src/AI/Factory/ChatFactory.php`

Creates a `Symfony\AI\Chat\Chat` instance composed of:

| Component | Role |
|-----------|------|
| `Agent` (Symfony AI) | Orchestrates LLM calls and tool calls |
| `AgentProcessor` | Handles tool calls (max 1 call per turn) |
| `AILogStore` | Database-backed message store |

Model used: `mistral-small-latest`.

---

### 4. `AILogStore` — conversation history

**File:** `src/AI/Store/AILogStore.php`

Implements `MessageStoreInterface` + `ManagedStoreInterface` from Symfony AI.

**`load()`** rebuilds the `MessageBag` sent to Mistral:
- Injects the system prompt
- Calls `AILogRepository::summarizePreviousRequests()` to compress old messages
- If `< 10` requests: injects the full history
- Otherwise: injects the rolling summary + the last 4 requests
- Each historical user message is built via `UserMessageBuilder::build()`, which embeds the stored file's content (if any) — see section 4b

**`save()`** persists the last user/assistant pair:
- Detects whether a tool was called (stores the tool name as `url` on `Request`)
- Creates a `Request` + `Response` and attaches them to the `AILog`
- Stores **only the original user prompt** in `Request::content` (via `UserMessageBuilder::extractPrompt()`), not the extracted file content that was appended to the message sent to the LLM. The file itself is preserved as an attachment, so history can be rebuilt without duplicating its text in the database.
- Attaches the uploaded file to the `Request` entity (via `AILogFactory::attachFile()`) if one was present in the HTTP request

---

### 4b. `UserMessageBuilder`

**File:** `src/AI/Builder/UserMessageBuilder.php`

Centralizes the construction of `UserMessage` objects. Used by both `DispatchDataProcessor` (current request) and `AILogStore` (historical messages).

```php
public function build(string $text, ?string $filepath = null): UserMessage
```

Decision tree:
1. `$filepath` is `null` → return a plain text-only message.
2. `$filepath` is provided → delegate to `FileTextExtractor::extract()`:
   - **Text-extractable file** (PDF, XLSX, DOCX, `text/*`) → append the extracted text as a second `Text` content prefixed with `[Attached file content]`.
   - **Non text-extractable file** (image, archive, …) or extraction failure → fall back to inlining the file as a base64 `DocumentUrl`, so Mistral can still process it natively.

See [ai-file-extractor.md](ai-file-extractor.md) for the dispatcher architecture, per-format extractors, and the OCR fallback policy.

---

### 5. Long conversation management — `AILogRepository`

**File:** `src/Repository/AI/AILogRepository.php`

**`summarizePreviousRequests(AILog $log, tailSize=4, chunkSize=6)`**

Progressive summarization algorithm:
1. Keeps the **last 4** requests untouched (the tail)
2. Summarizes older requests **in chunks of 6**
3. `AILog::$summarizedRequestsCount` tracks how many have already been summarized
4. Delegates to `AILogSummarizer::summarizeConversation()` passing the existing summary

This allows unlimited conversation length without blowing up the context window.

**`addTitle(AILog $log)`**
- Takes the first request
- Calls `AILogSummarizer::summarizeRequest()` to generate a title (3–6 words + optional emoji)

---

### 6. `AILogSummarizer`

**File:** `src/AI/Service/Summarizer/AILogSummarizer.php`

| Method | Purpose | Prompt rules |
|--------|---------|-------------|
| `summarizeRequest()` | Generate a conversation title | 3–6 words, no quotes, no trailing period |
| `summarizeConversation()` | Summarize conversation history | Cumulative summary, max 1200 words |

Calls `PlatformInvoker::invokeAsText()` directly.

---

### 7. Tools — `src/AI/Tool/`

Tools are registered via the Symfony tag `ai.tool` (attribute `#[AsTool(name: '...', description: '...')]`). The agent's toolbox is built from an `#[AutowireIterator('ai.tool')]` and the LLM calls them automatically when it emits a `tool_call`.

Two broad families of tools live in `src/AI/Tool/`:
- **Extraction tools** (`src/AI/Tool/Extract/`) — thin wrappers around an `ai.extractor` service that returns a structured JSON snapshot of an entity (no LLM call). See [ai-extractor.md](ai-extractor.md). The extractor is paired with two companion tools in `src/AI/Tool/Common/` (`fetch_entity_comments`, `fetch_related_entities`) that retrieve the activity history and other related entities (cross-module legacy mod_links) for the same entity — see [ai-comment-source.md](ai-comment-source.md) and [ai-related-entity-source.md](ai-related-entity-source.md).
- **Search tools** (`src/AI/Tool/Search/`) — thin wrappers around an `ai.searcher` service. See [ai-searcher.md](ai-searcher.md).

The agent fetches structured data via Extract tools and produces narrative responses itself. LLM-driven summarization is available out-of-band via the [`SummarizeDataProvider`](ai-summarizer.md) endpoint.

The current set of registered tools is whatever is tagged `ai.tool` in the container — browse `src/AI/Tool/` to see what's live.

---

## Data model

```
AILog
 ├── id
 ├── title                     ← generated on first request
 ├── type                      ← e.g. 'chat'
 ├── conversationSummary       ← rolling summary of history
 ├── summarizedRequestsCount   ← how many requests have been summarized
 ├── people                    ← Blameable (auto-set creator)
 ├── createdAt                 ← Timestampable
 ├── rating → Rating           ← user rating (OneToOne)
 └── requests → Request[]
          ├── url               ← tool name called or 'chat'
          ├── options           ← request parameters
          ├── content           ← original user prompt (file content excluded)
          └── response → Response
                   └── content  ← assistant response
```

---

## Service registration

| Service | Tag | Resolved by |
|---------|-----|-------------|
| Tools | `ai.tool` | Agent Toolbox via `#[AutowireIterator]` |
| Extractors | `ai.extractor` | `GenericSummarizer` via `#[AutowireIterator]`; Extract tools also inject their dedicated extractor directly — see [ai-extractor.md](ai-extractor.md) |
| Model factories | `ai.model_factory` | `AbstractExtractor` via `#[AutowireIterator]` |
| `AIEntityRegistry` | — | `FetchEntityCommentsTool` and `FetchRelatedEntitiesTool` — populated from `config/ai_entities.php`. See [ai-comment-source.md](ai-comment-source.md) and [ai-related-entity-source.md](ai-related-entity-source.md). |
| Searchers | `ai.searcher` | `SearchDataProvider` via `#[AutowireIterator]` |
| File extractors | `app.file_extractor` | `FileTextExtractor` via `#[AutowireIterator]` |

---

## Adding a new Tool

1. Create a class in `src/AI/Tool/` implementing `ToolInterface`
2. Add `#[AsTool(name: '...', description: '...')]`
3. Implement `__invoke(...): mixed`
4. If it summarizes an entity → see [ai-summarizer.md](ai-summarizer.md)
5. If it extracts a structured snapshot of an entity → see [ai-extractor.md](ai-extractor.md)
6. If it searches an entity → see [ai-searcher.md](ai-searcher.md)
