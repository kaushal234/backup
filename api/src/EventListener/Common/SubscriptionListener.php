<?php

declare(strict_types=1);

namespace App\EventListener\Common;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Common\Subscription;
use App\Event\Activity\SubscriptionCreatedEvent;
use App\Request\Activity\CommentRequestManager;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class SubscriptionListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['afterSubscriptionCreationOrDeletion', EventPriorities::POST_WRITE],
            ],
        ];
    }

    public function afterSubscriptionCreationOrDeletion(ViewEvent $event)
    {
        $subscription = $event->getControllerResult();

        if (!$subscription instanceof Subscription) {
            return;
        }

        $item = $this->serviceLocator->get(IriConverterInterface::class)->getResourceFromIri($subscription->getResource());

        switch ($event->getRequest()->getMethod()) {
            case Request::METHOD_POST:
                $messageSameUser = '%s subscribed';
                $message = '%s has been added as subscriber';
                $this->serviceLocator->get(EventDispatcherInterface::class)->dispatch(new SubscriptionCreatedEvent($subscription, $item));
                break;
            case Request::METHOD_DELETE:
                $messageSameUser = '%s unsubscribed';
                $message = '%s has been removed as subscriber';
                break;
            default:
                return;
        }

        if ($this->serviceLocator->get(Security::class)->getUser() === $subscription->getUser()) {
            $message = \sprintf($messageSameUser, $subscription->getUser());
        } else {
            $message = \sprintf($message, $subscription->getUser());
        }

        $this->serviceLocator->get(CommentRequestManager::class)->insertComment($item, $message, null, ['subscription' => $subscription->getUser()->getId()]);
    }

    public static function getSubscribedServices(): array
    {
        return [
            CommentRequestManager::class,
            Security::class,
            IriConverterInterface::class,
            EventDispatcherInterface::class,
        ];
    }
}
