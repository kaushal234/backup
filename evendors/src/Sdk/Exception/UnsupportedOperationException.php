<?php

declare(strict_types=1);

namespace App\Sdk\Exception;

use Psl\Str;
use RuntimeException;

final class UnsupportedOperationException extends RuntimeException
{
    public static function forDownload(string $resource): self
    {
        return new self(Str\format('Resource "%s" does not support download operation.', $resource));
    }

    public static function forFinding(string $resource): self
    {
        return new self(Str\format('Resource "%s" does not support find operation.', $resource));
    }

    public static function forUpdating(string $resource): self
    {
        return new self(Str\format('Resource "%s" does not support update operation.', $resource));
    }

    public static function forListing(string $resource): self
    {
        return new self(Str\format('Resource "%s" does not support list operation.', $resource));
    }

    public static function forPagination(string $resource): self
    {
        return new self(Str\format('Resource "%s" does not support pagination operation.', $resource));
    }
}
