<?php

declare(strict_types=1);

namespace App\Entity\Archived;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Directory\Location;
use App\Entity\EquipmentRecord;
use App\Entity\Sales\Product;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ApiResource(operations: [])]
#[ORM\Table(name: 'shipping_quotation_request_lines')]
class ShippingQuotationRequestLine
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    private ?Location $factory = null;

    #[ORM\Column(type: 'text', length: 1024, nullable: true)]
    private ?string $otherLocation = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $incoterms = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $incoLocation = null;

    #[ORM\Column(type: 'boolean')]
    private bool $transshipmentAuthorized = false;

    #[ORM\Column(type: 'string')]
    private string $type;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $estimatedPickUpDeadline = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\EquipmentRecord')]
    #[ORM\JoinColumn(nullable: true)]
    private ?EquipmentRecord $equipmentRecord = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\Product')]
    #[ORM\JoinColumn(nullable: false)]
    private Product $product;

    #[ORM\Column(type: 'integer')]
    private int $quantity;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $harmonizedSystemCode = null;

    #[ORM\Column(type: 'boolean')]
    private bool $rollingEquipment = false;

    #[ORM\Column(type: 'integer')]
    private int $length;

    #[ORM\Column(type: 'integer')]
    private int $width;

    #[ORM\Column(type: 'integer')]
    private int $height;

    #[ORM\Column(type: 'integer')]
    private int $weight;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $groundClearance = null;

    #[ORM\Column(type: 'boolean')]
    private bool $unloadingPlanned = false;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $note = null;

    /**
     * @var Collection<Truck>
     */
    #[ORM\OneToMany(mappedBy: 'shippingQuotationRequestLine', targetEntity: 'App\Entity\Archived\Truck', cascade: ['persist'], orphanRemoval: true)]
    private Collection $trucks;

    /**
     * @var Collection<Container>
     */
    #[ORM\OneToMany(mappedBy: 'shippingQuotationRequestLine', targetEntity: 'App\Entity\Archived\Container', cascade: ['persist'], orphanRemoval: true)]
    private Collection $containers;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Archived\ShippingQuotationRequest', inversedBy: 'shippingQuotationRequestLines')]
    #[ORM\JoinColumn(nullable: false)]
    private ShippingQuotationRequest $shippingQuotationRequest;

    public function __construct()
    {
        $this->trucks = new ArrayCollection();
        $this->containers = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getFactory(): ?Location
    {
        return $this->factory;
    }

    public function setFactory(?Location $factory): self
    {
        $this->factory = $factory;

        return $this;
    }

    public function getOtherLocation(): ?string
    {
        return $this->otherLocation;
    }

    public function setOtherLocation(?string $otherLocation): self
    {
        $this->otherLocation = $otherLocation;

        return $this;
    }

    public function getIncoterms(): ?string
    {
        return $this->incoterms;
    }

    public function setIncoterms(?string $incoterms): self
    {
        $this->incoterms = $incoterms;

        return $this;
    }

    public function getIncoLocation(): ?string
    {
        return $this->incoLocation;
    }

    public function setIncoLocation(?string $incoLocation): self
    {
        $this->incoLocation = $incoLocation;

        return $this;
    }

    public function isTransshipmentAuthorized(): bool
    {
        return $this->transshipmentAuthorized;
    }

    public function setTransshipmentAuthorized(bool $transshipmentAuthorized): self
    {
        $this->transshipmentAuthorized = $transshipmentAuthorized;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getEstimatedPickUpDeadline(): ?string
    {
        return $this->estimatedPickUpDeadline;
    }

    public function setEstimatedPickUpDeadline(?string $estimatedPickUpDeadline): self
    {
        $this->estimatedPickUpDeadline = $estimatedPickUpDeadline;

        return $this;
    }

    public function getEquipmentRecord(): ?EquipmentRecord
    {
        return $this->equipmentRecord;
    }

    public function setEquipmentRecord(?EquipmentRecord $equipmentRecord): self
    {
        $this->equipmentRecord = $equipmentRecord;

        return $this;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getHarmonizedSystemCode(): ?string
    {
        return $this->harmonizedSystemCode;
    }

    public function setHarmonizedSystemCode(?string $harmonizedSystemCode): self
    {
        $this->harmonizedSystemCode = $harmonizedSystemCode;

        return $this;
    }

    public function isRollingEquipment(): bool
    {
        return $this->rollingEquipment;
    }

    public function setRollingEquipment(bool $rollingEquipment): self
    {
        $this->rollingEquipment = $rollingEquipment;

        return $this;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function setLength(int $length): self
    {
        $this->length = $length;

        return $this;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function setWidth(int $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function setHeight(int $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function getWeight(): int
    {
        return $this->weight;
    }

    public function setWeight(int $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    public function getGroundClearance(): ?int
    {
        return $this->groundClearance;
    }

    public function setGroundClearance(?int $groundClearance): self
    {
        $this->groundClearance = $groundClearance;

        return $this;
    }

    public function isUnloadingPlanned(): bool
    {
        return $this->unloadingPlanned;
    }

    public function setUnloadingPlanned(bool $unloadingPlanned): self
    {
        $this->unloadingPlanned = $unloadingPlanned;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;

        return $this;
    }

    /**
     * @return Collection<Truck>
     */
    public function getTrucks(): Collection
    {
        return $this->trucks;
    }

    public function addTruck(Truck $truck): self
    {
        $this->trucks->add($truck);
        $truck->setShippingQuotationrequestLine($this);

        return $this;
    }

    public function removeTruck(Truck $truck): self
    {
        $this->trucks->removeElement($truck);

        return $this;
    }

    /**
     * @return Collection<Container>
     */
    public function getContainers(): Collection
    {
        return $this->containers;
    }

    public function addContainer(Container $container): self
    {
        $this->containers->add($container);
        $container->setShippingQuotationrequestLine($this);

        return $this;
    }

    public function removeContainer(Container $container): self
    {
        $this->containers->removeElement($container);

        return $this;
    }

    public function getShippingQuotationRequest(): ShippingQuotationRequest
    {
        return $this->shippingQuotationRequest;
    }

    public function setShippingQuotationRequest(ShippingQuotationRequest $shippingQuotationRequest): self
    {
        $this->shippingQuotationRequest = $shippingQuotationRequest;

        return $this;
    }
}
