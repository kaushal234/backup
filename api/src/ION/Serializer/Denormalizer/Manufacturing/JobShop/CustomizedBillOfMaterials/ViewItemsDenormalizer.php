<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\Manufacturing\JobShop\CustomizedBillOfMaterials;

use App\ION\Resources\Manufacturing\JobShop\CustomizedBillOfMaterials\ViewItems;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ViewItemsDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'CUSTOMIZED_BILL_OF_MATERIAL_VIEW_ITEMS_DENORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return ViewItems::class === $type && !($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;

        $data['partNumberProject'] = IONXmlDecoder::trim($data['partNumberProject']);
        $data['partNumber'] = IONXmlDecoder::trim($data['partNumber']);
        $data['engineeringSignalCode'] = IONXmlDecoder::trim($data['engineeringSignalCode']);
        $data['itemDescription'] = IONXmlDecoder::trim($data['itemDescription']);
        $data['itemOtherDescription'] = IONXmlDecoder::trim($data['itemOtherDescription']);
        $data['extraInformation'] = IONXmlDecoder::trim($data['extraInformation']);
        $data['customized'] = IONXmlDecoder::enforceYesNoToBoolean($data['customized']);
        $data['operation'] = (int) $data['operation'];
        $data['engineeringRevision'] = IONXmlDecoder::trim($data['engineeringRevision']);
        $data['productQuantity'] = (float) $data['pbomQuantity'];

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }
}
