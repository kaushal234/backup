<?php

declare(strict_types=1);

namespace LegacyBundle\Doctrine\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use LegacyBundle\Doctrine\Voter\SynchronizationVoter;
use LegacyBundle\Event\FlushEvent;
use LegacyBundle\Event\PersistEvent;
use LegacyBundle\Event\PostFlushEvent;
use LegacyBundle\Event\RemoveEvent;
use LegacyBundle\Event\UpdateEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\Event;

// Priority ensures that double-write event is always trigger after all subscriber listening for doctrine events
// And that the correct object state will be reflected in the legacy database
#[AsDoctrineListener(Events::prePersist, priority: -9999, connection: 'default')]
#[AsDoctrineListener(Events::preUpdate, priority: -9999, connection: 'default')]
#[AsDoctrineListener(Events::preRemove, priority: -9999, connection: 'default')]
#[AsDoctrineListener(Events::onFlush, priority: -9999, connection: 'default')]
#[AsDoctrineListener(Events::postFlush, priority: -9999, connection: 'default')]
class PersistenceSubscriber
{
    private readonly SynchronizationVoter $voter;
    private readonly EventDispatcherInterface $eventDispatcher;

    public function __construct(SynchronizationVoter $voter, EventDispatcherInterface $eventDispatcher)
    {
        $this->voter = $voter;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function prePersist(PrePersistEventArgs $args)
    {
        $this->doDispatch(new PersistEvent($args->getObject()));
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $this->doDispatch(new UpdateEvent($args->getObject(), $args->getEntityChangeSet()));
    }

    public function preRemove(PreRemoveEventArgs $args)
    {
        $this->doDispatch(new RemoveEvent($args->getObject()));
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $this->doDispatch(new FlushEvent($args->getObjectManager()));
    }

    public function postFlush(PostFlushEventArgs $args)
    {
        $this->doDispatch(new PostFlushEvent());
    }

    private function doDispatch(Event $event)
    {
        if (!$this->voter->vote()) {
            return;
        }

        $this->eventDispatcher->dispatch($event);
    }
}
