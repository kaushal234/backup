<?php

declare(strict_types=1);

namespace App\Tests\ION\Resources;

use App\ION\Resources\Manufacturing\JobShop\PartNumberTrait;
use PHPUnit\Framework\TestCase;

class PartNumberTraitTest extends TestCase
{
    public function testIsExpired()
    {
        $partNumberTrait = $this->getObjectForTrait(PartNumberTrait::class);

        $partNumberTrait->engineeringRevisionExpiryDate = (new \DateTime('now -1 day'))->format('Y-m-d');
        $this->assertTrue($partNumberTrait->isExpired());
    }

    /**
     * @dataProvider provideNonExpiredDate
     */
    public function testIsNotExpired($expiryDate)
    {
        $partNumberTrait = $this->getObjectForTrait(PartNumberTrait::class);

        $partNumberTrait->engineeringRevisionExpiryDate = $expiryDate;
        $this->assertFalse($partNumberTrait->isExpired());
    }

    public function provideNonExpiredDate()
    {
        return [[''], ['1979-12-31'], [(new \DateTime('now +1 day'))->format('Y-m-d')]];
    }
}
