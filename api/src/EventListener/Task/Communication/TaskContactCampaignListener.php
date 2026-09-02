<?php

declare(strict_types=1);

namespace App\EventListener\Task\Communication;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Task\Task;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class TaskContactCampaignListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    public function __construct(
        private ContainerInterface $serviceLocator,
    ) {
    }

    public function resetIsVerified(ViewEvent $event): void
    {
        $task = $event->getControllerResult();
        $request = $event->getRequest();
        $route = $request->attributes->get('_route');

        if (!$task instanceof Task || !$request->isMethod(Request::METHOD_POST) || 'task_close_comment' === $route) {
            return;
        }

        if ('contact.validation.address' !== $task->template?->name || 'XU' !== $task->module->getName()) {
            return;
        }

        $entityManager = $this->serviceLocator->get(EntityManagerInterface::class);

        /** @var ExtranetUser $extranetUser */
        $extranetUser = $entityManager->getRepository(ExtranetUser::class)->findOneBy(['id' => $task->referenceId]);
        $extranetUser->getExtranetUserProfile()->isVerified = false;

        $entityManager->persist($extranetUser);
        $entityManager->flush();
    }

    public function addressVerified(ViewEvent $event): void
    {
        $task = $event->getControllerResult();
        $request = $event->getRequest();
        $route = $request->attributes->get('_route');

        if (!$task instanceof Task || 'task_close_comment' !== $route) {
            return;
        }

        if ('contact.validation.address' !== $task->template?->name || 'XU' !== $task->module->getName()) {
            return;
        }

        $entityManager = $this->serviceLocator->get(EntityManagerInterface::class);

        /** @var ExtranetUser $extranetUser */
        $extranetUser = $entityManager->getRepository(ExtranetUser::class)->findOneBy(['id' => $task->referenceId]);

        $address = $extranetUser->getAddress();
        if (null === $address->getStreet1()
            || null === $address->getPostalCode()
            || null === $address->getCity()
            || null === $extranetUser->getExtranetUserProfile()->country?->getName()
        ) {
            throw new BadRequestHttpException('Fields street1, Postal Code, City and Country are required.');
        }

        $extranetUser->getExtranetUserProfile()->isVerified = true;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['addressVerified', EventPriorities::PRE_WRITE],
                ['resetIsVerified', EventPriorities::POST_WRITE],
            ],
        ];
    }

    public static function getSubscribedServices(): array
    {
        return [
            EntityManagerInterface::class,
        ];
    }
}
