<?php

declare(strict_types=1);

namespace App\EventListener\Sales\Order;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Directory\Location;
use App\Entity\Sales\Customer;
use App\Entity\Sales\Order;
use App\Notifier\Tasks\LegacyTaskNotifier;
use LegacyBundle\Manager\TaskManager;
use LegacyBundle\Model\Task;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class OrderListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['autofillBuyer', EventPriorities::PRE_VALIDATE],
                ['checkCRT', EventPriorities::PRE_WRITE],
            ],
        ];
    }

    public function autofillBuyer(ViewEvent $event)
    {
        $result = $event->getControllerResult();

        $request = $event->getRequest();
        if (!$result instanceof Order || !\in_array($request->getMethod(), [Request::METHOD_POST, Request::METHOD_PUT], true)) {
            return;
        }

        if (null === ($endUser = $result->getEndUser())) {
            return;
        }

        $result->setCustomerName($endUser->getName());

        if (null !== $result->getBuyer()) {
            return;
        }

        $result->setBuyer($endUser);
    }

    public function checkCRT(ViewEvent $event)
    {
        $result = $event->getControllerResult();

        if (!$result instanceof Order || !$event->getRequest()->isMethod(Request::METHOD_POST)) {
            return;
        }

        $endUser = $result->getEndUser();
        $sso = $result->getSso();
        if (null !== $endUser->getMainSalesRepresentative() && $endUser->getCrt()->isEmpty()) {
            $this->createTask($endUser, $sso);
        }

        $buyer = $result->getBuyer();
        if ($endUser !== $buyer && null !== $buyer->getMainSalesRepresentative() && $buyer->getCrt()->isEmpty()) {
            $this->createTask($buyer, $sso);
        }
    }

    public static function getSubscribedServices(): array
    {
        return [
            TaskManager::class,
            LegacyTaskNotifier::class,
            RouterInterface::class,
        ];
    }

    private function createTask(Customer $customer, Location $sso): void
    {
        $route = $this->serviceLocator->get(RouterInterface::class)->generate('customer', ['id' => $customer->getId()], Router::ABSOLUTE_URL);
        $description = \sprintf('Please manage new created eCustomer in TLD Intranet and create CRT
<a href="%s">%s</a>

CUSTOMER NAME: %s (#%s)', $route, $route, $customer->getName(), $customer->getId());

        if (null === ($salesRepresentative = $customer->getMainSalesRepresentative())) {
            return;
        }

        $asm = $salesRepresentative->asm;
        $task = (new Task())
            ->setAssignee($asm)
            ->setAssignor($asm->getSupervisor() ?? $asm)
            ->setLocation($sso)
            ->setModule('ECUST')
            ->setParentId($customer->getLegacyId())
            ->setDescription($description);

        try {
            $this->serviceLocator->get(TaskManager::class)->insert($task);
        } catch (\Exception $exception) {
            throw new \LogicException('Task not created. Reason : '.$exception->getMessage(), $exception->getCode(), $exception);
        }

        $this->serviceLocator->get(LegacyTaskNotifier::class)->sendEmail($task);
    }
}
