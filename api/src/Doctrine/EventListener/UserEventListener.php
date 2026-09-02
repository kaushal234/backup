<?php

declare(strict_types=1);

namespace App\Doctrine\EventListener;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Agile\Event\GrantAgileGroupAccessEvent;
use App\Agile\Message\UpdateUserMessage;
use App\Entity\Directory\People;
use App\Entity\User;
use App\Event\UserDisabledEvent;
use App\Javelo\Event\GrantJaveloGroupAccessEvent;
use App\Javelo\Event\UpdateJaveloUserEvent;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsDoctrineListener(Events::preUpdate)]
#[AsDoctrineListener(Events::postUpdate)]
class UserEventListener
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly MessageBusInterface $messageBus,
        private readonly IriConverterInterface $iriConverter,
    ) {
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        /** @var User $user */
        $user = $args->getObject();
        if (!$user instanceof User) {
            return;
        }
        $changes = $args->getObjectManager()->getUnitOfWork()->getEntityChangeSet($user);

        if (array_keys($changes) === ['lastLogin']) {
            return;
        }

        if (isset($changes['disabled']) && (true === $changes['disabled'][1])) {
            $this->eventDispatcher->dispatch(new UserDisabledEvent($user));
        }
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $people = $args->getObject();
        if (!$people instanceof People) {
            return;
        }

        $changes = $args->getObjectManager()->getUnitOfWork()->getEntityChangeSet($people);

        if (array_keys($changes) === ['lastLogin']) {
            return;
        }

        if ((isset($changes['disabled']) && false === $changes['disabled'][1])
            || (!$people->isDisabled() && isset($changes['contractType']))
        ) {
            $this->eventDispatcher->dispatch(new GrantJaveloGroupAccessEvent($people));
            $this->eventDispatcher->dispatch(new GrantAgileGroupAccessEvent($people));
        }

        $this->eventDispatcher->dispatch(new UpdateJaveloUserEvent($people, $changes));
        $this->messageBus->dispatch(new UpdateUserMessage($this->iriConverter->getIriFromResource($people)));
    }
}
