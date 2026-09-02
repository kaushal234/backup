<?php

declare(strict_types=1);

namespace App\Sdk\Resource\Traits;

use Psl\Type;

/**
 * @template T
 * @template TSimplified
 */
trait SimplifiedTypeStructureTrait
{
    use TypeStructureTrait;

    /**
     * @return Type\TypeInterface<array{"@type": non-empty-string, "hydra:member": list<TSimplified>, "hydra:totalItems": int, "hydra:view": array{"@type": non-empty-string, "hydra:previous"?: string, "hydra:next"?: string}}>
     */
    public static function getPageTypeStructure(): Type\TypeInterface
    {
        return self::getPageTypeStructureOf(self::getSimplifiedTypeStructure());
    }

    /**
     * @return Type\TypeInterface<array{"@type": non-empty-string, "hydra:member": list<TSimplified>}>
     */
    public static function getCollectionTypeStructure(): Type\TypeInterface
    {
        return self::getCollectionTypeStructureOf(self::getSimplifiedTypeStructure());
    }

    /**
     * @return Type\TypeInterface<T>
     */
    abstract public static function getTypeStructure(): Type\TypeInterface;

    /**
     * @return Type\TypeInterface<TSimplified>
     */
    abstract protected static function getSimplifiedTypeStructure(): Type\TypeInterface;
}
