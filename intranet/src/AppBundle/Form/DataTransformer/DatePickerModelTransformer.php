<?php

declare(strict_types=1);

namespace AppBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class DatePickerModelTransformer implements DataTransformerInterface
{
    public function transform(mixed $value): mixed
    {
        return $value;
    }

    public function reverseTransform(mixed $value): mixed
    {
        return '' !== $value ? $value : null;
    }
}
