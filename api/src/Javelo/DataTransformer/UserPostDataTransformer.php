<?php

declare(strict_types=1);

namespace App\Javelo\DataTransformer;

use App\Javelo\Resources\User;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class UserPostDataTransformer implements DataTransformerInterface
{
    public function __construct(private readonly NormalizerInterface $normalizer)
    {
    }

    public function transform($value): ?array
    {
        if (!$value instanceof User) {
            return null;
        }

        $data = $this->normalizer->normalize($value, 'json');
        // Required SCIM schema https://api.javelo.io/public/doc/api/scim#tag/Users/paths/~1scim~1v2~1Users/post
        $data['schemas'] = ['urn:ietf:params:scim:schemas:core:2.0:User'];

        return $data;
    }

    public function reverseTransform($value): mixed
    {
        throw new \Exception('Not implemented');
    }
}
