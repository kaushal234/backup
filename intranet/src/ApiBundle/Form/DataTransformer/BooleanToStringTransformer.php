<?php

declare(strict_types=1);

namespace ApiBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class BooleanToStringTransformer implements DataTransformerInterface
{
    /** @var string */
    private $trueValue;

    /** @var string */
    private $falseValue;

    public function __construct($trueValue, $falseValue)
    {
        $this->trueValue = $trueValue;
        $this->falseValue = $falseValue;
    }

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
        if (!\is_bool($value)) {
            throw new TransformationFailedException('Expected a Boolean.');
        }

        return $value ? $this->trueValue : $this->falseValue;
    }
}
