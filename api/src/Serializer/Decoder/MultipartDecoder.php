<?php

declare(strict_types=1);

namespace App\Serializer\Decoder;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\TypeInfo\TypeIdentifier;

final class MultipartDecoder implements DecoderInterface
{
    public const FORMAT = 'multipart';

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly PropertyInfoExtractorInterface $propertyInfoExtractor
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function decode(string $data, string $format, array $context = []): ?array
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request instanceof Request) {
            return null;
        }

        $resourceClass = $request->attributes->get('_api_resource_class');
        $encodedData = [];

        foreach ($request->request->all() as $element => $value) {
            $propertyType = $this->propertyInfoExtractor->getType($resourceClass, $element);

            if (null !== $propertyType && $propertyType->isIdentifiedBy(TypeIdentifier::BOOL)) {
                $encodedData[$element] = !\in_array($value, [false, 'false', '0'], true);

                continue;
            }

            // Multipart form values will be encoded in JSON.
            $decoded = json_decode($value, true);

            $encodedData[$element] = \is_array($decoded) ? $decoded : $value;
        }

        return $encodedData + $request->files->all();
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDecoding(string $format): bool
    {
        return self::FORMAT === $format;
    }
}
