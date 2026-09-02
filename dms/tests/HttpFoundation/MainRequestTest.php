<?php

declare(strict_types=1);

namespace App\Tests\HttpFoundation;

use App\HttpFoundation\MainRequest;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophet;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;

class MainRequestTest extends TestCase
{
    private $prophet;

    public function testGetRequest()
    {
        $requestProphecy = $this->prophet->prophesize(Request::class);
        $requestProphecy->query = new InputBag(['m' => []]);

        $mainRequest = new MainRequest($requestProphecy->reveal());
        $request = $mainRequest->getRequest();

        self::assertInstanceOf(Request::class, $request);
    }

    /**
     * @dataProvider parametersProvider
     */
    public function testTransformAsRouteWithParameters(array $parameters, string $expected)
    {
        $requestProphecy = $this->prophet->prophesize(Request::class);
        $requestProphecy->query = new InputBag(['m' => $parameters]);

        $mainRequest = new MainRequest($requestProphecy->reveal());
        $route = $mainRequest->getCurrentRoute();

        $this->assertSame($expected, $route);
    }

    public static function parametersProvider()
    {
        yield 'One parameter' => [[0 => 'first'], '/first'];
        yield 'two parameters' => [[0 => 'first', 1 => 'second'], '/first/second'];
        yield 'two parameters with order' => [[1 => 'second', 0 => 'first'], '/first/second'];
        yield 'One parameters with letter' => [['a' => 'A'], '/A'];
        yield 'two parameters with letter order' => [['b' => 'second', 'a' => 'first'], '/first/second'];
    }

    protected function setUp(): void
    {
        $this->prophet = new Prophet();
    }

    protected function tearDown(): void
    {
        $this->prophet->checkPredictions();
    }
}