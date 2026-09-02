<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer;

use App\Entity\Report\ReportSnapshot;
use App\Report\Report;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ReportSnapshotJsonLdNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function getSupportedTypes(?string $format): array
    {
        return [
            ReportSnapshot::class => true,
        ];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof ReportSnapshot && 'jsonld' === $format;
    }

    /**
     * @param ReportSnapshot $object
     *
     * @throws ExceptionInterface
     */
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        /** @var array $normalizedData */
        $normalizedData = $this->normalizer->normalize($object, JsonEncoder::FORMAT);

        $normalizedReport = $this->normalizer->normalize(new Report($object->resource, $object->x, $object->y), 'jsonld', $context);

        $atKeys = [];
        foreach ($normalizedReport as $key => $value) {
            if (0 === mb_strpos((string) $key, '@')) {
                $atKeys[$key] = $value;
            }
        }

        return $atKeys + $normalizedData;
    }
}
