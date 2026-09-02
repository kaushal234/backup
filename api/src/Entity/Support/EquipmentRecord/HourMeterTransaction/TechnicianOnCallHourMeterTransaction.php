<?php

declare(strict_types=1);

namespace App\Entity\Support\EquipmentRecord\HourMeterTransaction;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Service\TechnicianOnCall;
use App\Validator\Constraints\HourMeterTransaction as HourMeterTransactionConstraint;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\ObjectToProperty;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\MaxDepth;

#[ORM\Entity]
#[ORM\Table(name: 'toc_hour_meter_transactions')]
#[ApiResource(
    operations: [
        new Post(
            denormalizationContext: ['groups' => ['toc_hour_meter_transaction:create', 'hour_meter_transaction:create']],
            name: 'toc_hour_meter_transaction_post'
        ),
        new Get(),
        new Put(),
    ],
    routePrefix: 'support/equipment_record',
    normalizationContext: ['groups' => ['hour_meter_transaction', 'equipment_list']],
    denormalizationContext: ['groups' => ['hour_meter_transaction:edit']],
)]
#[Legacy\Synchronize(table: 'service_hourmeter')]
#[HourMeterTransactionConstraint]
class TechnicianOnCallHourMeterTransaction extends HourMeterTransaction
{
    #[ORM\Column(type: 'integer', nullable: true)]
    #[Legacy\Column(column: 'module_id')]
    #[Groups(['hour_meter_transaction', 'toc_hour_meter_transaction:create'])]
    public int $tocLegacyId;

    #[ORM\ManyToOne(targetEntity: TechnicianOnCall::class, inversedBy: 'hourMeterTransactions')]
    #[ORM\JoinColumn(nullable: true)]
    #[Legacy\Column(column: 'module_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    #[Groups(['hour_meter_transaction:create', 'hour_meter_transaction'])]
    #[MaxDepth(1)]
    public TechnicianOnCall $technicianOnCall;

    #[Legacy\Column(column: 'module')]
    #[Groups(['hour_meter_transaction', 'hour_meter_transaction:create'])]
    protected string $module = TechnicianOnCall::MODULE_NAME;

    public function getModule(): string
    {
        return $this->module;
    }

    public function setModule(string $module): self
    {
        $this->module = $module;

        return $this;
    }

    public function getTocLegacyId(): int
    {
        return $this->tocLegacyId;
    }

    public function setTocLegacyId(int $tocLegacyId): self
    {
        $this->tocLegacyId = $tocLegacyId;

        return $this;
    }

    public function getTechnicianOnCall(): TechnicianOnCall
    {
        return $this->technicianOnCall;
    }

    public function setTechnicianOnCall(TechnicianOnCall $technicianOnCall): self
    {
        $this->technicianOnCall = $technicianOnCall;
        $this->tocLegacyId = $technicianOnCall->getLegacyId();

        if (!isset($this->equipmentRecord)) {
            $this->equipmentRecord = $technicianOnCall->equipmentRecord;
        }

        return $this;
    }
}
