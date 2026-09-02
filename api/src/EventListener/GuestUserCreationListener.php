<?php

declare(strict_types=1);

namespace App\EventListener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\MIS\GuestUser\GuestUser;
use App\Notifier\Tasks\SequenceNotifier;
use LegacyBundle\Manager\SequenceManager;
use LegacyBundle\Model\Sequence;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

#[AsEventListener(event: KernelEvents::VIEW, method: 'onCreate', priority: EventPriorities::POST_WRITE)]
class GuestUserCreationListener implements ServiceSubscriberInterface
{
    private const string SEQUENCE_TEMPLATE = 'guest_user.new';

    public function __construct(
        private readonly ContainerInterface $serviceLocator
    ) {
    }

    public function onCreate(ViewEvent $event)
    {
        $guestUser = $event->getControllerResult();
        $request = $event->getRequest();

        if (!$guestUser instanceof GuestUser || Request::METHOD_POST !== $request->getMethod()) {
            return;
        }

        $assignee = $guestUser->getSupervisor();
        $sequence = new Sequence();
        $sequence
            ->setTemplateName(self::SEQUENCE_TEMPLATE)
            ->setTemplateDescription('Sequence for new Guest User')
            ->setCloseParams([
                'module' => 'Guest User',
                'assignor' => $assignee,
                'assignee' => $assignee,
            ])
            ->setModule('Guest User')
            ->setAssignee($assignee)
            ->setAssignor($assignee)
            ->setParentId($guestUser->getId())
            ->setLocation($assignee->getBusinessUnit()->getLocation())
            ->setDescription(\sprintf('New Guest User : 
                Firstname: %s
                Lastname: %s
                Position: %s
                Premise: %s
                Planned disable at: %s', $guestUser->getFirstname(), $guestUser->getLastname(), $guestUser->getPosition()?->getCode() ?? '-', $guestUser->getPremise()->name, $guestUser->getPlannedDisableAt()->format('Y-m-d')))
        ;

        try {
            $this->serviceLocator->get(SequenceManager::class)->insert($sequence);
        } catch (\Exception $exception) {
            throw new \LogicException('Sequence not inserted. Reason: '.$exception->getMessage(), $exception->getCode(), $exception);
        }

        $this->serviceLocator->get(SequenceNotifier::class)->sendEmail($sequence);
    }

    public static function getSubscribedServices(): array
    {
        return [
            SequenceManager::class,
            SequenceNotifier::class,
        ];
    }
}
