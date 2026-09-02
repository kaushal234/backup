<?php

declare(strict_types=1);

namespace App\Sdk\Resource\Traits;

use Psl\Type;

/**
 * @template T
 */
trait CompleteTypeStructureTrait
{
    use TypeStructureTrait;

    /**
     * @return Type\TypeInterface<array{"@type": non-empty-string, "hydra:member": list<T>, "hydra:totalItems": int, "hydra:view": array{"@type": non-empty-string, "hydra:previous"?: string, "hydra:next"?: string}}>
     */
    public static function getPageTypeStructure(): Type\TypeInterface
    {
        return self::getPageTypeStructureOf(self::getTypeStructure());
    }

    /**
     * @return Type\TypeInterface<array{"@type": non-empty-string, "hydra:member": list<T>}>
     */
    public static function getCollectionTypeStructure(): Type\TypeInterface
    {
        return self::getCollectionTypeStructureOf(self::getTypeStructure());
    }

    /**
     * @return Type\TypeInterface<T>
     */
    abstract public static function getTypeStructure(): Type\TypeInterface;
}
