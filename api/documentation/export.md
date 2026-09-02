# 📊 Spreadsheet Exporter (XLSX & CSV) with API Platform

This library provides **Excel (.xlsx)** and **CSV (.csv)** export features for API Platform collections in Symfony.

---

## 🎯 Features

- Export any API Platform collection to `xlsx` **or** `csv`
- Dynamic column selection (`?columns=`)
- Resource-specific formatting
- Computed / virtual columns
- Safe output (formula injection prevention, invalid chars removed)
- Generic fallback formatter when no specific formatter exists
- Shared sanitization/formatting logic between both export formats
- Post-generation hook for XLSX via a dispatched event

---

## 🧩 Architecture

```
API Platform (GET collection)
            │
            ▼
   SpreadsheetDataProvider (decorator)
            │
            ▼
         Exporter (facade)
            │
   ┌────────┴─────────┐
   ▼                   ▼
ExcelExporter      CsvExporter
   └────────┬─────────┘
            ▼
 AbstractSpreadsheetExporter
    (shared row/value logic)
            │
            ▼
SpreadsheetFormatter (specific or generic)
            │
   ┌────────┴─────────┐
   ▼                   ▼
PhpSpreadsheet      fputcsv
  → XLSX              → CSV
```

`ExcelExporter` and `CsvExporter` both extend `AbstractSpreadsheetExporter` and implement `ExporterInterface`, so they share the same column resolution, value formatting, and sanitization logic, while each handles its own file writing.

---

## 1️⃣ SpreadsheetDataProvider

📁 `App\DataProvider\SpreadsheetDataProvider`

This is the actual entry point into the export flow. It decorates API Platform's Doctrine ORM collection provider and has been simplified down to pure routing: all the "should we export, and how" logic now lives in `Exporter`.

```php
#[AsDecorator(decorates: 'api_platform.doctrine.orm.state.collection_provider')]
final class SpreadsheetDataProvider implements ProviderInterface
{
    public function __construct(
        #[AutowireDecorated]
        private readonly ProviderInterface $provider,
        private readonly RequestStack $requestStack,
        private readonly Exporter $exporter,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$operation instanceof GetCollection) {
            return $this->provider->provide($operation, $uriVariables, $context);
        }

        if (!$this->exporter->isExportable($request)) {
            return $this->provider->provide($operation, $uriVariables, $context);
        }

        $collection = $this->provider->provide($operation->withForceEager(false), $uriVariables, $context);

        return $this->exporter->export($operation, $collection, $request);
    }
}
```

**Responsibilities**

- Decorates the default Doctrine collection provider
- Only intervenes for `GetCollection` operations — anything else is passed straight through to the decorated provider
- Delegates the "is this request exportable?" decision entirely to `Exporter::isExportable()`
- When exportable, fetches the collection **without** forced eager loading (`withForceEager(false)`) — since the exporter forces lazy-loading itself only for the values it actually needs (see Doctrine proxy handling below), avoiding unnecessary joins/hydration for an export
- Delegates the actual export (format detection, column/filename resolution, streaming) entirely to `Exporter::export()`

Compared to the previous version, this class no longer resolves columns or filenames itself, and no longer branches on the request format directly — it's now a thin decorator whose only real logic is *"is this a collection request, and is it exportable?"*.

---

## 2️⃣ Triggering an Export

📁 `App\Serializer\Exporter\Exporter`

Export is triggered when:

- The operation is a `GetCollection` (checked in `SpreadsheetDataProvider`)
- The `columns` query parameter is present (see `ColumnsFilter::PARAMETER_NAME`)
- The request format is one of the supported formats: `xlsx` or `csv`

```php
public function isExportable(Request $request): bool
{
    return $request->query->has(ColumnsFilter::PARAMETER_NAME)
        && \in_array($request->getRequestFormat(), self::EXPORT_FORMATS, true);
}
```

Example requests:

```
GET /mis/trouble_tickets?columns=id,title,createdAt          (Accept: xlsx)
GET /mis/trouble_tickets?columns=id,title,createdAt          (Accept: csv)
```

---

## 3️⃣ Exporter (facade)

📁 `App\Serializer\Exporter\Exporter`

### Responsibilities

- Detects whether the current request is exportable (`isExportable()`)
- Resolves the requested columns from the query string
- Resolves the output filename (resource short name + correct extension)
- Delegates the actual export to `ExcelExporter` or `CsvExporter` depending on the request format

```php
public function export(Operation $operation, iterable $data, Request $request): object|array|null
{
    $format = $request->getRequestFormat();
    $columns = $this->resolveColumns($request);
    $filename = $this->resolveFilename($operation, $format);
    $operationName = $operation->getName() ?? '';

    return match ($format) {
        'xlsx' => $this->excelExporter->export($operation->getClass(), $data, $columns, $operationName, $filename),
        'csv' => $this->csvExporter->export($operation->getClass(), $data, $columns, $operationName, $filename),
        default => null,
    };
}
```

### Column resolution

