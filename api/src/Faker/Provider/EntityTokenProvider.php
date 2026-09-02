<?php

declare(strict_types=1);

namespace App\Faker\Provider;

use App\Entity\Directory\People;
use Faker\Provider\Base;

class EntityTokenProvider extends Base
{
    /**
     * @return string
     */
    public function entityToken(People $people)
    {
        /*
         * Differs from the actual PrePersist trigger
         * it is only used for fixtures,
         * the jwtManager had a variation of string length that was too big.
         */
        return uniqid($people->getSalt().$people->getId().date('dgmyHisu'), true);
    }
}
