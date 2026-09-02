<?php

declare(strict_types=1);

namespace App\EventListener\Sales\CustomerRelationshipTeam;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Directory\People;
use App\Entity\Module\Module;
use App\Entity\Sales\CustomerRelationshipTeam;
use App\Notifier\Tasks\LegacyTaskNotifier;
use App\Repository\Directory\PeopleRepository;
use App\Repository\Module\ModuleRepository;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Manager\TaskManager;
use LegacyBundle\Model\Task;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class CustomerRelationShipTeamCreationListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    public function __construct(
        private readonly ContainerInterface $serviceLocator
    ) {
    }

    public function createTaskWhenCRTIsCreated(ViewEvent $event)
    {
        /** @var CustomerRelationshipTeam $crt */
        $crt = $event->getControllerResult();

        $request = $event->getRequest();

        if (!$crt instanceof CustomerRelationshipTeam || !$request->isMethod(Request::METHOD_POST)) {
            return;
        }

        // refresh to get the legacy ID set in the CRT
        $em = $this->serviceLocator->get(EntityManagerInterface::class);
        $em->refresh($crt);

        $route = $this->serviceLocator->get(RouterInterface::class)->generate('crt_show', ['id' => $crt->getId()], Router::ABSOLUTE_URL);

        /** @var PeopleRepository $peopleRepository */
        $peopleRepository = $em->getRepository(People::class);

        /** @var ModuleRepository $moduleRepository */
        $moduleRepository = $em->getRepository(Module::class);
        $module = $moduleRepository->findByName('CRT');

        $salesAdmins = [];

        if (null !== $crt->getErpLocation()) {
            $salesAdmins = $peopleRepository->findGroupMembers('ROLE_SAM', $crt->getErpLocation());
        }

        $assignee = $salesAdmins[0] ?? $crt->getCustomer()->getMainSalesRepresentative()->asm;

        /** @var People $assignor */
        $assignor = $this->serviceLocator->get(Security::class)->getUser();
        $task = (new Task())
            ->setAssignee($assignee)
            ->setAssignor($assignor)
            ->setModule('CRT')
            ->setParentId($crt->getLegacyId())
            ->setLocation($crt->getErpLocation() ?? $assignor->getBusinessUnit()->getLocation())
            ->setDescription(
                \sprintf("This CRT was created by %s. Please finalize CRT<a href='%s'>#%d</a> information",
                    $assignor->getDisplayName(),
                    $route,
                    $crt->getId()
                )
            )
        ;

        try {
            $taskId = $this->serviceLocator->get(TaskManager::class)->insert($task);
        } catch (\Exception $exception) {
            throw new \LogicException('Task not inserted. Reason: '.$exception->getMessage(), $exception->getCode(), $exception);
        }

        $crt->setTaskId($taskId);
        $em->persist($crt);
        $em->flush();

        $this->serviceLocator->get(LegacyTaskNotifier::class)->sendEmail($task);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [['createTaskWhenCRTIsCreated', EventPriorities::POST_WRITE - 1]],
        ];
    }

    public static function getSubscribedServices(): array
    {
        return [
            TaskManager::class,
            RouterInterface::class,
            LegacyTaskNotifier::class,
            EntityManagerInterface::class,
            Security::class,
        ];
    }
}
