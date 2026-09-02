<?php

declare(strict_types=1);

namespace App\Tests\Formatter\Snappy;

use App\Formatter\Snappy\Adapter;
use App\Formatter\Snappy\AdapterFactory\AdapterFactoryInterface;
use App\Formatter\Snappy\Formatter;
use Knp\Snappy\GeneratorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\Environment;

class FormatterTest extends KernelTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testAddingAnAdapterMakesDatetimeConvertibleToPDF()
    {
        $adapter = new DummyAdapterFactory(\DateTime::class, 'datetime_to_pdf');

        $formatter = $this->getPdfFormatterWithoutTwig([$adapter]);

        self::assertNotEmpty($formatter->convert(new \DateTime(), 'datetime_to_pdf', 'pdf'));
    }

    public function formatterProvider()
    {
        yield ['app.pdf.formatter'];
        yield ['app.image.formatter'];
    }

    /**
     * @dataProvider optionsProvider
     */
    public function testConvertUsesAdapterOptions($expected, $options)
    {
        $templatingMock = $this->getMockBuilder(Environment::class)->disableOriginalConstructor()->getMock();
        $templatingMock->expects($this->any())->method('render')->willReturn('foo');

        /** @var GeneratorInterface|MockObject $generatorMock */
        $generatorMock = $this->getMockBuilder(GeneratorInterface::class)->getMock();
        $generatorMock
            ->expects(self::once())
            ->method('getOutputFromHtml')
            ->with('foo', $expected)
            ->willReturn('bar')
        ;

        $adapterFactoryMock = $this->getMockBuilder(AdapterFactoryInterface::class)->getMock();
        $adapterFactoryMock->expects(self::once())->method('getPurpose')->willReturn('foo');
        $obj = new \stdClass();

        $adapter = new Adapter('template', []);
        $adapter->setHeaderTemplate('foo');
        $adapter->setFooterTemplate('foo');
        foreach ($options as $name => $value) {
            $adapter->addOption($name, $value);
        }

        $adapterFactoryMock->expects(self::once())->method('getAdapter')->with($obj, 'format')->willReturn($adapter);
        $adapterFactoryMock->expects($this->any())->method('supports')->willReturn(true);

        $formatter = new Formatter($templatingMock, $generatorMock, [$adapterFactoryMock]);

        $formatter->convert($obj, 'foo', 'format');
    }

    public function optionsProvider(): iterable
    {
        yield 'defaults options' => [['header-html' => 'foo', 'header-spacing' => 5, 'footer-html' => 'foo'], []];
        yield 'custom options' => [['header-html' => 'foo', 'header-spacing' => 5, 'footer-html' => 'foo', 'bar' => 'buz'], ['bar' => 'buz']];
        yield 'custom options that overrides defaults' => [['header-html' => 'lol', 'header-spacing' => 42, 'footer-html' => 'biz', 'bar' => 'buz'], ['header-html' => 'lol', 'header-spacing' => 42, 'footer-html' => 'biz', 'bar' => 'buz']];
    }

    private function getPdfFormatterWithoutTwig(array $adapters = []): Formatter
    {
        $templatingMock = $this->getMockBuilder(Environment::class)->disableOriginalConstructor()->getMock();
        $templatingMock->method('render')->willReturn('<html><body><h1>html_string</h1></body></html>');
        /** @var GeneratorInterface $generator */
        $generator = static::getContainer()->get('knp_snappy.pdf');

        /* @var Environment $templatingMock */
        return new Formatter(
            $templatingMock,
            $generator,
            $adapters
        );
    }
}
