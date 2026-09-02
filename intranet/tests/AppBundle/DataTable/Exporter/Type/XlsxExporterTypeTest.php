<?php

declare(strict_types=1);

namespace App\Tests\AppBundle\DataTable\Exporter\Type;

use AppBundle\DataTable\Exporter\Type\XlsxExporterType;
use Kreyu\Bundle\DataTableBundle\Exporter\Type\ExporterType;
use Kreyu\Bundle\DataTableBundle\Test\Exporter\ExporterTypeTestCase;

class XlsxExporterTypeTest extends ExporterTypeTestCase
{
    public function test()
    {
        $this->setUp();
        $exporter = $this->createExporter();

        $this->assertSame('xlsx', $exporter->getName());
        $this->assertSame('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $exporter->getConfig()->getOption('format'));
    }

    protected function getTestedType(): string
    {
        return XlsxExporterType::class;
    }

    protected function getTypes(): array
    {
        return [
            new XlsxExporterType(),
            new ExporterType(),
        ];
    }
}
