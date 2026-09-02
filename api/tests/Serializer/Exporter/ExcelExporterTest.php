<?php

declare(strict_types=1);

namespace App\Tests\Serializer\Exporter;

use App\Event\SpreadsheetGeneratedEvent;
use App\Formatter\Spreadsheet\SpreadsheetFormatterInterface;
use App\Serializer\Exporter\ExcelExporter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class ExcelExporterTest extends TestCase
{
    private PropertyAccessorInterface&MockObject $propertyAccessor;
    private SpreadsheetFormatterInterface&MockObject $formatter;
    private EventDispatcherInterface&MockObject $dispatcher;
    private ExcelExporter $excelExporter;

    protected function setUp(): void
    {
        $this->propertyAccessor = $this->createMock(PropertyAccessorInterface::class);
        $this->formatter = $this->createMock(SpreadsheetFormatterInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->excelExporter = new ExcelExporter(
            $this->propertyAccessor,
            [$this->formatter],
            $this->dispatcher
        );
    }

    public function testExportReturnsCorrectStreamedResponseAndDispatchesEvent(): void
    {
        $class = 'App\Entity\User';
        $columns = ['id', 'username'];
        $filename = 'users.xlsx';

        $this->formatter->method('supports')->with($class, 'export_op')->willReturn(true);
        $this->formatter->method('formatColumnName')->willReturnMap([
            ['id', 'ID'],
            ['username', 'User name'],
        ]);
        $this->formatter->method('getComputedColumns')->willReturn([]);

        $user = new \stdClass();
        $data = [$user];

        $this->propertyAccessor->method('getValue')->willReturnMap([
            [$user, 'id', 1],
            [$user, 'username', 'jdoe'],
        ]);

        $this->dispatcher
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(static function (SpreadsheetGeneratedEvent $event) use ($class) {
                return $event->getContext() === ['resource_class' => $class];
            }))
            ->willReturnArgument(0);

        $response = $this->excelExporter->export($class, $data, $columns, 'export_op', $filename);

        self::assertInstanceOf(StreamedResponse::class, $response);
        self::assertSame(200, $response->getStatusCode());
        self::assertSame(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8',
            $response->headers->get('Content-Type')
        );
        self::assertSame('attachment; filename="users.xlsx"', $response->headers->get('Content-Disposition'));

        ob_start();
        $response->sendContent();
        $output = ob_get_clean();

        self::assertStringStartsWith('PK', $output);
    }
}
