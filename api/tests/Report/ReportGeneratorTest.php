<?php

declare(strict_types=1);

namespace App\Tests\Report;

use App\Entity\Directory\People;
use App\Entity\News\News;
use App\Report\DataProvider\Extractor\LabelExtractor;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultHandler;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\ReportCell;
use App\Report\ReportDataExtractor;
use App\Report\ReportGenerator;
use App\Report\ReportQueriesBuilderFactory;
use App\Routing\IriToClassnameConverter;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ReportGeneratorTest extends KernelTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
    }

    public function testThrowsAnErrorWithInvalidResource(): void
    {
        $this->expectException(UnprocessableEntityHttpException::class);
        $this->expectExceptionMessage("Can't find any resource matching /teletubbies");

        $extractor = $this->getMockBuilder(ReportDataExtractor::class)->disableOriginalConstructor()->getMock();
        $factory = $this->getMockBuilder(ReportQueriesBuilderFactory::class)->disableOriginalConstructor()->getMock();

        $factory
            ->expects(self::never())
            ->method('getQueriesBuilder');

        static::getContainer()->set(ReportDataExtractor::class, $extractor);
        static::getContainer()->set(ReportQueriesBuilderFactory::class, $factory);

        /** @var ReportGenerator $generator */
        $generator = static::getContainer()->get(ReportGenerator::class);

        $generator->getReport('/teletubbies', 'color', 'happiness');
    }

    public function testReportIsReturnedForValidParameters(): void
    {
        $securityMock = $this->createMock(Security::class);
        $iriToClassnameConverterMock = $this->createMock(IriToClassnameConverter::class);

        $data = $this->getDummyData();
        $reportDataProvider = new ReportDataProvider(
            $data,
            (new LabelExtractor($data, 'x'))(),
            (new LabelExtractor($data, 'y'))()
        );

        $iriToClassnameConverterMock->expects($this->once())->method('convert')->with('/news')->willReturn(News::class);
        $securityMock->expects($this->once())->method('getUser')->willReturn($people = new People());

        $defaultHandler = $this->getMockBuilder(DefaultHandler::class)->disableOriginalConstructor()->getMock();
        $defaultHandler->expects($this->once())->method('isGranted')->with($people)->willReturn(true);

        $defaultHandler
            ->expects(self::once())
            ->method('handle')
            ->with(News::class, 'color', 'happiness', [])
            ->willReturn($reportDataProvider);

        $extractor = $this->getMockBuilder(ReportDataExtractor::class)->disableOriginalConstructor()->getMock();
        $extractor
            ->expects(self::once())
            ->method('extract')
            ->with($reportDataProvider)
            ->willReturn($this->getExtractedDummyData())
        ;

        static::getContainer()->set(ReportDataExtractor::class, $extractor);

        /** @var ReportGenerator $generator */
        $generator = new ReportGenerator($iriToClassnameConverterMock, $extractor, $securityMock);
        $generator->setHandlers([$defaultHandler]);

        $report = $generator->getReport('/news', 'color', 'happiness');

        self::assertSame('color', $report->getX());
        self::assertSame('happiness', $report->getY());
        self::assertSame('/news', $report->getResource());
        self::assertSame(['blue', 'pink'], array_keys($report->getxTotals()));
        self::assertSame(['average', 'high', 'low'], array_keys($report->getyTotals()));
        self::assertSame(['blue' => 54.0, 'pink' => 13.0], $report->getxTotals());
        self::assertSame(['average' => 25.0, 'high' => 16.0, 'low' => 26.0], $report->getyTotals());

        foreach ($report->getRows() as $key => $cells) {
            self::assertContains($key, ['pink', 'blue']);
            foreach ($cells as $cell) {
                self::assertInstanceOf(ReportCell::class, $cell);
            }
            self::assertCount(\count($report->getyTotals()), $cells);
        }

        self::assertSame(0.0, $report->getRows()['pink']['average']->getValue());
    }

    public function testThatTheFirstHandlerReturningAProviderStopHandlersLoop(): void
    {
        $securityMock = $this->createMock(Security::class);
        $iriToClassnameConverterMock = $this->createMock(IriToClassnameConverter::class);
        $extractor = $this->getMockBuilder(ReportDataExtractor::class)->disableOriginalConstructor()->getMock();
        $extractor->method('extract')->willReturn([]);

        static::getContainer()->set(ReportDataExtractor::class, $extractor);

        $generator = new ReportGenerator($iriToClassnameConverterMock, $extractor, $securityMock);

        $iriToClassnameConverterMock->expects($this->once())->method('convert')->with('/news')->willReturn(News::class);
        $securityMock->expects($this->once())->method('getUser')->willReturn($people = new People());

        $handler1 = $this->getMockBuilder(ReportHandlerInterface::class)->getMock();
        $handler2 = $this->getMockBuilder(ReportHandlerInterface::class)->getMock();
        $handler3 = $this->getMockBuilder(ReportHandlerInterface::class)->getMock();
        $handler4 = $this->getMockBuilder(ReportHandlerInterface::class)->getMock();
        $provider = $this->getMockBuilder(ReportDataProvider::class)->disableOriginalConstructor()->getMock();

        $handler1->expects($this->once())->method('isGranted')->with($people)->willReturn(true);
        $handler1->expects(self::once())->method('handle')->with(News::class, 'color', 'happiness', [])->willReturn(null);
        $handler1->expects($this->once())->method('isGranted')->with($people)->willReturn(true);
        $handler2->expects(self::once())->method('handle')->with(News::class, 'color', 'happiness', [])->willReturn($provider);
        $handler3->expects(self::never())->method('handle');
        $handler4->expects(self::never())->method('handle');

        $generator->setHandlers([$handler1, $handler2, $handler3, $handler4]);

        $generator->getReport('/news', 'color', 'happiness');
    }

    public function testReportIsReturnedWithMetadata(): void
    {
        $securityMock = $this->createMock(Security::class);
        $iriToClassnameConverterMock = $this->createMock(IriToClassnameConverter::class);

        $data = $this->getDummyData();
        $data[] = [
            'x' => '123',
            'y' => 'low',
            'value' => 16,
            'meta' => 'carpe',
        ];

        $reportDataProvider = new ReportDataProvider(
            $data,
            (new LabelExtractor($data, 'x'))(),
            (new LabelExtractor($data, 'y'))()
        );

        $reportDataProvider->setMetadataExtractor(static function (array $results): array {
            $metadata = [];
            foreach ($results as $result) {
                $metadata[$result['x']] = ['name' => $result['x'], 'foo' => $result['meta'] ?? 'bar'];
            }
            $metadata['random'] = 'value';

            return $metadata;
        });

        $iriToClassnameConverterMock->expects($this->once())->method('convert')->with('/news')->willReturn(News::class);
        $securityMock->expects($this->once())->method('getUser')->willReturn($people = new People());
        $defaultHandler = $this->getMockBuilder(DefaultHandler::class)->disableOriginalConstructor()->getMock();

        $defaultHandler->expects($this->once())->method('isGranted')->with($people)->willReturn(true);
        $defaultHandler
            ->expects(self::once())
            ->method('handle')
            ->with(News::class, 'color', 'happiness', [])
            ->willReturn($reportDataProvider);

        $extraExtractedDummyData = [
            [
                'x' => '123',
                'y' => 'average',
                'value' => 0,
            ],
            [
                'x' => '123',
                'y' => 'high',
                'value' => 0,
            ],
            [
                'x' => '123',
                'y' => 'low',
                'value' => 16,
            ],
        ];
        $extractor = $this->getMockBuilder(ReportDataExtractor::class)->disableOriginalConstructor()->getMock();
        $extractor
            ->expects(self::once())
            ->method('extract')
            ->with($reportDataProvider)
            ->willReturn([...$this->getExtractedDummyData(), ...$extraExtractedDummyData])
        ;

        static::getContainer()->set(ReportDataExtractor::class, $extractor);

        $generator = new ReportGenerator($iriToClassnameConverterMock, $extractor, $securityMock);
        $generator->setHandlers([$defaultHandler]);

        $report = $generator->getReport('/news', 'color', 'happiness');

        self::assertSame(['blue' => ['name' => 'blue', 'foo' => 'bar'], 'pink' => ['name' => 'pink', 'foo' => 'bar'], '123' => ['name' => '123', 'foo' => 'carpe'], 'random' => 'value'], $report->getMetadata());
    }

    public function testReportIncludesExtraData(): void
    {
        $securityMock = $this->createMock(Security::class);
        $iriToClassnameConverterMock = $this->createMock(IriToClassnameConverter::class);

        $data = $this->getExtractedDummyData();
        $reportDataProvider = new ReportDataProvider(
            $data,
            (new LabelExtractor($data, 'x'))(),
            (new LabelExtractor($data, 'y'))()
        );

        $iriToClassnameConverterMock->expects($this->once())->method('convert')->with('/news')->willReturn(News::class);
        $securityMock->expects($this->once())->method('getUser')->willReturn($people = new People());

        $defaultHandler = $this->getMockBuilder(DefaultHandler::class)->disableOriginalConstructor()->getMock();
        $defaultHandler->expects($this->once())->method('isGranted')->with($people)->willReturn(true);

        $defaultHandler
            ->expects(self::once())
            ->method('handle')
            ->with(News::class, 'color', 'happiness', [])
            ->willReturn($reportDataProvider);

        $extractor = $this->getMockBuilder(ReportDataExtractor::class)->disableOriginalConstructor()->getMock();
        $extractor
            ->expects(self::once())
            ->method('extract')
            ->with($reportDataProvider)
            ->willReturn($data);

        static::getContainer()->set(ReportDataExtractor::class, $extractor);

        $generator = new ReportGenerator($iriToClassnameConverterMock, $extractor, $securityMock);
        $generator->setHandlers([$defaultHandler]);

        $report = $generator->getReport('/news', 'color', 'happiness');

        foreach ($report->getRows() as $xKey => $cells) {
            foreach ($cells as $cell) {
                $extraData = $cell->getExtraData();

                self::assertIsArray($extraData);
                if ('blue' === $xKey && 'average' === $cell->getY()) {
                    self::assertArrayHasKey('pdi', $extraData);
                    self::assertArrayHasKey('unitGreenTagged', $extraData);
                    self::assertSame(5, $extraData['pdi']);
                    self::assertSame(10, $extraData['unitGreenTagged']);
                } elseif ('pink' === $xKey && 'low' === $cell->getY()) {
                    self::assertEmpty($extraData);
                } elseif ('pink' === $xKey && 'high' === $cell->getY()) {
                    self::assertEmpty($extraData);
                }
            }
        }
    }

    private function getDummyData(): array
    {
        return [
            [
                'x' => 'blue',
                'y' => 'average',
                'value' => 25,
            ],
            [
                'x' => 'blue',
                'y' => 'high',
                'value' => 4,
            ],
            [
                'x' => 'blue',
                'y' => 'low',
                'value' => 25,
            ],
            [
                'x' => 'pink',
                'y' => 'high',
                'value' => 12,
            ],
            [
                'x' => 'pink',
                'y' => 'low',
                'value' => 1,
            ],
        ];
    }

    private function getExtractedDummyData(): array
    {
        return [
            [
                'x' => 'blue',
                'y' => 'average',
                'value' => 25,
                'extraData' => ['pdi' => 5, 'unitGreenTagged' => 10],
            ],
            [
                'x' => 'blue',
                'y' => 'high',
                'value' => 4,
                'extraData' => ['pdi' => 3, 'unitGreenTagged' => 15],
            ],
            [
                'x' => 'blue',
                'y' => 'low',
                'value' => 25,
                'extraData' => ['pdi' => 2, 'unitGreenTagged' => 20],
            ],
            [
                'x' => 'pink',
                'y' => 'average',
                'value' => 0,
                'extraData' => ['pdi' => 4, 'unitGreenTagged' => 25],
            ],
            [
                'x' => 'pink',
                'y' => 'high',
                'value' => 12,
            ],
            [
                'x' => 'pink',
                'y' => 'low',
                'value' => 1,
                'extraData' => [],
            ],
        ];
    }
}
