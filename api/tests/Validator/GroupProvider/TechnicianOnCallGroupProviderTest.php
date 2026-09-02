<?php

declare(strict_types=1);

namespace App\Tests\Validator\GroupProvider;

use App\Validator\GroupProvider\TechnicianOnCallGroupProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class TechnicianOnCallGroupProviderTest extends TestCase
{
    use ProphecyTrait;

    public function testWithoutRequest()
    {
        $requestStackProphecy = $this->prophesize(RequestStack::class);
        $requestStackProphecy->getCurrentRequest()->willReturn(null);

        $groupProvider = new TechnicianOnCallGroupProvider($requestStackProphecy->reveal());
        $groups = $groupProvider->getGroups(new \stdClass());

        self::assertSame(['TechnicianOnCall', 'contact_require', 'contacts'], $groups);
    }

    public function testNotOnDUplicationRoute()
    {
        $request = $this->prophesize(Request::class);
        $request->get('_route')->shouldBeCalledOnce()->willReturn('foobar');

        $requestStackProphecy = $this->prophesize(RequestStack::class);
        $requestStackProphecy->getCurrentRequest()->willReturn($request->reveal());

        $groupProvider = new TechnicianOnCallGroupProvider($requestStackProphecy->reveal());
        $groups = $groupProvider->getGroups(new \stdClass());

        self::assertSame(['TechnicianOnCall', 'contact_require', 'contacts'], $groups);
    }

    public function testOnDuplicationRoute()
    {
        $request = $this->prophesize(Request::class);
        $request->get('_route')->shouldBeCalledOnce()->willReturn('technician_on_call_duplicate');

        $requestStackProphecy = $this->prophesize(RequestStack::class);
        $requestStackProphecy->getCurrentRequest()->willReturn($request->reveal());

        $groupProvider = new TechnicianOnCallGroupProvider($requestStackProphecy->reveal());
        $groups = $groupProvider->getGroups(new \stdClass());

        self::assertSame(['TechnicianOnCall', 'contacts'], $groups);
    }
}
