<?php

declare(strict_types=1);

namespace App\Doctrine\Mapping\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
class Transferable
{
    public function __construct(
        public readonly array $conditions = [],
        public readonly string $handler = 'handler.generic',
        public readonly string $manager = 'manager.generic',
    ) {
    }
}
