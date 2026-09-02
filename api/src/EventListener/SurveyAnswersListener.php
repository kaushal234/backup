<?php

declare(strict_types=1);

namespace App\EventListener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Survey\Answer;
use App\Manager\Survey\PublishedSurveyManager;
use App\Notifier\Survey\SurveyNotifier;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class SurveyAnswersListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function onSurveyAnswered(ViewEvent $event)
    {
        $answer = $event->getControllerResult();

        if (!$answer instanceof Answer || Request::METHOD_POST !== $event->getRequest()->getMethod()) {
            return;
        }

        $survey = $answer->getPublishedSurvey();

        $this->serviceLocator->get(EntityManagerInterface::class)->refresh($survey);

        if ($this->serviceLocator->get(PublishedSurveyManager::class)->isCompleted($survey)) {
            $this->serviceLocator->get(SurveyNotifier::class)->notifyCompletion($survey);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['onSurveyAnswered', EventPriorities::POST_WRITE],
        ];
    }

    public static function getSubscribedServices(): array
    {
        return [
            EntityManagerInterface::class,
            PublishedSurveyManager::class,
            SurveyNotifier::class,
        ];
    }
}
