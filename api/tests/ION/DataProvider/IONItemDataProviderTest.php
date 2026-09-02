<?php

declare(strict_types=1);

namespace App\Tests\ION\DataProvider;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Validator\ValidatorInterface;
use App\Client\Exception\SoapException;
use App\ION\Client\Request\LogicalExpressionBuilderFactory;
use App\ION\Client\Request\SelectionBuilder;
use App\ION\DataProvider\AbstractIONDataProvider;
use App\ION\DataProvider\IONItemDataProvider;
use App\ION\ResourceSourceProvider\ResourceSourceProviderInterface;
use App\ION\SourceProvider\SourceProvider;
use Doctrine\Persistence\ManagerRegistry;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class IONItemDataProviderTest extends AbstractIONDataProviderTest
{
    public function testThatASoapFaultInGetItemIsTransformedIntoAnSoapException()
    {
        $this->expectException(SoapException::class);
        $this->expectExceptionMessage('Something went wrong while requesting external API');

        $sourceProviderProphecy = $this->prophesize(SourceProvider::class);
        $resourceSourceProviderInterface = $this->prophesize(ResourceSourceProviderInterface::class);

        $sourceProviderProphecy->getResourceSourceProvider(\stdClass::class)->shouldBeCalledOnce()->willReturn($resourceSourceProviderInterface->reveal());
        $resourceSourceProviderInterface->getResource()->shouldBeCalledOnce()->willReturn('IonLionZion');
        $resourceSourceProviderInterface->getDataAreaFilter()->shouldBeCalledOnce()->willReturn([]);
        $resourceSourceProviderInterface->getItemReadOperation()->shouldBeCalledOnce()->willReturn('bob');

        $ionSoapClientFactoryProphecy = $this->getSoapClientFactoryProphecy(
            'IonLionZion',
            'bob',
            [
                AbstractIONDataProvider::DATA_AREA => ['IonLionZion' => ['id' => 'heidi']],
            ],
            new \SoapFault('lyoko', 'error', '', '')
        );

        $normalizerProphecy = $this->prophesize(NormalizerInterface::class);
        $normalizerProphecy->normalize(Argument::any())->willReturn([]);

        $provider = new IONItemDataProvider(
            $sourceProviderProphecy->reveal(),
            $this->prophesize(SerializerInterface::class)->reveal(),
            $normalizerProphecy->reveal(),
            $ionSoapClientFactoryProphecy->reveal(),
            $this->prophesize(ValidatorInterface::class)->reveal(),
            $this->prophesize(ManagerRegistry::class)->reveal(),
            $this->prophesize(LogicalExpressionBuilderFactory::class)->reveal(),
            new SelectionBuilder(),
            $this->prophesize(EventDispatcherInterface::class)->reveal(),
            42
        );

        $provider->provide(new Get(class: \stdClass::class), ['id' => 'heidi']);
    }

    public function testADataAreaAttributeIsAlwaysPassedToION()
    {
        $sourceProviderProphecy = $this->prophesize(SourceProvider::class);
        $resourceSourceProviderInterface = $this->prophesize(ResourceSourceProviderInterface::class);

        $sourceProviderProphecy->getResourceSourceProvider(\stdClass::class)->shouldBeCalledOnce()->willReturn($resourceSourceProviderInterface->reveal());
        $resourceSourceProviderInterface->getResource()->shouldBeCalledOnce()->willReturn('LaTribu');
        $resourceSourceProviderInterface->getItemReadOperation()->shouldBeCalledOnce()->willReturn('Dana');
        $resourceSourceProviderInterface->getDataAreaFilter()->shouldBeCalledOnce()->willReturn(['area' => 51, 'pastis' => 'cinquante et un']);

        $ionSoapClientFactoryProphecy = $this->getSoapClientFactoryProphecy(
            'LaTribu',
            'Dana',
            [
                AbstractIONDataProvider::DATA_AREA => ['LaTribu' => ['id' => 'manau', 'area' => 51, 'pastis' => 'cinquante et un']],
            ]
        );

        $normalizerProphecy = $this->prophesize(NormalizerInterface::class);
        $normalizerProphecy->normalize(Argument::any())->willReturn([]);

        $provider = new IONItemDataProvider(
            $sourceProviderProphecy->reveal(),
            $this->prophesize(SerializerInterface::class)->reveal(),
            $normalizerProphecy->reveal(),
            $ionSoapClientFactoryProphecy->reveal(),
            $this->prophesize(ValidatorInterface::class)->reveal(),
            $this->prophesize(ManagerRegistry::class)->reveal(),
            $this->prophesize(LogicalExpressionBuilderFactory::class)->reveal(),
            new SelectionBuilder(),
            $this->prophesize(EventDispatcherInterface::class)->reveal(),
            42
        );

        $provider->provide(new Get(class: (\stdClass::class), normalizationContext: []), ['id' => 'manau']);
    }

    public function testASelectionNodeIsPassedToION()
    {
        $sourceProviderProphecy = $this->prophesize(SourceProvider::class);
        $resourceSourceProviderInterface = $this->prophesize(ResourceSourceProviderInterface::class);

        $sourceProviderProphecy->getResourceSourceProvider(\stdClass::class)->shouldBeCalledOnce()->willReturn($resourceSourceProviderInterface->reveal());
        $resourceSourceProviderInterface->getResource()->shouldBeCalledOnce()->willReturn('LaTribu');
        $resourceSourceProviderInterface->getItemReadOperation()->shouldBeCalledOnce()->willReturn('Dana');
        $resourceSourceProviderInterface->getDataAreaFilter()->shouldBeCalledOnce()->willReturn(['area' => 51, 'pastis' => 'cinquante et un']);

        $ionSoapClientFactoryProphecy = $this->getSoapClientFactoryProphecy(
            'LaTribu',
            'Dana',
            [
                AbstractIONDataProvider::DATA_AREA => ['LaTribu' => ['area' => 51, 'pastis' => 'cinquante et un']],
                AbstractIONDataProvider::CONTROL_AREA => [
                    'Selection' => [
                        'selectionAttribute' => [
                            'LaTribu.description',
                            'LaTribu.itemCode',
                            'LaTribu.unitOfWork',
                        ],
                    ],
                ],
            ]
        );

        $normalizerProphecy = $this->prophesize(NormalizerInterface::class);
        $normalizerProphecy->normalize(Argument::any())->willReturn([]);

        $provider = new IONItemDataProvider(
            $sourceProviderProphecy->reveal(),
            $this->prophesize(SerializerInterface::class)->reveal(),
            $normalizerProphecy->reveal(),
            $ionSoapClientFactoryProphecy->reveal(),
            $this->prophesize(ValidatorInterface::class)->reveal(),
            $this->prophesize(ManagerRegistry::class)->reveal(),
            $this->prophesize(LogicalExpressionBuilderFactory::class)->reveal(),
            new SelectionBuilder(),
            $this->prophesize(EventDispatcherInterface::class)->reveal(),
            1
        );

        $provider->provide(new Get(class: \stdClass::class, normalizationContext: []), [], [
            '_ion_selection_area' => [
                'description',
                'itemCode',
                'unitOfWork',
            ],
        ]);
    }

    public function testIonIdentifierCanBeChanged()
    {
        $sourceProviderProphecy = $this->prophesize(SourceProvider::class);
        $resourceSourceProviderInterface = $this->prophesize(ResourceSourceProviderInterface::class);

        $sourceProviderProphecy->getResourceSourceProvider(DummyChangedIdentifier::class)->shouldBeCalledOnce()->willReturn($resourceSourceProviderInterface->reveal());
        $resourceSourceProviderInterface->getResource()->shouldBeCalledOnce()->willReturn('LaTribu');
        $resourceSourceProviderInterface->getItemReadOperation()->shouldBeCalledOnce()->willReturn('Dana');
        $resourceSourceProviderInterface->getDataAreaFilter()->shouldBeCalledOnce()->willReturn(['area' => 51, 'pastis' => 'cinquante et un']);

        $ionSoapClientFactoryProphecy = $this->getSoapClientFactoryProphecy(
            'LaTribu',
            'Dana',
            [
                AbstractIONDataProvider::DATA_AREA => ['LaTribu' => ['id' => 'changed identifier', 'area' => 51, 'pastis' => 'cinquante et un']],
            ]
        );

        $normalizerProphecy = $this->prophesize(NormalizerInterface::class);
        $normalizerProphecy->normalize(Argument::any())->willReturn([]);

        $provider = new IONItemDataProvider(
            $sourceProviderProphecy->reveal(),
            $this->prophesize(SerializerInterface::class)->reveal(),
            $normalizerProphecy->reveal(),
            $ionSoapClientFactoryProphecy->reveal(),
            $this->prophesize(ValidatorInterface::class)->reveal(),
            $this->prophesize(ManagerRegistry::class)->reveal(),
            $this->prophesize(LogicalExpressionBuilderFactory::class)->reveal(),
            new SelectionBuilder(),
            $this->prophesize(EventDispatcherInterface::class)->reveal(),
            42
        );

        $provider->provide(new Get(class: DummyChangedIdentifier::class, normalizationContext: []), ['id' => 'manau']);
    }
}
