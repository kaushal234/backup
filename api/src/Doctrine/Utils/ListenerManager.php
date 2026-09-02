<?php

declare(strict_types=1);

namespace App\Doctrine\Utils;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;

class ListenerManager
{
    public function removeListener(EntityManagerInterface $entityManager, array $removedListeners): void
    {
        $eventManager = $entityManager->getEventManager();
        /** @var array $eventManagerListeners */
        $eventManagerListeners = $eventManager->getAllListeners();
        foreach ($eventManagerListeners as $listeners) {
            foreach ($listeners as $listener) {
                if (\in_array($listener::class, $removedListeners, true)) {
                    $events = $this->getListenerEvents($listener);
                    $eventManager->removeEventListener($events, $listener);
                }
            }
        }
    }

    public function getListenerEvents($listener): array
    {
        if (method_exists($listener, 'getSubscribedEvents')) {
            return $listener->getSubscribedEvents();
        }

        $events = [];
        $reflectionClass = new \ReflectionClass($listener);
        $attributes = $reflectionClass->getAttributes();

        foreach ($attributes as $attribute) {
            if (!($doctrineListener = $attribute->newInstance()) instanceof AsDoctrineListener) {
                continue;
            }

            $events[] = $doctrineListener->event;
        }

        return $events;
    }
}
