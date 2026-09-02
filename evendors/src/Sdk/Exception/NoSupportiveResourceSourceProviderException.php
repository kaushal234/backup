<?php

declare(strict_types=1);

namespace App\Sdk\Exception;

use App\Sdk\Http\ResourceSourceProvider\ResourceSourceProviderInterface;
use Psl\Str;
use RuntimeException;

/**
 * This exception is thrown when no {@see ResourceSourceProviderInterface} is found
 * to create the request for a given resource.
 */
final class NoSupportiveResourceSourceProviderException extends RuntimeException
{
    /**
     * @param class-string $resource
     */
    public static function forResource(string $resource): self
    {
        return new self(Str\format(
            'No supportive resource request factory is found for "%s" resource.',
            $resource,
        ));
    }
}
