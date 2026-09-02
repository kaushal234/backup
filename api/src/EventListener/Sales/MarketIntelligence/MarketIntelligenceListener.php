<?php

declare(strict_types=1);

namespace App\EventListener\Sales\MarketIntelligence;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Sales\MarketIntelligence\MarketIntelligence;
use App\Event\Activity\CommentCreatedEvent;
use App\Message\Sales\NotifyMarketIntelligenceComment;
use App\Message\Sales\NotifyMarketIntelligenceCreate;
use App\Message\Sales\NotifyMarketIntelligenceUpdate;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class MarketIntelligenceListener implements EventSubscriberInterface, ServiceSubscriberInterface
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
                ['onMarketIntelligenceCreation', EventPriorities::POST_WRITE],
                ['onMarketIntelligenceUpdate', EventPriorities::PRE_WRITE],
            ],
            CommentCreatedEvent::class => ['onCommentPost'],
        ];
    }

    public function onMarketIntelligenceCreation(ViewEvent $event)
    {
        $marketIntelligence = $event->getControllerResult();
        $request = $event->getRequest();
        if (!$marketIntelligence instanceof MarketIntelligence || !$request->isMethod(Request::METHOD_POST)) {
            return;
        }

        $iriConverter = $this->serviceLocator->get(IriConverterInterface::class);
        $security = $this->serviceLocator->get(Security::class);
        $entityManager = $this->serviceLocator->get(EntityManagerInterface::class);
        // +10000 to avoid having two MIMs with same legacyId
        $marketIntelligence->setLegacyId($marketIntelligence->getId() + 10_000);
        $entityManager->persist($marketIntelligence);
        $entityManager->flush();
        $message = new NotifyMarketIntelligenceCreate($iriConverter->getIriFromResource($security->getUser()), $iriConverter->getIriFromResource($marketIntelligence));

        $this->serviceLocator->get(MessageBusInterface::class)->dispatch($message);
    }

    public function onMarketIntelligenceUpdate(ViewEvent $event)
    {
        $marketIntelligence = $event->getControllerResult();
        $request = $event->getRequest();
        if (!$marketIntelligence instanceof MarketIntelligence || !$request->isMethod(Request::METHOD_PUT)) {
            return;
        }

        $iriConverter = $this->serviceLocator->get(IriConverterInterface::class);
        $security = $this->serviceLocator->get(Security::class);

        $uow = $this->serviceLocator->get(EntityManagerInterface::class)->getUnitOfWork();
        $uow->computeChangeSets();
        $changeSet = $uow->getEntityChangeSet($marketIntelligence);
        if (1 === \count($changeSet) && isset($changeSet['positionLevels'])) {
            return;
        }
        $message = new NotifyMarketIntelligenceUpdate($iriConverter->getIriFromResource($security->getUser()), $iriConverter->getIriFromResource($marketIntelligence));

        $this->serviceLocator->get(MessageBusInterface::class)->dispatch($message);
    }

    public function onCommentPost(CommentCreatedEvent $event)
    {
        $item = $event->getItem();

        if (!$item instanceof MarketIntelligence) {
            return;
        }

        $comment = $event->getComment();
        $this->serviceLocator->get(MessageBusInterface::class)->dispatch(new NotifyMarketIntelligenceComment(
            $this->serviceLocator->get(IriConverterInterface::class)->getIriFromResource($this->serviceLocator->get(Security::class)->getUser()),
            $this->serviceLocator->get(IriConverterInterface::class)->getIriFromResource($item),
            $comment->getMessage()))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        return [
            EntityManagerInterface::class,
            MessageBusInterface::class,
            IriConverterInterface::class,
            Security::class,
        ];
    }
}
