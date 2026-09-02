# HTML → ADF Converter

Converts an HTML fragment (as produced by CKEditor5 Classic, used for TOC/Activity comments)
into an [Atlassian Document Format](https://developer.atlassian.com/cloud/jira/platform/apis/document/structure/)
`doc` node, so comment bodies can be posted to Jira Cloud's `POST /rest/api/3/issue/{issueIdOrKey}/comment`.

---

## Class

📁 `App\Jira\Adf\HtmlToAdfConverter`

```php
$adf = (new HtmlToAdfConverter())->convert('<p>Hello <strong>world</strong></p>');
// ['type' => 'doc', 'version' => 1, 'content' => [...]]
```

Framework-agnostic and dependency-free (only `\DOMDocument`), so it is unit-testable on its own —
see `App\Jira\DataTransformer\HtmlToAdfTransformer` for how it's wired into the `#[JiraField]`
attribute/transformer pipeline used to build the Jira request payload.

---

## Supported tags

| HTML | ADF |
|---|---|
| `<p>` | `paragraph` node |
| `<h1>`–`<h6>` | `heading` node (`attrs.level` 1–6) |
| `<strong>` / `<b>` | `strong` mark |
| `<em>` / `<i>` | `em` mark |
| `<u>` | `underline` mark |
| `<a href="...">` | `link` mark (`attrs.href`) |
| `<ul>` / `<ol>` / `<li>` | `bulletList` / `orderedList` / `listItem` |
| `<blockquote>` | `blockquote` node |
| `<br>` | `hardBreak` node |
| any other tag (`<span>`, `<code>`, `<div>`, ...) | transparent — recurse into children, tag itself dropped |
| bare top-level text (no block wrapper) | wrapped in a `paragraph` (ADF requires block-level nodes at `doc.content`) |

`<code>` is deliberately **not** a mark: CKEditor5 Classic doesn't produce it by default, so it's
treated like any other unknown tag rather than adding speculative mapping.

To add a new mark tag (e.g. `<s>` → `strike`), add an entry to `MARK_TAGS`. To add a new block tag,
add it to `BLOCK_TAGS` and handle it in `convertBlockElement()`.

---

## Parsing details

`parse()` loads the fragment through `\DOMDocument::loadHTML()` with a couple of long-standing
libxml workarounds:

- `<?xml encoding="utf-8" ?>` prefix — forces UTF-8 decoding (`loadHTML()` otherwise assumes
  ISO-8859-1 unless the markup carries its own `<meta charset>`).
- `LIBXML_HTML_NOIMPLIED` — prevents libxml from wrapping the fragment in `<html><body>`, so the
  `<div>` container we supply becomes `documentElement` directly.
- `LIBXML_HTML_NODEFDTD` — suppresses the default doctype libxml would otherwise inject.

**Malformed HTML is parsed on a best-effort basis** (libxml's recovery mode) and any parse errors
are discarded on purpose — a broken comment must not fail to sync to Jira. If a caller ever needs
to detect that the input was malformed, read `libxml_get_errors()` before the `libxml_clear_errors()`
call inside `parse()`.

### Planned migration (PHP ≥ 8.4)

The project currently runs PHP 8.3. Once bumped to PHP ≥ 8.4, `parse()` should be replaced with
the new WHATWG-compliant HTML5 parser:

```php
Dom\HTMLDocument::createFromString($html, LIBXML_NOERROR)->documentElement
```

This removes the need for the encoding prefix, the `NOIMPLIED`/`NODEFDTD` flags, and the
internal-errors dance entirely — the new DOM API handles UTF-8 natively and parses real HTML5.

---

## ADF shape notes

- `doc.content` (and any container like `blockquote`/`listItem`) always gets at least one node —
  an empty `paragraph` — since ADF requires block-level content; `nonEmptyBlocks()` enforces this.
- Whitespace-only text sitting directly between block-level siblings (e.g. a newline between two
  `<p>` tags) is dropped rather than turned into a spurious empty paragraph — see
  `isSignificantContent()`.
- Marks accumulate while descending into nested formatting tags (e.g. `<strong><em>` produces a
  single text node carrying both `strong` and `em` marks), rather than nesting text nodes.
