<?php

declare(strict_types=1);

namespace App\Tests\Client;

use App\Client\Exception\SoapException;
use App\Client\Parser\SoapCallParser;
use App\Client\SoapClient;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\SerializerInterface;

class SoapClientTest extends KernelTestCase
{
    use ProphecyTrait;

    public function testThatNormalConfigurationDoesNotWriteFiles()
    {
        $requestStack = new RequestStack();
        $requestStack->push(new Request());
        $filesystemProphecy = $this->prophesize(Filesystem::class);
        $filesystemProphecy->dumpFile(Argument::any(), Argument::any())->shouldNotBeCalled();
        $filesystemProphecy->exists(Argument::any())->shouldNotBeCalled();
        $soapCallParserProphecy = $this->prophesize(SoapCallParser::class);
        $soapCallParserProphecy->parse(Argument::any())->shouldNotBeCalled();
        $serializerProphecy = $this->prophesize(DecoderInterface::class)->willImplement(SerializerInterface::class);
        $loggerProphecy = $this->prophesize(LoggerInterface::class);

        $IONSoapClient = new SoapClient(
            $this->mockSoapClient('foam', ['OPERA']),
            $requestStack,
            $filesystemProphecy->reveal(),
            $soapCallParserProphecy->reveal(),
            $serializerProphecy->reveal(),
            '',
            $loggerProphecy->reveal()
        );

        $IONSoapClient->__call('foam', ['OPERA']);
        $IONSoapClient->__getLastResponse();
    }

    public function testThatRecordConfigurationWritesFiles()
    {
        $requestStack = new RequestStack();
        $requestStack->push(new Request());
        $filesystemProphecy = $this->prophesize(Filesystem::class);
        $filesystemProphecy->dumpFile(Argument::any(), Argument::any())->shouldBeCalledOnce();
        $filesystemProphecy->exists(Argument::any())->shouldNotBeCalled();
        $soapCallParserProphecy = $this->prophesize(SoapCallParser::class);
        $soapCallParserProphecy->parse(Argument::any())->shouldBeCalledOnce();
        $serializerProphecy = $this->prophesize(DecoderInterface::class)->willImplement(SerializerInterface::class);
        $serializerProphecy->decode(Argument::any(), Argument::any())->shouldNotBeCalled();
        $loggerProphecy = $this->prophesize(LoggerInterface::class);

        $IONSoapClient = new SoapClient(
            $this->mockSoapClient('foam', ['OPERA'], '<?xml version="1.0"?><Coucou></Coucou>'),
            $requestStack,
            $filesystemProphecy->reveal(),
            $soapCallParserProphecy->reveal(),
            $serializerProphecy->reveal(),
            '',
            $loggerProphecy->reveal(),
        );

        $IONSoapClient->enableRecord();
        $IONSoapClient->__call('foam', ['OPERA']);
        $IONSoapClient->__getLastResponse();
    }

    public function testThatCallDisabledConfigurationReadsFilesAndThrowsIfFileDoesNotExist()
    {
        static::bootKernel();
        $projectDir = static::getContainer()->getParameter('kernel.project_dir');

        $this->expectException(SoapException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(\sprintf('Fixture file %s/tests/fixtures/soap/ion/pouet/camion/d71f2269b95fbdc6dab13ad5add5fa30441dbae48e5a911181d33673d24a1645.xml not found', $projectDir));

        $loggerProphecy = $this->prophesize(LoggerInterface::class);
        $requestStack = new RequestStack();
        $requestStack->push(Request::create('/ion/pouet/camion'));
        $filesystemProphecy = $this->prophesize(Filesystem::class);
        $filesystemProphecy->dumpFile(Argument::any(), Argument::any())->shouldNotBeCalled();
        $filesystemProphecy->exists(\sprintf('%s/tests/fixtures/soap/ion/pouet/camion/d71f2269b95fbdc6dab13ad5add5fa30441dbae48e5a911181d33673d24a1645.xml', $projectDir))->shouldBeCalledOnce()->willReturn(false);
        $soapCallParserProphecy = $this->prophesize(SoapCallParser::class);
        $soapCallParserProphecy->parse('OPERA')->shouldBeCalledOnce()->willReturn('OPERA');
        $serializerProphecy = $this->prophesize(DecoderInterface::class)->willImplement(SerializerInterface::class);
        $serializerProphecy->decode(Argument::any(), Argument::any())->shouldNotBeCalled();

        $IONSoapClient = new SoapClient(
            $this->mockSoapClient(),
            $requestStack,
            $filesystemProphecy->reveal(),
            $soapCallParserProphecy->reveal(),
            $serializerProphecy->reveal(),
            $projectDir,
            $loggerProphecy->reveal()
        );

        $IONSoapClient->disableSoapCalls();
        $IONSoapClient->__call('foam', ['OPERA']);
        $IONSoapClient->__getLastResponse();
    }

    public function testThatCallDisabledConfigurationReadsFilesAndThrowsSoapFaultWhenFixtureIsAnError()
    {
        static::bootKernel();
        $projectDir = static::getContainer()->getParameter('kernel.project_dir');

        $this->expectException(\SoapFault::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('PouetFault');

        $loggerProphecy = $this->prophesize(LoggerInterface::class);
        $requestStack = new RequestStack();
        $requestStack->push(Request::create('/phpunit/pouet/tagada', Request::METHOD_GET, ['pouet' => 'tagada']));
        $filesystemProphecy = $this->prophesize(Filesystem::class);
        $filesystemProphecy->dumpFile(Argument::any(), Argument::any())->shouldNotBeCalled();
        $file = \sprintf('%s/tests/fixtures/soap/phpunit/pouet/tagada/e3128b84b1d0eeda019e72867038fdfa427cc384d977025258702d3da3275d3d.xml', $projectDir);
        $filesystemProphecy->exists($file)->shouldBeCalledTimes(2)->willReturn(true);
        $soapCallParserProphecy = $this->prophesize(SoapCallParser::class);
        $soapCallParserProphecy->parse('OPERA')->shouldBeCalledOnce()->willReturn('OPERA');
        $serializer = static::getContainer()->get(SerializerInterface::class);

        $IONSoapClient = new SoapClient(
            $this->mockSoapClient(),
            $requestStack,
            $filesystemProphecy->reveal(),
            $soapCallParserProphecy->reveal(),
            $serializer,
            $projectDir,
            $loggerProphecy->reveal()
        );

        $IONSoapClient->disableSoapCalls();
        $IONSoapClient->__call('foam', ['OPERA']);
        $IONSoapClient->__getLastResponse();
    }

    private function mockSoapClient(?string $method = null, array $arguments = [], ?string $response = null): \SoapClient
    {
        $mock = $this->createMock(\SoapClient::class);

        if (null !== $method) {
            $mock
                ->expects($this->once())
                ->method('__soapCall')
                ->with($method, $arguments)
            ;
        }

        if (null !== $response) {
            $mock
                ->method('__getLastResponse')
                ->willReturn($response)
            ;
        }

        return $mock;
    }
}
