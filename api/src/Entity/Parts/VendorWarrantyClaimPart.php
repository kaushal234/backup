<?php

declare(strict_types=1);

namespace App\Entity\Parts;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Entity\Purchasing\VendorWarrantyClaim;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(operations: [new Get()], routePrefix: 'parts', normalizationContext: [], denormalizationContext: [])]
#[ORM\Table(name: 'vendor_warranty_claims_parts')]
class VendorWarrantyClaimPart extends Part
{
    /** @var string */
    final public const HYDRAULIC = 'HYDRAULIC';

    /** @var string */
    final public const ELECTRICAL = 'ELECTRICAL';

    /** @var string */
    final public const MECHANICAL = 'MECHANICAL';

    /** @var string */
    final public const PLUMBING = 'PLUMBING';

    /** @var string */
    final public const PNEUMATIC = 'PNEUMATIC';

    /** @var string */
    final public const REFRIGERATION = 'REFRIGERATION';

    /** @var string */
    final public const BRAKING = 'BRAKING';

    /** @var string */
    final public const ELEC_CONTROL = 'ELEC CONTROL';

    /** @var string */
    final public const ENGINE = 'ENGINE';

    /** @var string */
    final public const TRANSMISSION = 'TRANSMISSION';

    /** @var string */
    final public const SUSPENSION = 'SUSPENSION';

    /** @var string */
    final public const STRUCTURAL = 'STRUCTURAL';

    /** @var string */
    final public const CHASSIS = 'CHASSIS';

    /** @var string */
    final public const BODY = 'BODY';

    /** @var string */
    final public const COMPRESSOR = 'COMPRESSOR';

    /** @var string */
    final public const GENERATOR = 'GENERATOR';

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['part', 'part:admin'])]
    public ?string $serialNumber = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['part', 'part:admin'])]
    public ?string $vendorPartNumber = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['part', 'part:admin'])]
    public ?string $vendorSerialNumber = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\Choice(choices: [self::HYDRAULIC, self::ELECTRICAL, self::MECHANICAL, self::PLUMBING, self::PNEUMATIC, self::REFRIGERATION])]
    #[Groups(['part', 'part:admin'])]
    public ?string $failureType = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\Choice(choices: [self::BRAKING, self::HYDRAULIC, self::PNEUMATIC, self::REFRIGERATION, self::ELEC_CONTROL, self::ENGINE, self::TRANSMISSION, self::SUSPENSION, self::STRUCTURAL, self::CHASSIS, self::BODY, self::COMPRESSOR, self::GENERATOR])]
    #[Groups(['part', 'part:admin'])]
    public ?string $failureSystem = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['part', 'part:admin'])]
    public bool $ship = false;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['part', 'part:admin'])]
    public ?int $receivedQuantity = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Purchasing\VendorWarrantyClaim', inversedBy: 'parts')]
    #[ORM\JoinColumn(nullable: false)]
    #[MaxDepth(1)]
    public VendorWarrantyClaim $vendorWarrantyClaim;

    #[Groups(['part'])]
    public ?float $standardCost = null;
}
