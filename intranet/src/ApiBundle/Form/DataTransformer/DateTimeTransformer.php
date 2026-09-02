<?php

declare(strict_types=1);

namespace ApiBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class DateTimeTransformer implements DataTransformerInterface
{
    private $format;
    private ?\DateTimeZone $timezone = null;

    public function __construct($format = \DATE_ATOM, ?\DateTimeZone $timezone = null)
    {
        $this->format = $format;
        $this->timezone = $timezone;
    }

    /**
     * {@inheritdoc}
     */
    public function transform(mixed $value): mixed
    {
        if (empty($value)) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value;
        }

        $result = new \DateTime($value);

        return null === $this->timezone ? $result : $result->setTimezone($this->timezone);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform(mixed $value): mixed
    {
        return $value instanceof \DateTimeInterface ? $value->format($this->format) : null;
    }
}
