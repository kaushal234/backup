<?php

declare(strict_types=1);

namespace App\EventListener\Quality\FirstArticleQualification;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Quality\FirstArticleQualification\FirstArticleQualification;
use App\Event\Activity\CommentCreatedEvent;
use App\Notifier\Quality\FirstArticleQualification\FirstArticleQualificationNotifier;
use App\Workflow\WorkflowStatusUpdater;
use Cake\Chronos\Chronos;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Workflow\Exception\LogicException;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class FirstArticleQualificationActivityListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            CommentCreatedEvent::class => ['setInProgressStatusOnCommentPosted'],
            KernelEvents::VIEW => [
                ['overrideStatusForPrivilegedUser', EventPriorities::PRE_WRITE + 1], // must run before UpdatableStatusEntityListener::changeStatus
                ['checkIfStatusAllowsFieldsUpdate', EventPriorities::PRE_WRITE], // This one compute the changeset and needs to be called in last in all FAQ listeners
                ['setInProgressStatus', EventPriorities::POST_WRITE],
                ['onPlanApprovalStatusChange', EventPriorities::PRE_WRITE],
            ],
        ];
    }

    public function onPlanApprovalStatusChange(ViewEvent $event)
    {
        $result = $event->getControllerResult();
        if (!$result instanceof FirstArticleQualification || !$event->getRequest()->isMethod(Request::METHOD_PUT)) {
            return;
        }

        $em = $this->serviceLocator->get(EntityManagerInterface::class);
        $uow = $em->getUnitOfWork();
        $uow->computeChangeSets();

        $changeset = $uow->getEntityChangeSet($result);

        if (!isset($changeset['planApprovalStatus'])) {
            return;
        }

        $this->serviceLocator->get(FirstArticleQualificationNotifier::class)->sendPlanStatus($result);
    }

    public function overrideStatusForPrivilegedUser(ViewEvent $event)
    {
        $result = $event->getControllerResult();

        if (!$result instanceof FirstArticleQualification) {
            return;
        }

        $request = $event->getRequest();
        if (!$request->isMethod(Request::METHOD_PUT)) {
            return;
        }

        /** @var Operation $operation */
        $operation = $request->attributes->get('_api_operation');
        if ('update_first_article_qualification_status' !== $operation->getName()) {
            return;
        }

        $security = $this->serviceLocator->get(Security::class);
        if (!$security->isGranted('FEATURE_FIRST_ARTICLE_QUALIFICATION_STATUS_OVERRIDE') && !$security->isGranted('MOO_FAQ')) {
            return;
        }

        $previousData = $request->attributes->get('previous_data');
        if (!$previousData instanceof FirstArticleQualification) {
            return;
        }

        $previousStatus = $previousData->getStatus();
        if ($previousStatus === $result->getStatus()) {
            return;
        }

        $request->attributes->set('_faq_status_override', true);

        $this->serviceLocator->get(FirstArticleQualificationNotifier::class)
            ->sendStatus($result, $previousStatus, $security->getUser());
    }

    public function setInProgressStatusOnCommentPosted(CommentCreatedEvent $event)
    {
        $item = $event->getItem();

        if (!$item instanceof FirstArticleQualification) {
            return;
        }

        try {
            $this->serviceLocator->get(WorkflowStatusUpdater::class)->applyStatus($item, FirstArticleQualification::IN_PROGRESS);

            $em = $this->serviceLocator->get(EntityManagerInterface::class);
            $em->persist($item);
            $em->flush();
        } catch (LogicException $logicException) {
            // do nothing
        }

        $this->serviceLocator->get(FirstArticleQualificationNotifier::class)->sendComment($item, $event->getComment());
    }

    public function checkIfStatusAllowsFieldsUpdate(ViewEvent $event)
    {
        $result = $event->getControllerResult();

        if (!$result instanceof FirstArticleQualification) {
            return;
        }

        $request = $event->getRequest();
        if (!$request->isMethod(Request::METHOD_PUT)) {
            return;
        }

        /** @var Operation $operation */
        $operation = $request->attributes->get('_api_operation');
        if ('update_first_article_qualification_status' === $operation->getName()) {
            // This operation only allow to update the status and it should still be possible
            return;
        }

        $uow = $this->serviceLocator->get(EntityManagerInterface::class)->getUnitOfWork();
        $uow->computeChangeSets();

        $changeset = $uow->getEntityChangeSet($result);
        $dirty = !empty($changeset);
        if (empty($changeset)) {
            foreach ($uow->getScheduledCollectionUpdates() + $uow->getScheduledCollectionDeletions() as $collection) {
                if ($collection->isDirty()) {
                    $dirty = true;
                    break;
                }
            }
        }

        if (!$dirty) {
            return;
        }

        $status = isset($changeset['status']) ? $changeset['status'][0] : $result->getStatus();

        if (FirstArticleQualification::QUALIFIED === $status && $this->serviceLocator->get(Security::class)->isGranted('FEATURE_FIRST_ARTICLE_QUALIFICATION_EDIT_QUALIFIED')) {
            return;
        }

        if (\in_array($status, [FirstArticleQualification::QUALIFIED, FirstArticleQualification::CONDITIONAL, FirstArticleQualification::REJECTED], true)) {
            throw new BadRequestHttpException(\sprintf('You cannot update faq in status: %s', $status));
        }
    }

    public function setInProgressStatus(ViewEvent $event)
    {
        $result = $event->getControllerResult();

        if (!$result instanceof FirstArticleQualification) {
            return;
        }

        $request = $event->getRequest();

        if (!\in_array($request->getMethod(), [Request::METHOD_POST, Request::METHOD_PUT], true)) {
            return;
        }

        if (null !== $result->getPlanDefinitionCompletedAt() || $result->getPlan()->isEmpty()) {
            return;
        }

        $result->setPlanDefinitionCompletedAt(new \DateTime((new Chronos())->toDateTimeString()));
        $this->serviceLocator->get(FirstArticleQualificationNotifier::class)->sendPlanCompleted($result);
        try {
            $this->serviceLocator->get(WorkflowStatusUpdater::class)->applyStatus($result, FirstArticleQualification::IN_PROGRESS);
        } catch (LogicException $logicException) {
            throw new BadRequestHttpException(\sprintf('Status %s is not allowed', $result->getStatus()), $logicException);
        }

        $em = $this->serviceLocator->get(EntityManagerInterface::class);
        $em->persist($result);
        $em->flush();
    }

    public static function getSubscribedServices(): array
    {
        return [
            EntityManagerInterface::class,
            WorkflowStatusUpdater::class,
            FirstArticleQualificationNotifier::class,
            Security::class,
        ];
    }
}
