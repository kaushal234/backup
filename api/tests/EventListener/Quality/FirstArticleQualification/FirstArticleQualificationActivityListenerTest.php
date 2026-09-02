<?php

declare(strict_types=1);

namespace App\Tests\EventListener\Quality\FirstArticleQualification;

use App\Entity\Activity\Comment;
use App\Entity\Quality\FirstArticleQualification\FirstArticleQualification;
use App\Entity\Quality\FirstArticleQualification\PlanItem;
use App\Event\Activity\CommentCreatedEvent;
use App\EventListener\Quality\FirstArticleQualification\FirstArticleQualificationActivityListener;
use App\Notifier\Quality\FirstArticleQualification\FirstArticleQualificationNotifier;
use App\Workflow\WorkflowStatusUpdater;
use Cake\Chronos\Chronos;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Workflow\Exception\LogicException;

class FirstArticleQualificationActivityListenerTest extends KernelTestCase
{
    use ProphecyTrait;

    protected function setUp(): void
    {
        self::bootKernel();
    }

    /**
     * @dataProvider getSetInProgressStatusProvider
     */
    public function testActivityListenerSetInProgressStatus(Request $request, $faq, ?\DateTime $dateTest = null)
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $workflowStatusUpdaterProphecy = $this->prophesize(WorkflowStatusUpdater::class);
        $notifierProphecy = $this->prophesize(FirstArticleQualificationNotifier::class);
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        if (null !== $dateTest) {
            Chronos::setTestNow(Chronos::parse($dateTest));
            $serviceLocatorProphecy->get(WorkflowStatusUpdater::class)->shouldBeCalledTimes(1)->willReturn($workflowStatusUpdaterProphecy->reveal());
            $serviceLocatorProphecy->get(FirstArticleQualificationNotifier::class)->shouldBeCalledTimes(1)->willReturn($notifierProphecy->reveal());
            $notifierProphecy->sendPlanCompleted($faq)->shouldBeCalledOnce();
            $workflowStatusUpdaterProphecy->applyStatus($faq, FirstArticleQualification::IN_PROGRESS)->shouldBeCalledTimes(1);
            $serviceLocatorProphecy->get(EntityManagerInterface::class)->shouldBeCalledTimes(1)->willReturn($entityManagerProphecy->reveal());
            $entityManagerProphecy->persist($faq)->shouldBeCalledTimes(1);
            $entityManagerProphecy->flush()->shouldBeCalledTimes(1);
        } else {
            $serviceLocatorProphecy->get(WorkflowStatusUpdater::class)->shouldNotbeCalled();
            $workflowStatusUpdaterProphecy->applyStatus(Argument::any(), Argument::any())->shouldNotBeCalled();
            $serviceLocatorProphecy->get(EntityManagerInterface::class)->shouldNotbeCalled();
            $entityManagerProphecy->persist($faq)->shouldNotbeCalled();
            $entityManagerProphecy->flush()->shouldNotBeCalled();
        }

