<?php

declare(strict_types=1);

namespace App\Sdk\Utils;

use function Psl\Iter\last;

final class IriToId
{
    public static function iriToId(string $iri): int
    {
        return (int) last(explode('/', $iri));
    }
}
