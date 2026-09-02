<?php

declare(strict_types=1);

namespace App\Tests\Jira\DataTransformer;

use App\Jira\DataTransformer\PropertyToCustomUrlField;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PropertyToCustomUrlFieldTest extends TestCase
{
    use ProphecyTrait;

    public function testTransformerWithNullValue()
    {
        $urlGeneratorProphecy = $this->prophesize(UrlGeneratorInterface::class);
        $urlGeneratorProphecy->generate(Argument::cetera())->shouldNotBeCalled();

        self::assertSame(['customField' => null], (new PropertyToCustomUrlField($urlGeneratorProphecy->reveal()))(null, ['field' => 'customField', 'route' => 'my_route_name']));
    }

    public function testTransformer()
    {
        $urlGeneratorProphecy = $this->prophesize(UrlGeneratorInterface::class);
        $urlGeneratorProphecy->generate('my_route_name', ['id' => 'value'])->shouldBeCalledOnce()->willReturn('my_route_name/value');

        self::assertSame(['customField' => 'my_route_name/value'], (new PropertyToCustomUrlField($urlGeneratorProphecy->reveal()))('value', ['field' => 'customField', 'route' => 'my_route_name']));
    }
}
