<?php

declare(strict_types=1);

namespace App\Tests\EventListener\Service;

use App\Entity\Service\TechnicianOnCall;
use App\EventListener\Service\TechnicianOnCall\TechnicianOnCallNotifierListener;
use App\Notifier\Service\TechnicianOnCall\TechnicianOnCallMailSubject;
use App\Notifier\Service\TechnicianOnCall\TechnicianOnCallNotifier;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class TechnicianOnCallNotifierListenerTest extends TestCase
{
    use ProphecyTrait;

    public function testOnSolvedDoesNotSendEmailWhenTransitioningToSolvedAndConfidential(): void
    {
        $toc = new TechnicianOnCall();
        $toc->status = TechnicianOnCall::SOLVED;
        $toc->confidential = true;

        $previousToc = new TechnicianOnCall();
        $previousToc->status = TechnicianOnCall::IN_PROGRESS;

        $notifier = $this->prophesize(TechnicianOnCallNotifier::class);
        $notifier->sendEmails([TechnicianOnCallMailSubject::TOC_SOLVED_EXTERNAL], $toc)->shouldNotBeCalled();

        $container = $this->prophesize(ContainerInterface::class);
        $container->get(TechnicianOnCallNotifier::class)->willReturn($notifier->reveal());

        $request = new Request();
        $request->attributes->set('_route', 'technician_on_call_status');
        $request->attributes->set('previous_data', $previousToc);

        $event = new ViewEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $request,
            0,
            $toc
        );

        (new TechnicianOnCallNotifierListener($container->reveal()))->onSolved($event);
    }

    public function testOnSolvedSendsExternalEmailWhenTransitioningToSolvedAndNotConfidential(): void
    {
        $toc = new TechnicianOnCall();
        $toc->status = TechnicianOnCall::SOLVED;
        $toc->confidential = false;

        $previousToc = new TechnicianOnCall();
        $previousToc->status = TechnicianOnCall::IN_PROGRESS;

        $notifier = $this->prophesize(TechnicianOnCallNotifier::class);
        $notifier->sendEmails([TechnicianOnCallMailSubject::TOC_SOLVED_EXTERNAL], $toc)->shouldBeCalledOnce();

        $container = $this->prophesize(ContainerInterface::class);
        $container->get(TechnicianOnCallNotifier::class)->willReturn($notifier->reveal());

        $request = new Request();
        $request->attributes->set('_route', 'technician_on_call_edit');
        $request->attributes->set('previous_data', $previousToc);

        $event = new ViewEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $request,
            0,
            $toc
        );

        (new TechnicianOnCallNotifierListener($container->reveal()))->onSolved($event);
    }
}
