<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler\MaterialRequirementsPlanning;

use App\CQRS\Query\MaterialRequirementsPlanning\FindAllMaterialRequirementsPlanningsGroupedByMonthQuery;
use App\CQRS\Query\MaterialRequirementsPlanning\GetAllMaterialRequirementsPlanningsGroupedByMonthSpreadsheetQuery;
use App\CQRS\QueryBusInterface;
use App\CQRS\QueryHandler\QueryHandlerInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Contracts\Translation\TranslatorInterface;

final class GetAllMaterialRequirementsPlanningsGroupedByMonthSpreadsheetQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly QueryBusInterface $queryBus,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function __invoke(GetAllMaterialRequirementsPlanningsGroupedByMonthSpreadsheetQuery $query): Spreadsheet
    {
        $months = $this->queryBus->dispatch(new FindAllMaterialRequirementsPlanningsGroupedByMonthQuery());

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            $this->translator->trans('display.table.mrp.headers.erp'),
            $this->translator->trans('display.table.mrp.headers.part_number'),
            $this->translator->trans('display.table.mrp.headers.vendor_part_number'),
            $this->translator->trans('display.table.mrp.headers.revision'),
            $this->translator->trans('display.table.mrp.headers.description'),
            $this->translator->trans('display.table.mrp.headers.planned_delivery_date'),
            $this->translator->trans('display.table.mrp.headers.ordered_quantity'),
        ]);

        $dataset = [];

        foreach ($months as $month) {
            foreach ($month as $planning) {
                $dataset[] = [
                    $planning->erp,
                    $planning->partNumber,
                    $planning->vendorPartNumber,
                    $planning->revision,
                    $planning->description,
                    $planning->plannedDeliveryDate,
                    $planning->orderedQuantity,
                ];
            }
        }

        $sheet->fromArray($dataset, startCell: 'A2');
        foreach ($sheet->getColumnDimensions() as $columnDimension) {
            $columnDimension->setAutoSize(true);
        }

        return $spreadsheet;
    }
}
