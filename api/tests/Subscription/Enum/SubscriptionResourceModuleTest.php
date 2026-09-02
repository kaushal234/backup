<?php

declare(strict_types=1);

namespace App\Tests\Subscription\Enum;

use App\Entity\Common\SubscriptionResourceModule;
use PHPUnit\Framework\TestCase;

class SubscriptionResourceModuleTest extends TestCase
{
    /**
     * @dataProvider provideKnownIris
     */
    public function testFromResourceIriReturnsExpectedEnum(string $iri, SubscriptionResourceModule $expected): void
    {
        self::assertSame($expected, SubscriptionResourceModule::fromResourceIri($iri));
    }

    public static function provideKnownIris(): iterable
    {
        yield 'demo' => ['/sales/demos/123', SubscriptionResourceModule::Demo];
        yield 'task' => ['/tasks/42', SubscriptionResourceModule::Task];
        yield 'meeting' => ['/minutes_of_meeting/meetings/7', SubscriptionResourceModule::Meeting];
        yield 'sales forecast' => ['/sales/sales_forecasts/9', SubscriptionResourceModule::SalesForecast];
        yield 'trouble ticket' => ['/mis/trouble_tickets/1', SubscriptionResourceModule::TroubleTicket];
        yield 'scar' => ['/quality/supplier_corrective_action_requests/3', SubscriptionResourceModule::Scar];
        yield 'contract' => ['/contracts/55', SubscriptionResourceModule::Contract];
        yield 'vwc plain' => ['/purchasing/vendor_warranty_claims/1', SubscriptionResourceModule::VendorWarrantyClaim];
        yield 'vwc ncr variant' => ['/purchasing/ncr_vendor_warranty_claims/1', SubscriptionResourceModule::VendorWarrantyClaim];
        yield 'vwc wc variant' => ['/purchasing/wc_vendor_warranty_claims/1', SubscriptionResourceModule::VendorWarrantyClaim];
    }

    public function testFromResourceIriReturnsNullForUnknownIri(): void
    {
        self::assertNull(SubscriptionResourceModule::fromResourceIri('/unknown/path/1'));
    }

    public function testFromResourceIriReturnsNullForEmptyString(): void
    {
        self::assertNull(SubscriptionResourceModule::fromResourceIri(''));
    }
}
