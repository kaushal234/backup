<?php

declare(strict_types=1);

namespace App\SageParts\Factory\Items;

use App\SageParts\Models\Items\ItemInquiryRequest;

class ItemInquiryRequestFactory
{
    private ItemFactory $itemFactory;

    public function __construct(ItemFactory $itemFactory)
    {
        $this->itemFactory = $itemFactory;
    }

    public function create(string $identifier): ItemInquiryRequest
    {
        $itemInquiryRequest = new ItemInquiryRequest();

        return $itemInquiryRequest
            ->setItems([$this->itemFactory->create($identifier)])
            ->setPriceItems('YES')
        ;
    }
}
