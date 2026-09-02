<?php

declare(strict_types=1);

namespace App\Tests\ION\Filter;

use App\ION\Client\Request\LogicalExpression;
use App\ION\Client\Request\LogicalExpressionBuilderFactory;
use App\ION\Filter\IONFilter;
use App\ION\ResourceSourceProvider\ResourceSourceProviderInterface;
use App\ION\SourceProvider\SourceProvider;
use App\Tests\Mailer\DummyObject;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpFoundation\Request;

class IONFilterTest extends TestCase
{
    use ProphecyTrait;

    public function testDescriptionIsCorrectlyGenerated()
    {
        $builder = new LogicalExpressionBuilderFactory();

        $sourceProviderProphecy = $this->prophesize(SourceProvider::class);
        $sourceProviderProphecy->getResourceSourceProvider(Argument::any())->shouldNotBeCalled();

        $filter = new IONFilter($builder, $sourceProviderProphecy->reveal(), ['propertouille' => null, 'propertouze' => null]);

        $description = $filter->getDescription(DummyObject::class);

        $descriptionProperties = [
            'logicalOperator',
            'propertouille',
            'propertouille[eq]',
            'propertouille[eq][]',
            'propertouille[le]',
            'propertouille[le][]',
            'propertouille[lt]',
            'propertouille[lt][]',
            'propertouille[ge]',
            'propertouille[ge][]',
            'propertouille[gt]',
            'propertouille[gt][]',
            'propertouille[ne]',
            'propertouille[ne][]',
            'propertouille[like]',
            'propertouille[like][]',
            'propertouze',
            'propertouze[eq]',
            'propertouze[eq][]',
            'propertouze[le]',
            'propertouze[le][]',
            'propertouze[lt]',
            'propertouze[lt][]',
            'propertouze[ge]',
            'propertouze[ge][]',
            'propertouze[gt]',
            'propertouze[gt][]',
            'propertouze[ne]',
            'propertouze[ne][]',
            'propertouze[like]',
            'propertouze[like][]',
        ];

        $this->assertSame($descriptionProperties, array_keys($description));
        $this->assertSame($descriptionProperties, array_column($description, 'property'));
        $this->assertSame(['string'], array_unique(array_column($description, 'type')));
        $this->assertSame([false], array_unique(array_column($description, 'required')));
    }

    public function testNothingIsDoneWhenNoIONResourceIsSetInTheContext()
    {
        $builder = new LogicalExpressionBuilderFactory();

        $sourceProviderProphecy = $this->prophesize(SourceProvider::class);
        $sourceProviderProphecy->getResourceSourceProvider(Argument::any())->shouldNotBeCalled();

        $filter = new IONFilter($builder, $sourceProviderProphecy->reveal(), ['propertize' => null]);

        $request = new Request([], [], [], [], [], ['QUERY_STRING' => http_build_query([
            'propertize' => 'pouet',
        ])]);

        $context = [];

        $filter->apply($request, true, [], $context);

        $this->assertArrayNotHasKey('_ion_logical_expression', $context);
    }

    public function testASimpleFilterIsCorrectlyHandled()
    {
        $builder = new LogicalExpressionBuilderFactory();

        $sourceProviderProphecy = $this->prophesize(SourceProvider::class);
        $resourceSourceProvider = $this->prophesize(ResourceSourceProviderInterface::class);
        $sourceProviderProphecy->getResourceSourceProvider('IronLionZion')->shouldBeCalledOnce()->willReturn($resourceSourceProvider->reveal());
        $resourceSourceProvider->getResource()->shouldBeCalledOnce()->willReturn('IONIronLionZion');

        $filter = new IONFilter($builder, $sourceProviderProphecy->reveal(), ['propertize' => null, 'original.propertouze' => 'ion.propertouze']);

        $request = new Request([], [], ['_api_resource_class' => 'IronLionZion'], [], [], ['QUERY_STRING' => http_build_query([
            'propertize' => 'pouet',
            'original.propertouze' => 'renamed',
        ])]);

        $logicalExpression = new LogicalExpression('and');
        $context = ['_ion_logical_expression' => $logicalExpression];
        $filter->apply($request, true, [], $context);

        $this->assertSame('and', $logicalExpression->getLogicalOperator());
        $this->assertCount(2, $expressions = $logicalExpression->getComparisonExpressions());

        $this->assertSame('IONIronLionZion.propertize', $expressions[0]->attributeName);
        $this->assertSame('eq', $expressions[0]->comparisonOperator);
        $this->assertSame('pouet', $expressions[0]->instanceValue);

        $this->assertSame('IONIronLionZion.ion.propertouze', $expressions[1]->attributeName);
        $this->assertSame('eq', $expressions[1]->comparisonOperator);
        $this->assertSame('renamed', $expressions[1]->instanceValue);
    }

