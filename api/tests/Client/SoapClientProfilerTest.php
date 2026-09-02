<?php

declare(strict_types=1);

namespace App\Tests\Client;

use App\Client\SoapClient;
use App\Client\SoapClientProfiler;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SoapClientProfilerTest extends KernelTestCase
{
    use ProphecyTrait;

    public function testCallDecorated()
    {
        $mock = $this->createMock(SoapClient::class);
        $mock
            ->expects($this->once())
            ->method('__call')
            ->with('foam', ['OPERA'])
        ;
        $eventDispatcherProphecy = $this->prophesize(EventDispatcherInterface::class);
        $eventDispatcherProphecy->dispatch(Argument::cetera());

        $IONSoapClient = new SoapClientProfiler(
            $mock,
            $eventDispatcherProphecy->reveal()
        );

        $IONSoapClient->__call('foam', ['OPERA']);
    }

    public function testCaughtException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Caught exception');

        $mock = $this->createMock(SoapClient::class);
        $mock
            ->expects($this->once())
            ->method('__call')
            ->with('foam', ['OPERA'])
            ->willThrowException(new \Exception('Caught exception'))
        ;
        $eventDispatcherProphecy = $this->prophesize(EventDispatcherInterface::class);
        $eventDispatcherProphecy->dispatch(Argument::cetera());

        $IONSoapClient = new SoapClientProfiler(
            $mock,
            $eventDispatcherProphecy->reveal()
        );

        $IONSoapClient->__call('foam', ['OPERA']);
    }
}
