<?php

declare(strict_types=1);

namespace App\Tests\Link\Entity;

use App\Entity\Directory\Location;
use App\Link\DataTransformer\ArrayToLinkProperty;
use App\Link\DataTransformer\ArrayToLinkResource;
use App\Link\Mapping\Attributes\LinkField;

class LinkFieldAttributeDummy
{
    #[LinkField(fields: ['testPassion'], transformer: ArrayToLinkResource::class, options: ['property' => 'dumb', 'class' => Location::class])]
    protected string $dummyPassion;

    #[LinkField(fields: ['testMoore'], transformer: ArrayToLinkProperty::class, options: ['property' => 'custom'])]
    protected string $dummyMoore;

    public function __construct(string $dummyPassion, string $dummyMoore)
    {
        $this->dummyPassion = $dummyPassion;
        $this->dummyMoore = $dummyMoore;
    }
}
