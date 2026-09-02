<?php

declare(strict_types=1);

namespace App\Tests\Report\DataProvider\Extractor;

use App\Report\DataProvider\Extractor\LabelExtractor;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class LabelExtractorTest extends KernelTestCase
{
    public function testThatItExtractUniqueLabelAndSortThem()
    {
        $rawData = [
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
                'value' => 0.00,
                'y' => '2017 06',
                'x' => 'TLD GST',
            ],
            [
                'value' => 0.00,
                'y' => '2017 06',
                'x' => 'TLD_BAR',
            ],
            [
                'value' => 0.00,
                'y' => '2017 06',
                'x' => 'TLD_AME',
            ],
            [
                'value' => 0.00,
                'y' => '2017 06',
                'x' => 'XLF',
            ],
        ];
        $expectedXLabels = [
            ['x' => 'TLD AME'],
            ['x' => 'TLD EUR'],
            ['x' => 'TLD GST'],
            ['x' => 'TLD_AME'],
            ['x' => 'TLD_BAR'],
            ['x' => 'XLF'],
        ];
        self::assertSame($expectedXLabels, (new LabelExtractor($rawData, 'x'))());

        $expectedYLabels = [
            ['y' => '2017 06'],
            ['y' => '2017 08'],
            ['y' => '2017 09'],
            ['y' => '2017 10'],
        ];
        self::assertSame($expectedYLabels, (new LabelExtractor($rawData, 'y'))());
    }
}
