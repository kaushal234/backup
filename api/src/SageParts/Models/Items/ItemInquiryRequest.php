<?php

declare(strict_types=1);

namespace App\SageParts\Models\Items;

use Symfony\Component\Serializer\Annotation\SerializedName;

class ItemInquiryRequest
{
    /**
     * @var array<Item>
     */
    #[SerializedName('Item')]
    private array $items;

    #[SerializedName('@PriceItems')]
    private string $priceItems;

    public function getItems(): array
    {
        return $this->items;
    }

    public function setItems(array $items): self
    {
        $this->items = $items;

        return $this;
    }

    public function getPriceItems(): string
    {
        return $this->priceItems;
    }

    public function setPriceItems(string $priceItems): self
    {
        $this->priceItems = $priceItems;

        return $this;
    }
}
