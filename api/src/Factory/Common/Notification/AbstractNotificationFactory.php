<?php

declare(strict_types=1);

namespace App\Factory\Common\Notification;

use ApiPlatform\Metadata\UrlGeneratorInterface;
use App\Entity\Common\Notification\Notification;
use App\Entity\Common\Notification\NotificationTemplate;
use App\Entity\Directory\People;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

abstract class AbstractNotificationFactory
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        protected readonly UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function createNotification(object $object, ?People $recipient = null): Notification
    {
        $notification = new Notification();

        $template = $this->getTemplate($this->getTemplateName());
        $notification->template = $template;
        $notification->people = $recipient;
        $notification->referenceId = $object->getId();
        $notification->textDisplayed = $this->getFormattedText($object, $template);
        $notification->url = $this->getRoute($object);

        return $notification;
    }

    abstract protected function getTemplateName(): string;

    abstract protected function getFormattedText(object $object, NotificationTemplate $notificationTemplate): string;

    abstract protected function getRoute(object $object): string;

    protected function getTemplate(string $name): ?NotificationTemplate
    {
        $repository = $this->entityManager->getRepository(NotificationTemplate::class);

        $template = $repository->findOneBy(['name' => $name]);

        return null !== $template ? $template : throw new UnprocessableEntityHttpException('Please provide a valid template notification name.');
    }
}
