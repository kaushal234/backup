<?php

declare(strict_types=1);

namespace App\Tests\Report;

use App\Report\Report;
use App\Report\ReportSnapshotFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ReportSnapshotFactoryTest extends KernelTestCase
{
    public function testFactoryCreatesAProperSnapshotObject()
    {
        self::bootKernel();
        /** @var NormalizerInterface $normalizer */
        $normalizer = static::getContainer()->get(NormalizerInterface::class);
        /** @var DenormalizerInterface $denormalizer */
        $denormalizer = static::getContainer()->get(DenormalizerInterface::class);

        $report = new Report('resource', 'x', 'y');
        $report
            ->addCell('cellX1', 'cellY1', 1)
            ->addCell('cellX1', 'cellY2', 2)
            ->addCell('cellX2', 'cellY1', 3)
            ->addCell('cellX2', 'cellY2', 4)
            ->addMetadata('metadata1', 'meta')
            ->addMetadata('metadata2', ['key' => 'value', 'ar' => 'ray']);

        $factory = new ReportSnapshotFactory($normalizer, $denormalizer);

        $snapshot = $factory->create($report, ['entity' => '/business_units/42']);

        self::assertSame('resource', $snapshot->resource);
        self::assertSame('x', $snapshot->x);
        self::assertSame('y', $snapshot->y);
        self::assertSame(['cellX1', 'cellX2'], array_keys($snapshot->rows));
        self::assertSame(['cellY1', 'cellY2'], array_keys($snapshot->rows['cellX1']));
        self::assertSame(['cellY1', 'cellY2'], array_keys($snapshot->rows['cellX2']));
        self::assertSame('ReportCell', $snapshot->rows['cellX1']['cellY1']['@type']);
        self::assertArrayHasKey('@id', $snapshot->rows['cellX1']['cellY1']);
        self::assertSame('cellX1', $snapshot->rows['cellX1']['cellY1']['x']);
        self::assertSame('cellY1', $snapshot->rows['cellX1']['cellY1']['y']);
        self::assertSame(1.0, $snapshot->rows['cellX1']['cellY1']['value']);

        self::assertSame(['entity' => '/business_units/42'], $snapshot->options);

        self::assertSame([
            'metadata1' => 'meta',
            'metadata2' => [
                'key' => 'value',
                'ar' => 'ray',
            ],
        ], $snapshot->metadata);
    }
}
