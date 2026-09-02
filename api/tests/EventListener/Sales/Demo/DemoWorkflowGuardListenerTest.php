<?php

declare(strict_types=1);

namespace App\Tests\EventListener\Sales\Demo;

use App\Entity\Directory\People;
use App\Entity\Sales\Demo;
use App\EventListener\Sales\Demo\DemoWorkflowGuardListener;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\Marking;
use Symfony\Component\Workflow\Transition;

class DemoWorkflowGuardListenerTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @dataProvider dataProvider1
     */
    public function testSuperuserIsAllowedToSetStatusToActive(bool $isMoo, bool $isAuthorized, string $transitionName)
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy->getUser()->shouldBeCalledTimes(1)->willReturn(new People());
        $securityProphecy->isGranted('MOO_DEMO')->shouldBeCalledTimes(1)->willReturn($isMoo);

        $demo = new Demo();

        $securityProphecy->isGranted('FEATURE_DEMO_ADMIN', $demo)->shouldBeCalledTimes((int) !$isMoo)->willReturn($isAuthorized);
        $serviceLocatorProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());

        $listener = new DemoWorkflowGuardListener($serviceLocatorProphecy->reveal());
        $event = new GuardEvent($demo, new Marking(), new Transition($transitionName, [], []));
        $listener->guardDemoSecuredTransitions($event);

        if (!$isMoo && !$isAuthorized) {
            self::assertTrue($event->isBlocked());
        } else {
            self::assertFalse($event->isBlocked());
        }
    }

    public function dataProvider1(): ?\Generator
    {
        yield 'the MOO is the current user' => [true, false,  'to_est_fini_entre_nous'];
        yield 'the current user is an authorized person and transition is to_active' => [false, true, 'to_active'];
        yield 'the current user is an authorized person and transition is not to_active' => [false, true, 'to_est_fini_entre_nous'];
        yield 'the current user is a basic user' => [false, false, 'to_est_fini_entre_nous'];
    }
}
