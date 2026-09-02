<?php

declare(strict_types=1);

namespace App\Tests\EventListener\Workflow;

use App\Entity\EquipmentRecord;
use App\Entity\Service\CustomerServiceRecord\CommissioningCustomerServiceRecord;
use App\Entity\Service\CustomerServiceRecord\CustomerServiceRecord;
use App\EventListener\Service\CustomerServiceRecordWorkflowListener;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Workflow\Event\CompletedEvent;
use Symfony\Component\Workflow\Marking;

class CustomerServiceRecordListenerTest extends TestCase
{
    use ProphecyTrait;

    public function testOnCompletedDoesNothingForRegularCustomerServiceRecord(): void
    {
        $listener = new CustomerServiceRecordWorkflowListener();
        $customerServiceRecord = new CustomerServiceRecord();

        $completedEvent = new CompletedEvent($customerServiceRecord, new Marking());
        $listener->onCompleted($completedEvent);

        $this->assertNotNull($customerServiceRecord->completedAt, 'completedAt must not be null.');
    }

    public function testOnCompletedSetsCompletedAtForCommissioningRecord(): void
    {
        $listener = new CustomerServiceRecordWorkflowListener();

        $commissioningRecord = new CommissioningCustomerServiceRecord();

        $completedEvent = new CompletedEvent($commissioningRecord, new Marking());
        $listener->onCompleted($completedEvent);

        $this->assertNotNull($commissioningRecord->completedAt, 'completedAt must not be null.');
    }

    public function testOnCompletedSetsDateCommissionedFromContextForCommissioningRecord(): void
    {
        $listener = new CustomerServiceRecordWorkflowListener();

        $commissioningRecord = new CommissioningCustomerServiceRecord();
        $equipmentRecord = new EquipmentRecord();
        $commissioningRecord->equipmentRecord = $equipmentRecord;

        $endedAt = new \DateTime('2024-03-12 14:00:00');
        $completedEvent = new CompletedEvent($commissioningRecord, new Marking(), null, null, ['interventionEndedAt' => $endedAt]);
        $listener->onCompleted($completedEvent);

        $this->assertSame($endedAt, $commissioningRecord->equipmentRecord->getDateCommissioned());
    }
}
