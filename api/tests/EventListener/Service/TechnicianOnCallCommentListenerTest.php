<?php

declare(strict_types=1);

namespace App\Tests\EventListener\Service;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Activity\Comment;
use App\Entity\Directory\People;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Service\TechnicianOnCall;
use App\EventListener\Service\TechnicianOnCall\TechnicianOnCallCommentListener;
use App\Workflow\WorkflowStatusUpdater;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class TechnicianOnCallCommentListenerTest extends TestCase
{
    use ProphecyTrait;

    public function testOnCommentChangeStatusAppliesInProgressForPendingTechnicianOnCallAndPeopleUser(): void
    {
        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $securityProphecy = $this->prophesize(Security::class);
        $workflowStatusUpdaterProphecy = $this->prophesize(WorkflowStatusUpdater::class);

        $comment = new Comment();
        $comment->setResource('/service/technician_on_calls/1');

        $technicianOnCall = new TechnicianOnCall();
        $technicianOnCall->setStatus(TechnicianOnCall::PENDING);

        $containerProphecy->get(IriConverterInterface::class)->willReturn($iriConverterProphecy->reveal());
        $iriConverterProphecy->getResourceFromIri('/service/technician_on_calls/1')->shouldBeCalledTimes(2)->willReturn($technicianOnCall);

        $containerProphecy->get(Security::class)->shouldBeCalledOnce()->willReturn($securityProphecy->reveal());
        $securityProphecy->getUser()->shouldBeCalledOnce()->willReturn($this->prophesize(People::class)->reveal());

        $containerProphecy->get(WorkflowStatusUpdater::class)->shouldBeCalledOnce()->willReturn($workflowStatusUpdaterProphecy->reveal());
        $workflowStatusUpdaterProphecy->applyStatus($technicianOnCall, TechnicianOnCall::IN_PROGRESS)->shouldBeCalledOnce();

        $event = new ViewEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            $comment
        );

        $listener = new TechnicianOnCallCommentListener($containerProphecy->reveal());
        $listener->onCommentChangeStatus($event);
    }

    public function testOnCommentChangeStatusDoesNothingWhenUserIsNotPeople(): void
    {
        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $securityProphecy = $this->prophesize(Security::class);

        $comment = new Comment();
        $comment->setResource('/service/technician_on_calls/1');

        $technicianOnCall = new TechnicianOnCall();
        $technicianOnCall->setStatus(TechnicianOnCall::PENDING);

        $containerProphecy->get(IriConverterInterface::class)->willReturn($iriConverterProphecy->reveal());
        $iriConverterProphecy->getResourceFromIri('/service/technician_on_calls/1')->shouldBeCalledOnce()->willReturn($technicianOnCall);

        $containerProphecy->get(Security::class)->shouldBeCalledOnce()->willReturn($securityProphecy->reveal());
        $securityProphecy->getUser()->shouldBeCalledOnce()->willReturn($this->prophesize(ExtranetUser::class)->reveal());

        $containerProphecy->get(WorkflowStatusUpdater::class)->shouldNotBeCalled();

        $event = new ViewEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            $comment
        );

        $listener = new TechnicianOnCallCommentListener($containerProphecy->reveal());
        $listener->onCommentChangeStatus($event);
    }

    public function testSetsCommentAsPrivateWhenConditionsAreMet(): void
    {
        $comment = new Comment();
        $comment->metadata = ['subscription' => true];
        $comment->setResource('/service/technician_on_calls/1');

        $event = new ViewEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            $comment
        );

        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $iriConverterProphecy->getResourceFromIri($comment->getResource())->willReturn(new TechnicianOnCall());
        $containerProphecy->get(IriConverterInterface::class)->willReturn($iriConverterProphecy->reveal());

        $listener = new TechnicianOnCallCommentListener($containerProphecy->reveal());

        $listener->onCommentForSubscription($event);

        $this->assertFalse($comment->isPublic());
    }
}
