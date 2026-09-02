<?php

declare(strict_types=1);

namespace App\Entity\Purchasing;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Group;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
    ],
    routePrefix: 'purchasing',
    normalizationContext: ['groups' => ['vendor_warranty_claim_status']],
    denormalizationContext: [],
    security: "is_granted('ACCESS_PEOPLE')",
)]
#[ORM\Table(name: 'vendor_warranty_claim_status')]
#[ApiFilter(SearchFilter::class, properties: ['name'])]
#[ApiFilter(OrderFilter::class, properties: ['position' => 'ASC'])]
class VendorWarrantyClaimStatus implements \Stringable
{
    final public const string PENDING = 'PENDING';
    final public const string QA_ANALYSIS = 'QA ANALYSIS';
    final public const string VENDOR_TO_RESPOND = 'VENDOR_TO_RESPOND';
    final public const string REVIEW_VENDOR_RESPONSE = 'REVIEW_VENDOR_RESPONSE';
    final public const string CREATE_PO = 'CREATE_PO';
    final public const string SHIP_TO_VENDOR = 'SHIP_TO_VENDOR';
    final public const string ISSUE_CREDIT_NOTE = 'ISSUE_CREDIT_NOTE';
    final public const string REC_FROM_VENDOR = 'REC_FROM_VENDOR';
    final public const string ISSUE_DEBIT_NOTE = 'ISSUE_DEBIT_NOTE';
    final public const string VALIDATE_SCAR = 'VALIDATE_SCAR';
    final public const string CLOSED_RESOLVED = 'CLOSED_RESOLVED';
    final public const string CLOSED_LOW_VALUE = 'CLOSED_LOW_VALUE';
    final public const string CLOSED_VENDOR_REJECTED = 'CLOSED_VENDOR_REJECTED';
    final public const string CLOSED_DUPLICATE = 'CLOSED_DUPLICATE';
    final public const string CLOSED_NOT_VENDOR_ISSUE = 'CLOSED_NOT_VENDOR_ISSUE';
    final public const array CLOSED_STATUSES = [self::CLOSED_LOW_VALUE, self::CLOSED_NOT_VENDOR_ISSUE, self::CLOSED_RESOLVED, self::CLOSED_VENDOR_REJECTED, self::CLOSED_DUPLICATE];

    #[ORM\Column(type: 'string')]
    #[Groups(['vendor_warranty_claim_status'])]
    public string $name;

    #[ORM\Column(type: 'string')]
    #[Groups(['vendor_warranty_claim_status'])]
    public string $description;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Group')]
    #[ORM\JoinColumn(nullable: false)]
    public Group $group;

    #[ORM\Column(type: 'integer')]
    #[Groups(['vendor_warranty_claim_status'])]
    public int $position;

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    public function __toString()
    {
        return $this->name;
    }

    public function getId(): int
    {
        return $this->id;
    }
}
