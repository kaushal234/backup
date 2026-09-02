<?php

declare(strict_types=1);

namespace App\Entity\Sales;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\QueryParameter;
use App\DataProvider\Support\TopLateSolsProvider;
use App\Entity\Directory\Location;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\BooleanToChar;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use LegacyBundle\Entity\LegacyIdInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/top_late_sols',
            provider: TopLateSolsProvider::class,
            parameters: [
                'manufacturerLocation' => new QueryParameter(),
            ],
            extraProperties: ['query_parameter_validate' => false],
        ),
    ],
)]
#[ORM\Entity]
#[ORM\Table(name: 'sales_order_lines')]
#[Legacy\Synchronize(table: 'sor_lines')]
class OrderLine implements LegacyIdInterface
{
    use LegacyIdentifierTrait;

    public const PENDING = 'PENDING';
    public const CREATE_PO = 'CREATE_PO';
    public const EVP_APPROVAL = 'EVP_APPROVAL';
    public const PRINT_SO_ACK = 'PRINT_SO_ACK';
    public const CREATE_SO_ACK = 'CREATE_SO_ACK';
    public const PRINT_PO = 'PRINT_PO';
    public const CREATE_FACTORY_SO = 'CREATE_FACTORY_SO';
    public const PRINT_FACTORY_SO_ACK = 'PRINT_FACTORY_SO_ACK';
    public const ENGINEER_REVIEW = 'ENGINEER_REVIEW';
    public const ENGINEERING_APPROVAL = 'ENGINEERING_APPROVAL';
    public const MATERIALS_PLANNING = 'MATERIALS_PLANNING';
    public const PSM_APPROVAL = 'PSM_APPROVAL';
    public const IN_PROGRESS = 'IN_PROGRESS';
    public const SHIPPED = 'SHIPPED';
    public const CLOSED = 'CLOSED';

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['order_line'])]
    public ?\DateTimeInterface $purchaseOrderAcceptedDate = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['order_line'])]
    #[Legacy\Column(column: 'conf_cis', transformer: BooleanToChar::class, options: ['trueValue' => 'Y', 'falseValue' => 'N'])]
    public bool $inspection = false;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['order_line'])]
    public bool $shipWithParts = false;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\Incoterm')]
    #[Groups(['order_line'])]
    public ?Incoterm $incoterm = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    public ?Location $factory = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['order_line'])]
    public ?string $incotermLocation = null;

    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'lines')]
    #[Groups(['order_line'])]
    public ?Order $order = null;

    #[ORM\Column(type: 'boolean')]
    public bool $isPaymentTermValid = false;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['order_line'])]
    public ?string $paymentTerms = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\Choice(choices: [self::PENDING, self::CREATE_PO, self::EVP_APPROVAL, self::PRINT_SO_ACK, self::CREATE_SO_ACK, self::PRINT_PO, self::CREATE_FACTORY_SO, self::PRINT_FACTORY_SO_ACK, self::ENGINEER_REVIEW, self::ENGINEERING_APPROVAL, self::MATERIALS_PLANNING, self::PSM_APPROVAL, self::IN_PROGRESS, self::SHIPPED, self::CLOSED])]
    #[Groups(['order_line'])]
    public ?string $status = null;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    public bool $deliveryPenalties = false;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $deliveryPenaltiesConditions = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups(['order_line'])]
    public ?bool $deliveredEarly = null;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    /**
     * @var Collection<OrderToFactory>
     */
    #[ORM\OneToMany(mappedBy: 'orderLine', targetEntity: OrderToFactory::class)]
    private Collection $factoryOrders;

    public function __construct()
    {
        $this->factoryOrders = new ArrayCollection();
    }

    /**
     * @return Collection<OrderToFactory>
     */
    public function getFactoryOrders(): Collection
    {
        return $this->factoryOrders;
    }

    public function addFactoryOrder(OrderToFactory $factoryOrder): self
    {
        $factoryOrder->orderLine = $this;
        $this->factoryOrders->add($factoryOrder);

        return $this;
    }

    public function removeFactoryOrder(OrderToFactory $factoryOrder): self
    {
        $this->factoryOrders->removeElement($factoryOrder);

        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }
}
