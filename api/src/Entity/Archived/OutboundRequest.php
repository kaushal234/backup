<?php

declare(strict_types=1);

namespace App\Entity\Archived;

use App\Entity\Directory\Location;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(readOnly: true)]
#[ORM\Table(name: 'outbound_requests')]
class OutboundRequest
{
    final public const CREATED_FROM_BAAN = 'CREATED FROM BAAN';
    final public const DELIVERED_PARTIALLY = 'DELIVERED PARTIALLY';
    final public const CLOSED = 'CLOSED';

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\Column(type: 'integer')]
    private int $productionOrder;

    #[ORM\Column(type: 'integer')]
    private int $operation;

    #[ORM\Column(type: 'string', length: 6)]
    private string $project;

    #[ORM\Column(type: 'string', length: 8, options: ['default' => ''])]
    private string $slot = '';

    #[ORM\Column(type: 'string', length: 30, nullable: true)]
    private ?string $taskDescription = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(name: 'closed_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $closedAt = null;

    #[ORM\Column(type: 'date')]
    private \DateTimeInterface $baanOperationStartingDate;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $requestCreatedAt = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $requestedToBeDeliveredAt = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[ORM\JoinColumn(nullable: false)]
    private Location $location;

    #[ORM\Column(type: 'string', length: 25)]
    private string $status = self::CREATED_FROM_BAAN;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $totalNumberOfLines = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $numberOfLinesToBeDelivered = 0;

    private ?string $customerName = null;

    private ?string $model = null;

    private ?string $serialNumber = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getProductionOrder(): int
    {
        return $this->productionOrder;
    }

    public function setProductionOrder(int $productionOrder): self
    {
        $this->productionOrder = $productionOrder;

        return $this;
    }

    public function getOperation(): int
    {
        return $this->operation;
    }

    public function setOperation(int $operation): self
    {
        $this->operation = $operation;

        return $this;
    }

    public function getProject(): string
    {
        return $this->project;
    }

    public function setProject(string $project): self
    {
        $this->project = $project;

        return $this;
    }

    public function getSlot(): string
    {
        return $this->slot;
    }

    public function setSlot(string $slot): self
    {
        $this->slot = $slot;

        return $this;
    }

    public function getTaskDescription(): ?string
    {
        return $this->taskDescription;
    }

    public function setTaskDescription(?string $taskDescription): self
    {
        $this->taskDescription = mb_convert_encoding($taskDescription, 'UTF-8', 'ASCII,GB2312,ISO-8859-1,CP936,UTF-8');

        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getClosedAt(): ?\DateTimeInterface
    {
        return $this->closedAt;
    }

    public function setClosedAt(?\DateTimeInterface $closedAt): self
    {
        $this->closedAt = $closedAt;

        return $this;
    }

    public function getBaanOperationStartingDate(): \DateTimeInterface
    {
        return $this->baanOperationStartingDate;
    }

    public function setBaanOperationStartingDate(\DateTimeInterface $baanOperationStartingDate): self
    {
        $this->baanOperationStartingDate = $baanOperationStartingDate;

        return $this;
    }

    public function getRequestCreatedAt(): ?\DateTimeInterface
    {
        return $this->requestCreatedAt;
    }

    public function setRequestCreatedAt(?\DateTimeInterface $requestCreatedAt): self
    {
        $this->requestCreatedAt = $requestCreatedAt;

        return $this;
    }

    public function getRequestedToBeDeliveredAt(): ?\DateTimeInterface
    {
        return $this->requestedToBeDeliveredAt;
    }

    public function setRequestedToBeDeliveredAt(?\DateTimeInterface $requestedToBeDeliveredAt): self
    {
        $this->requestedToBeDeliveredAt = $requestedToBeDeliveredAt;

        return $this;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }

    public function setLocation(Location $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getTotalNumberOfLines(): int
    {
        return $this->totalNumberOfLines;
    }

    public function setTotalNumberOfLines(int $totalNumberOfLines): self
    {
        $this->totalNumberOfLines = $totalNumberOfLines;

        return $this;
    }

    public function getNumberOfLinesToBeDelivered(): int
    {
        return $this->numberOfLinesToBeDelivered;
    }

    public function setNumberOfLinesToBeDelivered(int $numberOfLinesToBeDelivered): self
    {
        $this->numberOfLinesToBeDelivered = $numberOfLinesToBeDelivered;

        return $this;
    }

    public function getCustomerName(): ?string
    {
        return $this->customerName;
    }

    public function setCustomerName(?string $customerName): self
    {
        $this->customerName = $customerName;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(?string $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function getSerialNumber(): ?string
    {
        return $this->serialNumber;
    }

    public function setSerialNumber(?string $serialNumber): self
    {
        $this->serialNumber = $serialNumber;

        return $this;
    }
}
