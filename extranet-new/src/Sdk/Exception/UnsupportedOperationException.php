<?php

declare(strict_types=1);

namespace App\Sdk\Exception;

final class UnsupportedOperationException extends \RuntimeException
{
    public static function forFinding(string $resource): self
    {
        return new self(\sprintf('Resource "%s" does not support find operation.', $resource));
    }

    public static function forListing(string $resource): self
    {
        return new self(\sprintf('Resource "%s" does not support list operation.', $resource));
    }

    public static function forDownload(string $resource): self
    {
        return new self(\sprintf('Resource "%s" does not support download operation.', $resource));
    }

    public static function forUpload(string $resource): self
    {
        return new self(\sprintf('Resource "%s" does not support upload operation.', $resource));
    }

    public static function forCreating(string $resource): self
    {
        return new self(\sprintf('Resource "%s" does not support create operation.', $resource));
    }

    public static function forUpdating(string $resource): self
    {
        return new self(\sprintf('Resource "%s" does not support update operation.', $resource));
    }

    public static function forPagination(string $resource): self
    {
        return new self(\sprintf('Resource "%s" does not support pagination operation.', $resource));
    }

    public static function forExport(string $resource): self
    {
        return new self(\sprintf('Resource "%s" does not support export operation.', $resource));
    }
}
