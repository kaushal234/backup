<?php

declare(strict_types=1);

namespace App\EventListener\Quality\FirstArticleQualification;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Quality\FirstArticleQualification\FirstArticleQualification;
use App\Entity\Quality\FirstArticleQualification\PlanItem;
use App\Workflow\WorkflowStatusUpdater;
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

class FirstArticleQualificationPlanValidationListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function onPlanItemCreation(ViewEvent $event): void
    {
        $result = $event->getControllerResult();

        if (!$result instanceof FirstArticleQualification) {
            return;
        }

        if (!$event->getRequest()->isMethod(Request::METHOD_PUT)) {
            return;
        }

        $planItems = $result->getPlan();

        if (null === $this->serviceLocator->get(Security::class)->getUser()) {
            return;
        }

        if (\in_array($result->getStatus(), [FirstArticleQualification::IN_PROGRESS_PLAN_COMPLETED, FirstArticleQualification::IN_PROGRESS], true) && !$planItems->isEmpty()) {
            $status = $this->allPlanItemsCompleted($planItems)
                ? FirstArticleQualification::IN_PROGRESS_PLAN_COMPLETED
                : FirstArticleQualification::IN_PROGRESS;

            try {
                $this->serviceLocator->get(WorkflowStatusUpdater::class)->applyStatus($result, $status);
            } catch (LogicException $logicException) {
                throw new BadRequestHttpException(\sprintf('Status %s is not allowed', $result->getStatus()), $logicException);
            }
        }

        $em = $this->serviceLocator->get(EntityManagerInterface::class);
        $em->persist($result);
        $em->flush();
    }

    public function onPlanItemValidationEdition(ViewEvent $event): void
    {
        $result = $event->getControllerResult();

        if (!$result instanceof PlanItem) {
            return;
        }

        if (!$event->getRequest()->isMethod(Request::METHOD_PUT)) {
            return;
        }

        if (null === $this->serviceLocator->get(Security::class)->getUser()) {
            return;
        }

        $faq = $result->getFirstArticleQualification();
        $planItems = $faq->getPlan();

        if (FirstArticleQualification::IN_PROGRESS === $faq->getStatus() && !$planItems->isEmpty() && $this->allPlanItemsCompleted($planItems)) {
            try {
                $this->serviceLocator->get(WorkflowStatusUpdater::class)->applyStatus($faq, FirstArticleQualification::IN_PROGRESS_PLAN_COMPLETED);
            } catch (LogicException $logicException) {
                throw new BadRequestHttpException(\sprintf('Status %s is not allowed', $faq->getStatus()), $logicException);
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['onPlanItemValidationEdition', EventPriorities::POST_VALIDATE],
                ['onPlanItemCreation', EventPriorities::POST_WRITE],
            ],
        ];
    }

    public static function getSubscribedServices(): array
    {
        return [
            Security::class,
            WorkflowStatusUpdater::class,
            EntityManagerInterface::class,
        ];
    }

    private function allPlanItemsCompleted(iterable $planItems): bool
    {
        foreach ($planItems as $planItem) {
            if (!$planItem instanceof PlanItem || 100 !== $planItem->getCompletionRate()) {
                return false;
            }
        }

        return true;
    }
}
