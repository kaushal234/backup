<?php

declare(strict_types=1);

namespace App\SageParts\Models;

use App\SageParts\Models\Header\Header;
use App\SageParts\Models\Items\ItemInquiryRequest;
use Symfony\Component\Serializer\Annotation\SerializedName;

class PriceAndAvailability
{
    #[SerializedName('@payloadID')]
    private string $payloadID;

    #[SerializedName('@timestamp')]
    private string $timestamp;

    #[SerializedName('Header')]
    private Header $header;

    #[SerializedName('ItemInquiryRequest')]
    private ItemInquiryRequest $items;

    public function __construct()
    {
        $this->payloadID = '201305101513504024886@Lan.com';
        $this->timestamp = '2013-05-10T15:13:56-04:00';
    }

    public function getHeader(): Header
    {
        return $this->header;
    }

    public function setHeader(Header $header): self
    {
        $this->header = $header;

        return $this;
    }

    public function getPayloadID(): string
    {
        return $this->payloadID;
    }

    public function getTimestamp(): string
    {
        return $this->timestamp;
    }

    public function getItems(): ItemInquiryRequest
    {
        return $this->items;
    }

    public function setItems(ItemInquiryRequest $items): self
    {
        $this->items = $items;

        return $this;
    }
}
