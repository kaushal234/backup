<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler\MaterialRequirementsPlanning;

use App\CQRS\Query\MaterialRequirementsPlanning\FindAllMaterialRequirementsPlanningsQuery;
use App\CQRS\Query\MaterialRequirementsPlanning\GetAllMaterialRequirementsPlanningsSpreadsheetQuery;
use App\CQRS\QueryBusInterface;
use App\CQRS\QueryHandler\QueryHandlerInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Contracts\Translation\TranslatorInterface;

final class GetAllMaterialRequirementsPlanningsSpreadsheetQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly QueryBusInterface $queryBus,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function __invoke(GetAllMaterialRequirementsPlanningsSpreadsheetQuery $query): Spreadsheet
    {
        $forecast = $this->queryBus->dispatch(new FindAllMaterialRequirementsPlanningsQuery());

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            $this->translator->trans('display.table.mrp.headers.erp'),
            $this->translator->trans('display.table.mrp.headers.part_number'),
            $this->translator->trans('display.table.mrp.headers.vendor_part_number'),
            $this->translator->trans('display.table.mrp.headers.revision'),
            $this->translator->trans('display.table.mrp.headers.description'),
            $this->translator->trans('display.table.mrp.headers.ordered_quantity'),
            $this->translator->trans('display.table.mrp.headers.planned_order_date'),
            $this->translator->trans('display.table.mrp.headers.planned_delivery_date'),
        ]);

        $dataset = [];
        foreach ($forecast as $planning) {
            $dataset[] = [
                $planning->erp,
                $planning->partNumber,
                $planning->vendorPartNumber,
                $planning->revision,
                $planning->description,
                $planning->orderedQuantity,
                $planning->plannedOrderDate,
                $planning->plannedDeliveryDate,
            ];
        }

        $sheet->fromArray($dataset, startCell: 'A2');
        foreach ($sheet->getColumnDimensions() as $columnDimension) {
            $columnDimension->setAutoSize(true);
        }

        return $spreadsheet;
    }
}
