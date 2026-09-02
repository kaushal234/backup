<?php

declare(strict_types=1);

namespace App\Entity\Sales;

use App\Entity\EquipmentRecord;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use LegacyBundle\Entity\LegacyIdInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'sales_order_factory')]
class OrderToFactory implements LegacyIdInterface
{
    use LegacyIdentifierTrait;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['order_factory'])]
    public bool $commissioning = false;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['order_factory'])]
    public ?\DateTimeInterface $requestedDeliveryDate = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['order_factory'])]
    public ?\DateTimeInterface $factoryPromisedDeliveryDate = null;

    #[ORM\ManyToOne(targetEntity: OrderLine::class, inversedBy: 'factoryOrders')]
    #[Groups(['order_factory'])]
    public OrderLine $orderLine;

    #[ORM\OneToOne(inversedBy: 'orderFactory', targetEntity: EquipmentRecord::class)]
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