```php
private function resolveColumns(Request $request): array
{
    return array_values(array_filter(explode(',', $request->query->get(ColumnsFilter::PARAMETER_NAME, ''))));
}
```

Empty/blank column names are filtered out and the array keys are re-indexed.

### Filename resolution

Derived from the resource short name, with an `export` fallback, and now suffixed with the actual requested format:

```php
private function resolveFilename(Operation $operation, string $format): string
{
    return mb_strtolower($operation->getShortName() ?? 'export').'.'.$format;
}
```

---

## 4️⃣ Columns API Filter

📁 `App\Filter\ColumnsFilter`

Documents the `columns` query parameter in the API documentation, and exposes the `PARAMETER_NAME` constant used by `Exporter` for detection and resolution. It does not enforce any filtering logic itself.

Example:

```
GET /people?columns=id,email,createdAt
```

---

## 5️⃣ AbstractSpreadsheetExporter

📁 `App\Serializer\Exporter\AbstractSpreadsheetExporter`

The shared base class for both `ExcelExporter` and `CsvExporter`. It centralizes everything that doesn't depend on the output file format.

**Responsibilities**

- Resolve the appropriate formatter for a resource/operation
- Build the header row
- Build each data row (column value resolution + formatting)
- Sanitize values so they are safe to write to a spreadsheet

Each concrete exporter (`ExcelExporter`, `CsvExporter`) is only responsible for actually writing/streaming the file, plus any format-specific escaping tweaks.

**Formatter resolution**

```php
public function getFormatter(string $class, string $operationName = ''): ?SpreadsheetFormatterInterface
{
    foreach ($this->formatters as $formatter) {
        if ($formatter->supports($class, $operationName)) {
            return $formatter;
        }
    }

    return null;
}
```

Formatters are ordered using Symfony tag priority (`spreadsheet.formatter`). The `$operationName` lets a formatter target a specific API Platform operation (not only a class).

---

## 6️⃣ Column Value Resolution

```php
private function getColumnValue(object $item, string $propertyPath, ?SpreadsheetFormatterInterface $formatter): mixed
```

**Resolution order**

1. Computed columns (declared by the formatter via `getComputedColumns()` / `computeColumn()`)
2. `PropertyAccessor` access on the entity
3. Safe fallback to `null` if:
    - The entity was soft-deleted (`EntityNotFoundException`)
    - Any other exception occurs (caught via `\Throwable`)

**Doctrine proxy handling**

When the resolved value is an uninitialized Doctrine proxy, lazy-loading is forced explicitly so the export contains real data:

```php
if ($value instanceof Proxy) {
    $value->__load();
}
```

If loading fails because the underlying entity no longer exists (e.g. soft-deleted), `EntityNotFoundException` is caught and the cell falls back to `null`.

This logic is unchanged and shared by both the XLSX and CSV exports.

---

## 7️⃣ Value Formatting

```php
private function formatValue(mixed $value, ?SpreadsheetFormatterInterface $formatter): ?string
```

| Type              | Output                 |
| ----------------- | ---------------------- |
| DateTimeInterface | Formatted date         |
| Object            | `__toString()` or null |
| Boolean           | `Yes` / `No`           |
| String            | Sanitized string       |
| Scalar            | Cast to string         |
| Null              | null                   |

Identical for both export formats — only the sanitization hooks differ slightly per format (see below).

---

## 8️⃣ Sanitizing Values

```php
private function sanitize(string $value): string
```

Protects against:

- Invalid control characters
- Problematic Unicode spaces (NBSP, zero-width, BOM, etc.)
- Formula injection: escapes a leading `=`, `+`, `-`, `@` by prefixing with a single quote
- Values over Excel's 32,767 character limit (truncated)

Two steps are delegated to the concrete exporter via overridable hooks, since they differ between formats:

```php
protected function escapeQuotes(string $value): string
{
    return $value; // default: no-op
}

protected function normalizeLineBreaks(string $value): string
{
    return str_replace(["\r\n", "\r", "\n"], ' ', $value); // default: collapse to space
}
```

### ExcelExporter overrides

```php
protected function escapeQuotes(string $value): string
{
    return str_replace('"', "''", $value);
}

protected function normalizeLineBreaks(string $value): string
{
    return str_replace(["\r\n", "\r"], "\n", $value);
}
```

XLSX cells keep internal line breaks (normalized to `\n`) and replace double quotes with two single quotes.

### CsvExporter

`CsvExporter` does not override these hooks, so it uses the abstract defaults: line breaks collapsed to a single space, and no extra quote escaping (quote-wrapping/escaping is instead handled natively by `fputcsv()`).

**Why this matters**

Without sanitization, Excel may display:

```
Formula Error: An unexpected error occurred
```

or silently execute formulas.

---

## 9️⃣ ExcelExporter

📁 `App\Serializer\Exporter\ExcelExporter`

- Builds a `PhpOffice\PhpSpreadsheet\Spreadsheet` in memory via `Spreadsheet::fromArray()`
- Dispatches a `SpreadsheetGeneratedEvent` **before** saving, so listeners can further customize the spreadsheet (styling, extra sheets, etc.)
- Streams the result with `PhpOffice\PhpSpreadsheet\Writer\Xlsx`

