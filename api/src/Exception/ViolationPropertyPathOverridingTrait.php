<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\Validator\ConstraintViolationListInterface;

trait ViolationPropertyPathOverridingTrait
{
    public static function cleanViolationPath(ConstraintViolationListInterface $errors)
    {
        foreach ($errors as $violation) {
            $refObject = new \ReflectionObject($violation);
            $refProperty = $refObject->getProperty('propertyPath');
            $refProperty->setAccessible(true);
            $refProperty->setValue($violation, mb_trim($refProperty->getValue($violation), '[]'));
        }
    }
}
