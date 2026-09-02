<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Exporter;

use Kreyu\Bundle\DataTableBundle\DataTableInterface;
use Kreyu\Bundle\DataTableBundle\Exporter\ExportData;
use Kreyu\Bundle\DataTableBundle\Exporter\ExportStrategy;

class DefaultExportData extends ExportData
{
    public static function fromDefaultArray(): parent
    {
        return parent::fromArray([
            'filename' => null,
            'exporter' => 'xlsx',
            'strategy' => ExportStrategy::IncludeCurrentPage,
            'include_personalization' => true,
        ]);
    }

    public static function fromDataTable(DataTableInterface $dataTable): self
    {
        $self = new self();
        $self->filename = \sprintf('%s_%s', $dataTable->getConfig()->getName(), date('Y-m-d'));

        return $self;
    }
}
