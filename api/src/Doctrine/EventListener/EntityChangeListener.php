<?php

declare(strict_types=1);

namespace App\Doctrine\EventListener;

use App\Doctrine\Change;
use App\Doctrine\ChangesBag;
use App\Event\EntityChangeEvent;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\UnitOfWork;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Trigger an exploitable event when an entity changes.
 *  - Trigger the INSERT event AFTER the entity is flushed to have an ID
 *  - Trigger the DELETE event BEFORE the entity is flushed to keep the ID
 *  - Flush the Additional changes fired by the listeners
 *      => if each listener trigger a flush.
 *
 * Class EntityChangeListener
 */
#[AsDoctrineListener(Events::onFlush)]
#[AsDoctrineListener(Events::postFlush)]
class EntityChangeListener
{
    private readonly EventDispatcherInterface $eventDispatcher;

    private readonly ChangesBag $changesBag;

    private bool $changeTriggered = false;

    /**
     * EntityChangeListener constructor.
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, ChangesBag $changesBag)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->changesBag = $changesBag;
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $uow = $args->getObjectManager()->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            $this->scheduleChange($this->createChangeObject($uow, Change::ACTION_CREATE, $entity));
        }
        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            $this->scheduleChange($this->createChangeObject($uow, Change::ACTION_UPDATE, $entity));
        }
        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            $this->triggerChange($this->createChangeObject($uow, Change::ACTION_DELETE, clone $entity));
            $uow->computeChangeSets();
        }
    }

    public function postFlush(PostFlushEventArgs $args)
    {
        while (\count($this->changesBag) > 0) {
            $this->triggerChange($this->changesBag->pop());
        }

        if ($this->changeTriggered) {
            $this->changeTriggered = false;
            $args->getObjectManager()->flush();
        }
    }

    /**
     * Create a object Change for the given $entity and $action.
     *
     * @return Change
     */
    private function createChangeObject(UnitOfWork $uow, $action, $entity)
    {
        $changeSet = $uow->getEntityChangeSet($entity);

        if (Change::ACTION_UPDATE === $action) {
            foreach ($uow->getScheduledCollectionUpdates() as $collection) {
                if ($collection->getOwner() !== $entity) {
                    continue;
                }
                $deleteDiff = $collection->getDeleteDiff();
                $insertDiff = $collection->getInsertDiff();
                if ([] === $deleteDiff && [] === $insertDiff) {
                    continue;
                }
                $changeSet[$collection->getMapping()->fieldName] = [$deleteDiff, $insertDiff];
            }
        }

        return (new Change())
            ->setAction($action)
            ->setEntity($entity)
            ->setChangeSet($changeSet)
        ;
    }

    /**
     * Schedule a change, which will be triggered after the flush.
     */
    private function scheduleChange(Change $change)
    {
        $this->changesBag->push($change);
    }

    /**
     * Trigger a Change event.
     */
    private function triggerChange(Change $change)
    {
        $this->changeTriggered = true;
        $this->eventDispatcher->dispatch(new EntityChangeEvent($change));
    }
}
