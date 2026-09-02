<?php

declare(strict_types=1);

namespace App\ION\Resources\Manufacturing\JobShop\BillOfMaterials;

use ApiPlatform\Metadata\ApiProperty;
use App\ION\Resources\Manufacturing\JobShop\ItemInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Attribute\Groups;

abstract class BillOfMaterials
{
    #[ApiProperty(identifier: true)]
    #[Groups(['bom'])]
    public int $site;

    #[ApiProperty(identifier: true)]
    #[Groups(['bom'])]
    public string $project;

    #[ApiProperty(identifier: true)]
    #[Groups(['bom'])]
    public string $product;

    #[Groups(['bom'])]
    public string $itemDescription;

    #[Groups(['bom'])]
    public string $itemOtherDescription;

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
