<?php

declare(strict_types=1);

namespace App\EventListener\Activity;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Activity\Comment;
use App\Entity\User;
use App\Event\Activity\CommentCreatedEvent;
use App\Event\Activity\CommentPreCreateEvent;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class CommentListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['preCommentPost', EventPriorities::PRE_WRITE],
                ['onCommentPost', EventPriorities::POST_WRITE],
            ],
        ];
    }

    public function onCommentPost(ViewEvent $event): void
    {
        $result = $event->getControllerResult();
        $request = $event->getRequest();
        if (!$result instanceof Comment || !$request->isMethod(Request::METHOD_POST)) {
            return;
        }

        try {
            $item = $this->serviceLocator->get(IriConverterInterface::class)->getResourceFromIri($result->getResource());
        } catch (\Exception $exception) {
            return;
        }

        $this->serviceLocator->get(EventDispatcherInterface::class)->dispatch(new CommentCreatedEvent($result, $item, $event->isMainRequest()));
    }

    public function preCommentPost(ViewEvent $event)
    {
        $result = $event->getControllerResult();
        $request = $event->getRequest();

        if (!$result instanceof Comment || !$request->isMethod(Request::METHOD_POST)) {
            return;
        }

        try {
            $item = $this->serviceLocator->get(IriConverterInterface::class)->getResourceFromIri($result->getResource());
        } catch (\Exception $exception) {
            return;
        }

        if (null !== ($user = $this->serviceLocator->get(Security::class)->getUser()) && $user instanceof User) {
            $result->setUser($user);
        }

        $this->serviceLocator->get(EventDispatcherInterface::class)->dispatch(new CommentPreCreateEvent($result, $item, $event->isMainRequest()));
    }

    public static function getSubscribedServices(): array
    {
        return [
            IriConverterInterface::class,
            EventDispatcherInterface::class,
            Security::class,
        ];
    }
}
