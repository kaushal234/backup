<?php

declare(strict_types=1);

namespace App\Tests\Link\DataTransformer;

use App\Entity\Directory\Location;
use App\Entity\EmissionRating;
use App\Link\DataTransformer\ArrayToLinkProperty;
use PHPUnit\Framework\TestCase;

class ArrayToLinkPropertyTest extends TestCase
{
    public function testTransformer()
    {
        self::assertSame(['foobar' => 'bar'], (new ArrayToLinkProperty())(['foo' => 'bar'], ['property' => 'foo'], 'foobar'));
    }

    public function testTransformerWithLocationFormatter()
    {
        self::assertSame(['organization' => 'TLD_MTL'], (new ArrayToLinkProperty())(['name' => 'TLD MTL'], ['property' => 'name', 'class' => Location::class], 'organization'));
    }

    public function testTransformerWithEmissionRatingFormatter()
    {
        self::assertSame(['energySource' => 'FUEL'], (new ArrayToLinkProperty())(['name' => 'CN GB'], ['property' => 'name', 'class' => EmissionRating::class], 'energySource'));
    }
}
