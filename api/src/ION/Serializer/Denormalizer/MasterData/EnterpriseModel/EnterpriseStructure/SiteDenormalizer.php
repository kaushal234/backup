<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\MasterData\EnterpriseModel\EnterpriseStructure;

use App\ION\Resources\MasterData\EnterpriseModel\EnterpriseStructure\Site;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class SiteDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'SITE_DENORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return Site::class === $type && !($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;

        $data['siteID'] = IONXmlDecoder::trim($data['siteID'] ?? $data['SiteID']);
        $data['siteDescription'] = IONXmlDecoder::trim($data['siteDescription'] ?? $data['SiteDescription']);

        foreach (['siteAddressCode' => 'SiteAddressCode', 'siteAddressName' => 'SiteAddressName'] as $key => $value) {
            if (isset($data[$key]) || isset($data[$value])) {
                $data[$key] = IONXmlDecoder::trim($data[$key] ?? $data[$value]);
            }
        }

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }
}
