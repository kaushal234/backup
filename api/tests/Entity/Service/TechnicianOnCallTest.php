<?php

declare(strict_types=1);

namespace App\Tests\Entity\Service;

use App\Entity\IndiceFactor;
use App\Entity\Service\TechnicianOnCall;
use App\Entity\Service\TechnicianOnCallDaysWithoutActivityStatus;
use App\Validator\Constraints\Service\IndiceFactorUnitOperationalStatus;
use PHPUnit\Framework\TestCase;

class TechnicianOnCallTest extends TestCase
{
    public function testHasValidator()
    {
        $technicianOnCallClass = new \ReflectionClass(TechnicianOnCall::class);
        $validatorAttribute = $technicianOnCallClass->getAttributes(IndiceFactorUnitOperationalStatus::class);

        self::assertCount(1, $validatorAttribute);
    }

    /** @dataProvider openDaysDataProvider */
    public function testGetOpenDaysStatus(TechnicianOnCall $technicianOnCall, string $expected)
    {
        $status = $technicianOnCall->getDaysWithoutActivityStatus();

        self::assertSame($expected, $status);
    }

    public function openDaysDataProvider()
    {
        // IF 1
        $technicianOnCall = new TechnicianOnCall();
        $technicianOnCall->updatedAt = new \DateTime('-13 days');
        $technicianOnCall->indiceFactor = IndiceFactor::IF_1->value;

        yield 'IF 1 on time' => [$technicianOnCall, TechnicianOnCallDaysWithoutActivityStatus::ON_TIME->name];

        $technicianOnCall = new TechnicianOnCall();
        $technicianOnCall->updatedAt = new \DateTime('-14 days');
        $technicianOnCall->indiceFactor = IndiceFactor::IF_1->value;

        yield 'IF 1 outdated' => [$technicianOnCall, TechnicianOnCallDaysWithoutActivityStatus::OUTDATED->name];

        // IF 10
        $technicianOnCall = new TechnicianOnCall();
        $technicianOnCall->updatedAt = new \DateTime('-5 days');
        $technicianOnCall->indiceFactor = IndiceFactor::IF_10->value;

        yield 'IF 10 on time' => [$technicianOnCall, TechnicianOnCallDaysWithoutActivityStatus::ON_TIME->name];

        $technicianOnCall = new TechnicianOnCall();
        $technicianOnCall->updatedAt = new \DateTime('-6 days');
        $technicianOnCall->indiceFactor = IndiceFactor::IF_10->value;

        yield 'IF 10 outdated' => [$technicianOnCall, TechnicianOnCallDaysWithoutActivityStatus::OUTDATED->name];

        // IF 100
        $technicianOnCall = new TechnicianOnCall();
        $technicianOnCall->updatedAt = new \DateTime('-1 days');
        $technicianOnCall->indiceFactor = IndiceFactor::IF_100->value;

        yield 'IF 100 on time' => [$technicianOnCall, TechnicianOnCallDaysWithoutActivityStatus::ON_TIME->name];

        $technicianOnCall = new TechnicianOnCall();
        $technicianOnCall->updatedAt = new \DateTime('-2 days');
        $technicianOnCall->indiceFactor = IndiceFactor::IF_100->value;

        yield 'IF 100 outdated' => [$technicianOnCall, TechnicianOnCallDaysWithoutActivityStatus::OUTDATED->name];

        // IF 1000
        $technicianOnCall = new TechnicianOnCall();
        $technicianOnCall->updatedAt = new \DateTime('-1 days');
        $technicianOnCall->indiceFactor = IndiceFactor::IF_1000->value;

        yield 'IF 1000 on time' => [$technicianOnCall, TechnicianOnCallDaysWithoutActivityStatus::ON_TIME->name];

        $technicianOnCall = new TechnicianOnCall();
        $technicianOnCall->updatedAt = new \DateTime('-2 days');
        $technicianOnCall->indiceFactor = IndiceFactor::IF_1000->value;

        yield 'IF 1000 outdated' => [$technicianOnCall, TechnicianOnCallDaysWithoutActivityStatus::OUTDATED->name];
    }
}
