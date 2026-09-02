<?php

declare(strict_types=1);

namespace App\Formatter\Spreadsheet\Parts;

use App\Entity\Parts\SBSparePartsRequest;
use App\Entity\Parts\SparePartsRequest;
use App\Entity\Parts\TOCSparePartsRequest;
use App\Formatter\Spreadsheet\AbstractSpreadsheetFormatter;
use Symfony\Contracts\Translation\TranslatorInterface;

class SparePartsRequestSpreadsheetFormatter extends AbstractSpreadsheetFormatter
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function getColumnToRename(): array
    {
        return [
            'linkedModuleId' => $this->translator->trans('spare_parts_request.fields.parent', [], 'spare_parts_request'),
            'sph.name' => $this->translator->trans('spq.quotations.fields.sph', [], 'spq'),
            'factory.name' => $this->translator->trans('fields.factory'),
            'airport.code' => $this->translator->trans('fields.airport'),
            'customer.name' => $this->translator->trans('fields.customer'),
        ];
    }

    public function getComputedColumns(): array
    {
        return ['linkedModuleId'];
    }

    /**
     * @param SparePartsRequest $item
     */
    public function computeColumn(object $item, string $column): mixed
    {
        return match ($column) {
            'linkedModuleId' => (static function () use ($item) {
                if ($item instanceof SBSparePartsRequest) {
                    return \sprintf('SB#%d', $item->sbId);
                }

                if ($item instanceof TOCSparePartsRequest) {
                    return \sprintf('TOC#%d', $item->technicianOnCall->getId());
                }

                return '';
            })(),
            default => parent::computeColumn($item, $column),
        };
    }

    public function supports(string $class, string $operationName): bool
    {
        return SparePartsRequest::class === $class;
    }
}
