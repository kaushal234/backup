<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\Manufacturing\JobShop;

use App\ION\Resources\Manufacturing\JobShop\BillOfMaterialItem;
use App\ION\Resources\Manufacturing\JobShop\CustomizedBillOfMaterials;
use App\ION\Resources\Manufacturing\JobShop\ExtendedCustomizedBillOfMaterials;
use App\ION\Resources\Manufacturing\JobShop\ManualCustomizedBillOfMaterials;
use App\ION\Resources\Manufacturing\ProductConfiguration\ProductVariant;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class CustomizedBillOfMaterialsDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'CUSTOMIZED_BILL_OF_MATERIAL_DENORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return (
            CustomizedBillOfMaterials::class === $type
            || ManualCustomizedBillOfMaterials::class === $type
            || BillOfMaterialItem::class === $type
            || ExtendedCustomizedBillOfMaterials::class === $type
        ) && !($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;

        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '300');

        if (empty($data)) {
            return null;
        }

        array_walk_recursive($data, static function (&$value) { $value = mb_trim($value); });
        self::setPMOC($data);
        $effectiveDate = new \DateTime();
        $expiryDate = new \DateTime();

        $data['billOfMaterials']['effectiveDate'] = $effectiveDate->setTimestamp((int) $data['billOfMaterials']['effectiveDate'])->format('c');
        $data['billOfMaterials']['expiryDate'] = $expiryDate->setTimestamp((int) $data['billOfMaterials']['expiryDate'])->format('c');

        $data['estimatedStandardCost'] = (null === IONXmlDecoder::trim($data['estimatedStandardCost'], true)) ? null : (float) $data['estimatedStandardCost'];

        if (\array_key_exists('customized', $data)) {
            $data['customized'] = IONXmlDecoder::enforceYesNoToBoolean($data['customized']);
        }
        $data['backflushIfMaterial'] = IONXmlDecoder::enforceYesNoToBoolean($data['backflushIfMaterial']);
        $data['phantom'] = IONXmlDecoder::enforceYesNoToBoolean($data['phantom']);
        $data['supplyTime'] = '' === IONXmlDecoder::trim($data['supplyTime']) ? null : (int) $data['supplyTime'];
        $data['items'] = IONXmlDecoder::enforceIndexedCollection($data['items']['customizedBillOfMaterialItem'] ?? []);
        $data['engineeringRevisionDrawing'] = IONXmlDecoder::trim($data['engineeringRevisionDrawing'], true, '0');

        if (\in_array(ProductVariant::NORMALIZATION_GROUP, $context[AbstractObjectNormalizer::GROUPS], true)) {
            $data['productVariants'] = IONXmlDecoder::enforceIndexedCollection($data['variants']['variant'] ?? []);
        }

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }

    public static function setPMOC(array &$data): void
    {
        $data['preventive'] = false !== mb_strpos($data['pmoc'] ?? '', 'p');
        $data['maintenance'] = false !== mb_strpos($data['pmoc'] ?? '', 'm');
        $data['overhaul'] = false !== mb_strpos($data['pmoc'] ?? '', 'o');
        $data['critical'] = false !== mb_strpos($data['pmoc'] ?? '', 'c');
    }
}
