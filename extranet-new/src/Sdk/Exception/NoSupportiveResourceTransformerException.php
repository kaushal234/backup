<?php

declare(strict_types=1);

namespace App\Sdk\Exception;

use App\Sdk\Http\ResourceSourceProvider\ResourceSourceProviderInterface;

/**
 * This exception is thrown when no {@see ResourceSourceProviderInterface} is found
 * to get sources for a given class.
 */
final class NoSupportiveResourceTransformerException extends \RuntimeException
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
