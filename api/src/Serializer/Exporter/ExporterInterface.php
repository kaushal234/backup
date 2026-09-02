<?php

declare(strict_types=1);

namespace App\Serializer\Exporter;

use Alvest\FeatureDoc\Attribute\FeatureDoc;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[FeatureDoc(path: 'export.md')]
interface ExporterInterface
{
    public function export(string $class, iterable $data, array $columns, string $operationName = '', ?string $filename = null): StreamedResponse;
}
