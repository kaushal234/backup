<?php

declare(strict_types=1);

namespace App\Tests\Link\Mapper;

use App\Entity\Directory\Location;
use App\Link\DataTransformer\ArrayToLinkProperty;
use App\Link\DataTransformer\ArrayToLinkResource;
use App\Link\Mapping\Mapper\FieldMapper;
use App\Tests\Link\Entity\LinkFieldAttributeDummy;
use PHPUnit\Framework\TestCase;

class FieldMapperTest extends TestCase
{
    public function testMappingIsCorrect()
    {
        $copyAnnotationDummy = new LinkFieldAttributeDummy('dummyPassion', 'dummyMoore');
        $class = new \ReflectionClass($copyAnnotationDummy);
        $mapper = new FieldMapper();

        $mapping = $mapper->getMapping($class);

        self::assertSame([
            'dummyPassion' => [
                'fields' => ['testPassion'],
                'transformer' => [ArrayToLinkResource::class],
                'options' => ['property' => 'dumb', 'class' => Location::class],
            ],
            'dummyMoore' => [
                'fields' => ['testMoore'],
                'transformer' => [ArrayToLinkProperty::class],
                'options' => ['property' => 'custom'],
            ],
        ], $mapping);
    }
}
