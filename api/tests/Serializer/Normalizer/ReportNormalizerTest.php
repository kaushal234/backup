<?php

declare(strict_types=1);

namespace App\Tests\Serializer\Normalizer;

use App\Report\Report;
use App\Serializer\Normalizer\ReportNormalizer;
use PHPUnit\Framework\TestCase;

class ReportNormalizerTest extends TestCase
{
    public function testSupportNormalization()
    {
        $normalizer = new ReportNormalizer();
        self::assertTrue($normalizer->supportsNormalization(new Report('iri', 'x', 'y'), 'csv'));
        self::assertFalse($normalizer->supportsNormalization(new Report('iri', 'x', 'y'), 'json'));
        self::assertFalse($normalizer->supportsNormalization(new \stdClass(), 'json'));
    }

    public function testNormalize()
    {
        $expected = [
            ['' => 'TLD AME', 'PENDING' => 2.0, 'IN PROGRESS' => 4.0, 'CLOSED' => 8.0, 'Totals' => 14.0],
            ['' => 'TLD STL', 'PENDING' => 3.0, 'IN PROGRESS' => 9.0, 'CLOSED' => 27.0, 'Totals' => 39.0],
            ['' => 'TLD MTL', 'PENDING' => 4.0, 'IN PROGRESS' => 16.0, 'CLOSED' => 64.0, 'Totals' => 84.0],
            ['' => 'Totals', 'PENDING' => 9.0, 'IN PROGRESS' => 29.0, 'CLOSED' => 99.0, 'Totals' => 137.0],
        ];

        $result = (new ReportNormalizer())->normalize($this->getReport(), 'csv');

        self::assertSame($expected, $result);
    }

    public function testNormalizeEmptyReport()
    {
        $expected = [];

        $result = (new ReportNormalizer())->normalize(new Report('foo', 'x', 'y'), 'csv');

        self::assertSame($expected, $result);
    }

    public function testNormalizeWithoutYTotals()
    {
        $expected = [
            ['' => 'TLD AME', 'PENDING' => 2.0, 'IN PROGRESS' => 4.0, 'CLOSED' => 8.0, 'Totals' => 14.0],
            ['' => 'TLD STL', 'PENDING' => 3.0, 'IN PROGRESS' => 9.0, 'CLOSED' => 27.0, 'Totals' => 39.0],
            ['' => 'TLD MTL', 'PENDING' => 4.0, 'IN PROGRESS' => 16.0, 'CLOSED' => 64.0, 'Totals' => 84.0],
        ];

        $result = (new ReportNormalizer())->normalize($this->getReport(), 'csv', ['hideTotals' => 'y']);

        self::assertSame($expected, $result);
    }

    public function testNormalizeWithoutXTotals()
    {
        $expected = [
            ['' => 'TLD AME', 'PENDING' => 2.0, 'IN PROGRESS' => 4.0, 'CLOSED' => 8.0],
            ['' => 'TLD STL', 'PENDING' => 3.0, 'IN PROGRESS' => 9.0, 'CLOSED' => 27.0],
            ['' => 'TLD MTL', 'PENDING' => 4.0, 'IN PROGRESS' => 16.0, 'CLOSED' => 64.0],
            ['' => 'Totals', 'PENDING' => 9.0, 'IN PROGRESS' => 29.0, 'CLOSED' => 99.0],
        ];

        $result = (new ReportNormalizer())->normalize($this->getReport(), 'csv', ['hideTotals' => 'x']);

        self::assertSame($expected, $result);
    }

    public function testNormalizeWithoutTotals()
    {
        $expected = [
            ['' => 'TLD AME', 'PENDING' => 2.0, 'IN PROGRESS' => 4.0, 'CLOSED' => 8.0],
            ['' => 'TLD STL', 'PENDING' => 3.0, 'IN PROGRESS' => 9.0, 'CLOSED' => 27.0],
            ['' => 'TLD MTL', 'PENDING' => 4.0, 'IN PROGRESS' => 16.0, 'CLOSED' => 64.0],
        ];

        $result = (new ReportNormalizer())->normalize($this->getReport(), 'csv', ['hideTotals' => 'both']);

        self::assertSame($expected, $result);
    }

