<?php

declare(strict_types=1);

namespace App\EventListener\Sales\Demo;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Activity\Comment;
use App\Entity\Directory\People;
use App\Entity\EmissionRating;
use App\Entity\Sales\Demo;
use App\Manager\Manufacturing\IntelligentBatterySystemManager;
use App\Notifier\Tasks\SequenceNotifier;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Manager\SequenceManager;
use LegacyBundle\Manager\TaskCommentsManager;
use LegacyBundle\Model\Sequence;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class DemoCreationListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    /**
     * @var string
     */
    final public const SEQUENCE_TEMPLATE = 'sales.demo.approval';

    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function createSequenceWhenDemoIsCreated(ViewEvent $event)
    {
        $demo = $event->getControllerResult();
        $request = $event->getRequest();

        if (!$demo instanceof Demo || !$request->isMethod(Request::METHOD_POST)) {
            return;
        }

        $route = $this->serviceLocator->get(RouterInterface::class)->generate('demos', ['id' => $demo->getId()], Router::ABSOLUTE_URL);

        $assignee = $demo->getAsm()->getSupervisor();

        $sequence = new Sequence();
        $sequence
            ->setTemplateName(self::SEQUENCE_TEMPLATE)
            ->setCloseParams([
                'assignor' => $demo->getAsm()->getDisplayName(),
                'assignee' => $assignee->getDisplayName(),
                'seqTpl' => 75,
                'module' => 'SEQ',
                'status' => $demo->getStatus(),
                'id' => $demo->getId(),
                'customer' => $demo->getCustomer()->getName(),
                'product' => $demo->getProduct()->getName(),
                'asm' => $demo->getAsm()->getDisplayName(),
                'country' => $demo->getCountry()->getName(),
                'sso' => $demo->getSso()->getErp(),
            ])
            ->setAssignee($assignee)
            ->setAssignor($demo->getAsm())
            ->setParentId($demo->getId())
            ->setLocation($demo->getSso())
            ->setDescription(
                \sprintf("Demo <a href='%s'>#%d</a> needs to be validated:\n
                                    SSO: %s,
                                    Factory: %s,
                                    Customer: %s,
                                    Product: %s,
                                    Country: %s,
                                    Expected starting date: %s,
                                    ASM: %s %s,
                                    AST: %s %s,
                                    PSM: %s %s,
                                    Comment: %s,
                                    Expected Start Date: %s,
                                    Expected End Date: %s.",
                    $route,
                    $demo->getId(),
                    $demo->getSso()->getName(),
                    $demo->getFactory()->getName(),
                    $demo->getCustomer()->getName(),
                    $demo->getProduct()->getName(),
                    $demo->getCountry()->getName(),
                    null !== $demo->getExpectedStartDate() ? $demo->getExpectedStartDate()->format('Y-m-d') : '',
                    $demo->getAsm()->getLastname(),
                    $demo->getAsm()->getFirstname(),
                    $demo->getAst()->getLastname(),
                    $demo->getAst()->getFirstname(),
                    $demo->getPsm()->getLastname(),
                    $demo->getPsm()->getFirstname(),
                    $demo->getComment(),
                    null !== $demo->getExpectedStartDate() ? $demo->getExpectedStartDate()->format('Y-m-d') : '',
                    null !== $demo->getExpectedEndDate() ? $demo->getExpectedEndDate()->format('Y-m-d') : ''
                )
            )
        ;

        $sequence->addCc($demo->getAsm());

        $user = $this->serviceLocator->get(Security::class)->getUser();
        if ($user instanceof People && $user !== $demo->getAsm()) {
            $sequence->addCc($user);
        }

        $em = $this->serviceLocator->get(EntityManagerInterface::class);
        try {
            $sequenceId = $this->serviceLocator->get(SequenceManager::class)->insert($sequence);
            $demo->setSequenceId($sequenceId);
            $em->persist($demo);
            $em->flush();
        } catch (\Exception $exception) {
            $em->remove($demo);
            $em->flush();
            throw new \LogicException('Sequence not inserted. Reason: '.$exception->getMessage(), $exception->getCode(), $exception);
        }

        $commentRepository = $em->getRepository(Comment::class);
        /** @var Comment[] $comments */
        $comments = $commentRepository->findBy(['resource' => \sprintf('/sales/demos/%s', $demo->getId())]);

        $this->serviceLocator->get(TaskCommentsManager::class)->insertComment($sequence, $comments[0]->getMessage());
        $this->serviceLocator->get(SequenceNotifier::class)->sendEmail($sequence);

        if (null !== $demo->getEmissionRating() && \in_array($demo->getEmissionRating()->getName(), EmissionRating::INTELLIGENT_BATTERY_SYSTEMS, true)) {
            $this->serviceLocator->get(IntelligentBatterySystemManager::class)->createTasks($demo->getAsm(), $demo->getSso(), $demo->getFactory(), 'DEMO', $demo->getId());
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['createSequenceWhenDemoIsCreated', EventPriorities::POST_WRITE - 1],
            ],
        ];
    }

    public static function getSubscribedServices(): array
    {
        return [
            SequenceManager::class,
            RouterInterface::class,
            SequenceNotifier::class,
            EntityManagerInterface::class,
            TaskCommentsManager::class,
            Security::class,
            IntelligentBatterySystemManager::class,
        ];
    }
}
