<?php

declare(strict_types=1);

namespace App\Tests\Entity\People;

use App\Entity\Common\Airport;
use App\Entity\Directory\People;
use App\Entity\Directory\Premise;
use PHPUnit\Framework\TestCase;

class PeopleTest extends TestCase
{
    /** @dataProvider dataProvider */
    public function testPeopleAirport($premiseAirport, $premiseLatitude, $premiseLongitude, $closestAirport, $latitude, $longitude, $expected): void
    {
        $people = new People();
        $premise = new Premise();
        $people->setPremise($premise);

        if ($premiseAirport) {
            $premiseAirport->setLatitude($premiseLatitude);
            $premiseAirport->setLongitude($premiseLongitude);
            $premise->airport = $premiseAirport;
            $premise->airport->hasCoordinate();
        }

        if ($closestAirport) {
            $closestAirport->setLatitude($latitude);
            $closestAirport->setLongitude($longitude);
            $people->closestAirport = $closestAirport;
            $people->closestAirport->hasCoordinate();
        }

        $this->assertSame($expected, $people->getAirport());
    }

    public function dataProvider()
    {
        $premiseAirport = new Airport();
        $closestAirport = new Airport();
        yield 'airport premise valid' => [$premiseAirport, 1, 1, $closestAirport, 1, 1, $premiseAirport];
        yield 'airport premise valid and closestAirport non valid' => [$premiseAirport, 1, 1, $closestAirport, null, null, $premiseAirport];
        yield 'airport premise non valid but closestAirport valid' => [$premiseAirport, null, null, $closestAirport, 1, 1, $closestAirport];
        yield 'airport premise non valid and closestAirport non valid' => [$premiseAirport, null, null, $closestAirport, null, null, null];
        yield 'airport premise null but closestAirport valid' => [null, 1, 1, $closestAirport, 1, 1, $closestAirport];
        yield 'airport premise null and closestAirport non valid' => [null, 1, 1, $closestAirport, null, null, null];
        yield 'airport premise null and closestAirport null' => [null, null, null, null, null, null, null];
    }
}
