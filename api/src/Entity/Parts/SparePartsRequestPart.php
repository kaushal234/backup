<?php

declare(strict_types=1);

namespace App\Entity\Parts;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use App\Entity\Directory\Location;
use App\Repository\Parts\SparePartsRequestPartRepository;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\ObjectToProperty;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: SparePartsRequestPartRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new Delete(security: "is_granted('FEATURE_SPARE_PARTS_REQUESTS_EDIT_PARTS') or is_granted('FEATURE_SPARE_PARTS_REQUESTS_EDIT_FULL') or is_granted('MOO_SPR')"),
    ],
    routePrefix: 'parts',
    normalizationContext: [],
    denormalizationContext: [],
)]
#[ORM\Table(name: 'spare_parts_requests_parts')]
#[Legacy\Synchronize(table: 'spr_lines')]
class SparePartsRequestPart extends Part implements \Stringable
{
    use LegacyIdentifierTrait;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['part', 'part:admin'])]
    public ?string $comment = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Parts\SparePartsRequest', inversedBy: 'parts')]
    #[ORM\JoinColumn(nullable: false)]
    #[MaxDepth(1)]
    #[Legacy\Column(column: 'parent_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    public SparePartsRequest $sparePartsRequest;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[Groups(['part'])]
    public ?Location $shippingOrigin = null;

    #[Groups(['part'])]
    public ?\DateTime $plannedDeliveryDate = null;

    /**
     * @var PartTracking[]
     */
    #[Groups(['part'])]
    private array $trackings = [];

    public function __toString()
    {
        return \sprintf('%s - %s (Qty: %s %s)', $this->partNumber, $this->description, $this->quantity, $this->unitOfMeasure);
    }

    public function getTrackings(): array
    {
        return $this->trackings;
    }

    public function addTracking(PartTracking $tracking): self
    {
        $this->trackings[] = $tracking;

        return $this;
    }

    public function isFullyShipped(): bool
    {
        $shippedQuantity = 0;
        foreach ($this->trackings as $tracking) {
            $shippedQuantity += $tracking->shippedQuantity;
        }

        return $shippedQuantity >= $this->quantity;
    }
}
