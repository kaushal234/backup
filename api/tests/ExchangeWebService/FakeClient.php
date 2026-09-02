<?php

declare(strict_types=1);

namespace App\Tests\ExchangeWebService;

use jamesiarmes\PhpEws\ArrayType\ArrayOfRealItemsType;
use jamesiarmes\PhpEws\ArrayType\ArrayOfResponseMessagesType;
use jamesiarmes\PhpEws\Client;
use jamesiarmes\PhpEws\Enumeration\ResponseClassType;
use jamesiarmes\PhpEws\Response\CreateItemResponseType;
use jamesiarmes\PhpEws\Response\ItemInfoResponseMessageType;
use jamesiarmes\PhpEws\Type\CalendarItemType;
use jamesiarmes\PhpEws\Type\ItemIdType;

class FakeClient extends Client
{
    private array $calls = [];

    public function __call($method, $arguments)
    {
        if (!\array_key_exists($method, $this->calls)) {
            $this->calls[$method] = [];
        }

        $this->calls[$method][] = $arguments;
    }

    public function getMethodCalls(string $method): array
    {
        return $this->calls[$method] ?? [];
    }

    public function CreateItem($request): CreateItemResponseType
    {
        $this->calls[__FUNCTION__][] = $request;

        return $this->getFakeResponse();
    }

    private function getFakeResponse(): CreateItemResponseType
    {
        $itemId = new ItemIdType();
        $itemId->ChangeKey = 'changeKey';
        $itemId->Id = 'heidi';

        $calendarItem = new CalendarItemType();
        $calendarItem->ItemId = $itemId;

        $items = new ArrayOfRealItemsType();
        $items->CalendarItem = [$calendarItem];

        $itemInfo = new ItemInfoResponseMessageType();
        $itemInfo->ResponseClass = ResponseClassType::SUCCESS;
        $itemInfo->Items = $items;

        $arrayOfResponseType = new ArrayOfResponseMessagesType();
        $arrayOfResponseType->CreateItemResponseMessage = [$itemInfo];

        $response = new CreateItemResponseType();
        $response->ResponseMessages = $arrayOfResponseType;

        return $response;
    }
}
