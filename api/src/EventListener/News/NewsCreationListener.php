<?php

declare(strict_types=1);

namespace App\EventListener\News;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\News\News;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class NewsCreationListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function onCreation(ViewEvent $event)
    {
        $news = $event->getControllerResult();
        $request = $event->getRequest();

        if (!$news instanceof News || !$news->isBanner() || !\in_array($request->getMethod(), [Request::METHOD_POST, Request::METHOD_PUT], true)) {
            return;
        }
        $newsWithABanner = $this->serviceLocator->get(EntityManagerInterface::class)->getRepository(News::class)->findOneBy(['banner' => true]);

        if (
            null !== $newsWithABanner
            && (
                Request::METHOD_POST === $request->getMethod()
                || $newsWithABanner->getId() !== $news->getId()
            )
        ) {
            throw new BadRequestHttpException('Only 1 banner at a time is permitted.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['onCreation', EventPriorities::PRE_WRITE],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        return [
            EntityManagerInterface::class,
        ];
    }
}
