<?php

declare(strict_types=1);

namespace App\Tests\ION\DataProvider;

use App\Client\SoapClient;
use App\Client\SoapClientFactory;
use App\ION\Client\IONSoapConfigurator;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

abstract class AbstractIONDataProviderTest extends TestCase
{
    use ProphecyTrait;

    protected function getSoapClientFactoryProphecy(string $ionResource, string $method, array $arguments, ?\SoapFault $soapFault = null): ObjectProphecy
    {
        $soapClient = $this->getMockBuilder(SoapClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mocker = $soapClient->expects(self::once())
            ->method('__call')
            ->with($method, [$arguments])
        ;

        if (null !== $soapFault) {
            $mocker->willThrowException($soapFault);
        }

        $ionSoapClientFactoryProphecy = $this->prophesize(SoapClientFactory::class);
        $ionSoapClientFactoryProphecy->createClient(IONSoapConfigurator::CLIENT_NAME, $ionResource)->shouldBeCalledTimes(1)->willReturn($soapClient);

        return $ionSoapClientFactoryProphecy;
    }
}
