<?php

declare(strict_types=1);

namespace App\Tests\ION\Filter;

use App\ION\Filter\DataAreaFilter;
use App\ION\Filter\SelectionFilter;
use App\ION\ResourceSourceProvider\ResourceSourceProviderInterface;
use App\ION\SourceProvider\SourceProvider;
use App\Tests\Mailer\DummyObject;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\TypeInfo\TypeIdentifier;

class SelectionFilterTest extends TestCase
{
    use ProphecyTrait;

    public function testDescriptionIsCorrectlyGenerated()
    {
        $sourceProviderProphecy = $this->prophesize(SourceProvider::class);
        $sourceProviderProphecy->getResourceSourceProvider(Argument::any())->shouldNotBeCalled();

        $filter = new SelectionFilter($sourceProviderProphecy->reveal());

        $description = $filter->getDescription(DummyObject::class);

        $this->assertArrayHasKey(SelectionFilter::FILTER_PROPERTY, $description);
        $this->assertSame(SelectionFilter::FILTER_PROPERTY, $description[SelectionFilter::FILTER_PROPERTY]['property']);
        $this->assertSame(TypeIdentifier::ARRAY->value, $description[SelectionFilter::FILTER_PROPERTY]['type']);
        $this->assertFalse($description[SelectionFilter::FILTER_PROPERTY]['required']);
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

    public function testASimpleFilterIsCorrectlyHandled()
    {
        $sourceProviderProphecy = $this->prophesize(SourceProvider::class);
        $resourceSourceProvider = $this->prophesize(ResourceSourceProviderInterface::class);
        $sourceProviderProphecy->getResourceSourceProvider('IronLionZion')->shouldBeCalledOnce()->willReturn($resourceSourceProvider->reveal());
        $resourceSourceProvider->getResource()->shouldBeCalledOnce()->willReturn('IONIronLionZion');

        $filter = new SelectionFilter($sourceProviderProphecy->reveal());

        $request = new Request();
        $request->query->set('selection', ['pouet', 'pouette']);
        $request->attributes->set('_api_resource_class', 'IronLionZion');

        $context = [];

        $filter->apply($request, true, [], $context);

        $this->assertArrayHasKey(SelectionFilter::CONTEXT_SELECTION_AREA_KEY, $context);
        $this->assertContains('pouet', $context[SelectionFilter::CONTEXT_SELECTION_AREA_KEY]);
        $this->assertContains('pouette', $context[SelectionFilter::CONTEXT_SELECTION_AREA_KEY]);
    }
}