    public function testAComplexFilterIsCorrectlyHandledAndUndeclaredPropertiesAreIgnored()
    {
        $builder = new LogicalExpressionBuilderFactory();

        $sourceProviderProphecy = $this->prophesize(SourceProvider::class);
        $resourceSourceProvider = $this->prophesize(ResourceSourceProviderInterface::class);
        $sourceProviderProphecy->getResourceSourceProvider('IronLionZion')->shouldBeCalledOnce()->willReturn($resourceSourceProvider->reveal());
        $resourceSourceProvider->getResource()->shouldBeCalledOnce()->willReturn('IONIronLionZion');

        $filter = new IONFilter($builder, $sourceProviderProphecy->reveal(), ['propertize' => null, 'propertouffe' => null, 'propertasse' => null]);

        $request = new Request([], [], ['_api_resource_class' => 'IronLionZion'], [], [], ['QUERY_STRING' => http_build_query([
            'propertize' => ['pouet'],
            'propertouffe' => ['gt' => 'greater'],
            'propertasse' => ['lt' => 'lower'],
            'propertunknown' => ['nope'],
        ])]);

        $logicalExpression = new LogicalExpression('and');
        $context = ['_ion_logical_expression' => $logicalExpression];
        $filter->apply($request, true, [], $context);

        $this->assertSame('and', $logicalExpression->getLogicalOperator());
        $this->assertCount(3, $expressions = $logicalExpression->getComparisonExpressions());

        $this->assertSame('IONIronLionZion.propertize', $expressions[0]->attributeName);
        $this->assertSame('eq', $expressions[0]->comparisonOperator);
        $this->assertSame('pouet', $expressions[0]->instanceValue);

        $this->assertSame('IONIronLionZion.propertouffe', $expressions[1]->attributeName);
        $this->assertSame('gt', $expressions[1]->comparisonOperator);
        $this->assertSame('greater', $expressions[1]->instanceValue);

        $this->assertSame('IONIronLionZion.propertasse', $expressions[2]->attributeName);
        $this->assertSame('lt', $expressions[2]->comparisonOperator);
        $this->assertSame('lower', $expressions[2]->instanceValue);
    }

    public function testMultipleFiltersOnSamePropertyWithOrAreCorrectlyHandled()
    {
        $builder = new LogicalExpressionBuilderFactory();
        $sourceProviderProphecy = $this->prophesize(SourceProvider::class);
        $resourceSourceProvider = $this->prophesize(ResourceSourceProviderInterface::class);
        $sourceProviderProphecy->getResourceSourceProvider('IronLionZion')->shouldBeCalledOnce()->willReturn($resourceSourceProvider->reveal());
        $resourceSourceProvider->getResource()->shouldBeCalledOnce()->willReturn('IONIronLionZion');

        $filter = new IONFilter($builder, $sourceProviderProphecy->reveal(), ['propertor' => null]);

        $request = new Request([], [], ['_api_resource_class' => 'IronLionZion'], [], [], ['QUERY_STRING' => http_build_query([
            'propertor' => ['eq' => ['greater', 'lower']],
        ])]);

        $logicalExpression = new LogicalExpression('or');
        $context = ['_ion_logical_expression' => $logicalExpression, '_ion_resource' => 'IronLionZion'];
        $filter->apply($request, true, [], $context);

        $this->assertSame('or', $logicalExpression->getLogicalOperator());
        $this->assertCount(2, $expressions = $logicalExpression->getComparisonExpressions());

        $this->assertSame('IONIronLionZion.propertor', $expressions[0]->attributeName);
        $this->assertSame('eq', $expressions[0]->comparisonOperator);
        $this->assertSame('greater', $expressions[0]->instanceValue);

        $this->assertSame('IONIronLionZion.propertor', $expressions[1]->attributeName);
        $this->assertSame('eq', $expressions[1]->comparisonOperator);
        $this->assertSame('lower', $expressions[1]->instanceValue);
    }
}
