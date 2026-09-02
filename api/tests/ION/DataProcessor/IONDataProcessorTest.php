<?php

declare(strict_types=1);

namespace App\Tests\ION\DataProcessor;

use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Client\SoapClient;
use App\Client\SoapClientFactory;
use App\ION\Client\IONSoapConfigurator;
use App\ION\DataProcessor\IONDataProcessor;
use App\ION\DataProvider\AbstractIONDataProvider;
use App\ION\Resources\MasterData\BusinessPartners\BusinessPartner;
use App\ION\Resources\Procurement\Orders\PurchaseOrder;
use App\ION\ResourceSourceProvider\ResourceSourceProviderInterface;
use App\ION\SourceProvider\SourceProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class IONDataProcessorTest extends TestCase
{
    use ProphecyTrait;

    public function testDataPersistCreate()
    {
        $normalizerProphecy = $this->prophesize(NormalizerInterface::class);
        $sourceProviderProphecy = $this->prophesize(SourceProvider::class);
        $serializerProphecy = $this->prophesize(SerializerInterface::class);
        $serializerProphecy->deserialize(Argument::any(), Argument::any(), Argument::any())->shouldNotBeCalled();

        $data = new BusinessPartner();

        $resourceSourceProviderProphecy = $this->prophesize(ResourceSourceProviderInterface::class);
        $sourceProviderProphecy->getResourceSourceProvider(BusinessPartner::class)->shouldBeCalledOnce()->willReturn($resourceSourceProviderProphecy->reveal());
        $resourceSourceProviderProphecy->getResource()->shouldBeCalledOnce()->willReturn('Ion_business_partner_V8_vroum');
        $resourceSourceProviderProphecy->getCreateOperation()->shouldBeCalledOnce()->willReturn('create');
        $resourceSourceProviderProphecy->getDataAreaFilter()->shouldBeCalledOnce()->willReturn(['area' => 51, 'pastis' => 'cinquante et un']);
        $normalizerProphecy->normalize($data, null, ['groups' => [IONDataProcessor::ION_SYNC]])->shouldBeCalledOnce()->willReturn(['property' => 'normalizedValue']);

        $soapClient = $this->getMockBuilder(SoapClient::class)
            ->disableOriginalConstructor()
            ->getMock();
        $soapClient
            ->expects(self::once())
            ->method('__call')
            ->with('create', [[AbstractIONDataProvider::DATA_AREA => ['Ion_business_partner_V8_vroum' => ['property' => 'normalizedValue', 'area' => 51, 'pastis' => 'cinquante et un']]]])
        ;

        $IONSoapClientFactoryProphecy = $this->prophesize(SoapClientFactory::class);
        $IONSoapClientFactoryProphecy->createClient(IONSoapConfigurator::CLIENT_NAME, 'Ion_business_partner_V8_vroum')->shouldBeCalledOnce()->willReturn($soapClient);

        $IONDataProcessor = new IONDataProcessor($IONSoapClientFactoryProphecy->reveal(), $serializerProphecy->reveal(), $normalizerProphecy->reveal(), $sourceProviderProphecy->reveal());
        $IONDataProcessor->process($data, new Post());
    }

    public function testDataPersistUpdate()
    {
        $normalizerProphecy = $this->prophesize(NormalizerInterface::class);
        $sourceProviderProphecy = $this->prophesize(SourceProvider::class);
        $serializerProphecy = $this->prophesize(SerializerInterface::class);
        $serializerProphecy->deserialize(Argument::any(), Argument::any(), Argument::any())->shouldNotBeCalled();

        $data = new BusinessPartner();
        $context = ['previous_data' => 'DATA'];

        $resourceSourceProviderProphecy = $this->prophesize(ResourceSourceProviderInterface::class);
        $sourceProviderProphecy->getResourceSourceProvider(BusinessPartner::class)->shouldBeCalledOnce()->willReturn($resourceSourceProviderProphecy->reveal());
        $resourceSourceProviderProphecy->getResource()->shouldBeCalledOnce()->willReturn('Ion_business_partner_V8_vroum');
        $resourceSourceProviderProphecy->getUpdateOperation()->shouldBeCalledOnce()->willReturn('update');
        $resourceSourceProviderProphecy->getDataAreaFilter()->shouldBeCalledOnce()->willReturn([]);

        $normalizerProphecy->normalize($data, null, ['groups' => [IONDataProcessor::ION_SYNC]])->shouldBeCalledOnce()->willReturn(['property' => 'normalizedValue']);

        $soapClient = $this->getMockBuilder(SoapClient::class)
            ->disableOriginalConstructor()
            ->getMock();
        $soapClient
            ->expects(self::once())
            ->method('__call')
            ->with('update', [[AbstractIONDataProvider::DATA_AREA => ['Ion_business_partner_V8_vroum' => ['property' => 'normalizedValue']]]])
        ;

        $IONSoapClientFactoryProphecy = $this->prophesize(SoapClientFactory::class);
        $IONSoapClientFactoryProphecy->createClient(IONSoapConfigurator::CLIENT_NAME, 'Ion_business_partner_V8_vroum')->shouldBeCalledOnce()->willReturn($soapClient);

        $IONDataProcessor = new IONDataProcessor($IONSoapClientFactoryProphecy->reveal(), $serializerProphecy->reveal(), $normalizerProphecy->reveal(), $sourceProviderProphecy->reveal());
        $IONDataProcessor->process($data, new Put(), [], $context);
    }

    public function testDataPersistUpdateAndDeserializeResponse()
    {
        $normalizerProphecy = $this->prophesize(NormalizerInterface::class);
        $sourceProviderProphecy = $this->prophesize(SourceProvider::class);
        $serializerProphecy = $this->prophesize(SerializerInterface::class);
        $serializerProphecy
            ->deserialize('<xml>response</xml>', PurchaseOrder::class, 'ion_xml', Argument::any())
            ->shouldBeCalledOnce()
            ->willReturn(new PurchaseOrder())
        ;

        $data = new PurchaseOrder();
        $context = ['previous_data' => 'DATA', 'resource_class' => PurchaseOrder::class];

        $resourceSourceProviderProphecy = $this->prophesize(ResourceSourceProviderInterface::class);
        $sourceProviderProphecy->getResourceSourceProvider(PurchaseOrder::class)->shouldBeCalledOnce()->willReturn($resourceSourceProviderProphecy->reveal());
        $resourceSourceProviderProphecy->getResource()->shouldBeCalledOnce()->willReturn('Ion_business_partner_V8_vroum');
        $resourceSourceProviderProphecy->getUpdateOperation()->shouldBeCalledOnce()->willReturn('update');
        $resourceSourceProviderProphecy->getDataAreaFilter()->shouldBeCalledOnce()->willReturn([]);

        $resourceSourceProviderProphecy->deserializeAfterPersist()->shouldBeCalledOnce()->willReturn(true);

        $normalizerProphecy->normalize($data, null, ['groups' => [IONDataProcessor::ION_SYNC]])->shouldBeCalledOnce()->willReturn(['property' => 'normalizedValue']);

        $soapClient = $this->getMockBuilder(SoapClient::class)
            ->disableOriginalConstructor()
            ->getMock();
        $soapClient
            ->expects(self::once())
            ->method('__call')
            ->with('update', [[AbstractIONDataProvider::DATA_AREA => ['Ion_business_partner_V8_vroum' => ['property' => 'normalizedValue']]]])
        ;
        $soapClient
            ->expects(self::once())
            ->method('__getLastResponse')
            ->willReturn('<xml>response</xml>')
        ;

        $IONSoapClientFactoryProphecy = $this->prophesize(SoapClientFactory::class);
        $IONSoapClientFactoryProphecy->createClient(IONSoapConfigurator::CLIENT_NAME, 'Ion_business_partner_V8_vroum')->shouldBeCalledOnce()->willReturn($soapClient);

        $IONDataProcessor = new IONDataProcessor($IONSoapClientFactoryProphecy->reveal(), $serializerProphecy->reveal(), $normalizerProphecy->reveal(), $sourceProviderProphecy->reveal());
        $IONDataProcessor->process($data, new Put(normalizationContext: ['groups' => ['group1']]), [], $context);
    }

    public function testDataPersistCustomResourcePerOperation()
    {
        $normalizerProphecy = $this->prophesize(NormalizerInterface::class);
        $sourceProviderProphecy = $this->prophesize(SourceProvider::class);
        $serializerProphecy = $this->prophesize(SerializerInterface::class);
        $serializerProphecy->deserialize(Argument::any(), Argument::any(), Argument::any())->shouldNotBeCalled();

        $data = new BusinessPartner();
        $context = ['previous_data' => 'DATA'];

        $resourceSourceProviderProphecy = $this->prophesize(ResourceSourceProviderInterface::class);
        $sourceProviderProphecy->getResourceSourceProvider(BusinessPartner::class)->shouldBeCalledOnce()->willReturn($resourceSourceProviderProphecy->reveal());
        $resourceSourceProviderProphecy->getResource()->shouldBeCalledOnce()->willReturn('OriginalResource');
        $resourceSourceProviderProphecy->getUpdateOperation()->shouldBeCalledOnce()->willReturn(['customUpdate' => 'AnotherResource']);
        $resourceSourceProviderProphecy->getDataAreaFilter()->shouldBeCalledOnce()->willReturn([]);

        $normalizerProphecy->normalize($data, null, ['groups' => [IONDataProcessor::ION_SYNC]])->shouldBeCalledOnce()->willReturn(['property' => 'normalizedValue']);

        $soapClient = $this->getMockBuilder(SoapClient::class)
            ->disableOriginalConstructor()
            ->getMock();
        $soapClient
            ->expects(self::once())
            ->method('__call')
            ->with('customUpdate', [[AbstractIONDataProvider::DATA_AREA => ['AnotherResource' => ['property' => 'normalizedValue']]]])
        ;

        $IONSoapClientFactoryProphecy = $this->prophesize(SoapClientFactory::class);
        $IONSoapClientFactoryProphecy->createClient(IONSoapConfigurator::CLIENT_NAME, 'OriginalResource')->shouldNotBeCalled();
        $IONSoapClientFactoryProphecy->createClient(IONSoapConfigurator::CLIENT_NAME, 'AnotherResource')->shouldBeCalledOnce()->willReturn($soapClient);

        $IONDataProcessor = new IONDataProcessor($IONSoapClientFactoryProphecy->reveal(), $serializerProphecy->reveal(), $normalizerProphecy->reveal(), $sourceProviderProphecy->reveal());
        $IONDataProcessor->process($data, new Put(), [], $context);
    }
}
