<?php

declare(strict_types=1);

namespace App\Tests\AI\Factory\Engineering;

use App\AI\Factory\Directory\LocationModelFactory;
use App\AI\Factory\Directory\UserModelFactory;
use App\AI\Factory\Engineering\ProductInnovationProposalModelFactory;
use LegacyBundle\Entity\Directory\LocationById;
use LegacyBundle\Entity\Directory\PeopleById;
use LegacyBundle\Entity\Engineering\ProductInnovationProposal;
use PHPUnit\Framework\TestCase;

final class ProductInnovationProposalModelFactoryTest extends TestCase
{
    public function testSupports(): void
    {
        $factory = $this->makeFactory();

        self::assertTrue($factory->supports(ProductInnovationProposal::class));
        self::assertFalse($factory->supports(\stdClass::class));
    }

    public function testCreateMapsScalarFieldsWithMinimalEntity(): void
    {
        $entity = new ProductInnovationProposal();
        $entity->shortDescription = 'pip short';
        $entity->description = 'pip description';
        $entity->process = '';
        $entity->productType = '';
        $entity->model = '';
        $entity->status = '';
        $entity->importanceFactor = 0;
        $entity->submittedAt = new \DateTimeImmutable();

        $model = $this->makeFactory()->create($entity);

        self::assertSame('pip short', $model->shortDescription);
        self::assertSame('pip description', $model->description);
        self::assertNull($model->factory);
        self::assertNull($model->poster);
        self::assertNull($model->initiator);
    }

    public function testCreateMapsRelations(): void
    {
        $location = new LocationById();
        $location->name = 'LYON';
        $location->erp = 0;

        $poster = new PeopleById();
        $poster->firstname = 'Alice';
        $poster->lastname = 'X';
        $poster->username = 'alice';
        $poster->email = 'alice@x.test';

        $initiator = new PeopleById();
        $initiator->firstname = 'Bob';
        $initiator->lastname = 'X';
        $initiator->username = 'bob';
        $initiator->email = 'bob@x.test';

        $entity = new ProductInnovationProposal();
        $entity->shortDescription = '';
        $entity->description = '';
        $entity->process = '';
        $entity->productType = '';
        $entity->model = '';
        $entity->status = '';
        $entity->importanceFactor = 0;
        $entity->factory = $location;
        $entity->poster = $poster;
        $entity->initiator = $initiator;
        $entity->submittedAt = new \DateTimeImmutable('2025-02-01');

        $model = $this->makeFactory()->create($entity);

        self::assertNotNull($model->factory);
        self::assertSame('LYON', $model->factory->name);
        self::assertNotNull($model->poster);
        self::assertSame('Alice', $model->poster->firstname);
        self::assertNotNull($model->initiator);
        self::assertSame('Bob', $model->initiator->firstname);
        self::assertSame('2025-02-01', $model->submittedAt->format('Y-m-d'));
    }

    private function makeFactory(): ProductInnovationProposalModelFactory
    {
        return new ProductInnovationProposalModelFactory(new UserModelFactory(), new LocationModelFactory());
    }
}
