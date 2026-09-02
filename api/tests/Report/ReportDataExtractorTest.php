<?php

declare(strict_types=1);

namespace App\Tests\Report;

use App\Report\DataProvider\ReportDataProvider;
use App\Report\ReportDataExtractor;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ReportDataExtractorTest extends KernelTestCase
{
    public function testMissingValuesAreCorrectlyFilled()
    {
        $reportDataProvider = $this->getMockBuilder(ReportDataProvider::class)->disableOriginalConstructor()->getMock();
        $reportDataProvider
            ->expects(self::once())
            ->method('provideData')
            ->with()
            ->willReturn($this->getRawData())
        ;
        $reportDataProvider->expects(self::once())
            ->method('provideXLabels')
            ->with()
            ->willReturn($this->getXLabels())
        ;
        $reportDataProvider->expects(self::once())
            ->method('provideYLabels')
            ->with()
            ->willReturn($this->getYLabels())
        ;

        $results = (new ReportDataExtractor())->extract($reportDataProvider);

        self::assertCount(12, $results);
        $i = 0;
        foreach (['TLD AME', 'TLD EUR', 'TLD GST'] as $x) {
            foreach (['2017 06',  '2017 08', '2017 09', '2017 10'] as $y) {
                self::assertSame($x, $results[$i]['x']);
                self::assertSame($y, $results[$i]['y']);
                if (\in_array($i, [0, 1, 2, 4, 9, 10, 11], true)) {
                    self::assertSame(0, $results[$i]['value']);
                } else {
                    self::assertNotSame(0, $results[$i]['value']);
                }
                ++$i;
            }
        }
    }

    private function getRawData()
    {
        return [
            [
                'value' => 0.00,
                'y' => '2017 10',
                'x' => 'TLD AME',
            ],
            [
                'value' => 0.33,
                'y' => '2017 08',
                'x' => 'TLD EUR',
            ],
            [
                'value' => 2.80,
                'y' => '2017 09',
                'x' => 'TLD EUR',
            ],
            [
                'value' => 0.07,
                'y' => '2017 10',
                'x' => 'TLD EUR',
            ],
            [
                'value' => 0.90,
                'y' => '2017 06',
                'x' => 'TLD GST',
            ],
        ];
    }

    private function getXLabels()
    {
        return [
            ['x' => 'TLD AME'],
            ['x' => 'TLD EUR'],
            ['x' => 'TLD GST'],
        ];
    }

    private function getYLabels()
    {
        return [
            ['y' => '2017 06'],
            ['y' => '2017 08'],
            ['y' => '2017 09'],
            ['y' => '2017 10'],
        ];
    }
}
