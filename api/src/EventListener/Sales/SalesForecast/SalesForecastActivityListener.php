<?php

declare(strict_types=1);

namespace App\EventListener\Sales\SalesForecast;

use App\Dto\DeeplTranslator;
use App\Entity\Sales\SalesForecast;
use App\Event\Activity\CommentCreatedEvent;
use App\Http\DeeplClient;
use App\Notifier\Sales\SalesForecast\SalesForecastNotifier;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class SalesForecastActivityListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    public function __construct(private readonly ContainerInterface $serviceLocator)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CommentCreatedEvent::class => ['onCommentPost'],
        ];
    }

    public function onCommentPost(CommentCreatedEvent $event)
    {
        $comment = $event->getComment();
        $item = $event->getItem();

        if (!$item instanceof SalesForecast) {
            return;
        }

        if ($event->isMainRequest() && !$this->serviceLocator->get(Security::class)->isGranted('SALES_FORECAST_EDIT_VOTER', $item)) {
            throw new AccessDeniedHttpException(\sprintf('You are not allowed to edit Sales Forecast #%s', $item->getId()));
        }

        if (!$item->isNotificationRestricted()) {
            $item->setDelinquent(false);
        }

        if (!str_contains($comment->getMessage(), DeeplClient::ORIGINAL_COMMENT)) {
            $translation = $this->serviceLocator->get(DeeplClient::class)->getTranslation($comment->getMessage(), DeeplTranslator::DEFAULT_LANGUAGE_CODE, SalesForecast::class, $item->getId(), createLog: false);
            $comment->setMessage($translation);
        }

        $item->setComment($comment->getMessage());
        $item->setLastComment($comment->getMessage());

        $em = $this->serviceLocator->get(EntityManagerInterface::class);
        $em->persist($item);
        $em->flush();

        if ($event->isMainRequest()) {
            $this->serviceLocator->get(SalesForecastNotifier::class)->sendEmail($item, 'sfr.subject.comment', 'sales_forecast_edition.html.twig');
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        return [
            EntityManagerInterface::class,
            Security::class,
            SalesForecastNotifier::class,
            DeeplClient::class,
        ];
    }
}
