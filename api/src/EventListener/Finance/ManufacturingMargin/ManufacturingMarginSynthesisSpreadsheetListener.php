<?php

declare(strict_types=1);

namespace App\EventListener\Finance\ManufacturingMargin;

use App\Dto\Finance\ManufacturingMarginSynthesis;
use App\Event\SpreadsheetGeneratedEvent;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\RowCellIterator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ManufacturingMarginSynthesisSpreadsheetListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            SpreadsheetGeneratedEvent::class => ['onSpreadsheetCreation'],
        ];
    }

    public function onSpreadsheetCreation(SpreadsheetGeneratedEvent $event)
    {
        $context = $event->getContext();
        if (ManufacturingMarginSynthesis::class !== ($context['resource_class'] ?? null)) {
            return;
        }

        $customFields = [
            ManufacturingMarginSynthesis::QUANTITY,
            ManufacturingMarginSynthesis::AVERAGE_FACTORY_DISCOUNT,
            ManufacturingMarginSynthesis::AVERAGE_PROJECTED_DIRECT_MARGIN,
            ManufacturingMarginSynthesis::AVERAGE_ACTUAL_DIRECT_MARGIN,
            ManufacturingMarginSynthesis::AVERAGE_INDUSTRIAL_INCORPORATION_PARAMETER,
            ManufacturingMarginSynthesis::AVERAGE_FACTORY_STANDARD_EFFICIENCY,
        ];

        $averageFields = [
            ManufacturingMarginSynthesis::AVERAGE_TRANSFER_PRICE,
            ManufacturingMarginSynthesis::AVERAGE_UNIT_ALLOCATED_HOURS,
            ManufacturingMarginSynthesis::AVERAGE_ACTUAL_HOURS,
            ManufacturingMarginSynthesis::AVERAGE_MODEL_BASE_HOURS,
            ManufacturingMarginSynthesis::AVERAGE_OPTION_CONFIGURATION_PARAMETER_HOURS,
        ];

        $activeSheet = $event->getSpreadsheet()->getActiveSheet();

        $changingProductTypeRows = [];
        $formulaColumns = [];
        $customFormulaColumns = [];
        $productTypeColumn = null;
        $previousValue = null;
        foreach ($activeSheet->getColumnIterator() as $column) {
            foreach ($column->getCellIterator() as $cell) {
                if (1 === $cell->getRow() && ManufacturingMarginSynthesis::PRODUCT_TYPE !== $cell->getValue()) {
                    if (\in_array($cell->getValue(), $averageFields, true)) {
                        $formulaColumns[$cell->getValue()] = $cell->getColumn();
                    }
                    if (\in_array($cell->getValue(), $customFields, true)) {
                        $customFormulaColumns[$cell->getValue()] = $cell->getColumn();
                    }
                    continue 2;
                }

                if (1 === $cell->getRow() && ManufacturingMarginSynthesis::PRODUCT_TYPE === $cell->getValue()) {
                    $productTypeColumn = $cell->getColumn();
                    continue;
                }

                if (2 === $cell->getRow()) {
                    $changingProductTypeRows[$cell->getValue()] = [
                        'start' => $cell->getRow(),
                        'end' => 0,
                    ];
                    $previousValue = $cell->getValue();
                    continue;
                }

                if ($previousValue !== $cell->getValue()) {
                    $changingProductTypeRows[$cell->getValue()] = [
                        'start' => $cell->getRow(),
                        'end' => 0,
                    ];

                    $changingProductTypeRows[$previousValue]['end'] = $cell->getRow() - 1;
                    $previousValue = $cell->getValue();
                }
            }
        }

        if ([] === $changingProductTypeRows) {
            return;
        }

        $changingProductTypeRows[$previousValue]['end'] = $activeSheet->getHighestRow($productTypeColumn);

        $newRowGap = 1;
        foreach ($changingProductTypeRows as $row) {
            $newIndex = $row['end'] + $newRowGap;
            $activeSheet->insertNewRowBefore($newIndex);

            $start = $row['start'] + $newRowGap - 1;
            $end = $row['end'] + $newRowGap - 1;

            $activeSheet->getStyle((string) $newIndex)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('D0D0D0');

            foreach (new RowCellIterator($activeSheet, $newIndex) as $cell) {
                $column = $cell->getColumn();
                if (\in_array($column, $formulaColumns, true)) {
                    $cell->setValue(\sprintf('=ROUND((SUMPRODUCT(%s%s:%s%s,%s%s:%s%s)/SUM(%s%s:%s%s)), 0)',
                        $column, $start, $column, $end, $customFormulaColumns[ManufacturingMarginSynthesis::QUANTITY], $start, $customFormulaColumns[ManufacturingMarginSynthesis::QUANTITY], $end,
                        $customFormulaColumns[ManufacturingMarginSynthesis::QUANTITY], $start, $customFormulaColumns[ManufacturingMarginSynthesis::QUANTITY], $end)
                    );
                }
                if ($column === $customFormulaColumns[ManufacturingMarginSynthesis::QUANTITY]) {
                    $cell->setValue(\sprintf('=SUM(%s%s:%s%s)', $column, $start, $column, $end));
                }
                if (
                    $column === $customFormulaColumns[ManufacturingMarginSynthesis::AVERAGE_FACTORY_DISCOUNT]
                    || $column === $customFormulaColumns[ManufacturingMarginSynthesis::AVERAGE_PROJECTED_DIRECT_MARGIN]
                    || $column === $customFormulaColumns[ManufacturingMarginSynthesis::AVERAGE_ACTUAL_DIRECT_MARGIN]
                ) {
                    $cell->setValue(\sprintf('=ROUND((SUMPRODUCT(%s%s:%s%s, %s%s:%s%s, %s%s:%s%s)/SUMPRODUCT(%s%s:%s%s,%s%s:%s%s)), 0)',
                        $customFormulaColumns[ManufacturingMarginSynthesis::QUANTITY], $start, $customFormulaColumns[ManufacturingMarginSynthesis::QUANTITY], $end, $formulaColumns[ManufacturingMarginSynthesis::AVERAGE_TRANSFER_PRICE], $start,
                        $formulaColumns[ManufacturingMarginSynthesis::AVERAGE_TRANSFER_PRICE], $end, $column, $start, $column, $end,
                        $customFormulaColumns[ManufacturingMarginSynthesis::QUANTITY], $start, $customFormulaColumns[ManufacturingMarginSynthesis::QUANTITY], $end, $formulaColumns[ManufacturingMarginSynthesis::AVERAGE_TRANSFER_PRICE], $start,
                        $formulaColumns[ManufacturingMarginSynthesis::AVERAGE_TRANSFER_PRICE], $end));
                }
                if ($column === $customFormulaColumns[ManufacturingMarginSynthesis::AVERAGE_INDUSTRIAL_INCORPORATION_PARAMETER]) {
                    $cell->setValue(\sprintf('=ROUND((SUMPRODUCT(%s%s:%s%s, %s%s:%s%s, %s%s:%s%s)/SUMPRODUCT(%s%s:%s%s,%s%s:%s%s)), 0)',
                        $customFormulaColumns[ManufacturingMarginSynthesis::QUANTITY], $start, $customFormulaColumns[ManufacturingMarginSynthesis::QUANTITY], $end, $formulaColumns[ManufacturingMarginSynthesis::AVERAGE_MODEL_BASE_HOURS], $start,
                        $formulaColumns[ManufacturingMarginSynthesis::AVERAGE_MODEL_BASE_HOURS], $end, $column, $start, $column, $end,
                        $customFormulaColumns[ManufacturingMarginSynthesis::QUANTITY], $start, $customFormulaColumns[ManufacturingMarginSynthesis::QUANTITY], $end, $formulaColumns[ManufacturingMarginSynthesis::AVERAGE_MODEL_BASE_HOURS], $start,
                        $formulaColumns[ManufacturingMarginSynthesis::AVERAGE_MODEL_BASE_HOURS], $end));
                }
                if ($column === $customFormulaColumns[ManufacturingMarginSynthesis::AVERAGE_FACTORY_STANDARD_EFFICIENCY]) {
                    $cell->setValue(\sprintf('=ROUND((SUMPRODUCT(%s%s:%s%s, %s%s:%s%s, %s%s:%s%s)/SUMPRODUCT(%s%s:%s%s,%s%s:%s%s)), 0)',
                        $customFormulaColumns[ManufacturingMarginSynthesis::QUANTITY], $start, $customFormulaColumns[ManufacturingMarginSynthesis::QUANTITY], $end, $formulaColumns[ManufacturingMarginSynthesis::AVERAGE_ACTUAL_HOURS], $start,
                        $formulaColumns[ManufacturingMarginSynthesis::AVERAGE_ACTUAL_HOURS], $end, $column, $start, $column, $end,
                        $customFormulaColumns[ManufacturingMarginSynthesis::QUANTITY], $start, $customFormulaColumns[ManufacturingMarginSynthesis::QUANTITY], $end, $formulaColumns[ManufacturingMarginSynthesis::AVERAGE_ACTUAL_HOURS], $start,
                        $formulaColumns[ManufacturingMarginSynthesis::AVERAGE_ACTUAL_HOURS], $end));
                }
            }

            ++$newRowGap;
        }
    }
}
