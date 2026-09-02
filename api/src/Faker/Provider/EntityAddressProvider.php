<?php

declare(strict_types=1);

namespace App\Faker\Provider;

use App\Entity\Address;
use App\Entity\AddressWithCountry;
use Faker\Provider\Base;

class EntityAddressProvider extends Base
{
    public function entityAddress($countries = ['FR', 'US', 'GB'])
    {
        return (new AddressWithCountry())
            ->setStreet1($this->generator->streetAddress())
            ->setStreet2($this->generator->streetAddress())
            ->setPostalCode($this->generator->postcode())
            ->setCity($this->generator->city())
            ->setCountry($this->generator->randomElement($countries))
        ;
    }

    public function entityAddressCustom(string $street1, string $street2, string $postalCode, string $city, string $country)
    {
        return (new AddressWithCountry())
            ->setStreet1($street1)
            ->setStreet2($street2)
            ->setPostalCode($postalCode)
            ->setCity($city)
            ->setCountry($country)
        ;
    }

    public function entityAddressWithoutCountry()
    {
        return (new Address())
            ->setStreet1($this->generator->streetAddress())
            ->setPostalCode($this->generator->postcode())
            ->setCity($this->generator->city())
        ;
    }
}
