<?php

declare(strict_types=1);

namespace App\Serializer;

use App\Serializer\Encoder\XlsxEncoder;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Row;
use PhpOffice\PhpSpreadsheet\Worksheet\RowCellIterator;
use Symfony\Component\HttpFoundation\File\File;

class XlsxReader
{
    public function normalize(File $file, array $context = [])
    {
        $reader = IOFactory::createReader(ucfirst(XlsxEncoder::FORMAT));
        $reader->setReadDataOnly(true);

        $spreadsheet = $reader->load($file->getRealPath());
        $activeSheet = $spreadsheet->getActiveSheet();

        $results = [];
        $headers = [];

        /** @var RowCellIterator[] $rows */
        $rows = array_reduce([...$activeSheet->getRowIterator()], static function (array $memo, Row $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true);

            $memo[] = $cellIterator;

            return $memo;
        }, []);

        foreach ($rows[0] as $column => $cell) {
            $headers[$column] = $cell->getValue();
        }

        unset($rows[0]);

        $propertyConversion = $context['propertyConversion'] ?? [];
        $stringConversion = $context['stringConversion'] ?? [];
        $dateConversion = $context['dateConversion'] ?? [];
        $intConversion = $context['intConversion'] ?? [];
        $floatConversion = $context['floatConversion'] ?? [];
        foreach ($rows as $row) {
            $associatedRow = [];
            foreach ($row as $index => $cell) {
                if (null === $value = $cell->getValue()) {
                    continue;
                }
                if (isset($propertyConversion[$headers[$index]])) {
                    $associatedRow[$propertyConversion[$headers[$index]]] = \is_string($value) ? mb_trim($value) : $value;
                } elseif (isset($stringConversion[$headers[$index]])) {
                    $associatedRow[$stringConversion[$headers[$index]]] = (string) $value;
                } elseif (isset($dateConversion[$headers[$index]])) {
                    $associatedRow[$dateConversion[$headers[$index]]] = NumberFormat::toFormattedString($value, 'YYYY-MM-DD');
                } elseif (isset($intConversion[$headers[$index]])) {
                    $associatedRow[$intConversion[$headers[$index]]] = (int) $value;
                } elseif (isset($floatConversion[$headers[$index]])) {
                    $associatedRow[$floatConversion[$headers[$index]]] = (float) $value;
                } else {
                    $associatedRow[$headers[$index]] = $value;
                }
            }

            // Check if the line is empty, if yes don't send it
            $emptyLine = true;
            foreach ($associatedRow as $key => $cellValue) {
                if (null !== $cellValue) {
                    $emptyLine = false;
                }
            }

            if ($emptyLine) {
                continue;
            }

            $results[] = $associatedRow;
        }

        return $results;
    }
}
