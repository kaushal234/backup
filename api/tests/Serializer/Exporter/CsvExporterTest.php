<?php

declare(strict_types=1);

namespace App\Tests\Serializer\Exporter;

use App\Formatter\Spreadsheet\SpreadsheetFormatterInterface;
use App\Serializer\Exporter\CsvExporter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class CsvExporterTest extends TestCase
{
    private PropertyAccessorInterface&MockObject $propertyAccessor;
    private SpreadsheetFormatterInterface&MockObject $formatter;
    private CsvExporter $csvExporter;

    protected function setUp(): void
    {
        $this->propertyAccessor = $this->createMock(PropertyAccessorInterface::class);
        $this->formatter = $this->createMock(SpreadsheetFormatterInterface::class);

        $this->csvExporter = new CsvExporter(
            $this->propertyAccessor,
            [$this->formatter]
        );
    }

    public function testExportReturnsCorrectStreamedResponse(): void
    {
        $class = 'App\Entity\User';
        $columns = ['id', 'username'];
        $filename = 'users.csv';

        $this->formatter->method('supports')->with($class, 'export_op')->willReturn(true);
        $this->formatter->method('formatColumnName')->willReturnMap([
            ['id', 'ID'],
            ['username', 'User name'],
        ]);
        $this->formatter->method('getComputedColumns')->willReturn([]);

        $user1 = new \stdClass();
        $user2 = new \stdClass();
        $data = [$user1, $user2];

        $this->propertyAccessor->method('getValue')->willReturnMap([
            [$user1, 'id', 1],
            [$user1, 'username', 'jdoe'],
            [$user2, 'id', 2],
            [$user2, 'username', 'asmith'],
        ]);

        $response = $this->csvExporter->export($class, $data, $columns, 'export_op', $filename);

        self::assertInstanceOf(StreamedResponse::class, $response);
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('text/csv; charset=utf-8', $response->headers->get('Content-Type'));
        self::assertSame('attachment; filename="users.csv"', $response->headers->get('Content-Disposition'));

        ob_start();
        $response->sendContent();
        $output = ob_get_clean();

        $bom = "\xEF\xBB\xBF";

        $expectedCsv = $bom
            ."ID,\"User name\"\n"
            ."1,jdoe\n"
            ."2,asmith\n";

        self::assertSame($expectedCsv, $output);
    }
}
