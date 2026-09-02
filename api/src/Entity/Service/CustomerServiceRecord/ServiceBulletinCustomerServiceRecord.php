<?php

declare(strict_types=1);

namespace App\Entity\Service\CustomerServiceRecord;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Validator\Constraints\Service as TLDAssert;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Dto\ServiceBulletin;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(security: "is_granted('FEATURE_CUSTOMER_SERVICE_RECORD_CREATE')"),
        new Get(),
        new Put(
            denormalizationContext: ['groups' => ['customer_service_record:create', 'customer_service_record:detail', 'customer_service_record:update']],
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
)]
#[ApiFilter(SearchFilter::class, properties: [
    'serviceBulletinLegacyId',
])]
#[Legacy\ExtraColumn(column: 'module', value: 'SB')]
class ServiceBulletinCustomerServiceRecord extends AbstractCustomerServiceRecord
{
    #[ORM\Column(type: 'integer', nullable: false)]
    #[Groups(['customer_service_record', 'customer_service_record:create', 'customer_service_record:detail'])]
    #[Assert\NotBlank(message: 'SB Line could not be found for this ER on this SB')]
    public int $serviceBulletinLinesLegacyId;

    #[ORM\Column(type: 'integer', nullable: false)]
    #[Groups(['customer_service_record', 'customer_service_record:create', 'customer_service_record:detail'])]
    #[Assert\NotBlank]
    #[TLDAssert\ServiceBulletinExist]
    #[Legacy\Column(column: 'module_id')]
    public int $serviceBulletinLegacyId;

    #[Groups(['legacy:service_bulletin'])]
    public ?ServiceBulletin $serviceBulletin = null;

    public function getLegacyModuleName(): string
    {
        return 'SB3';
    }

    public function getLegacyModuleId(): ?int
    {
        return $this->serviceBulletinLegacyId;
    }
}
