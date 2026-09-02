<?php

declare(strict_types=1);

namespace App\Faker\Provider;

use App\Entity\BaanAddress;
use Faker\Provider\Base;

class EntityBaanAddressProvider extends Base
{
    public function entityBaanAddress($countries = ['FR', 'US', 'CN'], $className = BaanAddress::class)
    {
        return (new $className())
            ->setName($this->generator->name())
            ->setAddress($this->generator->address())
            ->setAddressExtra($this->generator->address())
            ->setCity($this->generator->city())
            ->setCityExtra($this->generator->city())
            ->setCountry($this->generator->randomElement($countries))
        ;
    }
}
