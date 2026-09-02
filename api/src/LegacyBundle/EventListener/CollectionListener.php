<?php

declare(strict_types=1);

namespace LegacyBundle\EventListener;

use Doctrine\ORM\PersistentCollection;
use LegacyBundle\Event\FlushEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CollectionListener implements EventSubscriberInterface
{
    private readonly iterable $handlers;

    public function __construct(iterable $handlers)
    {
        $this->handlers = $handlers;
    }

    public function onFlush(FlushEvent $event)
    {
        $uow = $event->getEntityManager()->getUnitOfWork();

        /** @var PersistentCollection[] $deletions */
        $deletions = $uow->getScheduledCollectionDeletions();
        foreach ($deletions as $collection) {
            $mapping = $collection->getMapping();
            foreach ($this->handlers as $handler) {
                if ($handler->supports($collection, $mapping['targetEntity'])) {
                    $handler->handleDeletions($collection);
                }
            }
        }

        /** @var PersistentCollection[] $updates */
        $updates = $uow->getScheduledCollectionUpdates();
        foreach ($updates as $collection) {
            if (!$collection->isDirty()) {
                continue;
            }

            $mapping = $collection->getMapping();
            foreach ($this->handlers as $handler) {
                if ($handler->supports($collection, $mapping['targetEntity'])) {
                    $handler->handleUpdates($collection);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            FlushEvent::class => 'onFlush',
        ];
    }
}
