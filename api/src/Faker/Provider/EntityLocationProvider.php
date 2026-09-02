<?php

declare(strict_types=1);

namespace App\Faker\Provider;

use App\Entity\Directory\LocationCapability;
use App\Entity\Directory\LocationContact;
use App\Entity\Directory\LocationState;
use Faker\Provider\Base;

class EntityLocationProvider extends Base
{
    public function entityLocationCapability($chanceOfGettingTrue = 50): LocationCapability
    {
        return (new LocationCapability())
            ->setSso($this->generator->boolean($chanceOfGettingTrue))
            ->setFactory($this->generator->boolean($chanceOfGettingTrue))
            ->setHeadQuarter($this->generator->boolean($chanceOfGettingTrue))
            ->setServiceHub($this->generator->boolean($chanceOfGettingTrue))
            ->setSparePartsHub($this->generator->boolean($chanceOfGettingTrue))
            ->setWarehouse($this->generator->boolean($chanceOfGettingTrue))
        ;
    }

    public function entityLocationSSO($chanceOfGettingTrue = 50): LocationCapability
    {
        return (new LocationCapability())
            ->setSso(true)
            ->setFactory($this->generator->boolean($chanceOfGettingTrue))
            ->setHeadQuarter($this->generator->boolean($chanceOfGettingTrue))
            ->setServiceHub($this->generator->boolean($chanceOfGettingTrue))
            ->setSparePartsHub($this->generator->boolean($chanceOfGettingTrue))
            ->setWarehouse($this->generator->boolean($chanceOfGettingTrue))
        ;
    }

    public function entityLocationSSOAndFactory($chanceOfGettingTrue = 50)
    {
        return (new LocationCapability())
            ->setSso(true)
            ->setFactory(true)
            ->setHeadQuarter($this->generator->boolean($chanceOfGettingTrue))
            ->setServiceHub($this->generator->boolean($chanceOfGettingTrue))
            ->setSparePartsHub($this->generator->boolean($chanceOfGettingTrue))
            ->setWarehouse($this->generator->boolean($chanceOfGettingTrue))
        ;
    }

    public function entityLocationFactory($chanceOfGettingTrue = 50)
    {
        return (new LocationCapability())
            ->setSso($this->generator->boolean($chanceOfGettingTrue))
            ->setFactory(true)
            ->setHeadQuarter($this->generator->boolean($chanceOfGettingTrue))
            ->setServiceHub($this->generator->boolean($chanceOfGettingTrue))
            ->setSparePartsHub($this->generator->boolean($chanceOfGettingTrue))
            ->setWarehouse($this->generator->boolean($chanceOfGettingTrue))
        ;
    }

    public function entityLocationSparePartHub($chanceOfGettingTrue = 50): LocationCapability
    {
        return (new LocationCapability())
            ->setSso($this->generator->boolean($chanceOfGettingTrue))
            ->setFactory($this->generator->boolean($chanceOfGettingTrue))
            ->setHeadQuarter($this->generator->boolean($chanceOfGettingTrue))
            ->setServiceHub($this->generator->boolean($chanceOfGettingTrue))
            ->setSparePartsHub(true)
            ->setWarehouse($this->generator->boolean($chanceOfGettingTrue))
        ;
    }

    public function entityLocationSparePartHubNotSso($chanceOfGettingTrue = 50): LocationCapability
    {
        return (new LocationCapability())
            ->setSso(false)
            ->setFactory($this->generator->boolean($chanceOfGettingTrue))
            ->setHeadQuarter($this->generator->boolean($chanceOfGettingTrue))
            ->setServiceHub($this->generator->boolean($chanceOfGettingTrue))
            ->setSparePartsHub(true)
            ->setWarehouse($this->generator->boolean($chanceOfGettingTrue))
        ;
    }

    public function entityLocationAllCapabilityButNotFactory(): LocationCapability
    {
        return (new LocationCapability())
            ->setSso(true)
            ->setFactory(false)
            ->setHeadQuarter(true)
            ->setServiceHub(true)
            ->setSparePartsHub(true)
            ->setWarehouse(true)
        ;
    }

    public function entityLocationServiceHub($chanceOfGettingTrue = 50): LocationCapability
    {
        return (new LocationCapability())
            ->setSso($this->generator->boolean($chanceOfGettingTrue))
            ->setFactory($this->generator->boolean($chanceOfGettingTrue))
            ->setHeadQuarter($this->generator->boolean($chanceOfGettingTrue))
            ->setServiceHub(true)
            ->setSparePartsHub($this->generator->boolean($chanceOfGettingTrue))
            ->setWarehouse($this->generator->boolean($chanceOfGettingTrue))
        ;
    }

    public function entityLocationWarehouse($chanceOfGettingTrue = 50): LocationCapability
    {
        return (new LocationCapability())
            ->setSso($this->generator->boolean($chanceOfGettingTrue))
            ->setFactory($this->generator->boolean($chanceOfGettingTrue))
            ->setHeadQuarter($this->generator->boolean($chanceOfGettingTrue))
            ->setServiceHub($this->generator->boolean($chanceOfGettingTrue))
            ->setSparePartsHub($this->generator->boolean($chanceOfGettingTrue))
            ->setWarehouse(true)
        ;
    }

    public function entityLocationWarehouseAndFactory($chanceOfGettingTrue = 50): LocationCapability
    {
        return (new LocationCapability())
            ->setSso($this->generator->boolean($chanceOfGettingTrue))
            ->setFactory($this->generator->boolean(true))
            ->setHeadQuarter($this->generator->boolean($chanceOfGettingTrue))
            ->setServiceHub($this->generator->boolean($chanceOfGettingTrue))
            ->setSparePartsHub($this->generator->boolean($chanceOfGettingTrue))
            ->setWarehouse(true)
        ;
    }

    public function entityLocationCustomCapability(array $capabilities = []): LocationCapability
    {
        return (new LocationCapability())
            ->setSso(\in_array('sso', $capabilities, true))
            ->setFactory(\in_array('factory', $capabilities, true))
            ->setWarehouse(\in_array('warehouse', $capabilities, true))
            ->setSparePartsHub(\in_array('sparePartsHub', $capabilities, true))
            ->setServiceHub(\in_array('serviceHub', $capabilities, true))
            ->setHeadQuarter(\in_array('headQuarter', $capabilities, true))
        ;
    }

    public function entityLocationContact(string $locationName = 'location')
    {
        return (new LocationContact())
            ->setFax(static::numerify('+33 1 ## ## ## ##'))
            ->setTelephone(static::numerify('+33 1 ## ## ## ##'))
            ->setServiceHubEmail(\sprintf('service-hub@%s.com', $locationName))
            ->setServiceHubTelephone(static::numerify('+33 1 ## ## ## ##'))
            ->setSparePartsEmail('parts@sph.fr')
            ->setPartsCustomerSupportEmail('partCustomerSupport@pcs.fr')
            ->setSparePartsFax(static::numerify('+33 1 ## ## ## ##'))
            ->setSparePartsTelephone(static::numerify('+33 1 ## ## ## ##'))
        ;
    }

    public function entityLocationState($chanceOfGettingTrue = 50): LocationState
    {
        return (new LocationState())
            ->setDisabled($this->generator->boolean($chanceOfGettingTrue))
            ->setHidden($this->generator->boolean($chanceOfGettingTrue))
            ->setPublic($this->generator->boolean($chanceOfGettingTrue))
        ;
    }
}
