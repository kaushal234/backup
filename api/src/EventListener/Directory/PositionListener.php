<?php

declare(strict_types=1);

namespace App\EventListener\Directory;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Directory\Position;
use App\Message\Directory\GroupPositionUpdate;
use App\Repository\Directory\PositionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class PositionListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    public Collection $messages;
    private ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        $this->messages = new ArrayCollection();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['onGroupPositionUpdatePreWrite', EventPriorities::PRE_WRITE],
                ['onGroupPositionUpdatePostWrite', EventPriorities::POST_WRITE],
            ],
        ];
    }

    public function onGroupPositionUpdatePreWrite(ViewEvent $event): void
    {
        /** @var Position $position */
        $position = $event->getControllerResult();

        if (!$position instanceof Position || !$event->getRequest()->isMethod(Request::METHOD_PUT)) {
            return;
        }

        $entityManager = $this->serviceLocator->get(EntityManagerInterface::class);
        /** @var PositionRepository $positionRepository */
        $positionRepository = $entityManager->getRepository(Position::class);

        $iriConverter = $this->serviceLocator->get(IriConverterInterface::class);

        foreach ($position->getDivisionGroups() as $divisionGroup) {
            $groupPosition = [];
            foreach ($divisionGroup->getGroups() as $group) {
                $groupPosition[] = (string) $group->getId();
            }
            $previousGroups = array_column($positionRepository->getPositionGroupsForDivision($position, $divisionGroup->division), 'id');

            $delete = array_diff($previousGroups, $groupPosition);
            $add = array_diff($groupPosition, $previousGroups);

            if (!empty($add) || !empty($delete)) {
                $this->messages->add(new GroupPositionUpdate(
                    $iriConverter->getIriFromResource($position),
                    $iriConverter->getIriFromResource($divisionGroup->division),
                    $add,
                    $delete
                ));
            }
        }
    }

    public function onGroupPositionUpdatePostWrite(ViewEvent $event): void
    {
        if ($this->messages->isEmpty() || !$event->getControllerResult() instanceof Position) {
            return;
        }

        foreach ($this->messages as $message) {
            $this->serviceLocator->get(MessageBusInterface::class)->dispatch($message);
        }
        $this->messages = new ArrayCollection();
    }

    public static function getSubscribedServices(): array
    {
        return [
            MessageBusInterface::class,
            IriConverterInterface::class,
            EntityManagerInterface::class,
        ];
    }
}
