<?php

declare(strict_types=1);

namespace App\Tests\ION\Filter;

use App\ION\Filter\DataAreaFilter;
use App\ION\ResourceSourceProvider\ResourceSourceProviderInterface;
use App\ION\SourceProvider\SourceProvider;
use App\Tests\Mailer\DummyObject;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpFoundation\Request;

class DataAreaFilterTest extends TestCase
{
    use ProphecyTrait;

    public function testDescriptionIsCorrectlyGenerated()
    {
        $sourceProviderProphecy = $this->prophesize(SourceProvider::class);
        $sourceProviderProphecy->getResourceSourceProvider(Argument::any())->shouldNotBeCalled();

        $filter = new DataAreaFilter($sourceProviderProphecy->reveal(), ['propertouille' => null, 'propertouze' => null]);

        $description = $filter->getDescription(DummyObject::class);

        $descriptionProperties = [
            'propertouille',
            'propertouze',
        ];

        $this->assertSame($descriptionProperties, array_keys($description));
        $this->assertSame($descriptionProperties, array_column($description, 'property'));
        $this->assertSame(['string'], array_unique(array_column($description, 'type')));
        $this->assertSame([false], array_unique(array_column($description, 'required')));
    }

    public function testNothingIsDoneWhenNoResourceClassIsSetInTheContext()
    {
        $sourceProviderProphecy = $this->prophesize(SourceProvider::class);
        $sourceProviderProphecy->getResourceSourceProvider(Argument::any())->shouldNotBeCalled();

        $filter = new DataAreaFilter($sourceProviderProphecy->reveal(), ['propertize' => null]);

        $request = new Request();
        $request->query->set('propertize', 'pouet');

        $context = [];

        $filter->apply($request, true, [], $context);

        $this->assertArrayNotHasKey('_api_resource_class', $context);
    }

    public function testNothingIsDoneWhenNoIONResourceIsSetInThenTheMetadata()
    {
        $sourceProviderProphecy = $this->prophesize(SourceProvider::class);
        $resourceSourceProvider = $this->prophesize(ResourceSourceProviderInterface::class);

        $sourceProviderProphecy->getResourceSourceProvider('IronLionZion')->shouldBeCalledOnce()->willReturn($resourceSourceProvider->reveal());

        $filter = new DataAreaFilter($sourceProviderProphecy->reveal(), ['propertize' => null]);

        $request = new Request();
        $request->query->set('propertize', 'pouet');
        $request->attributes->set('_api_resource_class', 'IronLionZion');

        $context = [];

        $filter->apply($request, true, [], $context);

        $this->assertArrayNotHasKey('_ion_resource', $context);
    }

    public function testNothingIsDoneWhenTheRequestNotContainAuthorizedProperties()
    {
        $sourceProviderProphecy = $this->prophesize(SourceProvider::class);
        $resourceSourceProvider = $this->prophesize(ResourceSourceProviderInterface::class);
        $sourceProviderProphecy->getResourceSourceProvider('IronLionZion')->shouldBeCalledOnce()->willReturn($resourceSourceProvider->reveal());
        $resourceSourceProvider->getResource()->shouldBeCalledOnce()->willReturn('IONIronLionZion');

        $filter = new DataAreaFilter($sourceProviderProphecy->reveal(), ['iron' => null, 'lion' => null, 'zion' => null]);

        $request = new Request();
        $request->query->set('propertize', 'propertouze');
        $request->attributes->set('_api_resource_class', 'IronLionZion');

        $context = [];

        $filter->apply($request, true, [], $context);

        $this->assertArrayNotHasKey('_ion_data_area', $context);
    }

    public function testASimpleFilterIsCorrectlyHandled()
    {
        $sourceProviderProphecy = $this->prophesize(SourceProvider::class);
        $resourceSourceProvider = $this->prophesize(ResourceSourceProviderInterface::class);
        $sourceProviderProphecy->getResourceSourceProvider('IronLionZion')->shouldBeCalledOnce()->willReturn($resourceSourceProvider->reveal());
        $resourceSourceProvider->getResource()->shouldBeCalledOnce()->willReturn('IONIronLionZion');

        $filter = new DataAreaFilter($sourceProviderProphecy->reveal(), ['propertize' => null]);

        $request = new Request();
        $request->query->set('propertize', 'pouet');
        $request->attributes->set('_api_resource_class', 'IronLionZion');

        $context = [];

        $filter->apply($request, true, [], $context);

        $this->assertArrayHasKey('_ion_data_area', $context);
        $this->assertArrayHasKey('propertize', $context['_ion_data_area']);
        $this->assertArrayNotHasKey('pouet', $context['_ion_data_area']);
    }
}