```php
$rows = [$this->buildHeader($columns, $formatter)];
foreach ($data as $item) {
    $rows[] = $this->buildRow($item, $columns, $formatter);
}

$sheet->fromArray($rows);

$this->dispatcher->dispatch(new SpreadsheetGeneratedEvent($spreadsheet, ['resource_class' => $class]));

(new Xlsx($spreadsheet))->save('php://output');
```

Response headers:

```
Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8
Content-Disposition: attachment; filename="<resource>.xlsx"
```

---

## 🔟 CsvExporter

📁 `App\Serializer\Exporter\CsvExporter`

- Writes directly to `php://output` using `fputcsv()`
- Emits a UTF-8 BOM (`\xEF\xBB\xBF`) first, so Excel correctly detects the encoding when opening the CSV
- Uses fixed delimiter/enclosure/escape characters:

```php
private const CSV_DELIMITER = ',';
private const CSV_ENCLOSURE = '"';
private const CSV_ESCAPE = '\\';
```

Response headers:

```
Content-Type: text/csv; charset=utf-8
Content-Disposition: attachment; filename="<resource>.csv"
```

---

## 1️⃣1️⃣ SpreadsheetGeneratedEvent

📁 `App\Event\SpreadsheetGeneratedEvent`

Dispatched only by `ExcelExporter`, after the spreadsheet has been populated with header + data rows but **before** it is written to the response. Carries the `Spreadsheet` instance plus a context array (currently `['resource_class' => $class]`), allowing listeners to apply extra styling, freeze panes, add sheets, etc., without touching the exporter itself.

---

## 1️⃣2️⃣ Spreadsheet Formatter System

### SpreadsheetFormatterInterface

📁 `App\Formatter\Spreadsheet\SpreadsheetFormatterInterface`

```php
interface SpreadsheetFormatterInterface
{
    public function formatDate(\DateTimeInterface $dateTime): string;
    public function formatColumnName(string $columnName): string;
    public function getComputedColumns(): array;
    public function computeColumn(object $item, string $column): mixed;
    public function supports(string $class, string $operationName): bool;
}
```

Each formatter is typically responsible for **one resource class**, but because `supports()` also receives the API Platform operation name, a formatter can be scoped to a specific (class, operation) pair when several exports of the same resource need different formatting. This is shared between XLSX and CSV exports.

### AbstractSpreadsheetFormatter

📁 `App\Formatter\Spreadsheet\AbstractSpreadsheetFormatter`

**Provides default behavior**

- Default date format: `Y-m-d`
- Human-readable column names
- No computed columns by default

**Column name formatting**

Transforms:

```
createdAt      → Created At
user_name      → User Name
module-owner   → Module Owner
```

Supports optional renaming via `getColumnToRename()`.

### Generic Fallback Formatter

📁 `App\Formatter\Spreadsheet\GenericSpreadsheetFormatter`

```php
#[AsTaggedItem(priority: -100)]
class GenericSpreadsheetFormatter extends AbstractSpreadsheetFormatter
{
    public function supports(string $class, string $operationName): bool
    {
        return true;
    }
}
```

- Acts as a fallback
- Always matches
- Lowest priority ensures it is used only if no specific formatter matches

### Resource-Specific Formatters

Example:

```php
class TroubleTicketSpreadsheetFormatter extends AbstractSpreadsheetFormatter
{
    public function supports(string $class, string $operationName): bool
    {
        return TroubleTicket::class === $class;
    }

    protected function getColumnToRename(): array
    {
        return [
            'module.application.name' => 'application',
            'module.operationalOwner' => 'MOO',
        ];
    }
}
```

**When to create one**:

- Custom column names
- Custom date formats
- Computed / virtual columns
- Resource-specific logic

Since formatters implement `SpreadsheetFormatterInterface` directly (not tied to XLSX specifics), the same formatter is reused automatically for both the `xlsx` and `csv` export of a given resource.

---

## 📝 Summary of what changed vs. the previous version

- ✅ Added CSV export (`CsvExporter`) alongside XLSX, both sharing the same base class
- ✅ Introduced `Exporter` facade class that decides which exporter to call based on request format
- ✅ Introduced `ExporterInterface` and `AbstractSpreadsheetExporter` to share row-building, value formatting, and sanitization logic
- ✅ Quote-escaping and line-break normalization are now overridable per-format hooks (`escapeQuotes()`, `normalizeLineBreaks()`)
- ✅ Added `SpreadsheetGeneratedEvent`, dispatched after building the XLSX spreadsheet and before saving, for further customization
- ✅ Filename now includes the correct extension for the requested format (`.xlsx` or `.csv`)
- ✅ `SpreadsheetDataProvider` still exists but has been simplified into a thin decorator: it only checks that the operation is a `GetCollection` and delegates the exportability check, column/filename resolution, and the export itself entirely to the new `Exporter` facade