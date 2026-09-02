<?php

declare(strict_types=1);

namespace App\Sdk\Resource\Traits;

use Psl\Type;

trait TypeStructureTrait
{
    public static function getCollectionTypeStructureOf(Type\TypeInterface $type): Type\TypeInterface
    {
        return Type\shape([
            '@type' => Type\literal_scalar('hydra:Collection'),
            'hydra:member' => Type\vec($type),
        ], allowUnknownFields: true);
    }

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
            ], allowUnknownFields: true),
        ], allowUnknownFields: true);
    }
}
