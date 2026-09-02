<?php

declare(strict_types=1);

namespace ApiBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class IrisResourceToIdTransformer implements DataTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform(mixed $value): mixed
    {
        if (empty($value)) {
            return null;
        }

        if (!\is_array($value)) {
            return $value;
        }

        if (!isset($value['@id'])) {
            $value = array_map(static fn ($object) => $object['@id'], $value);
        } else {
            $value = $value['@id'];
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value): mixed
    {
        return $value;
    }
}
