<?php

declare(strict_types=1);

namespace App\Serializer\Exporter;

use Alvest\FeatureDoc\Attribute\FeatureDoc;
use ApiPlatform\Metadata\Operation;
use App\Filter\ColumnsFilter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;

#[FeatureDoc(path: 'export.md')]
class Exporter
{
    public const EXPORT_FORMATS = ['xlsx', 'csv'];

    public function __construct(
        #[Autowire(service: ExcelExporter::class)]
        private readonly ExporterInterface $excelExporter,
        #[Autowire(service: CsvExporter::class)]
        private readonly ExporterInterface $csvExporter,
    ) {
    }

    public function isExportable(Request $request): bool
    {
        return $request->query->has(ColumnsFilter::PARAMETER_NAME) && \in_array($request->getRequestFormat(), self::EXPORT_FORMATS, true);
    }

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

    private function resolveColumns(Request $request): array
    {
        return array_values(array_filter(explode(',', $request->query->get(ColumnsFilter::PARAMETER_NAME, ''))));
    }

    private function resolveFilename(Operation $operation, string $format): string
    {
        return mb_strtolower($operation->getShortName() ?? 'export').'.'.$format;
    }
}
