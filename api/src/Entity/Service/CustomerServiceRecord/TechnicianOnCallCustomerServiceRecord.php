<?php

declare(strict_types=1);

namespace App\Entity\Service\CustomerServiceRecord;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Service\TechnicianOnCall;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[UniqueEntity(fields: 'technicianOnCall', message: 'customer_service_record.technician_on_call.already_exist')]
#[ApiResource(
    operations: [
        new Post(
            security: "is_granted('FEATURE_CUSTOMER_SERVICE_RECORD_CREATE')",
            name: 'technician_on_call_customer_service_records_post',
        ),
        new Get(),
        new Put(
            denormalizationContext: ['groups' => ['customer_service_record:create', 'customer_service_record:update']],
            security: "is_granted('FEATURE_CUSTOMER_SERVICE_RECORD_EDIT')",
        ),
    ],
    routePrefix: 'service',
    normalizationContext: [
        'groups' => self::NORMALIZATION_GROUP,
    ],
    denormalizationContext: [
        'groups' => ['customer_service_record:create', 'customer_service_record:detail'],
    ],
    openapi: true,
)]
#[Legacy\ExtraColumn(column: 'module', value: TechnicianOnCall::MODULE_NAME)]
class TechnicianOnCallCustomerServiceRecord extends AbstractCustomerServiceRecord
{
    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['customer_service_record', 'customer_service_record:create', 'customer_service_record:detail'])]
    #[Legacy\Column(column: 'module_id')]
    public ?int $tocLegacyId = null;

    #[ORM\ManyToOne(targetEntity: TechnicianOnCall::class, inversedBy: 'customerServiceRecords')]
    #[ORM\JoinColumn(nullable: true)]
    #[Assert\NotBlank]
    #[Groups(['customer_service_record', 'customer_service_record:create', 'customer_service_record:detail'])]
    #[ApiProperty(fetchEager: false)]
    private TechnicianOnCall $technicianOnCall;

    public function getTechnicianOnCall(): TechnicianOnCall
    {
        return $this->technicianOnCall;
    }

    public function setTechnicianOnCall(TechnicianOnCall $technicianOnCall): void
    {
        $this->technicianOnCall = $technicianOnCall;
        $this->tocLegacyId = $technicianOnCall->getLegacyId();
    }

    public function getLegacyModuleName(): string
    {
        return TechnicianOnCall::MODULE_NAME;
    }

    public function getLegacyModuleId(): ?int
    {
        return $this->tocLegacyId;
    }
}
