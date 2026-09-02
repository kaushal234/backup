<?php

declare(strict_types=1);

namespace App\Tests\EventListener\Sales\SalesForecast;

use App\Entity\Sales\ForecastClosure;
use App\Entity\Sales\SalesForecast;
use App\EventListener\Sales\SalesForecast\SalesForecastReviewListener;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\Marking;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\WorkflowInterface;

class SalesForecastReviewListenerTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @dataProvider eventProvider
     */
    public function testGuardBlockWhenLastClosureIsNotOK(GuardEvent $event, bool $blocked, bool $auth = false)
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $securityProphecy = $this->prophesize(Security::class);
        $serviceLocatorProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());
        $securityProphecy->isGranted(Argument::any())->shouldBeCalledTimes($auth ? 1 : 2)->willReturn($auth);

        $listener = new SalesForecastReviewListener($serviceLocatorProphecy->reveal());

        $listener->guardReview($event);

        self::assertSame($event->isBlocked(), $blocked);
    }

    public function eventProvider()
    {
        yield 'No Forecast Closure' => [
            new GuardEvent(
                $this->getSalesForecast(SalesForecast::ORDERED),
                $this->prophesize(Marking::class)->reveal(),
                new Transition('to_ordered', [], [SalesForecast::ORDERED]),
                $this->prophesize(WorkflowInterface::class)->reveal()
            ),
            true,
            false,
        ];

        yield 'No Forecast Closure auth override' => [
            new GuardEvent(
                $this->getSalesForecast(SalesForecast::ORDERED),
                $this->prophesize(Marking::class)->reveal(),
                new Transition('to_ordered', [], [SalesForecast::ORDERED]),
                $this->prophesize(WorkflowInterface::class)->reveal()
            ),
            false,
            true,
        ];

        yield 'ORDERED with a right Forecast Closure' => [
            new GuardEvent(
                $this->getSalesForecast(SalesForecast::ORDERED, SalesForecast::ORDERED),
                $this->prophesize(Marking::class)->reveal(),
                new Transition('to_ordered', [], [SalesForecast::ORDERED]),
                $this->prophesize(WorkflowInterface::class)->reveal()
            ),
            false,
            false,
        ];

        yield 'ORDERED with a wrong Forecast Closure' => [
            new GuardEvent(
                $this->getSalesForecast(SalesForecast::LOST, SalesForecast::LOST),
                $this->prophesize(Marking::class)->reveal(),
                new Transition('to_ordered', [], [SalesForecast::ORDERED]),
                $this->prophesize(WorkflowInterface::class)->reveal()
            ),
            true,
            false,
        ];

        yield 'LOST with a right Forecast Closure' => [
            new GuardEvent(
                $this->getSalesForecast(SalesForecast::LOST, SalesForecast::LOST),
                $this->prophesize(Marking::class)->reveal(),
                new Transition('to_lost', [], [SalesForecast::LOST]),
                $this->prophesize(WorkflowInterface::class)->reveal()
            ),
            false,
            false,
        ];

        yield 'LOST with a wrong Forecast Closure' => [
            new GuardEvent(
                $this->getSalesForecast(SalesForecast::ORDERED, SalesForecast::ORDERED),
                $this->prophesize(Marking::class)->reveal(),
                new Transition('to_lost', [], [SalesForecast::LOST]),
                $this->prophesize(WorkflowInterface::class)->reveal()
            ),
            true,
            false,
        ];

        yield 'PARTIAL with 2 right Forecast Closures' => [
            new GuardEvent(
                $this->getSalesForecast(SalesForecast::PARTIAL, ForecastClosure::PARTIAL_LOST, ForecastClosure::PARTIAL_ORDERED),
                $this->prophesize(Marking::class)->reveal(),
                new Transition('to_partial', [], [SalesForecast::PARTIAL]),
                $this->prophesize(WorkflowInterface::class)->reveal()
            ),
            false,
            false,
        ];

        yield 'PARTIAL with just one Forecast Closure' => [
            new GuardEvent(
                $this->getSalesForecast(SalesForecast::PARTIAL, ForecastClosure::PARTIAL_ORDERED),
                $this->prophesize(Marking::class)->reveal(),
                new Transition('to_partial', [], [SalesForecast::PARTIAL]),
                $this->prophesize(WorkflowInterface::class)->reveal()
            ),
            true,
            false,
        ];
    }

    private function getSalesForecast(string $salesForecastStatus, ?string $forecastClosureStatus = null, ?string $forecastClosureStatus2 = null)
    {
        $salesForecast = new SalesForecast();
        $salesForecast->setStatus($salesForecastStatus);

        if (null !== $forecastClosureStatus) {
            $salesForecast->addForecastClosure((new ForecastClosure())->setStatus($forecastClosureStatus));
        }

        if (null !== $forecastClosureStatus2) {
            $salesForecast->addForecastClosure((new ForecastClosure())->setStatus($forecastClosureStatus2));
        }

        return $salesForecast;
    }
}
