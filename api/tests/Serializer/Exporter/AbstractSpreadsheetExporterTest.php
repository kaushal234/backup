<?php

declare(strict_types=1);

namespace App\Tests\Serializer\Exporter;

use App\Formatter\Spreadsheet\SpreadsheetFormatterInterface;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\Persistence\Proxy;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class AbstractSpreadsheetExporterTest extends TestCase
{
    private PropertyAccessorInterface&MockObject $propertyAccessor;
    private SpreadsheetFormatterInterface&MockObject $formatter1;
    private SpreadsheetFormatterInterface&MockObject $formatter2;
    private StubSpreadsheetExporter $exporter;

    protected function setUp(): void
    {
        $this->propertyAccessor = $this->createMock(PropertyAccessorInterface::class);
        $this->formatter1 = $this->createMock(SpreadsheetFormatterInterface::class);
        $this->formatter2 = $this->createMock(SpreadsheetFormatterInterface::class);

        $this->exporter = new StubSpreadsheetExporter($this->propertyAccessor, [$this->formatter1, $this->formatter2]);
    }

    public function testGetFormatterReturnsMatchingFormatter(): void
    {
        $this->formatter1->method('supports')->with('App\Entity\User', 'export')->willReturn(false);
        $this->formatter2->method('supports')->with('App\Entity\User', 'export')->willReturn(true);

        $result = $this->exporter->getFormatter('App\Entity\User', 'export');

        self::assertSame($this->formatter2, $result);
    }

    public function testGetFormatterReturnsNullIfNoMatch(): void
    {
        $this->formatter1->method('supports')->willReturn(false);
        $this->formatter2->method('supports')->willReturn(false);

        self::assertNull($this->exporter->getFormatter('App\Entity\User', 'export'));
    }

    public function testBuildHeaderUsesFormatterOrFallback(): void
    {
        $this->formatter1->method('formatColumnName')
            ->willReturnMap([
                ['id', 'User ID'],
                ['name', 'name'],
            ]);

        $columns = ['id', 'name'];

        $headersWithFormatter = $this->exporter->callBuildHeader($columns, $this->formatter1);
        $headersWithoutFormatter = $this->exporter->callBuildHeader(['email'], null);

        self::assertSame(['User ID', 'name'], $headersWithFormatter);
        self::assertSame(['email'], $headersWithoutFormatter);
    }

    public function testBuildRowWithComputedColumns(): void
    {
        $item = new \stdClass();
        $this->formatter1->method('getComputedColumns')->willReturn(['fullName']);
        $this->formatter1->method('computeColumn')->with($item, 'fullName')->willReturn('John Doe');

        $this->propertyAccessor->expects(self::never())->method('getValue');

        $row = $this->exporter->callBuildRow($item, ['fullName'], $this->formatter1);

        self::assertSame(['John Doe'], $row);
    }

    /**
     * @dataProvider valueFormattingProvider
     */
    public function testBuildRowFormatsAndSanitizesValues(mixed $propertyValue, ?string $expectedResult, ?callable $formatterSetup = null): void
    {
        $item = new \stdClass();

        $this->propertyAccessor
            ->method('getValue')
            ->with($item, 'prop')
            ->willReturn($propertyValue);

        $formatter = null;
        if (null !== $formatterSetup) {
            $formatter = $this->createMock(SpreadsheetFormatterInterface::class);
            $formatterSetup($formatter);
        }

        $row = $this->exporter->callBuildRow($item, ['prop'], $formatter);

        self::assertSame([$expectedResult], $row);
    }

    public static function valueFormattingProvider(): array
    {
        return [
            'String standard' => ['Hello', 'Hello'],
            'Boolean True' => [true, 'Yes'],
            'Boolean False' => [false, 'No'],
            'Integer' => [42, '42'],
            'Null' => [null, null],
            'Default date format' => [
                new \DateTimeImmutable('2026-07-13 16:00:00'),
                '2026-07-13 16:00:00',
            ],
            'Formatted date' => [
                new \DateTimeImmutable('2026-07-13 16:00:00'),
                '13/07/2026',
                static function ($formatter) {
                    $formatter->method('formatDate')->willReturn('13/07/2026');
                },
            ],
            'Object with __toString' => [
                new class {
                    public function __toString()
                    {
                        return 'ObjectString';
                    }
                },
                'ObjectString',
            ],
            'Object without __toString' => [
                new \stdClass(),
                null,
            ],
            'Sanitize: Line break removal' => ["Line1\r\nLine2\nLine3", 'Line1 Line2 Line3'],
            'Sanitize: CSV Injection protection (=, +, -, @)' => ['=SUM(A1)', "'=SUM(A1)"],
            'Sanitize: Invisible control characters' => ['Text'.\chr(8).'With'.\chr(0).'Control', 'TextWithControl'],
            'Sanitize: Long string truncated to 32,767 characters' => [
                str_repeat('A', 35000),
                str_repeat('A', 32767),
            ],
            'Sanitize: Special Unicode spaces removal (NBSP, etc.)' => [
                "foo\u{00A0}bar\u{2000}baz",
                'foobarbaz',
            ],
        ];
    }

    public function testBuildRowTriggersProxyLoad(): void
    {
        $proxyItem = $this->createMock(Proxy::class);
        $proxyItem->expects(self::once())->method('__load');

        $item = new \stdClass();
        $this->propertyAccessor->method('getValue')->willReturn($proxyItem);

        $this->exporter->callBuildRow($item, ['proxyRelation'], null);
    }

    public function testBuildRowHandlesEntityNotFoundException(): void
    {
        $item = new \stdClass();
        $this->propertyAccessor
            ->method('getValue')
            ->willThrowException(new EntityNotFoundException());

        $row = $this->exporter->callBuildRow($item, ['deleted_soft'], null);

        self::assertSame([null], $row);
    }

    public function testBuildRowHandlesGenericThrowable(): void
    {
        $item = new \stdClass();
        $this->propertyAccessor
            ->method('getValue')
            ->willThrowException(new \Exception('Property does not exist'));

        $row = $this->exporter->callBuildRow($item, ['unknown'], null);

        self::assertSame([null], $row);
    }
}