        $listener = new FirstArticleQualificationActivityListener($serviceLocatorProphecy->reveal());
        $listener->setInProgressStatus(new ViewEvent(static::$kernel, $request, HttpKernelInterface::MAIN_REQUEST, $faq));
    }

    public function getSetInProgressStatusProvider()
    {
        $dateTimeTest = new \DateTime(Chronos::now()->toDateTimeString());

        $faq = new \stdClass();
        yield 'Other classes should not be impacted' => [new Request(), $faq];

        $request = new Request();
        $request->setMethod(Request::METHOD_DELETE);

        $faq = new FirstArticleQualification();
        yield 'FAQ should be only impacted by POST and PUT' => [$request, $faq];

        $request = new Request();

        $request->setMethod(Request::METHOD_POST);

        $faqProphecy = $this->prophesize(FirstArticleQualification::class);
        $faqProphecy->getPlanDefinitionCompletedAt()->willReturn(null)->shouldBeCalledTimes(1);
        $faqProphecy->getPlan()->willReturn(new ArrayCollection([new PlanItem()]))->shouldBeCalledTimes(1);
        $faqProphecy->setStatus(FirstArticleQualification::IN_PROGRESS)->shouldBeCalledTimes(5);
        $faqProphecy->setPlanDefinitionCompletedAt($dateTimeTest)->shouldBeCalledTimes(1);
        yield 'FAQ with plan should be IN_PROGRESS at POST' => [$request, $faq = $faqProphecy->reveal(), $dateTimeTest];

        $request = new Request();
        $request->setMethod(Request::METHOD_PUT);
        yield 'FAQ with plan should be IN_PROGRESS at PUT' => [$request, $faq, $dateTimeTest];

        $faqProphecy = $this->prophesize(FirstArticleQualification::class);
        $faqProphecy->getPlanDefinitionCompletedAt()->willReturn($dateTimeTest)->shouldBeCalledTimes(1);
        $faqProphecy->getPlan()->shouldNotBeCalled();
        $faqProphecy->setStatus(Argument::any())->shouldNotBeCalled();
        $faqProphecy->setPlanDefinitionCompletedAt($dateTimeTest)->shouldNotBeCalled();
        yield 'FAQ with plan definition completed date should not be impacted ' => [$request, $faq, $dateTimeTest];
    }

    public function testNothingHappensWithACommentOnAnotherResource()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);

        $event = new CommentCreatedEvent(new Comment(), new \stdClass(), true);

        $workflowStatusUpdaterProphecy = $this->prophesize(WorkflowStatusUpdater::class);
        $serviceLocatorProphecy->get(WorkflowStatusUpdater::class)->shouldNotbeCalled();
        $workflowStatusUpdaterProphecy->applyStatus(Argument::any())->shouldNotBeCalled();

        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $serviceLocatorProphecy->get(EntityManagerInterface::class)->shouldNotbeCalled();
        $entityManagerProphecy->persist(Argument::any())->shouldNotBeCalled();
        $entityManagerProphecy->flush()->shouldNotBeCalled();

        $firstArticleQualificationNotifierProphecy = $this->prophesize(FirstArticleQualificationNotifier::class);
        $serviceLocatorProphecy->get(FirstArticleQualificationNotifier::class)->shouldNotbeCalled();
        $firstArticleQualificationNotifierProphecy->sendComment(Argument::any())->shouldNotBeCalled();

        $listener = new FirstArticleQualificationActivityListener($serviceLocatorProphecy->reveal());
        $listener->setInProgressStatusOnCommentPosted($event);
    }

    public function testSetInProgressStatusOnCommentPosted()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $faq = new FirstArticleQualification();
        $event = new CommentCreatedEvent((new Comment())->setMessage('test'), $faq, true);

        $workflowStatusUpdaterProphecy = $this->prophesize(WorkflowStatusUpdater::class);
        $serviceLocatorProphecy->get(WorkflowStatusUpdater::class)->shouldBeCalledTimes(1)->willReturn($workflowStatusUpdaterProphecy->reveal());
        $workflowStatusUpdaterProphecy->applyStatus($faq, FirstArticleQualification::IN_PROGRESS)->shouldBeCalledTimes(1);

        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $serviceLocatorProphecy->get(EntityManagerInterface::class)->shouldBeCalledTimes(1)->willReturn($entityManagerProphecy->reveal());
        $entityManagerProphecy->persist($faq)->shouldBeCalledTimes(1);
        $entityManagerProphecy->flush()->shouldBeCalledTimes(1);

        $firstArticleQualificationNotifierProphecy = $this->prophesize(FirstArticleQualificationNotifier::class);
        $serviceLocatorProphecy->get(FirstArticleQualificationNotifier::class)->shouldBeCalledTimes(1)->willReturn($firstArticleQualificationNotifierProphecy->reveal());
        $firstArticleQualificationNotifierProphecy->sendComment($faq, 'test')->shouldBeCalledTimes(1);

        $listener = new FirstArticleQualificationActivityListener($serviceLocatorProphecy->reveal());
        $listener->setInProgressStatusOnCommentPosted($event);
    }

    public function testSetInProgressStatusOnCommentPostedWorkflowThrowsException()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $faq = new FirstArticleQualification();
        $event = new CommentCreatedEvent((new Comment())->setMessage('test'), $faq, true);

        $workflowStatusUpdaterProphecy = $this->prophesize(WorkflowStatusUpdater::class);
        $serviceLocatorProphecy->get(WorkflowStatusUpdater::class)->shouldBeCalledTimes(1)->willReturn($workflowStatusUpdaterProphecy->reveal());
        $workflowStatusUpdaterProphecy->applyStatus($faq, FirstArticleQualification::IN_PROGRESS)->willThrow(new LogicException());

        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $serviceLocatorProphecy->get(EntityManagerInterface::class)->shouldNotBeCalled();
        $entityManagerProphecy->persist(Argument::any())->shouldNotBeCalled();
        $entityManagerProphecy->flush()->shouldNotBeCalled();

        $firstArticleQualificationNotifierProphecy = $this->prophesize(FirstArticleQualificationNotifier::class);
        $serviceLocatorProphecy->get(FirstArticleQualificationNotifier::class)->shouldBeCalledTimes(1)->willReturn($firstArticleQualificationNotifierProphecy->reveal());
        $firstArticleQualificationNotifierProphecy->sendComment($faq, 'test')->shouldBeCalledTimes(1);

        $listener = new FirstArticleQualificationActivityListener($serviceLocatorProphecy->reveal());

        $listener->setInProgressStatusOnCommentPosted($event);
    }
}
