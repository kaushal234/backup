<?php

declare(strict_types=1);

namespace App\ION\Resources\Manufacturing\JobShop\CustomizedBillOfMaterials;

use App\ION\Resources\Manufacturing\JobShop\ItemInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Attribute\Groups;

class ManualPart implements ItemInterface
{
    #[Groups(['cbom'])]
    public string $partNumber;

    #[Groups(['cbom'])]
    public string $engineeringRevision;

    #[Groups(['cbom'])]
    public string $engineeringSignalCode;

    #[Groups(['cbom'])]
    public int $position;

    #[Groups(['cbom'])]
    public string $itemDescription;

    #[Groups(['cbom'])]
    public string $itemOtherDescription;

    #[Groups(['cbom'])]
    public ?string $signalCodeDescription = null;

    #[Groups(['cbom'])]
    public float $quantity;

    /**
     * @var Collection<ManualItem>
     */
    #[Groups(['cbom'])]
    protected Collection $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    public function getItems(): Collection
    {
        return $this->items;
    }

    /**
     * @param ArrayCollection|ManualItem[] $items
     */
    public function setItems(Collection $items): self
    {
        $this->items = $items;

        return $this;
    }

    public function addItem(ManualItem $item): self
    {
        $this->items->add($item);

        return $this;
    }

    public function removeItem(ManualItem $item): self
    {
        // do nothing, we do not remove element from this resource
        return $this;
    }
}
