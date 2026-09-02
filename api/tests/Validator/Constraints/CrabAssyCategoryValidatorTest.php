<?php

declare(strict_types=1);

namespace App\Tests\Validator\Constraints;

use App\Entity\EquipmentRecord;
use App\Entity\Quality\Crab;
use App\Validator\Constraints\CrabAssyCategory;
use App\Validator\Constraints\CrabAssyCategoryValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

final class CrabAssyCategoryValidatorTest extends ConstraintValidatorTestCase
{
    private const MESSAGE = 'crab.errors.assy_category_error';
    private const PATH = 'property.path.category';

    public static function provideCrabAssyCategoryCases(): array
    {
        $now = new \DateTimeImmutable();

        return [
            'category not ASSY → no violation' => [
                'category' => 'OTHER',
                'withRecord' => false,
                'firstGreenDate' => null,
                'yellowTagDate' => null,
                'expectedViolation' => false,
            ],
            'ASSY without record → no violation' => [
                'category' => Crab::ASSY,
                'withRecord' => false,
                'firstGreenDate' => null,
                'yellowTagDate' => null,
                'expectedViolation' => false,
            ],
            'ASSY with record, no tags → no violation' => [
                'category' => Crab::ASSY,
                'withRecord' => true,
                'firstGreenDate' => null,
                'yellowTagDate' => null,
                'expectedViolation' => false,
            ],
            'ASSY with firstGreenTagDate → violation' => [
                'category' => Crab::ASSY,
                'withRecord' => true,
                'firstGreenDate' => $now,
                'yellowTagDate' => null,
                'expectedViolation' => true,
            ],
            'ASSY with yellowTagDate → violation' => [
                'category' => Crab::ASSY,
                'withRecord' => true,
                'firstGreenDate' => null,
                'yellowTagDate' => $now,
                'expectedViolation' => true,
            ],
        ];
    }

    /**
     * @dataProvider provideCrabAssyCategoryCases
     */
    public function testValidateUsingProvider(
        string $category,
        bool $withRecord,
        ?\DateTimeImmutable $firstGreenDate,
        ?\DateTimeImmutable $yellowTagDate,
        bool $expectedViolation
    ): void {
        $crab = $this->createCrab(
            category: $category,
            withRecord: $withRecord,
            firstGreenDate: $firstGreenDate,
            yellowTagDate: $yellowTagDate
        );

        $constraint = new CrabAssyCategory();
        $this->validator->validate($crab, $constraint);

        if (!$expectedViolation) {
            $this->assertNoViolation();

            return;
        }

        $this->buildViolation(self::MESSAGE)
            ->atPath(self::PATH)
            ->assertRaised();
    }

    protected function createValidator(): CrabAssyCategoryValidator
    {
        return new CrabAssyCategoryValidator();
    }

    private function createCrab(
        string $category,
        bool $withRecord,
        ?\DateTimeImmutable $firstGreenDate,
        ?\DateTimeImmutable $yellowTagDate
    ): Crab {
        $crab = new Crab();
        $crab->category = $category;

        if ($withRecord) {
            $record = new EquipmentRecord();
            $record->setFirstGreenTagDate($firstGreenDate);
            $record->setYellowTagDate($yellowTagDate);
            $crab->equipmentRecord = $record;
        } else {
            $crab->equipmentRecord = null;
        }

        return $crab;
    }
}
