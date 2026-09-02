<?php

declare(strict_types=1);

namespace App\EventListener\Quality\Crab;

use App\Entity\Quality\Crab;
use App\Entity\Quality\Derogation;
use App\Notifier\Quality\Crab\CrabNotifier;
use App\Request\Activity\CommentRequestManager;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Manager\CrabManager;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Workflow\Event\CompletedEvent;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class DerogationWorkflowCompletedListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.derogation.completed.to_denied' => ['onDeny'],
            'workflow.derogation.completed.to_accepted' => ['onAccept'],
        ];
    }

    public function onDeny(CompletedEvent $event)
    {
        $derogation = $event->getSubject();

        if (!$derogation instanceof Derogation) {
            return;
        }

        /** @var Crab $crab */
        $crab = $derogation->getCrabs()->first();

        if (!$crab instanceof Crab) {
            throw new BadRequestHttpException('No crabs are affiliate to this derogation.');
        }

        $entityManager = $this->serviceLocator->get(EntityManagerInterface::class);

        $crab->status = Crab::TO_FIX;
        $entityManager->persist($crab);

        $user = $this->serviceLocator->get(Security::class)->getUser();
        $derogation->closedBy = $user;
        $derogation->closedAt = new \DateTime();
        $entityManager->persist($derogation);

        $this->serviceLocator->get(CrabNotifier::class)->sendDerogationClosed($derogation, 'denied');
    }

    public function onAccept(CompletedEvent $event)
    {
        $derogation = $event->getSubject();

        if (!$derogation instanceof Derogation) {
            return;
        }

        /** @var Crab $crab */
        $crab = $derogation->getCrabs()->first();

        if (!$crab instanceof Crab) {
            throw new BadRequestHttpException('No crabs are affiliate to this derogation.');
        }

        $entityManager = $this->serviceLocator->get(EntityManagerInterface::class);

        $crab->status = Crab::CLOSED;
        $entityManager->persist($crab);

        $user = $this->serviceLocator->get(Security::class)->getUser();
        $derogation->closedBy = $user;
        $derogation->closedAt = new \DateTime();
        $entityManager->persist($derogation);

        if (null !== $crab->piQuestionId) {
            $this->serviceLocator->get(CrabManager::class)->derogateQuestion($crab, $user);
            $this->serviceLocator->get(CommentRequestManager::class)->insertComment($crab, \sprintf('Question #%s derogated from CRAB', $crab->piQuestionId));
        }

        $this->serviceLocator->get(CrabNotifier::class)->sendDerogationClosed($derogation, 'accepted');
    }

    public static function getSubscribedServices(): array
    {
        return [
            EntityManagerInterface::class,
            CrabNotifier::class,
            CrabManager::class,
            Security::class,
            CommentRequestManager::class,
        ];
    }
}
