<?php

declare(strict_types=1);

namespace App\Tests\Report\DataProvider\Extractor;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\UrlGeneratorInterface;
use App\Report\DataProvider\Extractor\IrisExtractorBuilder;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Report;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class IriExtractorBuilderTest extends KernelTestCase
{
    use ProphecyTrait;

    public function testItDoesNotGeneratesIrisWhenXAndYAreNotSet()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('At least x or y class iri should be set');

        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $iriExtractorBuilder = new IrisExtractorBuilder($iriConverterProphecy->reveal());

        $iriExtractorBuilder->generate();
    }

    public function testItGeneratesIrisWhenXIsSet()
    {
        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $iriConverterProphecy->getIriFromResource('Foo', UrlGeneratorInterface::ABS_PATH, new GetCollection())->shouldBeCalledOnce()->willReturn('/foo');

        $iriExtractorBuilder = new IrisExtractorBuilder($iriConverterProphecy->reveal());

        $reportDataProvider = new ReportDataProvider([['foo_id' => 1, 'x' => 'FOO']]);
        $reportDataProvider->setMetadataExtractor($iriExtractorBuilder->setX('Foo', 'foo_id')->generate());

        $report = new Report('/foobar', 'x', 'y');
        foreach ($reportDataProvider->provideMetadata() as $value => $metadata) {
            $report->addMetadata($value, $metadata);
        }

        self::assertSame(['xIris' => ['FOO' => '/foo/1'], 'yIris' => []], $report->getMetadata());
    }

    public function testItGeneratesIrisWhenYIsSet()
    {
        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $iriConverterProphecy->getIriFromResource('Bar', UrlGeneratorInterface::ABS_PATH, new GetCollection())->shouldBeCalledOnce()->willReturn('/bar');

        $iriExtractorBuilder = new IrisExtractorBuilder($iriConverterProphecy->reveal());

        $reportDataProvider = new ReportDataProvider([['bar_id' => '1', 'y' => 'BAR']]);
        $reportDataProvider->setMetadataExtractor($iriExtractorBuilder->setY('Bar', 'bar_id')->generate());

        $report = new Report('/foobar', 'x', 'y');
        foreach ($reportDataProvider->provideMetadata() as $value => $metadata) {
            $report->addMetadata($value, $metadata);
        }

        self::assertSame(['xIris' => [], 'yIris' => ['BAR' => '/bar/1']], $report->getMetadata());
    }

    public function testItGeneratesIrisWhenYAndXAreSet()
    {
        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $iriConverterProphecy->getIriFromResource('Foo', UrlGeneratorInterface::ABS_PATH, new GetCollection())->shouldBeCalledOnce()->willReturn('/foo');
        $iriConverterProphecy->getIriFromResource('Bar', UrlGeneratorInterface::ABS_PATH, new GetCollection())->shouldBeCalledOnce()->willReturn('/bar');

        $iriExtractorBuilder = new IrisExtractorBuilder($iriConverterProphecy->reveal());

        $reportDataProvider = new ReportDataProvider([['bar_id' => 1, 'foo_id' => 2, 'x' => 'FOO', 'y' => 'BAR']]);
        $reportDataProvider->setMetadataExtractor($iriExtractorBuilder->setX('Foo', 'foo_id')->setY('Bar', 'bar_id')->generate());

        $report = new Report('/foobar', 'x', 'y');
        foreach ($reportDataProvider->provideMetadata() as $value => $metadata) {
            $report->addMetadata($value, $metadata);
        }

        self::assertSame(['xIris' => ['FOO' => '/foo/2'], 'yIris' => ['BAR' => '/bar/1']], $report->getMetadata());
    }
}
