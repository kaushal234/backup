<?php

declare(strict_types=1);

namespace App\Javelo\EventListener;

use App\Doctrine\Change;
use App\Entity\Activity\Log;
use App\Javelo\Event\LogUserClientEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class LogUserClientListener
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(LogUserClientEvent $event): void
    {
        $javeloUser = $event->getJaveloUser();
        $log = new Log();
        $log->setResource('/people/'.$javeloUser->intranetId);
        $log->discriminator = 'javelo';
        $log->setAction($javeloUser->id ? Change::ACTION_UPDATE : Change::ACTION_CREATE);
        $log->setChangeSet($event->getChanges());
        if (null !== $event->getPoster()) {
            $log->setUser($event->getPoster());
        }

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}
