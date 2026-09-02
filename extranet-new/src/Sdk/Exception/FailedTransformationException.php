<?php

declare(strict_types=1);

namespace App\Sdk\Exception;

use App\Sdk\DataTransformer\ResourceTransformer\ResourceTransformerInterface;

/**
 * This exception is thrown when a {@see ResourceTransformerInterface} fails
 * to transform the given data into a resource instance, or a collection.
 */
final class FailedTransformationException extends \RuntimeException
{
}
