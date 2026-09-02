<?php

declare(strict_types=1);

namespace App\SageParts\Factory;

use App\SageParts\Factory\Header\HeaderFactory;
use App\SageParts\Factory\Items\ItemInquiryRequestFactory;
use App\SageParts\Models\PriceAndAvailability;

class PriceAndAvailabilityFactory
{
    private HeaderFactory $headerFactory;
    private ItemInquiryRequestFactory $itemInquiryRequestFactory;

    public function __construct(HeaderFactory $headerFactory, ItemInquiryRequestFactory $itemInquiryRequestFactory)
    {
        $this->headerFactory = $headerFactory;
        $this->itemInquiryRequestFactory = $itemInquiryRequestFactory;
    }

    public function create(string $identifier): PriceAndAvailability
    {
        $priceAndAvailability = new PriceAndAvailability();

        $priceAndAvailability
            ->setHeader($this->headerFactory->create())
            ->setItems($this->itemInquiryRequestFactory->create($identifier))
        ;

        return $priceAndAvailability;
    }
}
