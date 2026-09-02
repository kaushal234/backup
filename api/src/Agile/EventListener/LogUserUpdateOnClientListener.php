<?php

declare(strict_types=1);

namespace App\Agile\EventListener;

use App\Agile\Event\LogUserUpdateOnClientEvent;
use App\Doctrine\Change;
use App\Entity\Activity\Log;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class LogUserUpdateOnClientListener
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(LogUserUpdateOnClientEvent $event): void
    {
        $agileUser = $event->getUser();
        $log = new Log();
        $log->setResource('/people/'.$agileUser->peopleId);
        $log->discriminator = 'agile';
        $log->setAction($agileUser->isNew ? Change::ACTION_CREATE : Change::ACTION_UPDATE);
        $log->setChangeSet($event->getChanges());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}
