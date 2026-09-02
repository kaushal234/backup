<?php

declare(strict_types=1);

namespace App\Tests\AI\Factory\Sales;

use App\AI\Factory\Directory\LocationModelFactory;
use App\AI\Factory\Directory\UserModelFactory;
use App\AI\Factory\Sales\SalesForecastModelFactory;
use App\Entity\Common\Airport;
use App\Entity\Country;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\EmissionRating;
use App\Entity\Sales\Customer;
use App\Entity\Sales\Product;
use App\Entity\Sales\ProductFamily;
use App\Entity\Sales\Quote;
use App\Entity\Sales\SalesForecast;
use PHPUnit\Framework\TestCase;

final class SalesForecastModelFactoryTest extends TestCase
{
    public function testSupports(): void
    {
        $factory = $this->makeFactory();

        self::assertTrue($factory->supports(SalesForecast::class));
        self::assertFalse($factory->supports(\stdClass::class));
    }

    public function testCreateMapsScalarFieldsWithMinimalEntity(): void
    {
        $entity = $this->createMock(SalesForecast::class);

        $entity->method('getStatus')->willReturn('OPEN');
        $entity->method('getCreatedAt')->willReturn(new \DateTimeImmutable('2025-01-01'));
        $entity->method('getUpdatedAt')->willReturn(new \DateTimeImmutable('2025-01-01'));
        $entity->method('getLastCommentedAt')->willReturn(null);
        $entity->method('getClosedAt')->willReturn(null);
        $entity->method('getEquoteId')->willReturn('EQ-1');
        $entity->method('getQuantity')->willReturn(10);
        $entity->method('getEstimatedSaleDate')->willReturn(new \DateTimeImmutable('2025-01-01'));
        $entity->method('getCustomerSuccessPercentage')->willReturn(50);
        $entity->method('getSuccessPercentage')->willReturn(60);
        $entity->method('isDelinquent')->willReturn(false);
        $entity->method('getPrice')->willReturn(1000);
        $entity->method('getMargin')->willReturn(25.5);

        $entity->method('getSso')->willReturn($this->createMock(Location::class));
        $entity->method('getFactory')->willReturn($this->createMock(Location::class));
        $entity->method('getAsm')->willReturn($this->createMock(People::class));
        $entity->method('getPoster')->willReturn($this->createMock(People::class));

        $entity->method('getBuyer')->willReturn(null);
        $entity->method('getEndUser')->willReturn(null);
        $entity->method('getThirdParty')->willReturn(null);
        $entity->method('getCountry')->willReturn(null);
        $entity->method('getAirport')->willReturn(null);
        $entity->method('getProduct')->willReturn(null);
        $entity->method('getTier')->willReturn(null);
        $entity->method('getQuote')->willReturn(null);

        $model = $this->makeFactory()->create($entity);

        self::assertSame('OPEN', $model->status);
        self::assertSame('EQ-1', $model->equoteId);
        self::assertSame(10, $model->quantity);
        self::assertSame(50, $model->customerSuccessPercentage);
        self::assertSame(60, $model->successPercentage);
        self::assertFalse($model->delinquent);
        self::assertSame(1000, $model->price);
        self::assertSame(25.5, $model->margin);

        self::assertNull($model->buyer);
        self::assertNull($model->endUser);
        self::assertNull($model->thirdParty);
        self::assertNull($model->country);
        self::assertNull($model->airport);
        self::assertNull($model->product);
        self::assertNull($model->tier);
        self::assertNull($model->quote);
    }

    public function testCreateMapsRelationsAndCollections(): void
    {
        $buyer = $this->createMock(Customer::class);
        $buyer->method('getName')->willReturn('Buyer');
        $buyer->method('getStatus')->willReturn('ACTIVE');

        $country = $this->createMock(Country::class);
        $country->method('getName')->willReturn('France');

        $airportCountry = $this->createMock(Country::class);
        $airportCountry->method('getName')->willReturn('France');

        $airport = $this->createMock(Airport::class);
        $airport->method('getCode')->willReturn('CDG');
        $airport->method('getType')->willReturn('INTERNATIONAL');
        $airport->method('getCityCode3')->willReturn('PAR');
        $airport->method('getCityName')->willReturn('Paris');
        $airport->method('getState')->willReturn(null);
        $airport->method('getCountry')->willReturn($airportCountry);
        $airport->method('getName')->willReturn('Charles de Gaulle');
        $airport->method('getSource')->willReturn('IATA');
        $airport->method('getLatitude')->willReturn(49.0097);
        $airport->method('getLongitude')->willReturn(2.5479);

        $family = $this->createMock(ProductFamily::class);
        $family->method('getName')->willReturn('Cabin');

        $product = $this->createMock(Product::class);
        $product->method('getName')->willReturn('Seat');
        $product->method('getFamily')->willReturn($family);

        $tier = $this->createMock(EmissionRating::class);
        $tier->method('getName')->willReturn('Tier 1');

        $quote = new Quote();
        $quote->quoteNumber = 'Q-001';

        $entity = $this->createMock(SalesForecast::class);

        $entity->method('getStatus')->willReturn('WON');
        $entity->method('getCreatedAt')->willReturn(new \DateTimeImmutable('2025-01-01'));
        $entity->method('getUpdatedAt')->willReturn(new \DateTimeImmutable('2025-01-01'));
        $entity->method('getLastCommentedAt')->willReturn(null);
        $entity->method('getClosedAt')->willReturn(null);
        $entity->method('getEquoteId')->willReturn('EQ-9');
        $entity->method('getQuantity')->willReturn(1);
        $entity->method('getEstimatedSaleDate')->willReturn(new \DateTimeImmutable('2025-01-01'));
        $entity->method('getCustomerSuccessPercentage')->willReturn(90);
        $entity->method('getSuccessPercentage')->willReturn(95);
        $entity->method('isDelinquent')->willReturn(false);
        $entity->method('getPrice')->willReturn(5000);
        $entity->method('getMargin')->willReturn(35.0);

        $entity->method('getSso')->willReturn($this->createMock(Location::class));
        $entity->method('getFactory')->willReturn($this->createMock(Location::class));
        $entity->method('getAsm')->willReturn($this->createMock(People::class));
        $entity->method('getPoster')->willReturn($this->createMock(People::class));

        $entity->method('getBuyer')->willReturn($buyer);
        $entity->method('getEndUser')->willReturn(null);
        $entity->method('getThirdParty')->willReturn(null);
        $entity->method('getCountry')->willReturn($country);
        $entity->method('getAirport')->willReturn($airport);
        $entity->method('getProduct')->willReturn($product);
        $entity->method('getTier')->willReturn($tier);
        $entity->method('getQuote')->willReturn($quote);

        $model = $this->makeFactory()->create($entity);

        self::assertNotNull($model->buyer);
        self::assertSame('Buyer', $model->buyer->name);

        self::assertSame('France', $model->country);

        self::assertNotNull($model->airport);
        self::assertSame('CDG', $model->airport->code);
        self::assertSame('Paris', $model->airport->cityName);

        self::assertNotNull($model->product);
        self::assertSame('Seat', $model->product->name);
        self::assertSame('Cabin', $model->product->family);

        self::assertSame('Tier 1', $model->tier);

        self::assertNotNull($model->quote);
        self::assertSame('Q-001', $model->quote->quoteNumber);
    }

    private function makeFactory(): SalesForecastModelFactory
    {
        return new SalesForecastModelFactory(
            new UserModelFactory(),
            new LocationModelFactory(),
        );
    }
}
