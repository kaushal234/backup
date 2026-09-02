<?php

declare(strict_types=1);

namespace App\Tests\ION\DataProvider;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Validator\ValidatorInterface;
use App\Client\Exception\SoapException;
use App\ION\Client\Request\LogicalExpressionBuilder;
use App\ION\Client\Request\LogicalExpressionBuilderFactory;
use App\ION\Client\Request\SelectionBuilder;
use App\ION\DataProvider\AbstractIONDataProvider;
use App\ION\DataProvider\IONCollectionDataProvider;
use App\ION\Event\IONPreNormalizeEvent;
use App\ION\ResourceSourceProvider\ResourceSourceProviderInterface;
use App\ION\SourceProvider\SourceProvider;
use Doctrine\Persistence\ManagerRegistry;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class IONCollectionDataProviderTest extends AbstractIONDataProviderTest
{
    public function testThatASoapFaultInGetCollectionIsTransformedIntoAnSoapException()
    {
        $this->expectException(SoapException::class);
        $this->expectExceptionMessage('Something went wrong while requesting external API');

        $sourceProviderProphecy = $this->prophesize(SourceProvider::class);
        $resourceSourceProviderProphecy = $this->prophesize(ResourceSourceProviderInterface::class);
        $sourceProviderProphecy->getResourceSourceProvider(\stdClass::class)->shouldBeCalledOnce()->willReturn($resourceSourceProviderProphecy->reveal());
        $resourceSourceProviderProphecy->getResource()->shouldBeCalledOnce()->willReturn('IonLionZion');
        $resourceSourceProviderProphecy->getDataAreaFilter()->shouldBeCalledOnce()->willReturn([]);
        $resourceSourceProviderProphecy->getCollectionReadOperation()->shouldBeCalledOnce()->willReturn('marley');

        $ionSoapClientFactoryProphecy = $this->getSoapClientFactoryProphecy(
            'IonLionZion',
            'marley',
            [AbstractIONDataProvider::CONTROL_AREA => ['maxNumberOfObjects' => 42, 'Filter' => ['LogicalExpression' => null]]],
            new \SoapFault('lyoko', 'error', '', '')
        );

        $logicalExpressionBuilder = new LogicalExpressionBuilder(\stdClass::class);
        $logicalExpressionBuilderFactoryProphecy = $this->prophesize(LogicalExpressionBuilderFactory::class);
        $logicalExpressionBuilderFactoryProphecy->create('IonLionZion')->shouldBeCalledOnce()->willReturn($logicalExpressionBuilder);
        $eventDispatcherProphecy = $this->prophesize(EventDispatcherInterface::class);
        $eventDispatcherProphecy->dispatch(Argument::type(IONPreNormalizeEvent::class))->shouldBeCalledOnce();
        $provider = new IONCollectionDataProvider(
            $sourceProviderProphecy->reveal(),
            $this->prophesize(SerializerInterface::class)->reveal(),
            $this->prophesize(NormalizerInterface::class)->reveal(),
            $ionSoapClientFactoryProphecy->reveal(),
            $this->prophesize(ValidatorInterface::class)->reveal(),
            $this->prophesize(ManagerRegistry::class)->reveal(),
            $logicalExpressionBuilderFactoryProphecy->reveal(),
            new SelectionBuilder(),
            $eventDispatcherProphecy->reveal(),
            42
        );

        $provider->provide(new GetCollection(class: \stdClass::class));
    }

    public function testADataAreaAttributeIsAlwaysPassedToION()
    {
        $sourceProviderProphecy = $this->prophesize(SourceProvider::class);
        $resourceSourceProviderProphecy = $this->prophesize(ResourceSourceProviderInterface::class);
        $sourceProviderProphecy->getResourceSourceProvider(\stdClass::class)->shouldBeCalledOnce()->willReturn($resourceSourceProviderProphecy->reveal());
        $resourceSourceProviderProphecy->getResource()->shouldBeCalledOnce()->willReturn('LaTribu');
        $resourceSourceProviderProphecy->getCollectionReadOperation()->shouldBeCalledOnce()->willReturn('Dana');
        $resourceSourceProviderProphecy->getDataAreaFilter()->shouldBeCalledOnce()->willReturn(['area' => 51, 'pastis' => 'cinquante et un']);

        $ionSoapClientFactoryProphecy = $this->getSoapClientFactoryProphecy(
            'LaTribu',
            'Dana',
            [
                AbstractIONDataProvider::DATA_AREA => ['LaTribu' => ['area' => 51, 'pastis' => 'cinquante et un']],
                AbstractIONDataProvider::CONTROL_AREA => ['maxNumberOfObjects' => 18, 'Filter' => ['LogicalExpression' => []]],
            ]
        );

        $normalizerProphecy = $this->prophesize(NormalizerInterface::class);
        $normalizerProphecy->normalize(Argument::any())->willReturn([]);

        $logicalExpressionBuilder = new LogicalExpressionBuilder('LaTribu');
        $logicalExpressionBuilderFactoryProphecy = $this->prophesize(LogicalExpressionBuilderFactory::class);
        $logicalExpressionBuilderFactoryProphecy->create('LaTribu')->shouldBeCalledOnce()->willReturn($logicalExpressionBuilder);

        $eventDispatcherProphecy = $this->prophesize(EventDispatcherInterface::class);
        $eventDispatcherProphecy->dispatch(Argument::type(IONPreNormalizeEvent::class))->shouldBeCalledOnce();

        $serializerProphecy = $this->prophesize(SerializerInterface::class);
        $serializerProphecy->deserialize(Argument::cetera())->shouldBeCalledOnce()->willReturn([]);

        $provider = new IONCollectionDataProvider(
            $sourceProviderProphecy->reveal(),
            $serializerProphecy->reveal(),
            $normalizerProphecy->reveal(),
            $ionSoapClientFactoryProphecy->reveal(),
            $this->prophesize(ValidatorInterface::class)->reveal(),
            $this->prophesize(ManagerRegistry::class)->reveal(),
            $logicalExpressionBuilderFactoryProphecy->reveal(),
            new SelectionBuilder(),
            $eventDispatcherProphecy->reveal(),
            18
        );

        $provider->provide(new GetCollection(class: \stdClass::class, normalizationContext: []));
    }

    public function testASelectionNodeIsPassedToION()
    {
        $sourceProviderProphecy = $this->prophesize(SourceProvider::class);
        $resourceSourceProviderProphecy = $this->prophesize(ResourceSourceProviderInterface::class);
        $sourceProviderProphecy->getResourceSourceProvider(\stdClass::class)->shouldBeCalledOnce()->willReturn($resourceSourceProviderProphecy->reveal());
        $resourceSourceProviderProphecy->getResource()->shouldBeCalledOnce()->willReturn('LaTribu');
        $resourceSourceProviderProphecy->getCollectionReadOperation()->shouldBeCalledOnce()->willReturn('Dana');
        $resourceSourceProviderProphecy->getDataAreaFilter()->shouldBeCalledOnce()->willReturn(['area' => 51, 'pastis' => 'cinquante et un']);

        $ionSoapClientFactoryProphecy = $this->getSoapClientFactoryProphecy(
            'LaTribu',
            'Dana',
            [
                AbstractIONDataProvider::DATA_AREA => ['LaTribu' => ['area' => 51, 'pastis' => 'cinquante et un']],
                AbstractIONDataProvider::CONTROL_AREA => [
                    'maxNumberOfObjects' => 18,
                    'Filter' => ['LogicalExpression' => []],
                    'Selection' => [
                        'selectionAttribute' => [
                            'LaTribu.description',
                            'LaTribu.itemCode',
                        ],
                    ],
                ],
            ]
        );

        $normalizerProphecy = $this->prophesize(NormalizerInterface::class);
        $normalizerProphecy->normalize(Argument::any())->willReturn([]);

        $logicalExpressionBuilder = new LogicalExpressionBuilder('LaTribu');
        $logicalExpressionBuilderFactoryProphecy = $this->prophesize(LogicalExpressionBuilderFactory::class);
        $logicalExpressionBuilderFactoryProphecy->create('LaTribu')->shouldBeCalledOnce()->willReturn($logicalExpressionBuilder);

        $eventDispatcherProphecy = $this->prophesize(EventDispatcherInterface::class);
        $eventDispatcherProphecy->dispatch(Argument::type(IONPreNormalizeEvent::class))->shouldBeCalledOnce();

        $serializerProphecy = $this->prophesize(SerializerInterface::class);
        $serializerProphecy->deserialize(Argument::cetera())->shouldBeCalledOnce()->willReturn([]);

        $provider = new IONCollectionDataProvider(
            $sourceProviderProphecy->reveal(),
            $serializerProphecy->reveal(),
            $normalizerProphecy->reveal(),
            $ionSoapClientFactoryProphecy->reveal(),
            $this->prophesize(ValidatorInterface::class)->reveal(),
            $this->prophesize(ManagerRegistry::class)->reveal(),
            $logicalExpressionBuilderFactoryProphecy->reveal(),
            new SelectionBuilder(),
            $eventDispatcherProphecy->reveal(),
            18
        );

        $provider->provide(new GetCollection(class: \stdClass::class, normalizationContext: []), [], [
            '_ion_selection_area' => [
                'description',
                'itemCode',
            ],
        ]);
    }
}
