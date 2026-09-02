<?php

declare(strict_types=1);

namespace App\ION\Resources\Manufacturing\JobShop\BillOfMaterials;

use App\ION\Resources\Manufacturing\JobShop\EngineeringRevisionTrait;
use App\ION\Resources\Manufacturing\JobShop\ItemInterface;
use App\ION\Resources\Manufacturing\JobShop\ItemTrait;
use App\ION\Resources\Manufacturing\JobShop\PurchasingTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Attribute\Groups;

class PurchasingViewItem implements ItemInterface
{
    use EngineeringRevisionTrait;
    use ItemTrait;
    use PurchasingTrait;

    #[Groups(['bom'])]
    public int $level;

    #[Groups(['bom'])]
    public int $position;

    #[Groups(['bom'])]
    public int $operation;

    /**
     * @var Collection<ItemInterface>
     */
    #[Groups(['bom'])]
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
     * @param ArrayCollection|ItemInterface[] $items
     */
    public function setItems(Collection $items): self
    {
        $this->items = $items;

        return $this;
    }

    public function addItem(ItemInterface $item): self
    {
        $this->items->add($item);

        return $this;
    }

    public function removeItem(ItemInterface $item): self
    {
        // do nothing, we do not remove element from this resource
        return $this;
    }
}
