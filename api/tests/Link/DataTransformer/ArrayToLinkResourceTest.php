<?php

declare(strict_types=1);

namespace App\Tests\Link\DataTransformer;

use App\Entity\Directory\Location;
use App\Entity\EmissionRating;
use App\Link\DataTransformer\ArrayToLinkResource;
use PHPUnit\Framework\TestCase;

class ArrayToLinkResourceTest extends TestCase
{
    public function testTransformer()
    {
        self::assertSame(['foobar' => ['foo' => 'bar']], (new ArrayToLinkResource())(['foo' => 'bar'], ['property' => 'foo'], 'foobar'));
    }

    public function testTransformerWithLocationFormatter()
    {
        self::assertSame(['organization' => ['name' => 'TLD_MTL']], (new ArrayToLinkResource())(['name' => 'TLD MTL'], ['property' => 'name', 'class' => Location::class], 'organization'));
    }

    public function testTransformerWithEmissionRatingFormatter()
    {
        self::assertSame(['energySource' => ['name' => 'FUEL']], (new ArrayToLinkResource())(['name' => 'CN GB'], ['property' => 'name', 'class' => EmissionRating::class], 'energySource'));
    }
}
