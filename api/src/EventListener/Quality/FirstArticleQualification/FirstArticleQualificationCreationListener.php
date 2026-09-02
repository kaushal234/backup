<?php

declare(strict_types=1);

namespace App\EventListener\Quality\FirstArticleQualification;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Quality\FirstArticleQualification\FirstArticleQualification;
use App\Notifier\Quality\FirstArticleQualification\FirstArticleQualificationNotifier;
use App\Repository\Directory\PeopleRepository;
use App\Repository\Quality\FirstArticleQualification\PlanItemTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class FirstArticleQualificationCreationListener implements EventSubscriberInterface, ServiceSubscriberInterface
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
                ['onFirstArticleQualificationCreation', EventPriorities::POST_VALIDATE + 1], // +1 because must be execute before onFAQEdition
                ['notifyCreation', EventPriorities::POST_WRITE],
            ],
        ];
    }

    public function onFirstArticleQualificationCreation(ViewEvent $event): void
    {
        $result = $event->getControllerResult();

        if (!$result instanceof FirstArticleQualification || !$event->getRequest()->isMethod(Request::METHOD_POST)) {
            return;
        }

        $this->addDefaultMembers($result);
    }

    public function notifyCreation(ViewEvent $event)
    {
        $result = $event->getControllerResult();

        if (!$result instanceof FirstArticleQualification || !$event->getRequest()->isMethod(Request::METHOD_POST)) {
            return;
        }

        $this->serviceLocator->get(FirstArticleQualificationNotifier::class)->sendCreation($result);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        return [
            PlanItemTypeRepository::class,
            PeopleRepository::class,
            FirstArticleQualificationNotifier::class,
        ];
    }

    private function addDefaultMembers(FirstArticleQualification $firstArticleQualification): void
    {
        $peopleRepository = $this->serviceLocator->get(PeopleRepository::class);
        $defaultMembers = [...$firstArticleQualification->getMembers()->toArray(), ...$peopleRepository->findGroupMembers('ROLE_MLM', $location = $firstArticleQualification->getLocation()), ...$peopleRepository->findGroupMembers('ROLE_EM', $location), ...$peopleRepository->findGroupMembers('ROLE_QAM', $location)];

        $firstArticleQualification->setMembers(new ArrayCollection(array_unique($defaultMembers)));
    }
}
