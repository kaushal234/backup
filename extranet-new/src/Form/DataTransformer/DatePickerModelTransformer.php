<?php

declare(strict_types=1);

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class DatePickerModelTransformer implements DataTransformerInterface
{
    public function transform($value): mixed
    {
        return $value;
    }

    public function reverseTransform($value): mixed
    {
        return '' !== $value ? $value : null;
    }
}
