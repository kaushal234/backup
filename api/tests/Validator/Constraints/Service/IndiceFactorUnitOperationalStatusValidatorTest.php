<?php

declare(strict_types=1);

namespace App\Tests\Validator\Constraints\Service;

use App\Entity\IndiceFactor;
use App\Entity\Service\TechnicianOnCall;
use App\Entity\Support\UnitOperationalStatus;
use App\Validator\Constraints\Service\IndiceFactorUnitOperationalStatus;
use App\Validator\Constraints\Service\IndiceFactorUnitOperationalStatusValidator;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class IndiceFactorUnitOperationalStatusValidatorTest extends ConstraintValidatorTestCase
{
    /** @dataProvider dataProvider */
    public function testViolation($unitOperationalStatusValue, $indiceFactorValue, $expectViolation)
    {
        $unitOperationalStatus = new UnitOperationalStatus();
        $unitOperationalStatus->setName($unitOperationalStatusValue);

        $constraint = new IndiceFactorUnitOperationalStatus();

        $technicianOnCall = new TechnicianOnCall();
        $technicianOnCall->unitOperationalStatus = $unitOperationalStatus;
        $technicianOnCall->indiceFactor = $indiceFactorValue;

        $this->validator->validate($technicianOnCall, $constraint);

        if (!$expectViolation) {
            $this->assertNoViolation();
        } else {
            $this->buildViolation('toc.messages.errors.indice_factor')
                ->atPath('property.path.indiceFactor')
                ->assertRaised()
            ;
        }
    }

    public function dataProvider()
    {
        yield 'No violation with IF 1 and MCF' => ['MCF', IndiceFactor::IF_1->value, false];
        yield 'No violation with IF 10 and NMC' => ['NMC', IndiceFactor::IF_10->value, false];
        yield 'No violation with IF 10 and MCF' => ['MCF', IndiceFactor::IF_10->value, false];
        yield 'Violation with IF 1 and NMC' => ['NMC', IndiceFactor::IF_1->value, true];
    }

    protected function createValidator(): ConstraintValidatorInterface
    {
        return new IndiceFactorUnitOperationalStatusValidator();
    }
}
