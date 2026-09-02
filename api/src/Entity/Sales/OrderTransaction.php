<?php

declare(strict_types=1);

namespace App\Entity\Sales;

use App\Entity\EquipmentRecord;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use LegacyBundle\Entity\LegacyIdInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'sales_order_transactions')]
class OrderTransaction implements LegacyIdInterface
{
    use LegacyIdentifierTrait;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['order_transaction'])]
    public ?string $invoice = null;

    #[ORM\OneToOne(inversedBy: 'orderTransaction', targetEntity: EquipmentRecord::class)]
    public ?EquipmentRecord $equipmentRecord = null;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
