<?php

declare(strict_types=1);

namespace App\EventListener\MIS\Module;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Module\Module;
use App\Manager\MIS\Module\ModuleDependencyManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

readonly class ModuleListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    public function __construct(
        private ContainerInterface $serviceLocator
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['onPreUpdate', EventPriorities::PRE_WRITE],
            ],
        ];
    }

    public function onPreUpdate(ViewEvent $event): void
    {
        $module = $event->getControllerResult();
        $request = $event->getRequest();

        if (!$module instanceof Module || Request::METHOD_PUT !== $request->getMethod()) {
            return;
        }

        $previousData = $request->attributes->get('previous_data');
        if (!$previousData instanceof Module) {
            return;
        }

        if (Module::DISABLED === $previousData->status) {
            return;
        }
        if (Module::DISABLED === $module->status) {
            $dependencyManager = $this->serviceLocator->get(ModuleDependencyManager::class);

            if (!$dependencyManager->canDisable($module)) {
                throw new UnprocessableEntityHttpException($dependencyManager->getDisableErrorMessage($module));
            }
        }
    }

    public static function getSubscribedServices(): array
    {
        return [
            EntityManagerInterface::class,
            ModuleDependencyManager::class,
        ];
    }
}
