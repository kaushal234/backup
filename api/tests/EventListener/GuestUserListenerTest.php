<?php

declare(strict_types=1);

namespace App\Tests\EventListener;

use App\Entity\Directory\Position;
use App\Entity\MIS\GuestUser\GuestUser;
use App\EventListener\GuestUserListener;
use App\Repository\Directory\PositionRepository;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class GuestUserListenerTest extends KernelTestCase
{
    use ProphecyTrait;

    public function testPreValidateDoesNothingWhenControllerResultIsNotGuestUser(): void
    {
        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->get(PositionRepository::class)->shouldNotBeCalled();

        $listener = new GuestUserListener($containerProphecy->reveal());

        $event = $this->createViewEvent(new \stdClass());

        $listener->preValidate($event);

        $this->addToAssertionCount(1);
    }

    public function testPreValidateSetsGuestPositionWhenControllerResultIsGuestUser(): void
    {
        $position = new Position();

        $positionRepositoryProphecy = $this->prophesize(PositionRepository::class);
        $positionRepositoryProphecy
            ->findOneBy(['code' => Position::GUEST])
            ->willReturn($position)
            ->shouldBeCalledOnce();

        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy
            ->get(PositionRepository::class)
            ->willReturn($positionRepositoryProphecy->reveal())
            ->shouldBeCalledOnce();

        $listener = new GuestUserListener($containerProphecy->reveal());

        $guestUser = new GuestUser();
        $event = $this->createViewEvent($guestUser);

        $listener->preValidate($event);

        $this->assertSame($position, $guestUser->getPosition());
    }

    private function createViewEvent(mixed $controllerResult): ViewEvent
    {
        $kernelProphecy = $this->prophesize(HttpKernelInterface::class);

        return new ViewEvent(
            $kernelProphecy->reveal(),
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            $controllerResult
        );
    }
}
