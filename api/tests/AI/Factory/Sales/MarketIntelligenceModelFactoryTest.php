<?php

declare(strict_types=1);

namespace App\Tests\AI\Factory\Sales;

use App\AI\Factory\Directory\UserModelFactory;
use App\AI\Factory\Sales\MarketIntelligenceModelFactory;
use App\Entity\Directory\Division;
use App\Entity\Directory\People;
use App\Entity\Sales\Competitor;
use App\Entity\Sales\Customer;
use App\Entity\Sales\MarketIntelligence\MarketIntelligence;
use App\Entity\Sales\MarketIntelligence\MarketIntelligenceType;
use App\Entity\Sales\ProductType;
use PHPUnit\Framework\TestCase;

final class MarketIntelligenceModelFactoryTest extends TestCase
{
    public function testSupports(): void
    {
        $factory = $this->makeFactory();

        self::assertTrue($factory->supports(MarketIntelligence::class));
        self::assertFalse($factory->supports(\stdClass::class));
    }

    public function testCreateMapsScalarFieldsWithMinimalEntity(): void
    {
        $entity = $this->createMock(MarketIntelligence::class);
        $entity->method('getCreatedAt')->willReturn(new \DateTime('2025-01-01'));
        $entity->method('getShortDescription')->willReturn('short');
        $entity->method('getDescription')->willReturn('long');
        $entity->method('getUrl')->willReturn(null);
        $entity->method('getType')->willReturn(null);
        $entity->method('getPoster')->willReturn(null);
        $entity->method('getCustomers')->willReturn(new \Doctrine\Common\Collections\ArrayCollection());
        $entity->method('getCompetitors')->willReturn(new \Doctrine\Common\Collections\ArrayCollection());
        $entity->method('getProductTypes')->willReturn(new \Doctrine\Common\Collections\ArrayCollection());
        $entity->method('getDivisions')->willReturn(new \Doctrine\Common\Collections\ArrayCollection());
        $entity->method('getSuppliers')->willReturn([]);

        $model = $this->makeFactory()->create($entity);

        self::assertSame('short', $model->shortDescription);
        self::assertSame('long', $model->description);
        self::assertNull($model->url);
        self::assertNull($model->type);
        self::assertNull($model->poster);
        self::assertSame([], $model->customers);
        self::assertSame([], $model->competitors);
        self::assertSame([], $model->productTypes);
        self::assertSame([], $model->divisions);
        self::assertSame([], $model->suppliers);
    }

    public function testCreateMapsRelationsAndCollections(): void
    {
        $type = new MarketIntelligenceType();
        $type->name = 'COMPETITOR';

        $poster = $this->createMock(People::class);
        $poster->method('getUsername')->willReturn('alice');
        $poster->method('getEmail')->willReturn('alice@x.test');
        $poster->method('getFirstname')->willReturn('Alice');
        $poster->method('getLastname')->willReturn('X');

        $customer = $this->createMock(Customer::class);
        $customer->method('getName')->willReturn('Acme');
        $customer->method('getStatus')->willReturn('ACTIVE');

        $competitor = $this->createMock(Competitor::class);
        $competitor->method('getName')->willReturn('Rival');
        $competitor->method('getShortDescription')->willReturn('a rival');
        $competitor->method('getUrl')->willReturn('https://rival.test');

        $productType = $this->createMock(ProductType::class);
        $productType->method('getEnglishName')->willReturn('GSE');

        $division = new Division();
        $division->name = 'EMEA';

        $entity = $this->createMock(MarketIntelligence::class);
        $entity->method('getCreatedAt')->willReturn(new \DateTime('2025-01-01'));
        $entity->method('getShortDescription')->willReturn('short');
        $entity->method('getDescription')->willReturn('long');
        $entity->method('getUrl')->willReturn('https://news.test');
        $entity->method('getType')->willReturn($type);
        $entity->method('getPoster')->willReturn($poster);
        $entity->method('getCustomers')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$customer]));
        $entity->method('getCompetitors')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$competitor]));
        $entity->method('getProductTypes')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$productType]));
        $entity->method('getDivisions')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$division]));
        $entity->method('getSuppliers')->willReturn(['SUP-1']);

        $model = $this->makeFactory()->create($entity);

        self::assertNotNull($model->type);
        self::assertSame('COMPETITOR', $model->type->name);
        self::assertNotNull($model->poster);
        self::assertSame('alice', $model->poster->username);
        self::assertCount(1, $model->customers);
        self::assertSame('Acme', $model->customers[0]->name);
        self::assertCount(1, $model->competitors);
        self::assertSame('Rival', $model->competitors[0]->name);
        self::assertCount(1, $model->productTypes);
        self::assertSame('GSE', $model->productTypes[0]->name);
        self::assertCount(1, $model->divisions);
        self::assertSame('EMEA', $model->divisions[0]->name);
        self::assertSame(['SUP-1'], $model->suppliers);
    }

    private function makeFactory(): MarketIntelligenceModelFactory
    {
        return new MarketIntelligenceModelFactory(new UserModelFactory());
    }
}
