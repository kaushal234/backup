<?php

declare(strict_types=1);

namespace App\Exception;

use ApiPlatform\Metadata\Exception\InvalidArgumentException;

final class NotLoggableEntityException extends \LogicException
{
    public function __construct(string $className, InvalidArgumentException $previous)
    {
        parent::__construct(\sprintf('The entity "%s" is not loggable: you may have put the #Loggable attribute on an entity which is not an ApiResource', $className), 0, $previous);
    }
}
