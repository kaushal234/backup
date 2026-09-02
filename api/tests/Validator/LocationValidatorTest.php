<?php

declare(strict_types=1);

namespace App\Tests\Validator;

use App\Entity\Directory\Location;
use App\Validator\Constraints\Location as ValidLocation;
use App\Validator\Constraints\LocationValidator;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class LocationValidatorTest extends ConstraintValidatorTestCase
{
    /**
     * @dataProvider provideLocations
     */
    public function testThatLocationCapabilitiesAreValidated(Location $location, ValidLocation $constraint, ?string $capability = null)
    {
        $this->validator->validate($location, $constraint);

        if (null !== $capability) {
            $this->buildViolation('This TLD location is not a {{ capability }}.')
                ->setParameter('{{ capability }}', $capability)
                ->atPath('property.path')
                ->assertRaised();
        } else {
            $this->assertNoViolation();
        }
    }

    public function provideLocations()
    {
        $location = new Location();

        $constraint = new ValidLocation(sso: true);

        yield 'SSO invalid' => [$location, $constraint, 'SSO'];

        $location = new Location();
        $location->getCapability()->setSso(true);

        yield 'SSO valid' => [$location, $constraint];

        $location = new Location();

        $constraint = new ValidLocation(factory: true);

        yield 'Factory invalid' => [$location, $constraint, 'Factory'];

        $location = new Location();
        $location->getCapability()->setFactory(true);

        yield 'Factory valid' => [$location, $constraint];

        $location = new Location();

        $constraint = new ValidLocation(serviceHub: true);

        yield 'Service Hub invalid' => [$location, $constraint, 'Service Hub'];

        $location = new Location();
        $location->getCapability()->setServiceHub(true);

        yield 'Service Hub valid' => [$location, $constraint];

        $location = new Location();

        $constraint = new ValidLocation(sparePartsHub: true);

        yield 'Spare Parts Hub invalid' => [$location, $constraint, 'Spare Parts Hub'];

        $location = new Location();
        $location->getCapability()->setSparePartsHub(true);

        yield 'Spare Parts Hub valid' => [$location, $constraint];

        $location = new Location();

        $constraint = new ValidLocation(warehouse: true);

        yield 'Warehouse invalid' => [$location, $constraint, 'Warehouse'];

        $location = new Location();
        $location->getCapability()->setWarehouse(true);

        yield 'Warehouse valid' => [$location, $constraint];
    }

    public function testThatLocationErpIsValidated()
    {
        $location = new Location();
        $location->setErpInLN(false);

        $constraint = new ValidLocation(erpInLN: true);

        $this->validator->validate($location, $constraint);

        $this->buildViolation('This location ERP is not in Baan.')
            ->atPath('property.path')
            ->assertRaised();
    }

    protected function createValidator(): ConstraintValidatorInterface
    {
        return new LocationValidator();
    }
}
