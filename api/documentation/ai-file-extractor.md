# AI — File Extractor

## Overview

`FileTextExtractor` extracts plain text from a file before sending it to the LLM, instead of inlining the file as a base64 `DocumentUrl`. This keeps token usage predictable and cheap — a base64-encoded PDF/XLSX/DOCX can balloon to millions of tokens once images are embedded, while its extracted text is typically a few tens of K.

It is consumed by:
- `UserMessageBuilder` (current chat request and historical messages — see [ai-chat.md](ai-chat.md))
- `DocumentPlatform::ask()` (used by summarizers, e.g. `DMSSummarizer`)

Both callers fall back to a base64 `DocumentUrl` when extraction returns `null`, so the LLM can still process the file natively if no textual content can be obtained.

---

## Architecture

```
caller
   │
   ▼
FileTextExtractor::extract($filepath)         ← dispatcher
   │
   ├── for each service tagged `app.file_extractor`
   │       └── supports($mime) ? extract($filepath) : skip
   │
   ├── extracted text ≥ 50 chars → return text
   │
   └── otherwise (no support / exception / too short)
           └── AzureDocumentIntelligenceClient::doRequest()   ← OCR fallback
```

`FileTextExtractor` is a **dispatcher**. The format-specific logic lives in dedicated extractors implementing `App\AI\Extractor\FileExtractorInterface`. The dispatcher is the only place that knows about the OCR fallback policy.

---

## Per-format extractors

Each implementation of `FileExtractorInterface` is auto-tagged as `app.file_extractor` via `#[AutoconfigureTag('app.file_extractor')]` on the interface itself — no YAML wiring required.

| Extractor | MIME | Library |
|-----------|------|---------|
| `PdfExtractor` | `application/pdf` | `smalot/pdfparser` |
| `XlsxExtractor` | `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet` | `phpoffice/phpspreadsheet` (read-only mode) |
| `DocxExtractor` | `application/vnd.openxmlformats-officedocument.wordprocessingml.document` | `phpoffice/phpword` |
| `PlainTextExtractor` | `text/*` | `file_get_contents()` |

The interface is intentionally minimal:

```php
interface FileExtractorInterface
{
    public function supports(string $mime): bool;
    public function extract(string $filepath): string;
}
```

Extractors are **pure**: they extract or throw. They do not know about OCR, base64, or the calling context.

---

## Dispatcher policy

The dispatcher applies the same fallback logic regardless of format:

1. Pick the first extractor whose `supports($mime)` returns `true`.
2. If the extractor throws → fall back to OCR.
3. If the extracted text is shorter than 50 characters (typically a scanned document with no embedded text, or a corrupted file) → fall back to OCR.
4. If no extractor supports the MIME → fall back to OCR (Azure Document Intelligence handles PDF, DOCX, XLSX, PPTX and images natively, so this is worth trying).
5. If the OCR fallback also returns `null` → return `null` and let the caller decide (typically a base64 `DocumentUrl`).

The 50-char threshold is a heuristic: a scanned-only PDF returns near-empty text from `smalot/pdfparser`, and an empty or boilerplate-only XLSX/DOCX behaves the same. The exact constant lives at `FileTextExtractor::SCANNED_THRESHOLD`.

---

## OCR fallback — Azure Document Intelligence

**File:** `src/Http/AzureDocumentIntelligenceClient.php`

The fallback calls the Azure `prebuilt-read:analyze` model (API version `2024-11-30`). It posts the raw file bytes, polls the operation until `succeeded`/`failed` (max 30 polls × 2 s), and returns `analyzeResult.content`.

Supported input formats: PDF, JPEG, PNG, BMP, TIFF, HEIF, DOCX, XLSX, PPTX, HTML. Azure infers the format from the bytes — the client is format-agnostic.

---

## Adding a new format

1. Create a class in `src/AI/Extractor/` implementing `FileExtractorInterface`.
2. Implement `supports($mime)` to declare the MIME type(s) handled.
3. Implement `extract($filepath)` to return the extracted plain text. Throw on failure — the dispatcher will fall back to OCR.
4. No service configuration needed: the interface attribute auto-tags the class.
5. Add a unit test under `tests/AI/Extractor/`.
