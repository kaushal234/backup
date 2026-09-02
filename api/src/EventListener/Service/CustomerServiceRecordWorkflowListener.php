<?php

declare(strict_types=1);

namespace App\EventListener\Service;

use App\Entity\Service\CustomerServiceRecord\CommissioningCustomerServiceRecord;
use App\Entity\Service\CustomerServiceRecord\CustomerServiceRecord;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\CompletedEvent;

class CustomerServiceRecordWorkflowListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.customer_service_record.completed.to_completed' => ['onCompleted'],
        ];
    }

    public function onCompleted(CompletedEvent $event): void
    {
        /** @var CustomerServiceRecord $customerServiceRecord */
        $customerServiceRecord = $event->getSubject();
        $customerServiceRecord->completedAt = new \DateTime();
        if (!$customerServiceRecord instanceof CommissioningCustomerServiceRecord) {
            return;
        }

        if (!isset($customerServiceRecord->equipmentRecord)) {
            return;
        }

        $customerServiceRecord->equipmentRecord->setDateCommissioned($event->getContext()['interventionEndedAt'] ?? null);
    }
}
