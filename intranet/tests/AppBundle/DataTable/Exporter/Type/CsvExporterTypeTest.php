<?php

declare(strict_types=1);

namespace App\Tests\AppBundle\DataTable\Exporter\Type;

use AppBundle\DataTable\Exporter\Type\CsvExporterType;
use Kreyu\Bundle\DataTableBundle\Exporter\Type\ExporterType;
use Kreyu\Bundle\DataTableBundle\Test\Exporter\ExporterTypeTestCase;

class CsvExporterTypeTest extends ExporterTypeTestCase
{
    public function test()
    {
        $this->setUp();
        $exporter = $this->createExporter();

        $this->assertSame('csv', $exporter->getName());
        $this->assertSame('text/csv', $exporter->getConfig()->getOption('format'));
    }

    protected function getTestedType(): string
    {
        return CsvExporterType::class;
    }

    protected function getTypes(): array
    {
        return [
            new CsvExporterType(),
            new ExporterType(),
        ];
    }
}
