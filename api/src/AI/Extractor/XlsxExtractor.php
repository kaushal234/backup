<?php

declare(strict_types=1);

namespace App\AI\Extractor;

use PhpOffice\PhpSpreadsheet\IOFactory;

class XlsxExtractor implements FileExtractorInterface
{
    public function supports(string $mime): bool
    {
        return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' === $mime;
    }

    public function extract(string $filepath): string
    {
        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filepath);

        $output = '';
        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $output .= \sprintf("=== Sheet: %s ===\n", $sheet->getTitle());
            foreach ($sheet->toArray(calculateFormulas: false) as $row) {
                $output .= implode("\t", array_map(static fn ($cell): string => null === $cell ? '' : (string) $cell, $row))."\n";
            }
            $output .= "\n";
        }

        return $output;
    }
}
