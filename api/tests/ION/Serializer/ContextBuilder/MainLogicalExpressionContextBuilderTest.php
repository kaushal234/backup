<?php

declare(strict_types=1);

namespace App\Tests\ION\Serializer\ContextBuilder;

use ApiPlatform\State\SerializerContextBuilderInterface;
use App\Entity\Directory\People;
use App\ION\Client\Request\LogicalExpression;
use App\ION\Client\Request\LogicalExpressionBuilderFactory;
use App\ION\Resources\MasterData\BusinessPartners\BusinessPartner;
use App\ION\Serializer\ContextBuilder\MainLogicalExpressionContextBuilder;
use App\ION\SourceProvider\SourceProvider;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

class MainLogicalExpressionContextBuilderTest extends KernelTestCase
{
    use ProphecyTrait;

    private SourceProvider $sourceProvider;
    private LogicalExpressionBuilderFactory $logicalExpressionBuilder;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->sourceProvider = static::getContainer()->get(SourceProvider::class);
        $this->logicalExpressionBuilder = static::getContainer()->get(LogicalExpressionBuilderFactory::class);
    }

    public function testContextIsTheSameForNonIONResources()
    {
        $request = new Request([], [], ['_api_resource_class' => People::class]);

        $context = [];
        $contextBuilderProphecy = $this->prophesize(SerializerContextBuilderInterface::class);
        $contextBuilderProphecy->createFromRequest($request, true, null)->shouldBeCalledOnce()->willReturn($context);
        $contextBuilder = new MainLogicalExpressionContextBuilder($contextBuilderProphecy->reveal(), $this->sourceProvider, $this->logicalExpressionBuilder);

        $newContext = $contextBuilder->createFromRequest($request, true);

        $this->assertSame($context, $newContext);
    }

    public function testContextIsPopulatedWithDefaultOperatorAnd()
    {
        $request = new Request([], [], ['_api_resource_class' => BusinessPartner::class]);

        $contextBuilderProphecy = $this->prophesize(SerializerContextBuilderInterface::class);
        $contextBuilderProphecy->createFromRequest($request, true, null)->shouldBeCalledOnce()->willReturn([]);
        $contextBuilder = new MainLogicalExpressionContextBuilder($contextBuilderProphecy->reveal(), $this->sourceProvider, $this->logicalExpressionBuilder);

        $newContext = $contextBuilder->createFromRequest($request, true);

        $this->assertArrayHasKey('_ion_logical_expression', $newContext);
        $this->assertInstanceOf(LogicalExpression::class, $newContext['_ion_logical_expression']);
        /** @var LogicalExpression $logicalExpression */
        $logicalExpression = $newContext['_ion_logical_expression'];
        $this->assertSame('and', $logicalExpression->getLogicalOperator());
    }

    public function testContextIsPopulatedWithOr()
    {
        $request = new Request($query = ['logicalOperator' => 'or'], [], ['_api_resource_class' => BusinessPartner::class], [], [], ['QUERY_STRING' => http_build_query($query)]);

        $contextBuilderProphecy = $this->prophesize(SerializerContextBuilderInterface::class);
        $contextBuilderProphecy->createFromRequest($request, true, null)->shouldBeCalledOnce()->willReturn([]);
        $contextBuilder = new MainLogicalExpressionContextBuilder($contextBuilderProphecy->reveal(), $this->sourceProvider, $this->logicalExpressionBuilder);

        $newContext = $contextBuilder->createFromRequest($request, true);

        $this->assertArrayHasKey('_ion_logical_expression', $newContext);
        $this->assertInstanceOf(LogicalExpression::class, $newContext['_ion_logical_expression']);
        /** @var LogicalExpression $logicalExpression */
        $logicalExpression = $newContext['_ion_logical_expression'];
        $this->assertSame('or', $logicalExpression->getLogicalOperator());
    }
}
