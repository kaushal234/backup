<?php

declare(strict_types=1);

namespace App\Tests\Serializer\Exporter;

use ApiPlatform\Metadata\Operation;
use App\Filter\ColumnsFilter;
use App\Serializer\Exporter\Exporter;
use App\Serializer\Exporter\ExporterInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExporterTest extends TestCase
{
    /** @dataProvider supportedFormatsProvider */
    public function testIsExportableReturnsTrueForSupportedFormats(string $format): void
    {
        $exporter = new Exporter(
            $this->createMock(ExporterInterface::class),
            $this->createMock(ExporterInterface::class),
        );

        $request = new Request(query: [ColumnsFilter::PARAMETER_NAME => 'name,email']);
        $request->setRequestFormat($format);

        self::assertTrue($exporter->isExportable($request));
    }

    public static function supportedFormatsProvider(): array
    {
        return [
            'csv' => ['csv'],
            'xlsx' => ['xlsx'],
        ];
    }

    /**
     * @dataProvider unsupportedCasesProvider
     */
    public function testIsExportableReturnsFalse(array $query, string $format): void
    {
        $exporter = new Exporter(
            $this->createMock(ExporterInterface::class),
            $this->createMock(ExporterInterface::class),
        );

        $request = new Request(query: $query);
        $request->setRequestFormat($format);

        self::assertFalse($exporter->isExportable($request));
    }

    public static function unsupportedCasesProvider(): array
    {
        return [
            'missing columns param' => [[], 'csv'],
            'unsupported format' => [[ColumnsFilter::PARAMETER_NAME => 'name'], 'html'],
        ];
    }

    public function testExportUsesExcelExporter(): void
    {
        $data = [['id' => 1]];
        $expectedResponse = $this->createMock(StreamedResponse::class);

        $operation = $this->createMock(Operation::class);
        $operation->method('getClass')->willReturn('App\Entity\User');
        $operation->method('getShortName')->willReturn('User');
        $operation->method('getName')->willReturn('export_users');

        $request = Request::create('/export?columns=id,name');
        $request->setRequestFormat('xlsx');

        $excelExporter = $this->createMock(ExporterInterface::class);
        $excelExporter
            ->expects(self::once())
            ->method('export')
            ->with(
                'App\Entity\User',
                $data,
                ['id', 'name'],
                'export_users',
                'user.xlsx'
            )
            ->willReturn($expectedResponse);

        $csvExporter = $this->createMock(ExporterInterface::class);
        $csvExporter
            ->expects(self::never())
            ->method('export');

        $exporter = new Exporter($excelExporter, $csvExporter);

        self::assertSame($expectedResponse, $exporter->export($operation, $data, $request));
    }

    public function testExportUsesCsvExporter(): void
    {
        $data = [['id' => 1]];
        $expectedResponse = $this->createMock(StreamedResponse::class);

        $operation = $this->createMock(Operation::class);
        $operation->method('getClass')->willReturn('App\Entity\User');
        $operation->method('getShortName')->willReturn('User');
        $operation->method('getName')->willReturn('export_users');

        $request = Request::create('/export?columns=id,email');
        $request->setRequestFormat('csv');

        $excelExporter = $this->createMock(ExporterInterface::class);
        $excelExporter
            ->expects(self::never())
            ->method('export');

        $csvExporter = $this->createMock(ExporterInterface::class);
        $csvExporter
            ->expects(self::once())
            ->method('export')
            ->with(
                'App\Entity\User',
                $data,
                ['id', 'email'],
                'export_users',
                'user.csv'
            )
            ->willReturn($expectedResponse);

        $exporter = new Exporter($excelExporter, $csvExporter);

        self::assertSame($expectedResponse, $exporter->export($operation, $data, $request));
    }

    public function testExportReturnsNullForUnsupportedFormat(): void
    {
        $operation = $this->createMock(Operation::class);

        $request = Request::create('/');
        $request->setRequestFormat('json');

        $excelExporter = $this->createMock(ExporterInterface::class);
        $excelExporter->expects(self::never())->method('export');

        $csvExporter = $this->createMock(ExporterInterface::class);
        $csvExporter->expects(self::never())->method('export');

        $exporter = new Exporter($excelExporter, $csvExporter);

        self::assertNull($exporter->export($operation, [], $request));
    }

    public function testExportUsesDefaultFilenameWhenShortNameIsNull(): void
    {
        $expectedResponse = $this->createMock(StreamedResponse::class);

        $operation = $this->createMock(Operation::class);
        $operation->method('getClass')->willReturn('App\Entity\User');
        $operation->method('getShortName')->willReturn(null);
        $operation->method('getName')->willReturn(null);

        $request = Request::create('/');
        $request->setRequestFormat('csv');

        $excelExporter = $this->createMock(ExporterInterface::class);

        $csvExporter = $this->createMock(ExporterInterface::class);
        $csvExporter
            ->expects(self::once())
            ->method('export')
            ->with(
                'App\Entity\User',
                [],
                [],
                '',
                'export.csv'
            )
            ->willReturn($expectedResponse);

        $exporter = new Exporter($excelExporter, $csvExporter);

        self::assertSame($expectedResponse, $exporter->export($operation, [], $request));
    }
}
