<?php

declare(strict_types=1);

namespace App\Entity\Parts;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Entity\Quality\NonConformity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new Get(),
    ],
    routePrefix: 'parts',
    normalizationContext: [],
    denormalizationContext: []
)]
#[ORM\Table(name: 'non_conformity_parts')]
class NonConformityPart extends Part
{
    /** @var string */
    final public const WO = 'WO';

    /** @var string */
    final public const DO = 'DO';

    /** @var string */
    final public const WHSE = 'WHSE';

    /** @var string */
    final public const WELD = 'WELD';

    /** @var string */
    final public const SN = 'SN';

    /** @var string */
    final public const PROD = 'PROD';

    /** @var string */
    final public const PO = 'PO';

    /** @var string */
    final public const PART = 'PART';

    /** @var string */
    final public const OTHER = 'OTHER';

    /** @var string */
    final public const CUST = 'CUST';

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Quality\NonConformity', inversedBy: 'parts')]
    #[ORM\JoinColumn(nullable: false)]
    #[MaxDepth(1)]
    public NonConformity $nonConformity;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\Choice(choices: [self::CUST, self::OTHER, self::PART, self::PO, self::PROD, self::SN, self::WELD, self::WHSE, self::WO, self::DO])]
    #[Groups(['part', 'part:admin'])]
    public ?string $reference = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['part', 'part:admin'])]
    public ?string $referenceNumber = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['part', 'part:admin'])]
    public ?string $serialNumber = null;

    #[Groups(['part'])]
    public ?float $standardCost = null;
}
