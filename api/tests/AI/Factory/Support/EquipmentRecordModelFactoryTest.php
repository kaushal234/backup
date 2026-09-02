<?php

declare(strict_types=1);

namespace App\Tests\AI\Factory\Support;

use App\AI\Factory\Directory\LocationModelFactory;
use App\AI\Factory\Support\EquipmentRecordModelFactory;
use App\Entity\Common\Airport;
use App\Entity\Country;
use App\Entity\Directory\Location;
use App\Entity\EmissionRating;
use App\Entity\EquipmentRecord;
use App\Entity\Sales\Customer;
use App\Entity\Sales\Product;
use App\Entity\Sales\ProductFamily;
use App\Entity\Sales\ProductType;
use PHPUnit\Framework\TestCase;

final class EquipmentRecordModelFactoryTest extends TestCase
{
    public function testSupports(): void
    {
        $factory = $this->makeFactory();

        self::assertTrue($factory->supports(EquipmentRecord::class));
        self::assertFalse($factory->supports(\stdClass::class));
    }

    public function testCreateMapsScalarFieldsWithMinimalEntity(): void
    {
        $entity = $this->createMock(EquipmentRecord::class);

        $entity->method('getId')->willReturn(42);
        $entity->method('getLegacyId')->willReturn(100);
        $entity->method('getSerialNumber')->willReturn('SN-123');
        $entity->method('getModel')->willReturn('Model-A');
        $entity->method('getType')->willReturn('Type-B');
        $entity->method('getWorkOrder')->willReturn('WO-1');
        $entity->method('getProjectNumber')->willReturn('PRJ-1');
        $entity->method('getState')->willReturn('delivered');

        $entity->method('getBuyer')->willReturn(null);
        $entity->method('getEndUser')->willReturn(null);
        $entity->method('getMaintainer')->willReturn(null);
        $entity->method('getProduct')->willReturn(null);
        $entity->method('getManufacturerLocation')->willReturn(null);
        $entity->method('getSalesOrganisation')->willReturn(null);
        $entity->method('getSalesOrganisationService')->willReturn(null);
        $entity->method('getAirport')->willReturn(null);
        $entity->method('getDeliveredCountry')->willReturn(null);
        $entity->method('getEmissionRating')->willReturn(null);
        $entity->method('getDateShipped')->willReturn(null);
        $entity->method('getOptionsDescription')->willReturn(null);

        $model = $this->makeFactory()->create($entity);

        self::assertSame(42, $model->id);
        self::assertSame(100, $model->legacyId);
        self::assertSame('SN-123', $model->serialNumber);
        self::assertSame('Model-A', $model->model);
        self::assertSame('Type-B', $model->type);
        self::assertSame('WO-1', $model->mainWorkOrder);
        self::assertSame('PRJ-1', $model->manufacturingProject);
        self::assertSame('delivered', $model->state);

        self::assertNull($model->buyer);
        self::assertNull($model->endUser);
        self::assertNull($model->maintainer);
        self::assertNull($model->product);
        self::assertNull($model->productType);
        self::assertNull($model->manufacturerLocation);
        self::assertNull($model->salesOrganisation);
        self::assertNull($model->salesOrganisationService);
        self::assertNull($model->airport);
        self::assertNull($model->country);
        self::assertNull($model->emissionRating);
        self::assertNull($model->dateShipped);
        self::assertNull($model->optionsDescription);
    }

