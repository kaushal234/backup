<?php

declare(strict_types=1);

namespace App\EventListener\Service;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Directory\People;
use App\Entity\Service\CustomerServiceRecord\AbstractCustomerServiceRecord;
use App\Entity\Service\CustomerServiceRecord\CommissioningCustomerServiceRecord;
use App\Entity\Service\SurveyCustomerServiceRecord\AnswerSurveyCustomerServiceRecord;
use App\Factory\Service\InterventionFactory;
use App\Notifier\Service\CustomerServiceRecord\CustomerServiceRecordCommissioningNotifier;
use App\Request\Activity\CommentRequestManager;
use App\Workflow\WorkflowStatusUpdater;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class CustomerServiceRecordCommissioningListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    public function __construct(
        private readonly ContainerInterface $serviceLocator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['onPostComplete', EventPriorities::PRE_WRITE],
                ['onPostAssignedStatus', EventPriorities::PRE_WRITE],
            ],
        ];
    }

    public function onPostComplete(ViewEvent $event): void
    {
        $customerServiceRecord = $event->getControllerResult();

        if (!$customerServiceRecord instanceof CommissioningCustomerServiceRecord) {
            return;
        }

        if (Request::METHOD_PUT !== $event->getRequest()->getMethod()) {
            return;
        }

        $answers = $customerServiceRecord->getAnswerSurveyCustomerServiceRecords()->filter(static function (AnswerSurveyCustomerServiceRecord $answer): bool {
            return \in_array($answer->questionSurveyCustomerServiceRecord->name, ['aspect', 'conformity', 'operational'], true);
        });

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->serviceLocator->get(EntityManagerInterface::class);
        $entityManager->getUnitOfWork()->computeChangeSets();
        $diff = 0;
        $perfect = true;

        foreach ($answers as $answer) {
            $perfect = $perfect ? '5' === $answer->getAnswer() : $perfect;

            if (!\array_key_exists('answer', $entityManager->getUnitOfWork()->getEntityChangeSet($answer))) {
                continue;
            }

            ++$diff;
        }
        // Only send alert when answer changed and one of answer not have value 5
        if (!$diff || $perfect) {
            return;
        }

        $this->serviceLocator->get(CustomerServiceRecordCommissioningNotifier::class)->sendCommissionedImperfectMail($customerServiceRecord);
    }

    public function onPostAssignedStatus(ViewEvent $event): void
    {
        $customerServiceRecord = $event->getControllerResult();

        if (!$customerServiceRecord instanceof CommissioningCustomerServiceRecord) {
            return;
        }

        if (Request::METHOD_POST !== $event->getRequest()->getMethod()) {
            return;
        }

        if (!$customerServiceRecord->plannedAt instanceof \DateTime || !$customerServiceRecord->leader instanceof People) {
            return;
        }

        if (!$customerServiceRecord->getOpenIntervention()) {
            $intervention = $this->serviceLocator->get(InterventionFactory::class)->createFromCustomerServiceRecord($customerServiceRecord);
            $customerServiceRecord->addIntervention($intervention);
        }

        $this->serviceLocator->get(WorkflowStatusUpdater::class)->applyStatus($customerServiceRecord, AbstractCustomerServiceRecord::ASSIGNED);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        return [
            CommentRequestManager::class,
            EntityManagerInterface::class,
            MessageBusInterface::class,
            IriConverterInterface::class,
            Security::class,
            CustomerServiceRecordCommissioningNotifier::class,
            InterventionFactory::class,
            WorkflowStatusUpdater::class,
        ];
    }
}
