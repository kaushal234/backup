<?php

declare(strict_types=1);

namespace App\Sdk\Exception;

use App\Sdk\DataTransformer\ResourceTransformer\ResourceTransformerInterface;
use Psl\Str;
use RuntimeException;

/**
 * This exception is thrown when no {@see ResourceTransformerInterface} is found
 * to transform arbitrary data into a resource instance.
 */
final class NoSupportiveResourceTransformerException extends RuntimeException
{
    /**
     * @param class-string $resource
     */
    public static function forResource(string $resource): self
    {
        return new self(Str\format(
            'No supportive resource transformer is found for "%s" resource.',
            $resource,
        ));
    }
}
