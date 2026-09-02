<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\Manufacturing\Project;

use App\ION\Resources\Manufacturing\Project\Project;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ProjectDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'PROJECT_DENORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return Project::class === $type && !($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;

        IONXmlDecoder::renameKey($data, 'project', 'projectIdentifier');
        $data['projectIdentifier'] = IONXmlDecoder::trim($data['projectIdentifier']);
        IONXmlDecoder::renameKey($data, 'projectStatus', 'status');

        if (isset($data['productionOrders']) && '' === $data['productionOrders']) {
            $data['productionOrders'] = [];
        }

        $data['productionOrders'] = IONXmlDecoder::enforceIndexedCollection($data['productionOrders']['productionOrder'] ?? []);

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }
}
