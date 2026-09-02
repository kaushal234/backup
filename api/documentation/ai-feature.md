# AI Feature

## Overview

The AI module is built on top of [Symfony AI](https://github.com/symfony/ai) and the **Mistral** model (`mistral-small-latest`). It exposes three independent capabilities:

| Capability | Entry point | Doc |
|-----------|-------------|-----|
| **Chat** — conversational agent with tool calls | `POST /ai/dispatch` | [ai-chat.md](ai-chat.md) |
| **Summarize** — AI summary of a business entity | `GET /api/[entity]/{id}/summarize` | [ai-summarizer.md](ai-summarizer.md) |
| **Search** — semantic vector search | `GET /ai/search/{module}` | [ai-searcher.md](ai-searcher.md) |

---

## Service tags

| Tag | Purpose | Auto-detected from |
|-----|---------|--------------------|
| `ai.tool` | Exposes a callable to the LLM agent | `ToolInterface` |
| `ai.extractor` | Loads + serializes one entity class to a JSON snapshot (consumed by `GenericSummarizer` and Extract tools) | `ExtractorInterface` |
| `ai.model_factory` | Maps a Doctrine entity to its AI DTO | `ModelFactoryInterface` |
| `ai.searcher` | Handles vector search for one DTO class | `SearcherInterface` |
| `app.file_extractor` | Extracts plain text from a file by MIME type | `FileExtractorInterface` |
