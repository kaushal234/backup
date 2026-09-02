<?php

declare(strict_types=1);

namespace App\Form\DataTransformer;

use App\Sdk\Resource\ResourceInterface;
use Symfony\Component\Form\DataTransformerInterface;

class IrisResourceToIdTransformer implements DataTransformerInterface
{
    /**
     * @param ?ResourceInterface $value
     */
    public function transform(mixed $value): ?string
    {
        return $value?->getIri();
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform(mixed $value): mixed
    {
        return $value;
    }
}
