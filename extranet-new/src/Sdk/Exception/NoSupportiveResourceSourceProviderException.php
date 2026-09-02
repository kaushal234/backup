<?php

declare(strict_types=1);

namespace App\Sdk\Exception;

use App\Sdk\DataTransformer\ResourceTransformer\ResourceTransformerInterface;

/**
 * This exception is thrown when no {@see ResourceTransformerInterface} is found
 * to transform arbitrary data into a resource instance.
 */
final class NoSupportiveResourceSourceProviderException extends \RuntimeException
{
    /**
     * @param class-string $resource
     */
    public static function forResource(string $resource): self
    {
        return new self(\sprintf(
            'No supportive resource transformer is found for "%s" resource.',
            $resource,
        ));
    }
}
