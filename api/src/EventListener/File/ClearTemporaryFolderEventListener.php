<?php

declare(strict_types=1);

namespace App\EventListener\File;

use App\FileSystem\TemporaryStorageManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ClearTemporaryFolderEventListener implements EventSubscriberInterface
{
    private readonly TemporaryStorageManager $temporaryStorageManager;

    public function __construct(TemporaryStorageManager $temporaryStorageManager)
    {
        $this->temporaryStorageManager = $temporaryStorageManager;
    }

    public function onKernelTerminate(TerminateEvent $event)
    {
        if ('application/pdf' === $event->getRequest()->headers->get('accept')) {
            $this->temporaryStorageManager->removeFolder();
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::TERMINATE => [
                ['onKernelTerminate', 0],
            ],
        ];
    }
}
