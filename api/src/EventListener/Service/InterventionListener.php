<?php

declare(strict_types=1);

namespace App\EventListener\Service;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Service\CustomerServiceRecord\Intervention;
use App\Request\Activity\CommentRequestManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class InterventionListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    public function __construct(
        private readonly ContainerInterface $serviceLocator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['onPostUpdate', EventPriorities::POST_WRITE],
            ],
        ];
    }

    public function onPostUpdate(ViewEvent $event): void
    {
        $intervention = $event->getControllerResult();

        if (!$intervention instanceof Intervention) {
            return;
        }

        $request = $event->getRequest();

        if (Request::METHOD_PUT !== $request->getMethod()) {
            return;
        }

        if (null !== $intervention->comments) {
            $file = null;
            $metadata = [];
            $discriminator = 'CSR';

            $this->serviceLocator->get(CommentRequestManager::class)->insertComment($intervention, $intervention->comments, $file, $metadata, $discriminator);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        return [
            CommentRequestManager::class,
        ];
    }
}
