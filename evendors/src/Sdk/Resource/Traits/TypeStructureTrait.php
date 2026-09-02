<?php

declare(strict_types=1);

namespace App\Sdk\Resource\Traits;

use Psl\Type;

trait TypeStructureTrait
{
    /**
     * @template T
     *
     * @param Type\TypeInterface<T> $type
     *
     * @return Type\TypeInterface<array{"@type": non-empty-string, "hydra:member": list<T>, "hydra:totalItems": int, "hydra:view": array{"@type": non-empty-string, "hydra:previous"?: string, "hydra:next"?: string}}>
     */
    public static function getPageTypeStructureOf(Type\TypeInterface $type): Type\TypeInterface
    {
        return Type\shape([
            '@type' => Type\literal_scalar('hydra:Collection'),
            'hydra:member' => Type\vec($type),
            'hydra:totalItems' => Type\int(),
            'hydra:view' => Type\shape([
                '@type' => Type\literal_scalar('hydra:PartialCollectionView'),
                'hydra:previous' => Type\optional(Type\string()),
                'hydra:next' => Type\optional(Type\string()),
            ], allow_unknown_fields: true),
        ], allow_unknown_fields: true);
    }

    /**
     * @template T
     *
     * @param Type\TypeInterface<T> $type
     *
     * @return Type\TypeInterface<array{"@type": non-empty-string, "hydra:member": list<T>}>
     */
    public static function getCollectionTypeStructureOf(Type\TypeInterface $type): Type\TypeInterface
    {
        return Type\shape([
            '@type' => Type\literal_scalar('hydra:Collection'),
            'hydra:member' => Type\vec($type),
        ], allow_unknown_fields: true);
    }
}
