<?php

declare(strict_types=1);

namespace App\EventListener\Finance\ManufacturingMargin;

use App\Entity\Finance\ManufacturingMargin;
use App\Event\SpreadsheetGeneratedEvent;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ManufacturingMarginSpreadsheetListener implements EventSubscriberInterface
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
        if (ManufacturingMargin::class !== ($context['resource_class'] ?? null)) {
            return;
        }

        $activeSheet = $event->getSpreadsheet()->getActiveSheet();
        $activeSheet->setAutoFilter($activeSheet->calculateWorksheetDimension());

        $columns = $activeSheet->getColumnIterator();

        foreach ($columns as $column) {
            foreach ($column->getCellIterator() as $cell) {
                if ($cell->getRow() > 1) {
                    continue 2;
                }

                if (\in_array($cell->getValue(), [
                    'Proj. DM (%)',
                    'Mbh',
                    'Iip (%)',
                    'Fse Budget',
                ], true)) {
                    $activeSheet->getStyle($column->getColumnIndex())->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('D0D0D0');
                }
            }
        }
    }
}