    public function testCreateMapsRelations(): void
    {
        $buyer = $this->createMock(Customer::class);
        $buyer->method('getName')->willReturn('Buyer Inc.');
        $buyer->method('getStatus')->willReturn('ACTIVE');

        $endUser = $this->createMock(Customer::class);
        $endUser->method('getName')->willReturn('End User Ltd.');
        $endUser->method('getStatus')->willReturn('ACTIVE');

        $maintainer = $this->createMock(Customer::class);
        $maintainer->method('getName')->willReturn('Maintainer Co.');
        $maintainer->method('getStatus')->willReturn('ACTIVE');

        $productType = $this->createMock(ProductType::class);
        $productType->method('getEnglishName')->willReturn('Belt Loader');

        $family = $this->createMock(ProductFamily::class);
        $family->method('getName')->willReturn('Ground Support');
        $family->method('getProductType')->willReturn($productType);

        $product = $this->createMock(Product::class);
        $product->method('getName')->willReturn('TBL-180');
        $product->method('getFamily')->willReturn($family);

        $manufacturerLocation = $this->createMock(Location::class);
        $manufacturerLocation->method('getName')->willReturn('Factory FR');

        $salesOrganisation = $this->createMock(Location::class);
        $salesOrganisation->method('getName')->willReturn('Sales Org');

        $salesOrganisationService = $this->createMock(Location::class);
        $salesOrganisationService->method('getName')->willReturn('SSO Service');

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

        $country = $this->createMock(Country::class);
        $country->method('getName')->willReturn('Germany');

        $emissionRating = $this->createMock(EmissionRating::class);
        $emissionRating->method('getName')->willReturn('Stage V');

        $entity = $this->createMock(EquipmentRecord::class);
        $entity->method('getId')->willReturn(7);
        $entity->method('getLegacyId')->willReturn(77);
        $entity->method('getSerialNumber')->willReturn('SN-999');
        $entity->method('getModel')->willReturn(null);
        $entity->method('getType')->willReturn(null);
        $entity->method('getWorkOrder')->willReturn(null);
        $entity->method('getProjectNumber')->willReturn(null);
        $entity->method('getState')->willReturn('shipped');
        $entity->method('getBuyer')->willReturn($buyer);
        $entity->method('getEndUser')->willReturn($endUser);
        $entity->method('getMaintainer')->willReturn($maintainer);
        $entity->method('getProduct')->willReturn($product);
        $entity->method('getManufacturerLocation')->willReturn($manufacturerLocation);
        $entity->method('getSalesOrganisation')->willReturn($salesOrganisation);
        $entity->method('getSalesOrganisationService')->willReturn($salesOrganisationService);
        $entity->method('getAirport')->willReturn($airport);
        $entity->method('getDeliveredCountry')->willReturn($country);
        $entity->method('getEmissionRating')->willReturn($emissionRating);
        $dateShipped = new \DateTimeImmutable('2026-01-15');
        $entity->method('getDateShipped')->willReturn($dateShipped);
        $entity->method('getOptionsDescription')->willReturn('GPU 28V + hydraulic hose reel');

        $model = $this->makeFactory()->create($entity);

        self::assertSame(7, $model->id);
        self::assertSame(77, $model->legacyId);
        self::assertSame($dateShipped, $model->dateShipped);
        self::assertSame('GPU 28V + hydraulic hose reel', $model->optionsDescription);

        self::assertNotNull($model->buyer);
        self::assertSame('Buyer Inc.', $model->buyer->name);
        self::assertNotNull($model->endUser);
        self::assertSame('End User Ltd.', $model->endUser->name);
        self::assertNotNull($model->maintainer);
        self::assertSame('Maintainer Co.', $model->maintainer->name);

        self::assertNotNull($model->product);
        self::assertSame('TBL-180', $model->product->name);
        self::assertSame('Ground Support', $model->product->family);

        self::assertNotNull($model->productType);
        self::assertSame('Belt Loader', $model->productType->name);

        self::assertNotNull($model->manufacturerLocation);
        self::assertSame('Factory FR', $model->manufacturerLocation->name);
        self::assertNotNull($model->salesOrganisation);
        self::assertSame('Sales Org', $model->salesOrganisation->name);
        self::assertNotNull($model->salesOrganisationService);
        self::assertSame('SSO Service', $model->salesOrganisationService->name);

        self::assertNotNull($model->airport);
        self::assertSame('CDG', $model->airport->code);

        self::assertNotNull($model->country);
        self::assertSame('Germany', $model->country->name);

        self::assertNotNull($model->emissionRating);
        self::assertSame('Stage V', $model->emissionRating->name);
    }

    private function makeFactory(): EquipmentRecordModelFactory
    {
        return new EquipmentRecordModelFactory(new LocationModelFactory());
    }
}
