<?php

declare(strict_types=1);

namespace App\Entity\Support\EquipmentRecord\HourMeterTransaction;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Service\CustomerServiceRecord\AbstractCustomerServiceRecord;
use App\Validator\Constraints\HourMeterTransaction as HourMeterTransactionConstraint;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\ObjectToProperty;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'csr_hour_meter_transactions')]
#[ApiResource(
    operations: [
        new Post(denormalizationContext: ['groups' => ['csr_hour_meter_transaction:create', 'hour_meter_transaction:create']]),
        new Get(),
        new Put(),
    ],
    routePrefix: 'support/equipment_record',
    normalizationContext: ['groups' => ['hour_meter_transaction', 'equipment_list']],
    denormalizationContext: ['groups' => ['hour_meter_transaction:edit']],
)]
#[Legacy\Synchronize(table: 'service_hourmeter')]
#[HourMeterTransactionConstraint]
class CustomerServiceRecordHourMeterTransaction extends HourMeterTransaction
{
    #[Legacy\Column(column: 'module')]
    #[Groups(['hour_meter_transaction', 'hour_meter_transaction:create'])]
    protected string $module = 'CSR';
    #[ORM\Column(type: 'integer')]
    #[Groups(['hour_meter_transaction', 'csr_hour_meter_transaction:create'])]
    private int $customerServiceRecordLegacyId;

    #[ORM\ManyToOne(targetEntity: AbstractCustomerServiceRecord::class, inversedBy: 'hourMeterTransactions')]
    #[Legacy\Column(column: 'module_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    #[Groups(['hour_meter_transaction', 'hour_meter_transaction:create'])]
    private AbstractCustomerServiceRecord $customerServiceRecord;

    public function getModule(): string
    {
        return $this->module;
    }

    public function setModule(string $module): self
    {
        $this->module = $module;

        return $this;
    }

    public function getCustomerServiceRecordLegacyId(): int
    {
        return $this->customerServiceRecordLegacyId;
    }

    public function setCustomerServiceRecordLegacyId(int $customerServiceRecordLegacyId): void
    {
        $this->customerServiceRecordLegacyId = $customerServiceRecordLegacyId;
    }

    public function getCustomerServiceRecord(): AbstractCustomerServiceRecord
    {
        return $this->customerServiceRecord;
    }

    public function setCustomerServiceRecord(AbstractCustomerServiceRecord $customerServiceRecord): void
    {
        $this->customerServiceRecordLegacyId = $customerServiceRecord->getLegacyId();
        $this->customerServiceRecord = $customerServiceRecord;
    }
}
