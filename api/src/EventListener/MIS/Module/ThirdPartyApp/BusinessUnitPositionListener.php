<?php

declare(strict_types=1);

namespace App\EventListener\MIS\Module\ThirdPartyApp;

use App\Entity\Module\ThirdPartyApp\BusinessUnitPosition;
use App\Manager\MIS\Module\ThirdPartyManager;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Events;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

#[AsEntityListener(event: Events::postPersist, method: 'syncUpdateTasksOnCreate', entity: BusinessUnitPosition::class)]
#[AsEntityListener(event: Events::postRemove, method: 'syncUpdateTasksOnRemove', entity: BusinessUnitPosition::class)]
class BusinessUnitPositionListener implements ServiceSubscriberInterface
{
    public function __construct(
        private readonly ContainerInterface $serviceLocator
    ) {
    }

    public static function getSubscribedServices(): array
    {
        return [
            ThirdPartyManager::class,
        ];
    }

    public function syncUpdateTasksOnCreate(BusinessUnitPosition $businessUnitPosition, PostPersistEventArgs $event): void
    {
        $this->serviceLocator->get(ThirdPartyManager::class)->createGrantAccessTasksByBusinessUnitPosition($businessUnitPosition);
    }

    public function syncUpdateTasksOnRemove(BusinessUnitPosition $businessUnitPosition, PostRemoveEventArgs $event): void
    {
        $this->serviceLocator->get(ThirdPartyManager::class)->createRemoveAccessTasksByBusinessUnitPosition($businessUnitPosition);
    }
}
