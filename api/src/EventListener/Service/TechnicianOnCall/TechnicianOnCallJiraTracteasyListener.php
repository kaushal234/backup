<?php

declare(strict_types=1);

namespace App\EventListener\Service\TechnicianOnCall;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Service\TechnicianOnCall;
use App\Message\Service\TechnicianOnCallJiraTracteasyIssueCreate;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsEventListener(event: KernelEvents::VIEW, method: 'onCreationCreateJiraTracteasyIssue', priority: EventPriorities::POST_WRITE)]
class TechnicianOnCallJiraTracteasyListener extends AbstractTechnicianOnCallListener
{
    public function __construct(
        private readonly ContainerInterface $container,
    ) {
    }

    public static function getSubscribedServices(): array
    {
        return [
            Security::class,
            IriConverterInterface::class,
            MessageBusInterface::class,
        ];
    }

    public function onCreationCreateJiraTracteasyIssue(ViewEvent $event): void
    {
        if (!$this->isTechnicianOnCallCreation($event)) {
            return;
        }

        /** @var TechnicianOnCall $toc */
        $toc = $event->getControllerResult();

        $iriConverter = $this->container->get(IriConverterInterface::class);
        $user = $this->container->get(Security::class)->getUser();

        $this->container->get(MessageBusInterface::class)->dispatch(
            new TechnicianOnCallJiraTracteasyIssueCreate(
                $iriConverter->getIriFromResource($toc),
                $iriConverter->getIriFromResource($user)
            )
        );
    }
}