    public function testNormalizeFilteringAndOrderingColumns()
    {
        $expected = [
            ['' => 'TLD AME', 'CLOSED' => 8.0, 'PENDING' => 2.0, 'FOO' => 0.0, 'Totals' => 10.0],
            ['' => 'TLD STL', 'CLOSED' => 27.0, 'PENDING' => 3.0, 'FOO' => 0.0, 'Totals' => 30.0],
            ['' => 'TLD MTL', 'CLOSED' => 64.0, 'PENDING' => 4.0, 'FOO' => 0.0, 'Totals' => 68.0],
            ['' => 'Totals', 'CLOSED' => 99.0, 'PENDING' => 9.0, 'FOO' => 0.0, 'Totals' => 108.0],
        ];

        $result = (new ReportNormalizer())->normalize($this->getReport(), 'csv', ['headers' => ['columns' => ['CLOSED', 'PENDING', 'FOO']]]);

        self::assertSame($expected, $result);
    }

    public function testNormalizeEmptyReportFilteringAndOrderingColumns()
    {
        $expected = [
            ['' => 'Totals', 'CLOSED' => 0.0, 'PENDING' => 0.0, 'FOO' => 0.0, 'Totals' => 0.0],
        ];

        $result = (new ReportNormalizer())->normalize(new Report('foo', 'x', 'y'), 'csv', ['headers' => ['columns' => ['CLOSED', 'PENDING', 'FOO']]]);

        self::assertSame($expected, $result);
    }

    public function testNormalizeEmptyReportFilteringAndOrderingColumnsWithMetadata()
    {
        $expected = [
            ['' => 'TLD AME', 'CLOSED' => 8.0, 'META' => 'bar 0', 'Totals' => 8.0],
            ['' => 'TLD STL', 'CLOSED' => 27.0, 'META' => 0.0, 'Totals' => 27.0],
            ['' => 'TLD MTL', 'CLOSED' => 64.0, 'META' => 'bar 1', 'Totals' => 64.0],
            ['' => 'Totals', 'CLOSED' => 99.0, 'META' => 0.0, 'Totals' => 99.0],
        ];

        $report = $this->getReport();
        foreach (['TLD AME', 'TLD MTL'] as $k => $v) {
            $report->addMetadata($v, ['name' => $v, 'META' => \sprintf('bar %s', $k)]);
        }
        $result = (new ReportNormalizer())->normalize($report, 'csv', ['headers' => ['columns' => ['CLOSED', 'META']]]);

        self::assertSame($expected, $result);
    }

    public function testNormalizeFilteringAndOrderingLines()
    {
        $expected = [
            ['' => 'TLD MTL', 'PENDING' => 4.0, 'IN PROGRESS' => 16.0, 'CLOSED' => 64.0, 'Totals' => 84.0],
            ['' => 'FOO', 'PENDING' => 0.0, 'IN PROGRESS' => 0.0, 'CLOSED' => 0.0, 'Totals' => 0.0],
            ['' => 'TLD STL', 'PENDING' => 3.0, 'IN PROGRESS' => 9.0, 'CLOSED' => 27.0, 'Totals' => 39.0],
            ['' => 'Totals', 'PENDING' => 7.0, 'IN PROGRESS' => 25.0, 'CLOSED' => 91.0, 'Totals' => 123.0],
        ];

        $result = (new ReportNormalizer())->normalize($this->getReport(), 'csv', ['headers' => ['lines' => ['TLD MTL', 'FOO', 'TLD STL']]]);

        self::assertSame($expected, $result);
    }

    public function testNormalizeEmptyReportFilteringAndOrderingLines()
    {
        $expected = [
            ['' => 'TLD MTL', 'Totals' => 0.0],
            ['' => 'FOO', 'Totals' => 0.0],
            ['' => 'TLD STL', 'Totals' => 0.0],
            ['' => 'Totals', 'Totals' => 0.0],
        ];

        $result = (new ReportNormalizer())->normalize(new Report('foo', 'x', 'y'), 'csv', ['headers' => ['lines' => ['TLD MTL', 'FOO', 'TLD STL']]]);

        self::assertSame($expected, $result);
    }

    public function testNormalizeWithBadLinesHeaders()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('CSV headers must be passed as arrays.');

        (new ReportNormalizer())->normalize(new Report('foo', 'x', 'y'), 'csv', ['headers' => ['lines' => 'TLD MTL']]);
    }

    public function testNormalizeWithBadColumnsHeaders()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('CSV headers must be passed as arrays.');

        (new ReportNormalizer())->normalize(new Report('foo', 'x', 'y'), 'csv', ['headers' => ['columns' => 'TLD MTL']]);
    }

    private function getReport(): Report
    {
        $report = new Report('foo', 'x', 'y');
        $i = 1;
        foreach (['TLD AME', 'TLD STL', 'TLD MTL'] as $x) {
            ++$i;
            foreach (['PENDING', 'IN PROGRESS', 'CLOSED'] as $pow => $y) {
                $report->addCell($x, $y, $i ** ($pow + 1));
            }
        }

        return $report;
    }
}
