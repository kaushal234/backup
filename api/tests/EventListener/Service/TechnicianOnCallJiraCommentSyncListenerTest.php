<?php

declare(strict_types=1);

namespace App\Tests\EventListener\Service;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Activity\Comment;
use App\Entity\Service\TechnicianOnCall;
use App\EventListener\Service\TechnicianOnCall\TechnicianOnCallJiraCommentSyncListener;
use App\Message\Service\TechnicianOnCallJiraCommentSync;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class TechnicianOnCallJiraCommentSyncListenerTest extends TestCase
{
    use ProphecyTrait;

    public function testDispatchesSyncMessageWhenTocHasAnIssueKey(): void
    {
        $comment = new Comment();
        $comment->setResource('/service/technician_on_calls/1');

        $technicianOnCall = new TechnicianOnCall();
        $technicianOnCall->jiraTracteasyIssueKey = 'AIRB-777';

        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $iriConverterProphecy->getResourceFromIri('/service/technician_on_calls/1')->willReturn($technicianOnCall);
        $iriConverterProphecy->getIriFromResource($comment)->willReturn('/comments/42');

        $messageBusProphecy = $this->prophesize(MessageBusInterface::class);
        $messageBusProphecy
            ->dispatch(Argument::that(static fn ($message) => $message instanceof TechnicianOnCallJiraCommentSync
                && '/comments/42' === $message->getCommentIri()))
            ->shouldBeCalledOnce()
            ->willReturn(new Envelope(new TechnicianOnCallJiraCommentSync('/comments/42')));

        $listener = $this->createListener($iriConverterProphecy->reveal(), $messageBusProphecy->reveal());

        $listener->onNewComment($this->createViewEvent($comment, 'api_comments_post_collection'));
    }

    public function testDoesNothingWhenRouteIsNotCommentCreation(): void
    {
        $comment = new Comment();
        $comment->setResource('/service/technician_on_calls/1');

        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->get(Argument::any())->shouldNotBeCalled();

        $listener = new TechnicianOnCallJiraCommentSyncListener($containerProphecy->reveal());

        $listener->onNewComment($this->createViewEvent($comment, 'some_other_route'));
    }

    public function testDoesNothingWhenControllerResultIsNotAComment(): void
    {
        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->get(Argument::any())->shouldNotBeCalled();

        $listener = new TechnicianOnCallJiraCommentSyncListener($containerProphecy->reveal());

        $listener->onNewComment($this->createViewEvent(new \stdClass(), 'api_comments_post_collection'));
    }

    public function testDoesNothingWhenCommentResourceIsNotATechnicianOnCall(): void
    {
        $comment = new Comment();
        $comment->setResource('/sales/orders/1');

        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $iriConverterProphecy->getResourceFromIri('/sales/orders/1')->willReturn(new \stdClass());

        $messageBusProphecy = $this->prophesize(MessageBusInterface::class);
        $messageBusProphecy->dispatch(Argument::any())->shouldNotBeCalled();

        $listener = $this->createListener($iriConverterProphecy->reveal(), $messageBusProphecy->reveal());

        $listener->onNewComment($this->createViewEvent($comment, 'api_comments_post_collection'));
    }

    public function testDoesNothingWhenTechnicianOnCallHasNoJiraIssueKey(): void
    {
        $comment = new Comment();
        $comment->setResource('/service/technician_on_calls/1');

        $technicianOnCall = new TechnicianOnCall();

        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $iriConverterProphecy->getResourceFromIri('/service/technician_on_calls/1')->willReturn($technicianOnCall);

        $messageBusProphecy = $this->prophesize(MessageBusInterface::class);
        $messageBusProphecy->dispatch(Argument::any())->shouldNotBeCalled();

        $listener = $this->createListener($iriConverterProphecy->reveal(), $messageBusProphecy->reveal());

        $listener->onNewComment($this->createViewEvent($comment, 'api_comments_post_collection'));
    }

    private function createListener(IriConverterInterface $iriConverter, MessageBusInterface $messageBus): TechnicianOnCallJiraCommentSyncListener
    {
        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->get(IriConverterInterface::class)->willReturn($iriConverter);
        $containerProphecy->get(MessageBusInterface::class)->willReturn($messageBus);

        return new TechnicianOnCallJiraCommentSyncListener($containerProphecy->reveal());
    }

    private function createViewEvent(mixed $controllerResult, string $route): ViewEvent
    {
        $request = new Request();
        $request->attributes->set('_route', $route);

        return new ViewEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $controllerResult
        );
    }
}
