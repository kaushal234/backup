<?php

declare(strict_types=1);

namespace App\EventListener\Quality\Crab;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Quality\Crab;
use App\Entity\Quality\Derogation;
use App\Manager\Quality\CrabManager as Manager;
use App\Message\Quality\Crab\CrabWrite;
use App\Request\Activity\CommentRequestManager;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Manager\CrabManager;
use LegacyBundle\Manager\ModLinkManager;
use LegacyBundle\Manager\TaskManager;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class CrabListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    final public const FEATURE_CRAB_FIX_INSPECT = 'FEATURE_CRAB_FIX_INSPECT';
    final public const FEATURE_CRAB_EDIT_FIX = 'FEATURE_CRAB_EDIT_FIX';
    final public const FEATURE_CRAB_INSPECT_QA = 'FEATURE_CRAB_INSPECT_QA';

    public function __construct(
        private readonly ContainerInterface $serviceLocator
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['onPostWrite', EventPriorities::POST_WRITE],
                ['onPreWrite', EventPriorities::PRE_WRITE],
                ['onCrabDelete', EventPriorities::PRE_WRITE],
            ],
        ];
    }

    public function onPostWrite(ViewEvent $event): void
    {
        $crab = $event->getControllerResult();
        $request = $event->getRequest();
        if (!$crab instanceof Crab || !\in_array($request->getMethod(), [Request::METHOD_PUT, Request::METHOD_POST], true)) {
            return;
        }

        $user = $this->serviceLocator->get(Security::class)->getUser();
        if (Request::METHOD_PUT === $request->getMethod()) {
            /** @var Crab $previousData */
            $previousData = $request->attributes->get('previous_data');

            if ($previousData->firstArticleQualification !== $crab->firstArticleQualification) {
                $this->serviceLocator->get(ModLinkManager::class)->createLink($crab->getId(), 'CRAB', $crab->firstArticleQualification->getId(), 'FAQ');
            }

            if (null !== $crab->piQuestionId && Crab::CLOSED !== $previousData->status && Crab::CLOSED === $crab->status) {
                $this->serviceLocator->get(CrabManager::class)->answerQuestion($crab, $user);
                $this->serviceLocator->get(CommentRequestManager::class)->insertComment($crab, \sprintf('Question #%s answered from CRAB', $crab->piQuestionId));
            }

            if (null !== $crab->piQuestionId && null === $previousData->derogation && null !== $crab->derogation) {
                $this->serviceLocator->get(CrabManager::class)->derogateQuestion($crab, $user);
                $this->serviceLocator->get(CommentRequestManager::class)->insertComment($crab, \sprintf('Question #%s derogated from CRAB', $crab->piQuestionId));
            }

            if (null !== ($actualEquipmentRecord = $crab->equipmentRecord)) {
                if ($previousData->equipmentRecord->getId() !== $crab->equipmentRecord->getId()) {
                    $this->serviceLocator->get(Manager::class)->updateIONCrabData($actualEquipmentRecord);
                    $this->serviceLocator->get(Manager::class)->updateIONCrabData($previousData->equipmentRecord);
                }
                if ($previousData->status !== $crab->status && Crab::CLOSED === $crab->status) {
                    $this->serviceLocator->get(Manager::class)->updateIONCrabData($actualEquipmentRecord);
                }
            }

            if ($previousData->equipmentRecord->getId() === $crab->equipmentRecord->getId()) {
                return;
            }
        }

        if (Request::METHOD_POST === $request->getMethod()) {
            if (null !== $crab->piQuestionId) {
                $piQuestionType = $this->serviceLocator->get(CrabManager::class)->getPiQuestionType($crab);
                $crab->piQuestionType = $piQuestionType;
                $entityManager = $this->serviceLocator->get(EntityManagerInterface::class);
                $entityManager->persist($crab);
                $entityManager->flush();
            }
            if (null !== ($equipmentRecord = $crab->equipmentRecord)) {
                $this->serviceLocator->get(Manager::class)->updateIONCrabData($equipmentRecord);
            }

            if (null !== $crab->firstArticleQualification) {
                $this->serviceLocator->get(ModLinkManager::class)->createLink($crab->getId(), 'CRAB', $crab->firstArticleQualification->getId(), 'FAQ');
            }
        }

        $iriConverter = $this->serviceLocator->get(IriConverterInterface::class);
        $this->serviceLocator->get(MessageBusInterface::class)->dispatch(new CrabWrite($iriConverter->getIriFromResource($user), $iriConverter->getIriFromResource($crab), $request->getMethod()));
    }

    public function onPreWrite(ViewEvent $event): void
    {
        $crab = $event->getControllerResult();
        $request = $event->getRequest();

        if (!$crab instanceof Crab || Request::METHOD_PUT !== $request->getMethod()) {
            return;
        }

        /** @var Crab $previousData */
        $previousData = $request->attributes->get('previous_data');

        if (null !== $previousData->fixingComments && $previousData->fixingComments !== $crab->fixingComments && !$this->serviceLocator->get(Security::class)->isGranted(self::FEATURE_CRAB_EDIT_FIX)) {
            throw new AccessDeniedHttpException('You cannot edit fixing comments');
        }

        if (Crab::CLOSED === $crab->status && Crab::CLOSED === $previousData->status && !$this->serviceLocator->get(Security::class)->isGranted('FEATURE_CRAB_EDIT_CLOSED')) {
            throw new BadRequestHttpException('Not possible to edit a closed CRAB.');
        }

        if (Crab::FOR_DEROGATION === $previousData->status && \in_array($crab->status, [Crab::TO_INSPECT, Crab::CLOSED], true)) {
            throw new BadRequestHttpException('Not possible to edit a CRAB with the status FOR DEROGATION.');
        }

        $taskManager = $this->serviceLocator->get(TaskManager::class);
        if ($previousData->status !== $crab->status
            && \in_array($crab->status, [Crab::TO_INSPECT, Crab::CLOSED, Crab::FOR_DEROGATION], true)
            && !empty($taskManager->findOpenTasksByModule('CRAB', $crab->getLegacyId()))
        ) {
            throw new BadRequestHttpException("Status can't be changed if tasks remains opened on CRAB");
        }

        if (null === $previousData->derogation && null !== $crab->derogation) {
            if (Derogation::ACCEPTED !== $crab->derogation->getStatus()) {
                throw new BadRequestHttpException('Only accepted derogation can be linked to a CRAB.');
            }

            $crab->status = Crab::CLOSED;

            return;
        }

        if (Crab::CLOSED !== $crab->status) {
            return;
        }

        $security = $this->serviceLocator->get(Security::class);
        $user = $security->getUser();

        if (null !== $user
            && null !== $crab->fixedBy
            && Crab::QA === $crab->category
            && !$this->serviceLocator->get(Security::class)->isGranted(self::FEATURE_CRAB_INSPECT_QA)) {
            throw new BadRequestHttpException('Only a QA Team member can inspect a QA Crab');
        }

        $isPdiCategory = \in_array($crab->category, [Crab::PDI, Crab::PDI_CSC, Crab::PDI_SOL], true);

        if (null !== $user
            && null !== $crab->fixedBy
            && $user === $crab->fixedBy
            && ($isPdiCategory || !$this->serviceLocator->get(Security::class)->isGranted(self::FEATURE_CRAB_FIX_INSPECT))) {
            throw new BadRequestHttpException('A given CRAB cannot be fixed and inspected by the same person');
        }

        $crab->inspectedBy = $user;
    }

    public function onCrabDelete(ViewEvent $event): void
    {
        $crab = $event->getControllerResult();
        $request = $event->getRequest();

        if (!$crab instanceof Crab || Request::METHOD_DELETE !== $request->getMethod()) {
            return;
        }

        if (null !== ($equipmentRecord = $crab->equipmentRecord)) {
            $this->serviceLocator->get(Manager::class)->updateIONCrabData($equipmentRecord, Crab::CLOSED === $crab->status);
        }
    }

    public static function getSubscribedServices(): array
    {
        return [
            EntityManagerInterface::class,
            MessageBusInterface::class,
            Security::class,
            IriConverterInterface::class,
            TaskManager::class,
            CrabManager::class,
            CommentRequestManager::class,
            Manager::class,
            ModLinkManager::class,
        ];
    }
}
