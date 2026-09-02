<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\Validator\ConstraintViolationListInterface;

abstract class AbstractValidationException extends \Exception
{
    use ViolationPropertyPathOverridingTrait;

    abstract public function getViolations(): ConstraintViolationListInterface;
}
