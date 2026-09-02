<?php

declare(strict_types=1);

namespace ApiBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class SalesRepresentativeTransformer implements DataTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($value): mixed
    {
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value): mixed
    {
        if ('' === $value['asm'] && '' === $value['subDivision']) {
            return null;
        }

        return $value;
    }
}
