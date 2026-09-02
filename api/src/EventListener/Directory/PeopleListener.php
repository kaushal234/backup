<?php

declare(strict_types=1);

namespace App\EventListener\Directory;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Directory\People;
use App\Manager\Directory\PeopleManager;
use App\Manager\MIS\Module\ThirdPartyManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class PeopleListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['updateClosestAirport', EventPriorities::PRE_WRITE],
                ['onMisPeopleCreate', EventPriorities::POST_WRITE],
                ['syncUpdateTasks', EventPriorities::POST_WRITE],
            ],
        ];
    }

    public function updateClosestAirport(ViewEvent $event): void
    {
        /** @var People $people */
        $people = $event->getControllerResult();
        $request = $event->getRequest();

        if (!$people instanceof People || !\in_array($request->getMethod(), [Request::METHOD_PUT, Request::METHOD_POST], true)) {
            return;
        }

        $routeName = $request->attributes->get('_route');
        $allowedRoutes = [
            '_api_/people{._format}_post',
            '_api_/people/{id}{._format}_put',
        ];

        if (!\in_array($routeName, $allowedRoutes, true)) {
            return;
        }

        // We don't want to do anything if we are disabling user
        if (Request::METHOD_PUT === $request->getMethod()) {
            /** @var People $previous */
            $previous = $request->attributes->get('previous_data');
            if (!$previous->isHidden() && !$previous->isDisabled() && $people->isHidden() && $people->isDisabled()) {
                return;
            }
        }

        $premise = $people->getPremise();

        if (null === $premise) {
            throw new BadRequestHttpException(\sprintf('Premise is mandatory: Please select a premise for %s %s.', $people->getLastname(), $people->getFirstname()));
        }

        $people->closestAirport = $premise->airport;
    }

    public function onMisPeopleCreate(ViewEvent $event): void
    {
        /** @var People $people */
        $people = $event->getControllerResult();
        $request = $event->getRequest();

        if (!$people instanceof People || Request::METHOD_POST !== $request->getMethod()) {
            return;
        }

        if (!$people->getDepartment() || 'Management of Information System' !== $people->getDepartment()->getName()) {
            return;
        }

        $this->serviceLocator->get(PeopleManager::class)->createTaskForMISUserAndNotify($people);
    }

    /**
     * Create GRANT_ACCESS or REMOVE_ACCESS update tasks of third party app.
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function syncUpdateTasks(ViewEvent $event): void
    {
        /** @var People $people */
        $people = $event->getControllerResult();
        $request = $event->getRequest();
        $previousData = $request->attributes->get('previous_data');

        /** @var ThirdPartyManager $thirdPartyManager */
        $thirdPartyManager = $this->serviceLocator->get(ThirdPartyManager::class);

        if (!$people instanceof People || Request::METHOD_PUT !== $request->getMethod()) {
            return;
        }

        // Create GRANT_ACCESS and/or REMOVE_ACCESS update tasks when updating business unit or position.
        // And disable or enable user.
        if ($people->getBusinessUnit() !== $previousData->getBusinessUnit()
        || $people->getPosition() !== $previousData->getPosition()
        || $thirdPartyManager->isEligibleForAccess($people) !== $thirdPartyManager->isEligibleForAccess($previousData)) {
            $thirdPartyManager->createGrantAccessTasksByUser($people);
            $thirdPartyManager->createRemoveAccessTasksByUser($people);
        }
    }

    public static function getSubscribedServices(): array
    {
        return [
            MessageBusInterface::class,
            IriConverterInterface::class,
            Security::class,
            EntityManagerInterface::class,
            ThirdPartyManager::class,
            PeopleManager::class,
        ];
    }
}
