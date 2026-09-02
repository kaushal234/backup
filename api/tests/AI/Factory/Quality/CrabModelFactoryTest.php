<?php

declare(strict_types=1);

namespace App\Tests\AI\Factory\Quality;

use App\AI\Factory\Directory\LocationModelFactory;
use App\AI\Factory\Directory\UserModelFactory;
use App\AI\Factory\Quality\CrabModelFactory;
use App\AI\Factory\Support\EquipmentRecordModelFactory;
use App\Entity\Quality\Crab;
use App\Entity\Quality\CrabCode;
use App\Entity\Quality\CrabDepartment;
use PHPUnit\Framework\TestCase;

final class CrabModelFactoryTest extends TestCase
{
    public function testSupports(): void
    {
        $factory = $this->makeFactory();

        self::assertTrue($factory->supports(Crab::class));
        self::assertFalse($factory->supports(\stdClass::class));
    }

    public function testCreateMapsScalarFieldsWithMinimalEntity(): void
    {
        $entity = $this->makeEntity();
        $entity->description = 'broken bracket';
        $entity->category = 'MECH';

        $model = $this->makeFactory()->create($entity);

        self::assertSame(Crab::TO_FIX, $model->status);
        self::assertSame('broken bracket', $model->description);
        self::assertSame('MECH', $model->category);
        self::assertSame('ASSEMBLY', $model->department->name);
        self::assertNull($model->code);
        self::assertNull($model->createdBy);
        self::assertNull($model->fixedBy);
        self::assertNull($model->inspectedBy);
        self::assertNull($model->equipmentRecord);
        self::assertNull($model->nonConformity);
        self::assertNull($model->derogation);
        self::assertNull($model->part);
    }

    public function testCreateAllowsNullableScalars(): void
    {
        $entity = $this->makeEntity();
        $entity->fixingComments = null;
        $entity->inspectingComments = null;
        $entity->eapId = null;
        $entity->piQuestionId = null;
        $entity->piQuestionType = null;

        $model = $this->makeFactory()->create($entity);

        self::assertNull($model->fixingComments);
        self::assertNull($model->inspectingComments);
        self::assertNull($model->eapId);
        self::assertNull($model->piQuestionId);
        self::assertNull($model->piQuestionType);
    }

    public function testCreateMapsCodeAndDepartment(): void
    {
        $code = new CrabCode();
        $code->code = 42;
        $code->description = 'a code';

        $entity = $this->makeEntity();
        $entity->code = $code;
        $entity->department->name = 'PAINT';

        $model = $this->makeFactory()->create($entity);

        self::assertSame('PAINT', $model->department->name);
        self::assertNotNull($model->code);
        self::assertSame(42, $model->code->code);
        self::assertSame('a code', $model->code->description);
    }

    private function makeFactory(): CrabModelFactory
    {
        return new CrabModelFactory(
            new UserModelFactory(),
            new EquipmentRecordModelFactory(new LocationModelFactory()),
        );
    }

    private function makeEntity(): Crab
    {
        $entity = new Crab();
        $entity->createdAt = new \DateTime('2025-01-01');
        $entity->description = '';
        $entity->category = '';
        $entity->department = new CrabDepartment();
        $entity->department->name = 'ASSEMBLY';
        $entity->equipmentRecord = null;

        return $entity;
    }
}
