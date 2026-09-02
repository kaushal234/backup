<?php

declare(strict_types=1);

namespace App\Sdk\Resource\Traits;

use Psl\Type;

trait CompleteTypeStructureTrait
{
    use TypeStructureTrait;

    public static function getPageTypeStructure(): Type\TypeInterface
    {
        return self::getPageTypeStructureOf(self::getTypeStructure());
    }

    public static function getCollectionTypeStructure(): Type\TypeInterface
    {
        return self::getCollectionTypeStructureOf(self::getTypeStructure());
    }

    abstract public static function getTypeStructure(): Type\TypeInterface;
}
