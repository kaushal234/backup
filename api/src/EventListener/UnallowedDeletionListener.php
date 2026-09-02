<?php

declare(strict_types=1);

namespace App\EventListener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\DeletionVoter\DeletionVoterInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * This listener asserts an entity is deletable.
 */
class UnallowedDeletionListener implements EventSubscriberInterface
{
    private iterable $voters = [];

    /**
     * @param iterable|DeletionVoterInterface[] $voters
     */
    public function __construct(iterable $voters = [])
    {
        $this->voters = $voters;
    }

    public function onPreDelete(ViewEvent $event)
    {
        $entity = $event->getControllerResult();
        if (!$event->getRequest()->isMethod(Request::METHOD_DELETE)) {
            return;
        }
        foreach ($this->voters as $voter) {
            if (!$voter->supports($entity)) {
                continue;
            }

            if (null !== $reason = $voter->abstainToDeletion($entity)) {
                throw new UnprocessableEntityHttpException((string) $reason);
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['onPreDelete', EventPriorities::PRE_WRITE + 1],
        ];
    }
}
